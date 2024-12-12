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

        <div class="graph-container">
            <h3>Rain fall of the period by month</h3>
            <div id="precipitationGraphContainerByMonth">
                <canvas id="precipitationChartByMonth" width="1024" height="400"></canvas>
    	    </div>
        </div>
        <div class="graph-container">
            <h3>Rain fall of the period by year</h3>
            <div id="precipitationGraphContainerByYear">
                <canvas id="precipitationChartByYear" width="1024" height="400"></canvas>
    	    </div>
        </div>
        <div class="graph-container">
            <h3>Rain fall of the period by season</h3>
            <div id="precipitationGraphContainerBySeason">
                <canvas id="precipitationChartBySeason" width="1024" height="400"></canvas>
    	    </div>
        </div>
    </div>  
    <script>
        $(document).ready(function () {
            var precipitationChart, monthlyPrecipitationChart;

            // Function to update the daily precipitation graph
            function updatePrecipitationGraph(dates, rainfall, cumulativePrecipitations) {
                precipitationChart.data.labels = dates;
                precipitationChart.data.datasets[0].data = rainfall;
                precipitationChart.data.datasets[1].data = cumulativePrecipitations;
                precipitationChart.update();
            }

            // Function to update the monthly precipitation graph
            /**
             * Updates the monthly precipitation chart with new data.
             * 
             * This function takes an array of month labels and an array of objects containing
             * monthly rainfall data, processes the rainfall data to extract numeric values, 
             * and updates the chart's labels and dataset. It then triggers the chart to redraw.
             * 
             * @param {Array<string>} dates - Array of month labels for the x-axis (e.g., ["2024-10", "2024-11"]).
             * @param {Array<Object>} rainfall - Array of objects containing monthly precipitation data.
             *        Each object should have a `MonthlyRainFall` property (e.g., [{ MonthlyRainFall: 50 }, { MonthlyRainFall: 75 }]).
             * 
             * @example
             * // Example usage:
             * const dates = ["2024-10", "2024-11"];
             * const rainfall = [{ MonthlyRainFall: 50 }, { MonthlyRainFall: 75 }];
             * updateMonthlyPrecipitationGraph(dates, rainfall);
             * 
             * @returns {void} - This function does not return a value; it updates the chart directly.
             */
            function updateMonthlyPrecipitationGraph(dates, rainfall) {
                console.log("Updating monthly precipitation graph with:", dates, rainfall);

                // Extract numeric values from rainfall if it's an array of objects
                const rainfallValues = rainfall.map(item => item.MonthlyRainFall || 0);

                console.log("Processed rainfall data for chart:", rainfallValues);

                monthlyPrecipitationChart.data.labels = dates;
                monthlyPrecipitationChart.data.datasets[0].data = rainfallValues;
                monthlyPrecipitationChart.update();
            }

            /**
             * Updates the yearly precipitation graph with new data.
             * @param {Array} dates - Array of years (e.g., ["2023", "2024"]).
             * @param {Array} rainfall - Array of objects containing yearly rainfall data.
             *                           Each object should have the property 'YearlyRainFall'.
             */
            function updateYearlyPrecipitationGraph(dates, rainfall) {
                console.log("Updating yearly precipitation graph with:", dates, rainfall);

                // Extract numeric values from rainfall if it's an array of objects
                const rainfallValues = rainfall.map(item => item.YearlyRainFall || 0);

                console.log("Processed rainfall data for chart:", rainfallValues);

                // Update the graph data
                yearlyPrecipitationChart.data.labels = dates;
                yearlyPrecipitationChart.data.datasets[0].data = rainfallValues;
                yearlyPrecipitationChart.update();
            }

            /**
             * Updates the seasonal precipitation graph with new data.
             * @param {Array} seasons - Array of season labels (e.g., ["Winter 2023", "Spring 2023"]).
             * @param {Array} rainfall - Array of objects containing seasonal rainfall data.
             *                           Each object should have the property 'SeasonalRainFall'.
             */
            function updateSeasonalPrecipitationGraph(seasons, rainfall) {
                console.log("Updating seasonal precipitation graph with:", seasons, rainfall);

                // Extract numeric values from rainfall if it's an array of objects
                const rainfallValues = rainfall.map(item => item.SeasonalRainFall || 0);

                console.log("Processed rainfall data for chart:", rainfallValues);

                // Update the graph data
                seasonalPrecipitationChart.data.labels = seasons;
                seasonalPrecipitationChart.data.datasets[0].data = rainfallValues;
                seasonalPrecipitationChart.update();
            }

            // Function to create gradient color for bars
            function createBarGradient(ctx, startColor, endColor) {
                var gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, startColor);
                gradient.addColorStop(1, endColor);
                return gradient;
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
                            backgroundColor: createBarGradient(ctxPrecipitation, 'rgba(0, 123, 255, 0.7)', 'rgba(0, 123, 255, 0.1)'),
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 1,
                        },
                        {
                            label: 'Cumulative Precipitation (mm)',
                            data: [],
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
                        x: { title: { display: true, text: 'Date' } },
                        y: { title: { display: true, text: 'Precipitation (mm)' } },
                        'cumulative-y-axis': {
                            type: 'linear',
                            position: 'right',
                            title: { display: true, text: 'Cumulative Precipitation (mm)' }
                        }
                    }
                }
            });

            var ctxMonthlyPrecipitation = document.getElementById('precipitationChartByMonth').getContext('2d');
            monthlyPrecipitationChart = new Chart(ctxMonthlyPrecipitation, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Monthly Precipitation (mm)',
                            data: [],
                            backgroundColor: createBarGradient(ctxMonthlyPrecipitation, 'rgba(0, 123, 255, 0.7)', 'rgba(0, 123, 255, 0.1)'),
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: { title: { display: true, text: 'Month' } },
                        y: { title: { display: true, text: 'Precipitation (mm)' } }
                    }
                }
            });

            var ctxYearlyPrecipitation = document.getElementById('precipitationChartByYear').getContext('2d');
            yearlyPrecipitationChart = new Chart(ctxYearlyPrecipitation, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Yearly Precipitation (mm)',
                            data: [],
                            backgroundColor: createBarGradient(ctxYearlyPrecipitation, 'rgba(0, 123, 255, 0.7)', 'rgba(0, 123, 255, 0.1)'),
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: { title: { display: true, text: 'Year' } },
                        y: { title: { display: true, text: 'Precipitation (mm)' } }
                    }
                }
            });

            var ctxSeasonalPrecipitation = document.getElementById('precipitationChartBySeason').getContext('2d');
            seasonalPrecipitationChart = new Chart(ctxSeasonalPrecipitation, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Seasonal Precipitation (mm)',
                            data: [],
                            backgroundColor: createBarGradient(ctxSeasonalPrecipitation, 'rgba(0, 123, 255, 0.7)', 'rgba(0, 123, 255, 0.1)'),
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: { title: { display: true, text: 'Season' } },
                        y: { title: { display: true, text: 'Precipitation (mm)' } }
                    }
                }
            });

            // AJAX request for form submission
            $('#formrain').submit(function (event) {
                event.preventDefault();

                if (!validateDates()) {
                    console.log('validateDates returned FALSE');
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: $(this).attr("action"),
                    data: $(this).serialize(),
                    success: function (response) {
                        try {
                            var responseData = JSON.parse(response);

                            // Update precipitation charts
                            updatePrecipitationGraph(responseData.dates, responseData.precipitations, responseData.cumulativePrecipitations);
                            updateMonthlyPrecipitationGraph(responseData.monthlyAvgLabels, responseData.monthlyAvgData);
                            updateYearlyPrecipitationGraph(responseData.yearlyAvgLabels, responseData.yearlyAvgData);
                            updateSeasonalPrecipitationGraph(responseData.seasonalAvgLabels, responseData.seasonalAvgData);
                            
                        } catch (error) {
                            console.error("Error parsing JSON response:", error);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Error processing formrain:", error);
                    }
                });
            });
        });

        /**
         * Function: toggleGraphVisibility
         * Purpose: Controls the visibility of each graph container based on the state of the associated checkboxes.
         * 
         * This function checks the state of each checkbox ('by_day', 'by_month', 'by_year', 'by_season').
         * For each graph type:
         *    - If the checkbox is checked, it displays the corresponding graph container by setting 'display' to 'block'.
         *    - If the checkbox is unchecked, it hides the graph container by setting 'display' to 'none'.
         * 
         * This allows users to selectively show or hide graphs based on the chosen period type.
         */
        function toggleGraphVisibility() {
            // Get the checkbox elements
            const byDay = document.querySelector('input[name="by_day"]');
            const byMonth = document.querySelector('input[name="by_month"]');
            const byYear = document.querySelector('input[name="by_year"]');
            const bySeason = document.querySelector('input[name="by_season"]');

            // Toggle visibility of entire graph-container divs based on checkbox states
            document.getElementById('precipitationGraphContainer').style.display = byDay.checked ? 'block' : 'none';
            document.getElementById('precipitationGraphContainerByMonth').style.display = byMonth.checked ? 'block' : 'none';
            document.getElementById('precipitationGraphContainerByYear').style.display = byYear.checked ? 'block' : 'none';
            document.getElementById('precipitationGraphContainerBySeason').style.display = bySeason.checked ? 'block' : 'none';
        }

        // Call the function initially to set up the display based on the default checkbox values
        toggleGraphVisibility();

        // Optional: Add an event listener for each checkbox for additional flexibility
        document.querySelectorAll('.checkbox-group input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', toggleGraphVisibility);
        });

    </script>
    </body>
</html>