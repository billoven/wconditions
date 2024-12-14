<?php
    // Common Header for all the weatherMetrics files
    include "weatherMetricsHeader.php";

    ini_set('display_errors', 1);
    error_reporting(E_ALL);
?>
    <?php include 'alertBox.php'; ?>
  
    <!-- Weather Metrics Section -->
    <div class="container" id="WeatherMetrics">
        <form id="formtemp" class="form-group" method="POST" action="weatherMetricsFormTemp.php">
            <div class="form-row">
                <div class="form-group">
                    <?php include 'weatherMetricsDateSelector.php'; ?> 
                </div>
            </div>
        </form>

        <!-- Graph containers -->
        <div class="graph-container" id="DailyTempContainer">
            <h3 style="display: flex; align-items: center; justify-content: space-between;">
                Daily Temperatures Graph
                <button id="resetZoom" class="btn btn-outline-primary btn-sm">Reset Zoom</button>
            </h3>
            <div id="DailyTempGraphContainer">
                <canvas id="DailyTempChart" width="1024" height="500"></canvas>
            </div>
            <div id="DailyTempSummary"></div> <!-- Container for Daily Temp Summary -->
        </div>
        <div class="graph-container" id="MonthlyTempContainer">
            <h3>Monthly Temperatures Graph</h3>
            <div id="MonthlyTempGraphContainer">
                <canvas id="MonthlyTempChart" width="1024" height="400"></canvas> 
            </div>
        </div>
        <div class="graph-container" id="YearlyTempContainer">
            <h3>Yearly Temperatures Graph</h3>
            <div id="YearlyTempGraphContainer">
                <canvas id="YearlyTempChart" width="1024" height="400"></canvas> 
            </div>
        </div>
        <div class="graph-container" id="SeasonalTempContainer">
            <h3>Seasonal Temperatures Graph</h3>
            <div id="SeasonalTempGraphContainer">
                <canvas id="SeasonalTempChart" width="1024" height="400"></canvas> 
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
            var yearlyAvgChart;
            var seasonalAvgChart;

            // Function to update the daily temperatures graph
            function updateDailyTempGraph(start_date,end_date,dates, averages, maximums, minimums, AvgTempAvgs, AvgTempHighs, AvgTempLows, movingAverages) {
              
                // Construct the period title based on start_date and end_date
                const periodTitle = `Period: From ${start_date} To ${end_date}`;

                // Update the existing chart instance (temperatureChart) with new data
                temperatureChart.data.labels = dates;
                temperatureChart.data.datasets[0].data = averages;
                temperatureChart.data.datasets[1].data = maximums;
                temperatureChart.data.datasets[2].data = minimums;
                temperatureChart.data.datasets[3].data = AvgTempAvgs;
                temperatureChart.data.datasets[4].data = AvgTempHighs;
                temperatureChart.data.datasets[5].data = AvgTempLows;
                temperatureChart.data.datasets[6].data = movingAverages;
                
                temperatureChart.options.plugins.title.text = periodTitle;  // Set title text here
                temperatureChart.update();  // Update chart to apply new title

                // Update the summary
                const normals = {
                    avg: calculateStatistics(AvgTempAvgs).avg,
                    max: calculateStatistics(AvgTempHighs).avg,
                    min: calculateStatistics(AvgTempLows).avg
                };
                updateSummary('DailyTempSummary', temperatureChart.data.datasets, normals);
            }

            function UpdateMonthlyTempGraph(labels, monthlyAvgData) {
                console.log("Month labels:", labels);
                console.log("monthlyAvgData:", monthlyAvgData);
                // Update the existing chart instance (monthlyAvgChart) with new data
                // Extract separate arrays for averages, maximums, and minimums from monthlyAvgData
                var averages = monthlyAvgData.map(data => parseFloat(data.avg));
                var maximums = monthlyAvgData.map(data => parseFloat(data.max));
                var minimums = monthlyAvgData.map(data => parseFloat(data.min));

                // Construct the period title based on start_date and end_date
                const periodTitle = `Period: From ${start_date} To ${end_date}`;
                monthlyAvgChart.options.plugins.title.text = periodTitle;

                // Update the existing chart instance (monthlyAvgChart) with new data
                monthlyAvgChart.data.labels = labels;
                monthlyAvgChart.data.datasets[0].data = averages;
                monthlyAvgChart.data.datasets[1].data = maximums;
                monthlyAvgChart.data.datasets[2].data = minimums;
                monthlyAvgChart.update();
            }  

            function UpdateYearlyTempGraph(labels, yearlyAvgData) {
                console.log("Year labels:", labels);
                console.log("yearlyAvgData:", yearlyAvgData);
                // Update the existing chart instance (yearlyAvgChart) with new data
                // Extract separate arrays for averages, maximums, and minimums from monthlyAvgData
                var averages = yearlyAvgData.map(data => parseFloat(data.avg));
                var maximums = yearlyAvgData.map(data => parseFloat(data.max));
                var minimums = yearlyAvgData.map(data => parseFloat(data.min));

                // Construct the period title based on start_date and end_date
                const periodTitle = `Period: From ${start_date} To ${end_date}`;
                yearlyAvgChart.options.plugins.title.text = periodTitle;

                // Update the existing chart instance (monthlyAvgChart) with new data
                yearlyAvgChart.data.labels = labels;
                yearlyAvgChart.data.datasets[0].data = averages;
                yearlyAvgChart.data.datasets[1].data = maximums;
                yearlyAvgChart.data.datasets[2].data = minimums;
                yearlyAvgChart.update();
            }

            function UpdateSeasonalTempGraph(labels, seasonalAvgData) {
                console.log("labels:", labels);
                console.log("seasonalAvgData:", seasonalAvgData);
                // Update the existing chart instance (seasonalAvgChart) with new data
                // Extract separate arrays for averages, maximums, and minimums from monthlyAvgData
                var averages = seasonalAvgData.map(data => parseFloat(data.avg));
                var maximums = seasonalAvgData.map(data => parseFloat(data.max));
                var minimums = seasonalAvgData.map(data => parseFloat(data.min));

                // Construct the period title based on start_date and end_date
                const periodTitle = `Period: From ${start_date} To ${end_date}`;
                seasonalAvgChart.options.plugins.title.text = periodTitle;

                // Update the existing chart instance (seasonalAvgChart) with new data
                seasonalAvgChart.data.labels = labels;
                seasonalAvgChart.data.datasets[0].data = averages;
                seasonalAvgChart.data.datasets[1].data = maximums;
                seasonalAvgChart.data.datasets[2].data = minimums;
                seasonalAvgChart.update();
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
                        title: {
                            display: true,
                            text: "",  // Use the period title here
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            padding: {
                                top: 10,
                                bottom: 10
                            },
                            color: '#333' // Optional color customization
                        },
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
                        },
                        zoom: { // Nouvelle section ajoutée
                            zoom: {
                                wheel: {
                                    enabled: true // Active le zoom avec la molette
                                },
                                drag: {
                                    enabled: true, // Permet le zoom en cliquant-glissant
                                    backgroundColor: 'rgba(0,0,0,0.1)'
                                },
                                pinch: {
                                    enabled: true // Active le zoom par pincement sur mobile
                                },
                                mode: 'x', // Zoom uniquement sur l'axe X
                            },
                            pan: {
                                enabled: true, // Active le panoramique
                                mode: 'x', // Pan uniquement sur l'axe X
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
                        title: {
                            display: true,
                            text: "",  // Use the period title here
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            padding: {
                                top: 10,
                                bottom: 10
                            },
                            color: '#333' // Optional color customization
                        },
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

            console.log("Avant New Chart YearlyAvgCtx");
            var yearlyAvgCtx = document.getElementById('YearlyTempChart').getContext('2d');
            yearlyAvgChart = new Chart(yearlyAvgCtx, {
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
                                text: 'Year'
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
                        title: {
                            display: true,
                            text: "",  // Use the period title here
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            padding: {
                                top: 10,
                                bottom: 10
                            },
                            color: '#333' // Optional color customization
                        },
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

            console.log("Avant New Chart SeasonalAvgCtx");
            var seasonalAvgCtx = document.getElementById('SeasonalTempChart').getContext('2d');
            seasonalAvgChart = new Chart(seasonalAvgCtx, {
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
                                text: 'Season'
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
                        title: {
                            display: true,
                            text: "",  // Use the period title here
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            padding: {
                                top: 10,
                                bottom: 10
                            },
                            color: '#333' // Optional color customization
                        },
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

            // Add the event listener for resetting zoom
            document.getElementById('resetZoom').addEventListener('click', function () {
                temperatureChart.resetZoom();
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
                            start_date = responseData.start_date;
                            end_date = responseData.end_date;

                            // Update the first chart (Daily Temperatures Graph)
                            updateDailyTempGraph(
                                responseData.start_date,
                                responseData.end_date,
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

                            // Update the Third Chart (Yearly Temperatures Graph)
                            UpdateYearlyTempGraph(
                                responseData.yearlyAvgLabels, // Fix typo from 'reponseData' to 'responseData'
                                responseData.yearlyAvgData
                            );

                            // Update the Fourth Chart (Seasonal Temperatures Graph)
                            UpdateSeasonalTempGraph(
                                responseData.seasonalAvgLabels, // Fix typo from 'reponseData' to 'responseData'
                                responseData.seasonalAvgData
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
            document.getElementById('DailyTempContainer').style.display = byDay.checked ? 'block' : 'none';
            document.getElementById('MonthlyTempContainer').style.display = byMonth.checked ? 'block' : 'none';
            document.getElementById('YearlyTempContainer').style.display = byYear.checked ? 'block' : 'none';
            document.getElementById('SeasonalTempContainer').style.display = bySeason.checked ? 'block' : 'none';
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