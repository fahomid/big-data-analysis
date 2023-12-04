<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.118.2">
    <title>Study Data Analysis Tool</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/sign-in/">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        html,
        body {
            height: 100%;
        }

        .form-signin {
            max-width: 500px;
            padding: 1rem;
        }

        .form-signin .form-floating:focus-within {
            z-index: 2;
        }

        .form-signin input[type="email"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }

        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .b-example-divider {
            width: 100%;
            height: 3rem;
            background-color: rgba(0, 0, 0, .1);
            border: solid rgba(0, 0, 0, .15);
            border-width: 1px 0;
            box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
        }

        .b-example-vr {
            flex-shrink: 0;
            width: 1.5rem;
            height: 100vh;
        }

        .bi {
            vertical-align: -.125em;
            fill: currentColor;
        }

        .nav-scroller {
            position: relative;
            z-index: 2;
            height: 2.75rem;
            overflow-y: hidden;
        }

        .nav-scroller .nav {
            display: flex;
            flex-wrap: nowrap;
            padding-bottom: 1rem;
            margin-top: -1px;
            overflow-x: auto;
            text-align: center;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .btn-bd-primary {
            --bd-violet-bg: #712cf9;
            --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

            --bs-btn-font-weight: 600;
            --bs-btn-color: var(--bs-white);
            --bs-btn-bg: var(--bd-violet-bg);
            --bs-btn-border-color: var(--bd-violet-bg);
            --bs-btn-hover-color: var(--bs-white);
            --bs-btn-hover-bg: #6528e0;
            --bs-btn-hover-border-color: #6528e0;
            --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
            --bs-btn-active-color: var(--bs-btn-hover-color);
            --bs-btn-active-bg: #5a23c8;
            --bs-btn-active-border-color: #5a23c8;
        }

        .bd-mode-toggle {
            z-index: 1500;
        }

        .bd-mode-toggle .dropdown-menu .active .bi {
            display: block !important;
        }
    </style>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>
<body class="d-flex align-items-center py-4 bg-body-tertiary">
<div class="position-relative w-100 h-100" id="bg_wait" style="display: none;">
    <div class="position-absolute top-50 start-50 translate-middle text-center">
        <p>Please wait...</p>
        <div class="spinner-grow text-success" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
<main id="form-container" class="form-signin w-100 m-auto">
    <!-- keyword form -->
    <form id="keyword-form">
        <p class="text-center"><i class="bi bi-activity"></i></p>
        <h1 class="h3 mb-3 fw-normal fw-bold text-center mb-4">Study Data Analysis</h1>
        <div id="form-error-container" class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
            <strong>Error!</strong> <span id="error-message"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="form-floating">
            <input type="text" class="form-control" id="keyword" placeholder="heart attack">
            <label for="keyword">Enter Keyword</label>
        </div>
        <div class="row g-2 mt-3">
            <div class="col">
                <div class="form-floating">
                    <input type="number" class="form-control" id="min" placeholder="1" value="1" min="1">
                    <label for="min">Minimum Rank</label>
                </div>
            </div>
            <div class="col">
                <div class="form-floating">
                    <input type="number" class="form-control" id="max" placeholder="1000" value="1000" max="1000">
                    <label for="max">Maximum Rank</label>
                </div>
            </div>
        </div>
        <div class="row g-2 mt-3">
            <p class="text-bg-secondary p-3 text-center rounded">Minimum rank and Maximum rank defines how many rows we are fetching from API server only.</span></p>
        </div>
        <button class="btn btn-primary w-100 py-2 mt-3" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Start Analyzing</button>
    </form>
</main>

<!-- Analysis data view -->
<div id="data-analysis-container" class="w-100 h-100 container" style="display: none;">
    <div class="row">
        <h3 class="fw-bold">Big Data Analysis</h3>
        <hr>
    </div>
    <div class="row">
        <div class="col-4 text-success">Your Keyword: <b id="analytic-keyword"></b></div>
        <div class="col-4 text-center">Total Number of Studies: <b id="analytic-number-of-studies"></b></div>
        <div class="col-4 text-end text-success">Total Conditions Found: <b id="analytic-total-conditions"></b></div>
        <hr class="mt-3">
    </div>
    <div class="row">
        <div class="col text-center g-0">
            <div id="chartContainer" style="height: 600px; width: 1000px; display: inline-block;"></div>
            <p class="text-info fw-bold">The data presented here indicate the number of research studies that have been conducted or are currently underway related to the provided keyword, along with all medical conditions associated with that particular keyword.</p>
        </div>
    </div>
    <div class="row">
        <hr>
        <div class="col g-0">
            <h4>List of all the conditions and number of studies</h4>
            <hr>
        </div>
    </div>
    <div class="row">
        <div class="col-12 g-0 pb-5">
            <table class="table table-striped">
                <thead class="table-dark">
                    <th scope="col">Rank</th>
                    <th scope="col">Condition Name</th>
                    <th scope="col">Count</th>
                </thead>
                <tbody id="analytic-condition-table"></tbody>
            </table>
        </div>
        <div class="col-12 text-center pb-5">
            <button type="button" class="btn btn-primary btn-lg" onclick="reload();">Go Back</button>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Please confirm</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Do you want to update database before analyzing?</p>
                <p class="text-primary fw-bold">Note: If you choose 'No,' the data that already exists in the database will be used; otherwise, data will be fetched through the https://clinicaltrials.gov/ API.</p>
            </div>
            <div class="modal-footer">
                <button id="old" data-type="old" type="button" class="btn btn-warning ph-5" data-bs-dismiss="modal">No</button>
                <button id="new" data-type="new" type="button" class="btn btn-success ph-5" data-bs-dismiss="modal">Yes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>

    var chart; // Global variable to store the chart instance

    // Function to draw the initial chart
    function drawChart() {
        // Your formatted analytics array
        let analyticsArray = [];

        // Create a DataTable from the array
        let data = new google.visualization.DataTable();
        data.addColumn('string', 'Condition');
        data.addColumn('number', 'Count');

        // loop throuh and add to row
        for (var i = 0; i < analyticsArray.length; i++) {
            data.addRow([analyticsArray[i].Condition, analyticsArray[i].Count]);
        }

        // Create a new instance of the PieChart class
        chart = new google.visualization.PieChart(document.getElementById('chartContainer'));

        // Draw the initial chart with the data and options
        chart.draw(data, {});
    }

    // Function to update the chart with new data
    function updateChart(data) {

        // Create empty array for pie chart data
        const pieChartData = [];

        // count total studies
        let total_studies = 0;

        // Process analytics data
        for(let i = 0; i < data.length; i++) {
            let condition = data[i].Condition;
            let count = data[i].Count;
            pieChartData.push([condition, count]);

            // increase study count
            total_studies += count;

            // add the conditions and count into table
            $("#analytic-condition-table").append('<tr><td>'+ (i+1) +'</td><td>'+ condition +'</td><td>'+ count +'</td></tr>');
        }

        // set total number of studies
        $("#analytic-number-of-studies").text(total_studies);

        // sort data
        pieChartData.sort((a, b) => b[1] - a[1]);

        // get top 10 conditions
        let topTenConditions = pieChartData.slice(0, 10);

        // calculate remaining conditions
        let remainingConditionsCount = 0;
        for (let i = 10; i < pieChartData.length; i++) {
            remainingConditionsCount += pieChartData[i][1];
        }

        // get others category
        let othersCategory = ['Others', remainingConditionsCount];

        // get filtered pie chart data
        let filteredPieChartData = topTenConditions.concat([othersCategory]);

        // Example: Update the data dynamically
        let newData = filteredPieChartData;

        // Create a new DataTable with the updated data
        let newDataTable = new google.visualization.DataTable();
        newDataTable.addColumn('string', 'Condition');
        newDataTable.addColumn('number', 'Count');

        for (var i = 0; i < newData.length; i++) {
            newDataTable.addRow(newData[i]);
        }

        // Set options for the chart
        let options = {
            title: 'Condition Analysis',
            chartArea:{width:'80%',height:'80%'},
            pieHole: 0.3,
            legend: {
                position: 'labeled',
                alignment: 'center',
                textStyle: {
                    italic: true
                }
            }
        };

        // Update the chart with the new data
        chart.draw(newDataTable, options);
    }

    // Load Google Charts library and draw the initial chart
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);
</script>
<script>

    /* ajax request handler */
    function handleAjax(url, method, callback) {

        // response holder
        let response = null;
        $.ajax({
            type: method,
            url: url,
            data: {
                keyword: $("#keyword").val(),
                min: $(min).val(),
                max: $(max).val()
            },
            success: function(r) {
                response = r;
            },
            error: function(r) {
                response = r;
            },
            complete: function(e, status) {
                callback(e, status, response);
            }
        });
    }

    /* Handle on click event for  no*/
    $("#new, #old").on("click", function() {

        // hide error container
        $("#form-error-container").hide();

        // setting up url
        let url = "http://localhost:5000/analyze/"+ $(this).attr("data-type");

        // show ajax processing
        $("#bg_wait").show();

        // hide form
        $("#form-container").hide();

        /* post data to python server */
        handleAjax(url, "POST", function (e, status, response) {

            // check response
            if(response !== null && response.total_conditions && response.analytics) {

                // show big data analysis container
                $("#data-analysis-container").show();

                // update analytic keyword
                $("#analytic-keyword").text($("#keyword").val());

                // update analytic total conditions
                $("#analytic-total-conditions").text(response.total_conditions);

                // draw data on chart
                updateChart(response.analytics);
            } else {

                // add error message
                $("#error-message").text(response.error ? response.error : "Something went wrong! Please refresh and try again.");

                // show the form again
                $("#form-container").show();

                // show error container
                $("#form-error-container").slideDown("slow");
            }

            // hide waiting
            $("#bg_wait").hide();
        });
    });

    // reload page
    function reload() {
        location.reload();
    }

</script>
</body>
</html>
