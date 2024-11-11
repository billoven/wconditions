<?php
    // Common Header for all the weatherMetrics files
    include "weatherMetricsHeader.php";

    ini_set('display_errors', 1);
    error_reporting(E_ALL);
?>

    <?php include 'alertBox.php'; ?>
     
    <!-- Section 1 and First Form -->
    <div class="container" id="Section1">
        <form id="formrain" class="form-group" method="POST" action="weatherMetricsFormRain.php">
            <div class="form-row">
                <div class="form-group">
                    <!-- Hidden field for date range type -->
                    <input type="hidden" id="date_range_type" name="date_range_type" value="custom">
                    
                    <?php include 'weatherMetricsDateSelector.php'; ?> <!-- Date selector inclusion -->
                                       
                </div>
            </div>
        </form>
        <div class="graph-container">
            <h3>Daily rain fall and cumulative of the period</h3>
            <div id="precipitationGraphContainer">
                <canvas id="precipitationChart" width="1024" height="400"></canvas>
    	    </div>
        </div>
    </div>  
    <script>

        console.log("Debut Script");

        // Define the temperatureChart variable outside the function
        var precipitationChart;

        $(document).ready(function () {
            
            // Function to update the precipitation graph
            function updatePrecipitationGraph(dates, rainfall, cumulativePrecipitations) {
                precipitationChart.data.labels = dates;
                precipitationChart.data.datasets[0].data = rainfall;
                precipitationChart.data.datasets[1].data = cumulativePrecipitations;
                precipitationChart.update();
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

            // Function to create gradient color for bars
            function createBarGradient(startColor, endColor) {
                var gradient = ctxPrecipitation.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, startColor);
                gradient.addColorStop(1, endColor);
                return gradient;
            }

            // Add the event listener to the formrain submission in weathergraphs.js
            document.getElementById('formrain').addEventListener('submit', function (event) {
                event.preventDefault(); // Prevent default form submission behavior

                // Validate the dates before form submission
                if (!validateDates()) {
                    console.log('validateDates retourn FALSE');
    
                    return; // If the dates are invalid, do not proceed with the AJAX request
                }

                // Perform AJAX request to process_formrain.php
                $.ajax({
                    type: "POST",
                    url: $(this).attr("action"),
                    data: $(this).serialize(), // Serialize form data
                    success: function (response) {  
                        try {
                            // Parse the JSON response
                            var responseData = JSON.parse(response);

                            console.log("responseData:", responseData);

                            // Calculate and update precipitation graph
                            updatePrecipitationGraph(
                                responseData.dates,
                                responseData.precipitations,
                                responseData.cumulativePrecipitations
                            );

                        } catch (error) {
                            console.log("Response Data:", response);
                            console.error("Error parsing JSON response:", error);
                        }

                        console.log("Response Data:", response);  
                        console.log("Raw JSON:", response);      
                        
 
                    },
                    error: function (xhr, status, error) {
                        console.error("Error processing formrain:", error);
                    }
                });
            });

            
        });

    </script>
    
</body>
</html>