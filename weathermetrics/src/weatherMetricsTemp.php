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
        <div id="DailyTempSummary"></div> <!-- Container for Daily Temp Summary -->
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

        $(document).ready(function () {
            // Function to calculate statistics with debugging information
            function calculateStatistics(data) {
                // Convert all string values to numbers
                const numericData = data.map(Number);
                
                // Log the input data
                console.log("Input data:", numericData);

                // Calculate the sum of all values in the data array
                const sum = numericData.reduce((acc, val) => {
                    console.log(`Acc: ${acc}, Val: ${val}, New Acc: ${acc + val}`);
                    return acc + val;
                }, 0);
                console.log("Sum of data:", sum);

                // Calculate the average value
                const avg = sum / numericData.length;
                console.log("Average value:", avg);

                // Find the maximum value in the data array
                const max = Math.max(...numericData);
                console.log("Maximum value:", max);

                // Find the minimum value in the data array
                const min = Math.min(...numericData);
                console.log("Minimum value:", min);

                // Return an object containing the average, maximum, and minimum values
                return { avg, max, min };
            }

            // Function to update the summary display
            function updateSummary(containerId, datasets, normals) {
                const summaryContainer = document.getElementById(containerId);
                summaryContainer.innerHTML = ''; // Clear previous summary

                const table = document.createElement('table');
                table.style.borderCollapse = 'collapse';
                table.style.width = '100%';

                const thead = document.createElement('thead');
                const tbody = document.createElement('tbody');

                const headerRow = document.createElement('tr');
                headerRow.innerHTML = `
                    <th>Metric</th>
                    <th>Average</th>
                    <th>Max</th>
                    <th>Min</th>
                `;
                thead.appendChild(headerRow);

                datasets.forEach((dataset, index) => {
                    if (dataset.label.includes('Norm')) return; // Skip normals

                    console.log("Before Calculated statistics:", dataset.data);
                    const stats = calculateStatistics(dataset.data);
                    console.log("After Calculated statistics:", stats);
                    let diff = '';
                    // Calculate differences with normals only if it's not Moving Average
                    if (!dataset.label.includes('Moving Average')) {
                        if (dataset.label.includes('Average Temperature')) {
                            diff = (stats.avg - normals.avg).toFixed(1);
                        } else if (dataset.label.includes('Maximum Temperature')) {
                            diff = (stats.avg - normals.max).toFixed(1);
                        } else if (dataset.label.includes('Minimum Temperature')) {
                            diff = (stats.avg - normals.min).toFixed(1);
                        }
                    }

                    const dataRow = document.createElement('tr');
                    dataRow.innerHTML = `
                        <td><strong>${dataset.label}</strong></td>
                        <td>${stats.avg.toFixed(1)}°C ${diff && `(${diff >= 0 ? '+' : ''}${diff}°C)`}</td>
                        <td>${stats.max.toFixed(1)}°C</td>
                        <td>${stats.min.toFixed(1)}°C</td>
                    `;
                    tbody.appendChild(dataRow);
                });

                // Display normals
                const normalsRow = document.createElement('tr');
                normalsRow.innerHTML = `
                    <td><strong>Average normal temperatures for the period</strong></td>
                    <td>${normals.avg.toFixed(1)}°C</td>
                    <td>${normals.max.toFixed(1)}°C</td>
                    <td>${normals.min.toFixed(1)}°C</td>
                `;
                tbody.appendChild(normalsRow);

                table.appendChild(thead);
                table.appendChild(tbody);
                summaryContainer.appendChild(table);
            }


            // Define the temperatureChart variable outside the function
            var temperatureChart;
            var monthlyAvgChart;

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

                // Update the summary
                const normals = {
                    avg: calculateStatistics(AvgTempAvgs).avg,
                    max: calculateStatistics(AvgTempHighs).avg,
                    min: calculateStatistics(AvgTempLows).avg
                };
                updateSummary('DailyTempSummary', temperatureChart.data.datasets, normals);
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