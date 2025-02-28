<?php
    // Common Header for all the weatherMetrics files
    include "weatherMetricsHeader.php";

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

?>
<style>

    .small {
        font-size: 0.85rem;
        color: #868B94;
    }

    /* Images */
    img {
        object-fit: contain;
        display: block;
    }

    /* Table Styling */
    .table-container {
        max-height: 300px; /* Limit height for scrolling */
        overflow-y: auto;  /* Enable vertical scrolling */
        border: 1px solid #dee2e6; /* Add a subtle border */
    }

    .table {
        margin-bottom: 0; /* Remove unnecessary bottom margin */
    }

    /* Sticky Table Header */
    .sticky-header thead th {
        position: sticky;
        top: 0; /* Fix header to the top */
        z-index: 2; /* Ensure header is above rows */
        background-color: #f8f9fa; /* Match header background */
    }

</style>

<!-- Start of PHP function that will display generic weather metrics sections -->
<?php
function generateWeatherMetricsSection($title, $idPrefix, $includeDailySummary = false, $unit = '') {
?>
    <!-- Graph containers -->
    <div class="graph-container mt-4" id="<?php echo $idPrefix; ?>Container">
        <h3 style="display: flex; align-items: center; justify-content: space-between;">
            <?php echo $title; ?>
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
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="<?php echo $idPrefix; ?>DarkModeSwitch">
                    <label class="form-check-label" for="<?php echo $idPrefix; ?>DarkModeSwitch">Dark Mode</label>
                </div>
                <div id="<?php echo $idPrefix; ?>Chart" style="width: 100%; height: 400px;"></div>
            </div>
            <div class="tab-pane fade" id="<?php echo $idPrefix; ?>-table" role="tabpanel" 
                aria-labelledby="<?php echo $idPrefix; ?>-table-tab">
                <div class="table-container">
                    <table class="table table-striped table-bordered sticky-header">
                        <thead>
                            <!-- Table header Content will be inserted dynamically -->
                        </thead>
                        <tbody>
                            <!-- Table body Content will be inserted dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($includeDailySummary) { ?>
                <div id="<?php echo $idPrefix; ?>Summary">
                    <!-- Content will be inserted dynamically -->
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
<!-- End of PHP function that will display generic weather metrics sections -->

<!-- Weather Metrics Section -->
<div class="container" id="WeatherMetrics">
    <div class="d-flex align-items-center justify-content-between">
        <!-- Title dynamically based on selected metric type -->
        <h5 class="mb-0">
            <?php 
            global $selectedMetric, $selectedStation;
            echo ucfirst($selectedMetric) . " : " . $selectedStation; 
            ?>
        </h5>
        <!-- Normals with smaller font size -->
        <p style="font-size: 0.7rem; margin: 0;">
            <em>Normals: <?php global $selectedCity, $selectedPeriod; echo "$selectedCity - $selectedPeriod"; ?></em>
        </p>
    </div>
    <form id="weatherMetricsForm" class="form-group" method="POST" action="weatherMetricsForm.php">
        <input type="hidden" name="metric" value="<?php echo htmlspecialchars($selectedMetric, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="form-group">
            <?php include 'weatherMetricsDateSelector.php'; ?>
        </div>
    </form>

    <?php
    // Dynamically determine which metric to display
    switch ($selectedMetric) {
        case 'Temperature':
            generateWeatherMetricsSection('Daily Temperature', 'DailyData', true, '°C');
            generateWeatherMetricsSection('Monthly Temperature', 'MonthlyData', false, '°C');
            generateWeatherMetricsSection('Yearly Temperature', 'YearlyData', false, '°C');
            generateWeatherMetricsSection('Seasonal Temperature', 'SeasonalData', false, '°C');
            break;
        case 'Rainfall':
            generateWeatherMetricsSection('Daily Rainfall', 'DailyData', false , 'mm');
            generateWeatherMetricsSection('Monthly Rainfall', 'MonthlyData', false, 'mm');
            generateWeatherMetricsSection('Yearly Rainfall', 'YearlyData', false, 'mm');
            generateWeatherMetricsSection('Seasonal Rainfall', 'SeasonalData', false, 'mm');
            break;
        case 'Pressure':
            generateWeatherMetricsSection('Daily Pressure', 'DailyData', false, 'hPa');
            generateWeatherMetricsSection('Monthly Pressure', 'MonthlyData', false, 'hPa');
            generateWeatherMetricsSection('Yearly Pressure', 'YearlyData', false, 'hPa');
            generateWeatherMetricsSection('Seasonal Pressure', 'SeasonalData', false, 'hPa');
            break;
        case 'Hygrometry':
            generateWeatherMetricsSection('Daily Humidity', 'DailyData', false, '%');
            generateWeatherMetricsSection('Monthly Humidity', 'MonthlyData', false, '%');
            generateWeatherMetricsSection('Yearly Humidity', 'YearlyData', false, '%');
            generateWeatherMetricsSection('Seasonal Humidity', 'SeasonalData', false, '%');
            break;
        default:
            echo "<p class='text-danger'>No valid metric selected.</p>";
            break;
    }
    ?>
</div>

<script>
    
    $(document).ready(function () {

        var start_date, end_date; // Explicit Global Variable
 
        // Function to calculate statistics with debugging information
        function calculateStatistics(data) {
            if (!data || data.length === 0) {
                console.warn("calculateStatistics: Data array is empty or undefined.");
                return { avg: 0, max: 0, min: 0 };
            }

            // Convert all string values to numbers and extract "value" if present
            const numericData = data
                .map(entry => (typeof entry === 'object' && entry.value !== undefined) ? Number(entry.value) : Number(entry))
                .filter(val => !isNaN(val)); // Remove NaN values

            if (numericData.length === 0) {
                console.warn("calculateStatistics: No valid numeric values found.");
                return { avg: 0, max: 0, min: 0 };
            }

            // Calculate sum, average, max, min
            const sum = numericData.reduce((acc, val) => acc + val, 0);
            const avg = sum / numericData.length;
            const max = Math.max(...numericData);
            const min = Math.min(...numericData);

            return { avg, max, min };
        }

        // Function to update the summary display (after the graph, optional)
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
                if (dataset.name.includes('Norm')) return; // Skip normals

                const stats = calculateStatistics(dataset.data);

                let diff = '';
                // Calculate differences with normals only if it's not Moving Average
                if (!dataset.name.includes('Moving Average')) {
                    if (dataset.name.includes('Average Temperature')) {
                        diff = (stats.avg - normals.avg).toFixed(1);
                    } else if (dataset.name.includes('Maximum Temperature')) {
                        diff = (stats.avg - normals.max).toFixed(1);
                    } else if (dataset.name.includes('Minimum Temperature')) {
                        diff = (stats.avg - normals.min).toFixed(1);
                    }
                }

                const dataRow = document.createElement('tr');
                dataRow.innerHTML = `
                    <td><strong>${dataset.name}</strong></td>
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

        // ============== Update Daily Metric Table =============
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
        function updateWeatherTable(period, dates, dataColumns, columnsHeaders, unit) {
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
                    cell.textContent = columnData[i] !== undefined ? `${columnData[i]}${unit}` : "°C";
                    row.appendChild(cell);
                }

                // Append the row to the table body
                tableBody.appendChild(row);
            }
        }
    

        // Define the dailyChart variable outside the function
        var dailyChart;
        var monthlyChart;
        var yearlyChart;
        var seasonalChart;

        // Global object to store chart instances
        // 4 instances are existing currently DailData, MonthlyData, YearlyData, SeasonalData
        var chartInstances = {};

        /**
         * Function to initialize an ECharts instance
         */
        function initializeChart(chartId, isDarkMode) {
            const chartDom = document.getElementById(chartId + 'Chart');
            if (!chartDom) {
                return null;
            }

            // Dispose of old chart instance if it exists
            if (chartInstances[chartId]) {
                chartInstances[chartId].dispose();
            }

            // Create a new chart instance
            chartInstances[chartId] = echarts.init(chartDom, isDarkMode ? 'dark' : 'light');
            return chartInstances[chartId];
        }

        /**
         * Function to load data into the chart
         */
        var chartData = {}; // Global storage for chart data

        //function loadChart(chartId, graphTitle, seriesData, xAxisData, isDarkMode) {
        function loadChart(chartId, chartOptions, isDarkMode) {
            if (!chartInstances[chartId]) {
                chartInstances[chartId] = initializeChart(chartId, isDarkMode);
            } else {
                chartInstances[chartId].clear(); // Keep instance but reset content
            }

            const chartInstance = chartInstances[chartId];

            if (!chartInstance) return;

            // Stocker toutes les options du graphique
            chartData[chartId] = { ...chartOptions };

            chartInstance.setOption(chartOptions);
        }
        
        <?php
        // Select the appropriate script based on the selected metric
        switch ($selectedMetric) {
            case "Temperature":
                include 'weatherMetricsTempGraph.php';
                break;          
            case "Rainfall":
                include 'weatherMetricsRainGraph.php';
                break;           
            case "Pressure":
                include 'weatherMetricsPressureGraph.php';
                break;          
            default:
                echo "<p class='text-danger'>No valid metric selected.</p>";
                break;
        }
        ?>
       
        // Function to reload the chart with the selected theme (light/dark)
        function reloadChart(chartId, theme) {

            if (chartInstances[chartId]) {
                chartInstances[chartId].dispose(); // Completely destroy the old instance
                delete chartInstances[chartId]; // Ensure it's fully removed from memory
            }

            // Reinitialize with the correct theme
            chartInstances[chartId] = initializeChart(chartId, theme === "dark");

            // Reload the data
            if (chartData[chartId]) {
                loadChart(chartId, chartData[chartId], theme === "dark");
            }
        }

        
        // Listens for changes on any dark mode switch and reloads the related chart
        document.addEventListener("change", function (event) {
            // Check if the changed element is a dark mode switch
            if (event.target.classList.contains("form-check-input")) {
                let switchId = event.target.id; // e.g., "DailyDataDarkModeSwitch"
                let chartId = switchId.replace("DarkModeSwitch", ""); // e.g., "DailyDataChart"
                let isDarkMode = event.target.checked; // true = dark mode, false = light mode

                // Reload the associated chart with the selected theme
                reloadChart(chartId, isDarkMode ? "dark" : "light");
            }
        });
    
        // Adjust chart size when switching tabs
        // This ensures that when a user switches to the "Graph" tab, 
        // the corresponding chart is resized properly to fit its container.
        document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                // Récupérer l'ID de l'onglet (ex: #DailyData-graph -> DailyData)
                const targetId = event.target.getAttribute("href").replace('-graph', '').replace('#', '');
                
                // Générer dynamiquement le nom de la variable du graphique
                const chartVarName = targetId.toLowerCase() + 'Chart';

                // Vérifier si la variable existe avant de l'utiliser
                if (window[chartVarName] && typeof window[chartVarName].resize === "function") {
                    window[chartVarName].resize();
                }
            });
        });

        /**
         * Function: toggleGraphVisibility
         * Purpose: Controls the visibility of each graph container based on the state of the associated checkboxes.
         * 
         * - If a checkbox is checked, it displays the corresponding graph container ('display: block').
         * - If a checkbox is unchecked, it hides the graph container ('display: none').
         * - The chart is only reloaded if its checkbox was explicitly toggled.
         */
        function toggleGraphVisibility(event) {
            // Identify which checkbox triggered the event
            const changedCheckbox = event?.target || null;
            
            // Define an array of objects mapping each graph container to its checkbox and chart ID
            const chartData = [
                { containerId: 'DailyDataContainer', chartId: 'DailyData', checkboxName: 'by_day' },
                { containerId: 'MonthlyDataContainer', chartId: 'MonthlyData', checkboxName: 'by_month' },
                { containerId: 'YearlyDataContainer', chartId: 'YearlyData', checkboxName: 'by_year' },
                { containerId: 'SeasonalDataContainer', chartId: 'SeasonalData', checkboxName: 'by_season' }
            ];

            // Iterate through each chart configuration
            chartData.forEach(({ containerId, chartId, checkboxName }) => {
                const checkbox = document.querySelector(`input[name="${checkboxName}"]`);
                const container = document.getElementById(containerId);

                // Toggle visibility based on checkbox state
                container.style.display = checkbox.checked ? 'block' : 'none';

                // Reload chart ONLY if the event was triggered by this specific checkbox
                if (changedCheckbox && changedCheckbox.name === checkboxName && checkbox.checked) {
                    const darkMode = document.getElementById(`${chartId}DarkModeSwitch`)?.checked || false;
                    reloadChart(chartId, darkMode ? 'dark' : 'light');
                }
            });
        }

        // Initial setup to apply visibility settings on page load
        toggleGraphVisibility();

        // Attach event listeners to all checkboxes
        document.querySelectorAll('.checkbox-group input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', toggleGraphVisibility);
        });

        // Gérer le resize au changement d'onglet
        document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function () {
                const targetId = this.getAttribute('href').replace('#', '');
                const chartName = targetId.replace('-graph', ''); // Ex: 'DailyData'

                //const chartInstance = window[chartName];
                const chartInstance = chartInstances[chartName];

                if (chartInstance && typeof chartInstance.resize === 'function') {
                    chartInstance.resize();
                } else {
                    console.warn(`chartInstance for ${chartName} is not an ECharts instance or is undefined.`);
                }
            });
        });
    });




</script>
  
</body>
</html>