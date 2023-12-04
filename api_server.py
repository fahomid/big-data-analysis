import json
import requests
from flask import Flask, request, jsonify, Response
from pyspark.sql import SparkSession
from pyspark.sql.types import StructType, StructField, IntegerType, StringType, ArrayType
import pandas as pd
from flask_cors import CORS

app = Flask(__name__)

# enabling CORS for all as we are testing it for internal server
CORS(app)

@app.route('/')
def hello():
    return 'Data analysis API server!'


@app.route('/analyze/old', methods=['POST'])
def analyze_old():
    # make sure keyword provided
    if 'keyword' in request.form and 3 <= len(request.form['keyword']) <= 20:

        # get keyword from request
        keyword = (request.form['keyword']).replace(" ", "_").lower()

        # create spark session
        spark = create_spark_session()

        # check if table already exist in database
        table_exists = spark.sql("SHOW TABLES").filter(f"tableName = '{keyword}'").count() > 0

        # if table exist start analyzing otherwise throw error
        if table_exists:

            # get response data
            response_data = get_data_and_analyze(table_exists, spark, keyword)

            # stop spark session
            spark.stop()

            # return response
            return Response(json.dumps(response_data, indent=2, sort_keys=False), content_type='application/json')

        else:
            return jsonify({'error': f'No previous data found! Please use new data option instead!'})
    else:
        return jsonify(
            {'error': 'The keyword is missing or the provided keyword not between 3 and 20 characters!'}), 200


@app.route('/analyze/new', methods=['POST'])
def analyze_new():

    # make sure keyword is provided
    if 'keyword' in request.form and 3 <= len(request.form['keyword']) <= 20 and 'min' in request.form and 'max' in request.form:

        # get keyword from request, make it lowercase and replace space with underscore
        keyword = (request.form['keyword']).replace(" ", "_").lower()

        # get min index from request
        min_index = request.form['min']

        # get max index from request
        max_index = request.form['max']

        # make sure min and max index are digits only and min always less than max
        if not (min_index.isdigit() and max_index.isdigit() and int(min_index) < int(max_index)):
            return jsonify({'error': 'Invalid minimum and maximum index provided!'}), 200

        # make sure min is grater than 0
        if not (int(min_index) > 0):
            return jsonify({'error': 'Min value must be greater than 0!'}), 200

        # calling api and storing into data
        data = get_data_from_api(keyword, min_index, max_index)

        # create spark session
        spark = create_spark_session()

        # check if table already exist in database
        table_exists = spark.sql("SHOW TABLES").filter(f"tableName = '{keyword}'").count() > 0

        # we need to start processing data only if API returned with anything
        if len(data) > 0 and "StudyFieldsResponse" in data and "StudyFields" in data.get("StudyFieldsResponse"):

            # fetch study fields
            study_fields = data.get("StudyFieldsResponse").get("StudyFields")

            # create table by keyword if not exists
            spark.sql(f"CREATE TABLE IF NOT EXISTS {keyword} (Rank INT, NCTId ARRAY<STRING>, BriefTitle ARRAY<STRING>, Condition ARRAY<STRING>)")

            # as of now we have the table to change the boolean value of table exists
            table_exists = True

            # setting target key
            target_key = "Rank"

            # extracting all Rank in an array
            ranks = [entry[target_key] for entry in study_fields if target_key in entry]

            # Create a string representation of the new ranks for the SQL query
            formatted_ranks = ", ".join(map(str, ranks))

            # check if data already in database using rank field
            result = spark.sql(f"SELECT Rank FROM {keyword} WHERE Rank IN ({formatted_ranks})").toPandas()

            # get all the ranks
            all_ranks = result['Rank'].tolist()

            # filtering the data to insert only the unique rows
            study_fields = [item for item in study_fields if item['Rank'] not in all_ranks]

            # checking if we have at least one data for insertion
            if len(study_fields) > 0:
                # Defining the the schema
                schema = StructType([
                    StructField("Rank", IntegerType(), False),
                    StructField("NCTId", ArrayType(StringType()), False),
                    StructField("BriefTitle", ArrayType(StringType()), False),
                    StructField("Condition", ArrayType(StringType()), False)
                ])

                # converting list data into spark data frame
                df = spark.createDataFrame(study_fields, schema)

                # registering the DataFrame as a temporary table
                df.createOrReplaceTempView("temp_table")

                # preparing insert query
                insert_query = f"INSERT INTO {keyword} SELECT * FROM temp_table"

                # executing the insert query
                spark.sql(insert_query)

                # analyze data and get response ***sync***
                response_data = get_data_and_analyze(table_exists, spark, keyword)

            else:
                # analyze data and get response
                response_data = get_data_and_analyze(table_exists, spark, keyword)

            # return response
            return Response(json.dumps(response_data, indent=2, sort_keys=False), content_type='application/json')

        else:

            # stop spark session
            spark.stop()

            return jsonify({'error': 'API did not return any new data! Please try different keyword!'}), 200


    else:
        return jsonify({'error': 'The keyword, min or max field(s) missing or the provided keyword not between 3 and 20 characters!'}), 200

# route to dumb database
@app.route('/spark/dump_data_by_keyword', methods=['GET'])
def dump_data_by_keyword():

    if all(param in request.args for param in ['keyword', 'min', 'max'])and request.args['min'].isdigit() and request.args['max'].isdigit() and int(request.args['min']) < int(request.args['max']):

        # get query parameters
        keyword = request.args.get('keyword')
        min = int(request.args.get('min'))
        max = int(request.args.get('max'))

        # create spark session
        spark = create_spark_session()

        # check if table already exist in database
        table_exists = spark.sql("SHOW TABLES").filter(f"tableName = '{keyword}'").count() > 0

        # if table does not exist then return error
        if not table_exists:
            return jsonify({'error': 'No data found related to the keyword you provided!'})

        # get data from database
        result = spark.sql(f"SELECT * FROM {keyword} WHERE RANK BETWEEN {min} and {max}").toPandas()

        # make result to dictionary format
        result = result.to_dict()

        # Prepare the response dictionary
        response_data = {
            'success': f'Data fetched successfully from rank {min} to {max}!',
            'data': result
        }

        # return response data
        return response_data



    else:
        # return error
        return jsonify({'error': 'Please provide min and max value, make sure both are numeric and min less than max value!'}), 200


# route to reset all database tables
@app.route('/spark/reset', methods=['GET'])
def resetAllTables():

    # create spark session
    spark = create_spark_session()

    # Get the list of tables
    tables = spark.sql("SHOW TABLES").collect()

    # Drop each table
    for table_row in tables:
        table_name = table_row["tableName"]
        spark.sql(f"DROP TABLE IF EXISTS {table_name}")

    # Stop the Spark session
    spark.stop()

    # return response
    return jsonify({'success': 'All database table dropped successfully!'}), 200



# create spark session
def create_spark_session():

    # setting up hive warehouse location
    warehouse_location = "/Projects/Python/BigData/spark-warehouse"

    # spark master
    spark_master = "spark://10.0.0.52:7077"

    # creating spark session with hive support
    spark = (SparkSession.builder.appName("big_data_processing")
             .config("spark.sql.warehouse.dir", warehouse_location)
             .config("spark.master", spark_master)
             .config("spark.driver.memory", "4g")
             .config("spark.executor.memory", "4g")
             .config("spark.sql.createTempTableUsing", "org.apache.spark.sql.hive.execution.HiveTemporaryTable")
             .enableHiveSupport()
             .getOrCreate())

    # return spark instance
    return spark


# fetch data from api url
def get_data_from_api(keyword, min_index, max_index):

    # setting url as currently we are using only the study fields api
    url = f'https://classic.clinicaltrials.gov/api/query/study_fields?expr={keyword}&fields=NCTId%2CBriefTitle%2CCondition&min_rnk={min_index}&max_rnk={max_index}&fmt=json';
    response = requests.get(url)

    if response.status_code == 200:
        return response.json()
    else:
        raise Exception(f"Failed to fetch data from API. Status code: {response.status_code}")


# get data from database and analyze
def get_data_and_analyze(table_exists, spark, keyword):

    if table_exists:

        # query Spark SQL table to get all the data
        result = spark.sql(f"SELECT * FROM {keyword}").toPandas()

        # Flatten the 'Condition' column
        flattened_conditions = [condition for sublist in result['Condition'] for condition in sublist]

        # Count occurrences of each condition
        condition_counts = pd.Series(flattened_conditions).value_counts().to_dict()

        # Convert the result to a JSON object
        result_json = [{"Condition": condition, "Count": count} for condition, count in condition_counts.items()]

        # Count total conditions
        total_conditions = len(result_json)

        # Prepare the response dictionary
        response_data = {
            'success': 'Data analysis completed successfully!',
            'total_conditions': total_conditions,
            'analytics': result_json
        }

        # return response data
        return response_data
    else:
        response_data = {
            'error': 'Data analysis failed as no appropriate database table found!'
        }

        # return error
        return response_data


if __name__ == '__main__':
    app.run(debug=True)
