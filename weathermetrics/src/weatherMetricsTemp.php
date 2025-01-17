<?php
    // Common Header for all the weatherMetrics files
    include "weatherMetricsHeader.php";
    include 'alertBox.php';

    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
?>
<style>
    .table-container {
        max-height: 300px; /* Limiter la hauteur à environ 10 lignes (ajustez selon vos besoins) */
        overflow-y: auto;  /* Activer le défilement vertical */
        border: 1px solid #dee2e6; /* Optionnel : ajoute une bordure pour délimiter la table */
    }

    .table {
        margin-bottom: 0; /* Évite les marges inutiles sous la table */
    }
    .sticky-header thead th {
        position: sticky;
        top: 0; /* Fixes the header to the top of the container */
        z-index: 2; /* Ensures the header stays above the table rows */
        background-color: #f8f9fa; /* Matches the table header background */
    }
</style>

<?php
function generateWeatherMetricsSection($title, $idPrefix, $includeDailySummary = false) {
    ?>

    <!-- Graph containers -->
    <div class="graph-container mt-4" id="<?php echo $idPrefix; ?>Container">
        <h3 style="display: flex; align-items: center; justify-content: space-between;">
            <?php echo $title; ?>
            <?php if ($idPrefix === 'DailyTemp') { ?>
                <button id="resetZoom" class="btn btn-outline-primary btn-sm">Reset Zoom</button>
            <?php } ?>
        </h3>
        <ul class="nav nav-tabs" id="<?php echo $idPrefix; ?>Tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="<?php echo $idPrefix; ?>-graph-tab" data-bs-toggle="tab" 
                   href="#<?php echo $idPrefix; ?>-graph" role="tab" 
                   aria-controls="<?php echo $idPrefix; ?>-graph" aria-selected="true">Graph</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="<?php echo $idPrefix; ?>-table-tab" data-bs-toggle="tab" 
                   href="#<?php echo $idPrefix; ?>-table" role="tab" 
                   aria-controls="<?php echo $idPrefix; ?>-table" aria-selected="false">Table</a>
            </li>
        </ul>
        <div class="tab-content mt-3" id="<?php echo $idPrefix; ?>TabContent">
            <div class="tab-pane fade show active" id="<?php echo $idPrefix; ?>-graph" role="tabpanel" 
                 aria-labelledby="<?php echo $idPrefix; ?>-graph-tab">
                <canvas id="<?php echo $idPrefix; ?>Chart" width="1024" height="500"></canvas>
            </div>
            <div class="tab-pane fade" id="<?php echo $idPrefix; ?>-table" role="tabpanel" 
                 aria-labelledby="<?php echo $idPrefix; ?>-table-tab">
                <div class="table-container">
                    <table class="table table-striped table-bordered sticky-header">
                        <thead>
                            <!-- Le contenu du header de table sera inséré dynamiquement -->
                        </thead>
                        <tbody>
                            <!-- Le contenu sera inséré dynamiquement -->
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($includeDailySummary) { ?>
                <div id="<?php echo $idPrefix; ?>Summary"></div>
            <?php } ?>
        </div>
    </div>
    <?php
}
?>


<!-- Weather Metrics Section -->
<div class="container" id="WeatherMetrics">
    <h5 class="mb-4">Temperatures : <?php global $selectedStation; echo $selectedStation; ?></h5>
    <form id="formtemp" class="form-group" method="POST" action="weatherMetricsFormTemp.php">
        <div class="form-group">
            <?php include 'weatherMetricsDateSelector.php'; ?>
        </div>
    </form>

    <?php
    // Génération des sections avec la fonction générique
    generateWeatherMetricsSection('Daily Temperatures', 'DailyTemp', true);
    generateWeatherMetricsSection('Monthly Temperatures', 'MonthlyTemp');
    generateWeatherMetricsSection('Yearly Temperatures', 'YearlyTemp');
    generateWeatherMetricsSection('Seasonal Temperatures', 'SeasonalTemp');
    ?>
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

                // Display normalsstrtolower
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

            // ============== Update Daily Temperature Table =============
            /**
             * Updates a weather data table dynamically with headers and data.
             * 
             * @param {string} period - The ID of the table (e.g., "SeasonalTemp").
             * @param {Array<string>} dates - An array of date labels for the rows (e.g., ["Spring 2024", "Summer 2024"]).
             * @param {Object} dataColumns - An object where keys are column names and values are arrays of data 
             *                               (e.g., { "Average": [15.5, 25.3], "Max": [20.3, 30.5], "Min": [10.2, 18.3] }).
             * @param {Array<string>} columnsHeaders - An array of column headers for the table (e.g., ["Period", "Mean temperature", "Min temperature", "Max temperature"]).
             * 
             * The function:
             * 1. Dynamically generates the <thead> section with the provided headers.
             * 2. Populates the <tbody> with rows, each containing a date and corresponding data for the columns.
             * 3. Handles missing data by displaying "N/A" for undefined values.
             */
            function updateWeatherTable(period, dates, dataColumns, columnsHeaders) {
                // Select the table element based on the period
                const table = document.querySelector(`#${period}-table`);

                // Check if the table exists in the DOM
                if (!table) {
                    console.error(`Table not found for period: ${period}`);
                    return;
                }

                // Select or create the table header
                let tableHeader = table.querySelector("thead");
                if (!tableHeader) {
                    tableHeader = document.createElement("thead");
                    table.appendChild(tableHeader);
                }

                // Clear the current content of the table header
                tableHeader.innerHTML = "";

                // Create a header row <tr>
                const headerRow = document.createElement("tr");

                // Loop through the columnsHeaders array to create <th> elements
                for (const header of columnsHeaders) {
                    const headerCell = document.createElement("th");
                    headerCell.textContent = header;
                    headerRow.appendChild(headerCell);
                }

                // Append the header row to the table header
                tableHeader.appendChild(headerRow);

                // Select the table body
                let tableBody = table.querySelector("tbody");

                // Check if the table body exists; create it if it doesn't
                if (!tableBody) {
                    tableBody = document.createElement("tbody");
                    table.appendChild(tableBody);
                }

                // Clear the current content of the table body
                tableBody.innerHTML = "";

                // Loop through the dates array to create new rows
                for (let i = 0; i < dates.length; i++) {
                    // Create a new table row <tr>
                    const row = document.createElement("tr");

                    // Add a <td> cell for the date
                    const dateCell = document.createElement("td");
                    dateCell.textContent = dates[i];
                    row.appendChild(dateCell);

                    // Dynamically add <td> cells for each data column
                    for (const columnData of Object.values(dataColumns)) {
                        const cell = document.createElement("td");
                        // Check if the data exists for the current index; if not, use "N/A"
                        cell.textContent = columnData[i] !== undefined ? `${columnData[i]}°C` : "N/A";
                        row.appendChild(cell);
                    }

                    // Append the row to the table body
                    tableBody.appendChild(row);
                }
            }



            // Define the temperatureChart variable outside the function
            var temperatureChart;
            var monthlyAvgChart;
            var yearlyAvgChart;
            var seasonalAvgChart;

            // Function to update the daily temperatures graph
            function updateDailyTemp(start_date,end_date,dates, averages, maximums, minimums, AvgTempAvgs, AvgTempHighs, AvgTempLows, movingAverages) {
              
                // ============== Update Daily Temperature Graph =============
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

                // ============== Update Daily Temperature Table =============
                // Call the function to update the table
                // Titres pour les colonnes de la table
                const columnsHeaders = [
                    "Day", 
                    "Daily Mean temperature", 
                    "Daily Max temperature", 
                    "Daily Min temperature"
                ];

                // Appel de la fonction
                updateWeatherTable("DailyTemp", dates, {
                    "Average": averages,
                    "Max": maximums,
                    "Min": minimums
                }, columnsHeaders);

                // ============== Update Daily Temperature Metrics Summary =============
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

                // ============== Update Monthly Temperature Table =============
                // Sélectionnez le <tbody> de la table
                // Call the function to update the table
                const columnsHeaders = [
                    "Month", 
                    "Monthly Avg of Daily Mean Temps", 
                    "Monthly Avg of Daily Max Temps", 
                    "Monthly Avg of Daily Min Temps"
                ];

                // Appel de la fonction
                updateWeatherTable("MonthlyTemp", labels, {
                    "Average": averages,
                    "Max": maximums,
                    "Min": minimums
                }, columnsHeaders);

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

                // ============== Update Yearly Temperature Table =============
                // Sélectionnez le <tbody> de la table
                // Call the function to update the table
                const columnsHeaders = [
                    "Year", 
                    "Yearly Avg of Daily Mean Temps", 
                    "Yearly Avg of Daily Max Temps", 
                    "Yearly Avg of Daily Min Temps"
                ];

                // Appel de la fonction
                updateWeatherTable("YearlyTemp", labels, {
                    "Average": averages,
                    "Max": maximums,
                    "Min": minimums
                }, columnsHeaders);         
            }

            function UpdateSeasonalTempGraph(labels, seasonalAvgData) {
                console.log("labels:", labels);
                console.log("seasonalAvgData:", seasonalAvgData);
                // Update the existing chart instance (seasonalAvgChart) with new data
                // Extract separate arrays for averages, maximums, and minimums from seasonalAvgData
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


                // ============== Update Seasonal Temperature Table =============
                // Sélectionnez le <tbody> de la table
                // Call the function to update the table
                const columnsHeaders = [
                    "Season", 
                    "Seasonal Avg of Daily Mean Temps", 
                    "Seasonal Avg of Daily Max Temps", 
                    "Seasonal Avg of Daily Min Temps"
                ];

                // Appel de la fonction
                updateWeatherTable("SeasonalTemp", labels, {
                    "Average": averages,
                    "Max": maximums,
                    "Min": minimums
                }, columnsHeaders);     
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
                            updateDailyTemp(
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