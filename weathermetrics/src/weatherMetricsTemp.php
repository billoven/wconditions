<?php
    // Common Header for all the weatherMetrics files
    include "weatherMetricsHeader.php";

    ini_set('display_errors', 1);
    error_reporting(E_ALL);
?>

    <?php include 'alertBox.php'; ?>
     
    <!-- Temperatures Section -->
    <div class="container" id="Temperatures">
      <h2>Select Date Range</h2>
      <form id="formtemp" class="form-group" method="POST" action="weatherMetricsFormTemp.php">
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
    </div>  
    <script>

        console.log("Debut Script");

        // Define the temperatureChart variable outside the function
        var temperatureChart;
        var monthlyAvgChart;

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
                            data: [],
                            borderColor: 'green',
                            backgroundColor: 'rgba(0, 255, 0, 0.1)',
                            fill: true
                        },
                        {
                            label: '<?php global $selectedPeriod, $selectedCity; echo substr($selectedCity, 0, 2) . "-" . $selectedPeriod; ?> Avg Norm. Temp.',
                            data: [],
                            borderColor: 'blue',
                            backgroundColor: 'rgba(0, 0, 255, 0.1)',
                            fill: true,
                            pointRadius: 0,
                            borderDash: [5, 5]
                        },
                        {
                            label: '<?php global $selectedPeriod, $selectedCity; echo substr($selectedCity, 0, 2) . "-" . $selectedPeriod; ?> High Norm. Temp.',
                            data: [],
                            borderColor: 'red',
                            backgroundColor: 'rgba(0, 0, 255, 0.1)',
                            fill: true,
                            pointRadius: 0,
                            borderDash: [5, 5]
                        },
                        {
                            label: '<?php global $selectedPeriod, $selectedCity; echo substr($selectedCity, 0, 2) . "-" . $selectedPeriod; ?> Low Norm. Temp.',
                            data: [],
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
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y.toFixed(1) + '°C';
                                    }
                                    return label;
                                }
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
                            data: [],
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
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y.toFixed(1) + '°C';
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // Add the event listener to the formtemp submission in weathergraphs.js
            document.getElementById('formtemp').addEventListener('submit', function (event) {
                event.preventDefault(); // Prevent default form submission behavior

                // Validate the dates before form submission
                if (!validateDates()) {
                    console.log('validateDates retourn FALSE');
    
                    return; // If the dates are invalid, do not proceed with the AJAX request
                }

                // Perform AJAX request to process_formtemp.php
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

                        } catch (error) {
                            console.log("Response Data:", response);
                            console.error("Error parsing JSON response:", error);
                        }

                        console.log("Response Data:", response);  
                        console.log("Raw JSON:", response);      
                        
 
                    },
                    error: function (xhr, status, error) {
                        console.error("Error processing formtemp:", error);
                    }
                });
            });
        });

    </script>
    
</body>
</html>