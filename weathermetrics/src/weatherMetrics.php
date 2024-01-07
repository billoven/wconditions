<?php
    // Common Header for all the weatherMetrics files
    include "weatherMetricsHeader.php";

    ini_set('display_errors', 1);
    error_reporting(E_ALL);
?>

    <?php include 'alertBox.php'; ?>
     
    <!-- Section 1 and First Form -->
    <div class="container" id="Section1">
      <h2>Select Date Range</h2>
      <form id="form1" class="form-group" method="POST" action="weatherMetricsForm1.php">
        <!-- Add a hidden input field to store the selectedDb value -->
        <input type="hidden" name="selectedDb" value="<?php echo isset($_GET['selectedDb']) ? htmlspecialchars($_GET['selectedDb']) : 'db1'; ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" class="input-small" requiredrequired pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">
            </div>
            <div class="form-group">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" class="input-small" requiredrequired pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
            </div>
            <div class="form-group">
                <input type="submit" value="Generate Graph">
            </div>
        </div>
      </form>
      <div class="graph-container">
        <h2>Daily Temperatures Graph</h2>
        <div id="DailyTempGraphContainer">
           <canvas id="DailyTempChart" width="1024" height="400"></canvas>  
        </div>
      </div>
      <div class="graph-container">
        <h2>Monthly Temperatures Graph</h2>
        <div id="MonthlyTempGraphContainer">
            <canvas id="MonthlyTempChart" width="1024" height="400"></canvas> 
        </div>
      </div>

      <div class="graph-container">
        <h2>Daily rain fall and cumulative of the period</h2>
        <div id="precipitationGraphContainer">
            <canvas id="precipitationChart" width="1024" height="400"></canvas>
    	</div>
      </div>
    </div>  
    <div class="container" id="Section2">
      <h2>Compare Two Periods</h2>           
      <form id="form2" method="POST" action="weatherMetricsForm2.php">
                <label for="start_date_1">Start Date (Period 1):</label>
        
                <!-- Add a hidden input field to store the selectedDb value -->
                <input type="hidden" name="selectedDb" value="<?php echo isset($_GET['selectedDb']) ? htmlspecialchars($_GET['selectedDb']) : 'db1'; ?>">

                <input type="date" name="start_date_1" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['start_date_1']) ? $_POST['start_date_1'] : ''; ?>">
                
                <label for="end_date_1">End Date (Period 1):</label>
                <input type="date" name="end_date_1" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['end_date_1']) ? $_POST['end_date_1'] : ''; ?>">
                
                <label for="start_date_2">Start Date (Period 2):</label>
                <input type="date" name="start_date_2" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['start_date_2']) ? $_POST['start_date_2'] : ''; ?>">
                
                <label for="end_date_2">End Date (Period 2):</label>
                <input type="date" name="end_date_2" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['end_date_2']) ? $_POST['end_date_2'] : ''; ?>">
                
                <label for="weather_data_type">Select Weather Data Type:</label>
                <select id="weather_data_type" name="weather_data_type" required>
                    <option value="WC_TempAvg">Average Temperature</option>
                    <option value="WC_TempHigh">Maximum Temperature</option>
                    <option value="WC_TempLow">Minimum Temperature</option>
                    <option value="WC_PrecipitationSum">Precipitation Sum</option>
                </select>
                <input type="submit" value="Generate Comparison Graph">
        </form>
        <div class="graph-container">
            <h2>Comparison Graph</h2>
            <div id="ComparisonGraphContainer">
                <canvas id="comparisonChart" width="1024" height="400"></canvas>
            </div>
        </div>  
    </div>
    <script>

        console.log("Debut Script");

        // Define the temperatureChart variable outside the function
        var temperatureChart;
        var monthlyAvgChart;
        var precipitationChart;
        var comparisonChart;

        $(document).ready(function () {
            // Function to update the daily temperatures graph
            function updateDailyTempGraph(dates, averages, maximums, minimums, AvgTempAvgs, AvgTempHighs, AvgTempLows, movingAverages) {
                // Update the existing chart instance (temperatureChart) with new data
                temperatureChart.data.labels = dates;
                temperatureChart.data.datasets[0].data = averages;
                temperatureChart.data.datasets[1].data = maximums;
                temperatureChart.data.datasets[2].data = minimums;
                temperatureChart.data.datasets[3].data = AvgTempAvgs;
                temperatureChart.data.datasets[4].data = AvgTempHighs;
                temperatureChart.data.datasets[5].data = AvgTempLows;
                temperatureChart.data.datasets[6].data = movingAverages;
                temperatureChart.update();
            }

            function UpdateMonthlyTempGraph(labels, monthlyAvgData) {
                console.log("labels:", labels);
                console.log("monthlyAvgData:", monthlyAvgData);
                // Update the existing chart instance (monthlyAvgChart) with new data
                // Extract separate arrays for averages, maximums, and minimums from monthlyAvgData
                var averages = monthlyAvgData.map(data => parseFloat(data.avg));
                var maximums = monthlyAvgData.map(data => parseFloat(data.max));
                var minimums = monthlyAvgData.map(data => parseFloat(data.min));

                // Update the existing chart instance (monthlyAvgChart) with new data
                monthlyAvgChart.data.labels = labels;
                monthlyAvgChart.data.datasets[0].data = averages;
                monthlyAvgChart.data.datasets[1].data = maximums;
                monthlyAvgChart.data.datasets[2].data = minimums;
                monthlyAvgChart.update();
            }  
            
            // Function to update the precipitation graph
            function updatePrecipitationGraph(dates, rainfall, cumulativePrecipitations) {
                precipitationChart.data.labels = dates;
                precipitationChart.data.datasets[0].data = rainfall;
                precipitationChart.data.datasets[1].data = cumulativePrecipitations;
                precipitationChart.update();
            }

            function updateComparisonGraph(indexOfDay, datesPeriod1, datesPeriod2, weatherdata1, weatherdata2, datatype) {
                console.log("DataType:", datatype);
                console.log("DatesPeriod1:", datesPeriod1);
                console.log("DatesPeriod2:", datesPeriod2);
                console.log("WeatherData1:", weatherdata1);
                console.log("WeatherData2:", weatherdata2);
                console.log("IndexOfDay:", indexOfDay);

                // Format the dates for Period 1
                const fromDate1 = new Date(datesPeriod1[0]);
                const toDate1 = new Date(datesPeriod1[datesPeriod1.length - 1]);
                const formattedDatesPeriod1 = `${fromDate1.toLocaleDateString("en-GB")} to ${toDate1.toLocaleDateString("en-GB")}`;

                // Format the dates for Period 2
                const fromDate2 = new Date(datesPeriod2[0]);
                const toDate2 = new Date(datesPeriod2[datesPeriod2.length - 1]);
                const formattedDatesPeriod2 = `${fromDate2.toLocaleDateString("en-GB")} to ${toDate2.toLocaleDateString("en-GB")}`;

                // Update the labels for both Period 1 and Period 2 with the formatted dates
                comparisonChart.data.datasets[0].label = `From: ${formattedDatesPeriod1}`;
                comparisonChart.data.datasets[1].label = `From: ${formattedDatesPeriod2}`;

                // Update the title of the graph with the selected data type value
                const selectedDataType = $('#weather_data_type option:selected').text();
                comparisonChart.options.plugins.title.text = `${selectedDataType} Comparison Graph`;

                comparisonChart.data.labels = indexOfDay;
                comparisonChart.data.datasets[0].data = weatherdata1;
                comparisonChart.data.datasets[1].data = weatherdata2;
                comparisonChart.update();
            }

            // Helper function to format the date range as 'from: MM-DD-YYYY to MM-DD-YYYY'
            function formatDateRange(startDate, endDate) {
                var start = new Date(startDate);
                var end = new Date(endDate);
                return 'from: ' + formatDate(start) + ' to ' + formatDate(end);
            }

            // Helper function to format date as 'MM-DD-YYYY'
            function formatDate(date) {
                var day = date.getDate();
                var month = date.getMonth() + 1;
                var year = date.getFullYear();

                return (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day + '-' + year;
            }

            console.log("AVANT Chart update for DailyTempChart");

            // Chart update logic for DailyTempChart
            // Use the global temperatureChart variable here
            var ctx = document.getElementById('DailyTempChart').getContext('2d');
            temperatureChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                    {
                        label: 'Average Temperature',
                        data: [],
                        borderColor: 'blue',
                        backgroundColor: 'rgba(0, 0, 255, 0.1)',
                        fill: true
                    },
                    {
                        label: 'Maximum Temperature',
                        data: [],
                        borderColor: 'red',
                        backgroundColor: 'rgba(255, 0, 0, 0.1)',
                        fill: true
                    },
                    {
                        label: 'Minimum Temperature',
                        data:  [],
                        borderColor: 'green',
                        backgroundColor: 'rgba(0, 255, 0, 0.1)',
                        fill: true
                    },
                    {
                        label: 'Normals 2016-2022 Average Temperature',
                        data:  [],
                        borderColor: 'blue',
                        backgroundColor: 'rgba(0, 0, 255, 0.1)',
                        fill: true,
                        pointRadius: 0,
                        borderDash: [5, 5]
                    },
                    {
                        label: 'Normals 2016-2022 High Temperature',
                        data:  [],
                        borderColor: 'red',
                        backgroundColor: 'rgba(0, 0, 255, 0.1)',
                        fill: true,
                        pointRadius: 0,
                        borderDash: [5, 5]
                    },
                    {
                        label: 'Normals 2016-2022 Low Temperature',
                        data:  [],
                        borderColor: 'green',
                        backgroundColor: 'rgba(0, 0, 255, 0.1)',
                        fill: true,
                        pointRadius: 0,
                        borderDash: [5, 5]
                    },
                    {
                        label: 'Moving Average',
                        data: [],
                        borderColor: 'yellow',
                        borderWidth: 2,
                        fill: false,
                        pointRadius: 0 // Hide data points
                    }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Temperature'
                            }
                        }   
                    }
                }
            });
   
            console.log("Avant New Chart MonthlyAvgCtx");
            var monthlyAvgCtx = document.getElementById('MonthlyTempChart').getContext('2d');
            monthlyAvgChart = new Chart(monthlyAvgCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Average Temperature',
                            data: [],
                            backgroundColor: 'blue'
                        },
                        {
                            label: 'Maximum Temperature',
                            data: [],
                            backgroundColor: 'red'
                        },
                        {
                            label: 'Minimum Temperature',
                            data:  [],
                            backgroundColor: 'green'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Temperature'
                            }
                        }
                    }
                }
            });

            var ctxPrecipitation = document.getElementById('precipitationChart').getContext('2d');
            precipitationChart = new Chart(ctxPrecipitation, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Daily Precipitation (mm)',
                            data: [],
                            backgroundColor: createBarGradient('rgba(0, 123, 255, 0.7)', 'rgba(0, 123, 255, 0.1)'),
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 1,
                        },

                        {
                            label: 'Cumulative Precipitation (mm)',
                            data:  [],
                            type: 'line',
                            borderColor: 'orange',
                            borderWidth: 2,
                            fill: false,
                            yAxisID: 'cumulative-y-axis',
                            pointRadius: 0,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Precipitation (mm)'
                            }
                        },
                        'cumulative-y-axis': {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Cumulative Precipitation (mm)'
                            }
                        }
                    },
                    plugins: {
                        gradientColors: {
                        gradientColors: ['rgba(0, 123, 255, 0.1)', 'rgba(0, 123, 255, 0.7)']
                        }
                    }
                }
            });

            // Chart update logic for comparisonChart
            // Use the global comparisonChart variable here
            var comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
            comparisonChart = new Chart(comparisonCtx, {
                type: 'line', // Change the chart type to 'line'
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Period 1 Data',
                            data: [],
                            borderColor: 'blue', // Add border color for the curve
                            fill: false // Disable fill for the curve
                        },
                        {
                            label: 'Period 2 Data',
                            data: [],
                            borderColor: 'red', // Add border color for the curve
                            fill: false // Disable fill for the curve
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Comparison Graph', // Default title before any update
                        },
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Index of the Day',
                            },
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Weather Data',
                            },
                        }
                    },
                },
            });
            // Function to create gradient color for bars
            function createBarGradient(startColor, endColor) {
                var gradient = ctxPrecipitation.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, startColor);
                gradient.addColorStop(1, endColor);
                return gradient;
            }

            // Add the event listener to the form1 submission in weathergraphs.js
            document.getElementById('form1').addEventListener('submit', function (event) {
                event.preventDefault(); // Prevent default form submission behavior

                // Validate the dates before form submission
                if (!validateDates()) {
                    console.log('validateDates retourn FALSE');
    
                    return; // If the dates are invalid, do not proceed with the AJAX request
                }

                // Perform AJAX request to process_form1.php
                $.ajax({
                    type: "POST",
                    url: $(this).attr("action"),
                    data: $(this).serialize(), // Serialize form data
                    success: function (response) {  
                        try {
                            // Parse the JSON response
                            var responseData = JSON.parse(response);

                            console.log("responseData:", responseData);

                            // Update the first chart (Daily Temperatures Graph)
                            updateDailyTempGraph(
                                responseData.dates,
                                responseData.averages,
                                responseData.maximums,
                                responseData.minimums,
                                responseData.AvgTempAvgs,
                                responseData.AvgTempHighs,
                                responseData.AvgTempLows,
                                responseData.movingAverages
                            );

                            // Update the Second Chart (Monthly Temperatures Graph)
                            UpdateMonthlyTempGraph(
                                responseData.monthlyAvgLabels, // Fix typo from 'reponseData' to 'responseData'
                                responseData.monthlyAvgData
                            );

                            // Calculate and update precipitation graph
                            updatePrecipitationGraph(
                                responseData.dates,
                                responseData.precipitations,
                                responseData.cumulativePrecipitations
                            );

                        } catch (error) {
                            console.error("Error parsing JSON response:", error);
                        }

                        console.log("Response Data:", response);  
                        console.log("Raw JSON:", response);      
                        
 
                    },
                    error: function (xhr, status, error) {
                        console.error("Error processing form1:", error);
                    }
                });
            });

            console.log("Avant Handle Form2 submission");
            
            // Handle form2 submission
            $("#form2").submit(function (event) {
                event.preventDefault(); // Prevent default form submission behavior

                // Perform AJAX request to process_form2.php
                $.ajax({
                    type: "POST",
                    url: $(this).attr("action"),
                    data: $(this).serialize(), // Serialize form data
                    success: function (response) {
                        // Update "resultDiv" with the response from the server
                        //$("#Section2").html(response);
                        try {
                            // Parse the JSON response
                            var responseData = JSON.parse(response);

                            console.log("responseDataForm2:", responseData);

                            // Update the comparison chart 
                            updateComparisonGraph(
                                responseData.IndexOfDay,
                                responseData.DatesPeriod1,
                                responseData.DatesPeriod2,
                                responseData.WeatherData1,
                                responseData.WeatherData2,
                                responseData.DataType
                            );

                        } catch (error) {
                            console.error("Error parsing JSON response:", error);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Error processing form2:", error);
                    }
                });
            });
        });

    </script>
    
</body>
</html>