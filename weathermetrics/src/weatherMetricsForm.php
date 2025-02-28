<?php

// Function to get grouped weather data
function getGroupedData($db, $groupBy, $startDate, $endDate, $metrics, $normalMetrics, $normalsTable) {
    // Determine the grouping column based on the $groupBy value
    $groupColumn = getGroupColumn($groupBy);
    
    // Format the metrics for SQL query
    $metricsColumns = implode(", ", $metrics);
    $normalMetricsColumns = implode(", ", $normalMetrics);

    // SQL query to retrieve the grouped data
    $query = "SELECT $groupColumn AS label, $metricsColumns, $normalMetricsColumns
              FROM DayWeatherConditions d
              LEFT JOIN $normalsTable n 
              ON DATE_FORMAT(d.WC_Date, '%m-%d') = n.DayOfYear
              WHERE d.WC_Date BETWEEN ? AND ? 
              GROUP BY label ORDER BY MIN(d.WC_Date)";

    // Prepare the SQL statement
    if ($stmt = $db->prepare($query)) {
        // Bind parameters
        $stmt->bind_param('ss', $startDate, $endDate);

        // Execute the query
        $stmt->execute();

        // Fetch the results
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Close the prepared statement
        $stmt->close();

        // Round numerical values to 1 decimal place
        return array_map(function ($row) {
            foreach ($row as $key => $value) {
                if (is_numeric($value)) {
                    $row[$key] = round($value, 1);
                }
            }
            return $row;
        }, $results);
    } else {
        die('Error preparing statement: ' . $db->error);
    }
}

// Function to get the grouping column based on the $groupBy value
function getGroupColumn($groupBy) {
    switch ($groupBy) {
        case 'by_day':
            return 'DATE(WC_Date)';
        case 'by_month':
            return 'DATE_FORMAT(WC_Date, "%Y-%m")';
        case 'by_year':
            return 'YEAR(WC_Date)';
        case 'by_season':
            return "CONCAT(
                        CASE 
                            WHEN MONTH(WC_Date) IN (12, 1, 2) THEN 'Winter'
                            WHEN MONTH(WC_Date) IN (3, 4, 5) THEN 'Spring'
                            WHEN MONTH(WC_Date) IN (6, 7, 8) THEN 'Summer'
                            WHEN MONTH(WC_Date) IN (9, 10, 11) THEN 'Autumn'
                        END,
                        ' ',
                        CASE 
                            WHEN MONTH(WC_Date) = 12 THEN YEAR(WC_Date) + 1
                            ELSE YEAR(WC_Date)
                        END
                    )";
        default:
            return 'DATE(WC_Date)';
    }
}

// Function to calculate moving average
function calculateMovingAverage($data, $windowSize) {
    $movingAverages = [];
    $dataSize = count($data);

    for ($i = 0; $i < $dataSize; $i++) {
        $startIndex = max(0, $i - $windowSize + 1);
        $window = array_slice($data, $startIndex, $i - $startIndex + 1);
        $average = array_sum($window) / count($window);
        $movingAverages[] = round($average, 1); // Round to 1 decimal place
    }

    return $movingAverages;
}

// Function to get moving average of daily temperatures
function getMovingAverageByDay($db, $groupBy, $startDate, $endDate, $metrics, $normalMetrics, $normalsTable, $windowSize) {
    $data = getGroupedData($db, $groupBy, $startDate, $endDate, $metrics, $normalMetrics, $normalsTable);
    if (empty($data)) {
        return [];
    }

    // Extract the 'avg_temp' values from the data
    $avgTemps = array_column($data, 'avg_temp');
    
    // Calculate the moving averages
    $movingAverages = calculateMovingAverage($avgTemps, $windowSize);

    return array_map(null, $movingAverages);
}


// Fetch parameters from POST/GET requests or set default values
$start_date = $_POST['start_date'] ?? '2025-01-25';
$end_date = $_POST['end_date'] ?? '2025-02-25';
$metric = $_POST['metric'] ?? $_GET['metric'] ?? 'Temperature';
$selectedDb = $_COOKIE['selectedDb'] ?? "db1";

// Load database configuration file
$configFilePath = '/etc/wconditions/db_config.php';
if (!file_exists($configFilePath)) {
    die("File: $configFilePath not found.");
}
require_once($configFilePath);

// Validate database selection
if (!isset($dbConfigs[$selectedDb])) {
    die("Database configuration not found.");
}

// Establish database connection using mysqli
$dbConfig = $dbConfigs[$selectedDb];
$db = new mysqli(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['database']
);


// Check if the connection failed
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch city and period for normal data from cookies or default values
$selectedCity = $_COOKIE['selectedNormalsCity'] ?? $dbConfig['DefaultNormalsCity'];
$selectedPeriod = $_COOKIE['selectedNormals'] ?? $dbConfig['DefaultNormals'];
$normalsTable = $dbConfig['NormalsDB'] . "." . $selectedCity . "_Normals_" . $selectedPeriod;

// Define the weather metrics based on the selected type
// Define the weather metrics based on the selected metric
switch ($metric) {
    case 'Temperature':
        $metrics = [
            'AVG(d.WC_TempAvg) AS avg_temp',
            'AVG(d.WC_TempHigh) AS max_temp',
            'AVG(d.WC_TempLow) AS min_temp'
        ];
        break;
    case 'Rainfall':
        $metrics = [
            'SUM(WC_PrecipitationSum) AS total_precipitation'
        ];
        break;
    case 'Pressure':
        $metrics = [
            'AVG(WC_PressureAvg) AS avg_pressure',
            'AVG(WC_PressureHigh) AS max_pressure',
            'AVG(WC_PressureLow) AS min_pressure'
        ];
        break;
    default:
        die("Invalid metric type selected.");
}

// Define the corresponding normal weather metrics based on the selected metric
switch ($metric) {
    case 'Temperature':
        $normalMetrics = [
            'AVG(n.AvgTempAvg) AS normal_avg_temp',
            'MAX(n.AvgTempHigh) AS normal_max_temp',
            'MIN(n.AvgTempLow) AS normal_min_temp'
        ];
        break;
    case 'Rainfall':
        $normalMetrics = [
            'SUM(AvgPrecipitationSum) AS normal_total_precipitation',
            'MAX(MaxPrecipitationSum) AS normal_max_precipitation'
        ];
        break;
    case 'Pressure':
        $normalMetrics = [
            'AVG(n.AvgPressureAvg) AS normal_avg_pressure',
            'MAX(n.AvgPressureHigh) AS normal_max_pressure',
            'MIN(n.AvgPressureLow) AS normal_min_pressure'
        ];
        break;
    default:
        die("Invalid metric type selected.");
}


// Initialize weather data retrieval for different time groupings
$groupByOptions = ['by_day', 'by_month', 'by_year', 'by_season'];
$weatherDataArrays = [];

foreach ($groupByOptions as $groupBy) {
    // Fetch grouped data using procedural function
    $groupedData = getGroupedData($db, $groupBy, $start_date, $end_date, $metrics, $normalMetrics, $normalsTable);
    $weatherDataArrays[$groupBy] = $groupedData;
}

// Compute 7-day moving average for daily temperatures
$movingAvgData = isset($weatherDataArrays['by_day']) ? calculateMovingAverage(array_column($weatherDataArrays['by_day'], 'avg_temp'), 7) : [];

// Close the database connection
$db->close();



//print_r($weatherDataArrays);
//echo json_encode($weatherDataJsons, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$responseData=[];

// Dynamically determine which metric to display
switch ($metric) {
    case 'Temperature':
        // Build the JSON response with the necessary data for each graph
        $responseData = array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'dates' => array_column($weatherDataArrays['by_day'], 'label'),
            'averages' => array_column($weatherDataArrays['by_day'], 'avg_temp'),
            'maximums' => array_column($weatherDataArrays['by_day'], 'max_temp'),
            'minimums' => array_column($weatherDataArrays['by_day'], 'min_temp'),
            'AvgTempAvgs' => array_column($weatherDataArrays['by_day'], 'normal_avg_temp'),
            'AvgTempHighs' => array_column($weatherDataArrays['by_day'], 'normal_max_temp'),
            'AvgTempLows' => array_column($weatherDataArrays['by_day'], 'normal_min_temp'),
            'movingAverages' => $movingAvgData,
            'monthlyAvgLabels' => array_column($weatherDataArrays['by_month'], 'label'),
            'monthlyAvgMeanData' => array_column($weatherDataArrays['by_month'], 'avg_temp'),
            'monthlyAvgMaxData' => array_column($weatherDataArrays['by_month'], 'max_temp'),
            'monthlyAvgMinData' =>  array_column($weatherDataArrays['by_month'], 'min_temp'),
            'yearlyAvgLabels' => array_column($weatherDataArrays['by_year'], 'label'),
            'yearlyAvgMeanData' => array_column($weatherDataArrays['by_year'], 'avg_temp'),
            'yearlyAvgMaxData' => array_column($weatherDataArrays['by_year'], 'max_temp'),
            'yearlyAvgMinData' =>  array_column($weatherDataArrays['by_year'], 'min_temp'),
            'seasonalAvgLabels' => array_column($weatherDataArrays['by_season'], 'label'),
            'seasonalAvgMeanData' => array_column($weatherDataArrays['by_season'], 'avg_temp'),
            'seasonalAvgMaxData' => array_column($weatherDataArrays['by_season'], 'max_temp'),
            'seasonalAvgMinData' =>  array_column($weatherDataArrays['by_season'], 'min_temp') 
        );
        break;
    case 'Rainfall': 
        $cumulativeTotal = 0;
        $cumulativePrecipitations = [];
        $cumulNormPrecipitations = [];
        
        foreach (array_column($weatherDataArrays['by_day'], 'total_precipitation') as $p) {
            $cumulativeTotal += $p;
            $cumulativePrecipitations[] = round($cumulativeTotal, 1); // Arrondi à 1 décimale
        }
        $cumulativeTotal = 0;
        foreach (array_column($weatherDataArrays['by_day'], 'normal_total_precipitation') as $p) {
            $cumulativeTotal += $p;
            $cumulNormPrecipitations[] = round($cumulativeTotal, 1); // Arrondi à 1 décimale
        }

        $responseData = array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'dates' => array_column($weatherDataArrays['by_day'], 'label'),
            'precipitations' => array_column($weatherDataArrays['by_day'], 'total_precipitation'),
            'CumulPrecipitations' => $cumulativePrecipitations,
            'CumulNormPrecipitations' => $cumulNormPrecipitations,
            'monthlyLabels' => array_column($weatherDataArrays['by_month'], 'label'),
            'monthlyCumulPrecipitations' => array_column($weatherDataArrays['by_month'], 'total_precipitation'),
            'monthlyCumNormPrecipitations' => array_column($weatherDataArrays['by_month'], 'normal_total_precipitation'),
            'yearlyLabels' => array_column($weatherDataArrays['by_year'], 'label'),
            'yearlyCumulPrecipitations' => array_column($weatherDataArrays['by_year'], 'total_precipitation'),
            'yearlyCumNormPrecipitations' => array_column($weatherDataArrays['by_year'], 'normal_total_precipitation'),
            'seasonalLabels' => array_column($weatherDataArrays['by_season'], 'label'),
            'seasonalCumulPrecipitations' => array_column($weatherDataArrays['by_season'], 'total_precipitation'),
            'seasonalCumNormPrecipitations' => array_column($weatherDataArrays['by_season'], 'normal_total_precipitation')
        );
        break;
    case 'Pressure':
                // Build the JSON response with the necessary data for each graph
                $responseData = array(
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'dates' => array_column($weatherDataArrays['by_day'], 'label'),
                    'averages' => array_column($weatherDataArrays['by_day'], 'avg_pressure'),
                    'maximums' => array_column($weatherDataArrays['by_day'], 'max_pressure'),
                    'minimums' => array_column($weatherDataArrays['by_day'], 'min_pressure'),
                    'AvgPressureAvgs' => array_column($weatherDataArrays['by_day'], 'normal_avg_pressure'),
                    'AvgPressureHighs' => array_column($weatherDataArrays['by_day'], 'normal_max_pressure'),
                    'AvgPressureLows' => array_column($weatherDataArrays['by_day'], 'normal_min_pressure'),
                    'monthlyAvgLabels' => array_column($weatherDataArrays['by_month'], 'label'),
                    'monthlyAvgMeanData' => array_column($weatherDataArrays['by_month'], 'avg_pressure'),
                    'monthlyAvgMaxData' => array_column($weatherDataArrays['by_month'], 'max_pressure'),
                    'monthlyAvgMinData' =>  array_column($weatherDataArrays['by_month'], 'min_pressure'),
                    'yearlyAvgLabels' => array_column($weatherDataArrays['by_year'], 'label'),
                    'yearlyAvgMeanData' => array_column($weatherDataArrays['by_year'], 'avg_pressure'),
                    'yearlyAvgMaxData' => array_column($weatherDataArrays['by_year'], 'max_pressure'),
                    'yearlyAvgMinData' =>  array_column($weatherDataArrays['by_year'], 'min_pressure'),
                    'seasonalAvgLabels' => array_column($weatherDataArrays['by_season'], 'label'),
                    'seasonalAvgMeanData' => array_column($weatherDataArrays['by_season'], 'avg_pressure'),
                    'seasonalAvgMaxData' => array_column($weatherDataArrays['by_season'], 'max_pressure'),
                    'seasonalAvgMinData' =>  array_column($weatherDataArrays['by_season'], 'min_pressure') 
                );
        break;
    case 'Hygrometry':

        break;
    default:
        echo "<p class='text-danger'>No valid metric selected.</p>";
        break;
}

echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

?>