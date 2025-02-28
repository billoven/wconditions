<?php
// Class to handle weather data retrieval and processing
class WeatherData {
    private PDO $db; // Database connection
    private string $groupBy; // Grouping criteria (day, month, year, season)
    private string $startDate; // Start date for data retrieval
    private string $endDate; // End date for data retrieval
    private array $metrics; // Weather metrics to retrieve
    private array $normalMetrics; // Normalized weather metrics for comparison
    private string $normalsTable; // Table containing normal weather values

    // Constructor initializes the class with database connection and parameters
    public function __construct(PDO $db, string $groupBy, string $startDate, string $endDate, array $metrics, array $normalMetrics, string $normalsTable) {
        $this->db = $db;
        $this->groupBy = $groupBy;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->metrics = $metrics;
        $this->normalMetrics = $normalMetrics;
        $this->normalsTable = $normalsTable;
    }

    // Retrieves grouped weather data from the database
    public function getGroupedData(): array {
        $groupColumn = $this->getGroupColumn(); // Determine grouping column
        $metricsColumns = implode(", ", $this->metrics); // Format selected metrics
        $normalMetricsColumns = implode(", ", $this->normalMetrics); // Format normal metrics
    
        // SQL query to retrieve data with optional left join to normal values
        $query = "SELECT $groupColumn AS label, $metricsColumns, $normalMetricsColumns
                  FROM DayWeatherConditions d
                  LEFT JOIN $this->normalsTable n 
                  ON DATE_FORMAT(d.WC_Date, '%m-%d') = n.DayOfYear
                  WHERE d.WC_Date BETWEEN :startDate AND :endDate 
                  GROUP BY label ORDER BY MIN(d.WC_Date)";
    
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':startDate', $this->startDate);
        $stmt->bindParam(':endDate', $this->endDate);
        //print_r($query);
        $stmt->execute();
    
        // Fetch all results and round numerical values to 1 decimal place
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function ($row) {
            foreach ($row as $key => $value) {
                if (is_numeric($value)) {
                    $row[$key] = round($value, 1);
                }
            }
            return $row;
        }, $results);
    }
    
    // Returns the grouped data as a JSON string
    public function getGroupedDataAsJson(): string {
        return json_encode($this->getGroupedData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // Determines the SQL column to use for grouping data
    private function getGroupColumn(): string {
        return match ($this->groupBy) {
            'by_day' => 'DATE(WC_Date)',
            'by_month' => 'DATE_FORMAT(WC_Date, "%Y-%m")',
            'by_year' => 'YEAR(WC_Date)',
            'by_season' => "CONCAT(
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
                            )",
            default => 'DATE(WC_Date)',
        };
    }
    
    // Calculates a moving average for a given dataset
    public function calculateMovingAverage(array $data, int $windowSize): array {
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
    
    // Retrieves a moving average of daily temperatures
    public function getMovingAverageByDay(int $windowSize): array {
        $data = $this->getGroupedData();
        if (empty($data)) {
            return [];
        }
    
        $avgTemps = array_column($data, 'avg_temp');
        $labels = array_column($data, 'label');
        $movingAverages = $this->calculateMovingAverage($avgTemps, $windowSize);
    
        return array_map(null, $movingAverages);
    }
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

// Establish database connection using configuration settings
$dbConfig = $dbConfigs[$selectedDb];
$pdo = new PDO(
    "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8",
    $dbConfig['username'],
    $dbConfig['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Fetch city and period for normal data from cookies or default values
$selectedCity = $_COOKIE['selectedNormalsCity'] ?? $dbConfig['DefaultNormalsCity'];
$selectedPeriod = $_COOKIE['selectedNormals'] ?? $dbConfig['DefaultNormals'];
$normalsTable = $dbConfig['NormalsDB'] . "." . $selectedCity . "_Normals_" . $selectedPeriod;

// Define the weather metrics based on the selected type
$metrics = match ($metric) {
    'Temperature' => [
        'AVG(d.WC_TempAvg) AS avg_temp',
        'AVG(d.WC_TempHigh) AS max_temp',
        'AVG(d.WC_TempLow) AS min_temp'
    ],
    'Rainfall' => [
        'SUM(WC_PrecipitationSum) AS total_precipitation'
    ],
    'Pressure' => [
        'AVG(WC_PressureAvg) AS avg_pressure',
        'AVG(WC_PressureHigh) AS max_pressure',
        'AVG(WC_PressureLow) AS min_pressure'
    ],
    default => die("Invalid metric type selected.")
};

// Define the corresponding normal weather metrics
$normalMetrics = match ($metric) {
    'Temperature' => [
        'AVG(n.AvgTempAvg) AS normal_avg_temp',
        'MAX(n.AvgTempHigh) AS normal_max_temp',
        'MIN(n.AvgTempLow) AS normal_min_temp'
    ],
    'Rainfall' => [
        'SUM(AvgPrecipitationSum) AS normal_total_precipitation',
        'MAX(MaxPrecipitationSum) AS normal_max_precipitation'
    ],
    'Pressure' => [
        'AVG(n.AvgPressureAvg) AS normal_avg_pressure',
        'MAX(n.AvgPressureHigh) AS normal_max_pressure',
        'MIN(n.AvgPressureLow) AS normal_min_pressure'
    ],
    default => die("Invalid metric type selected.")
};

// Initialize weather data retrieval for different time groupings
$groupByOptions = ['by_day', 'by_month', 'by_year', 'by_season'];
$weatherDataObjects = [];  
$weatherDataArrays = [];

foreach ($groupByOptions as $groupBy) {
    $weatherData = new WeatherData($pdo, $groupBy, $start_date, $end_date, $metrics, $normalMetrics, $normalsTable);
    $weatherDataObjects[$groupBy] = $weatherData;
    $weatherDataArrays[$groupBy] = $weatherData->getGroupedData();
}

// Compute 7-day moving average for daily temperatures
$movingAvgData = isset($weatherDataObjects['by_day']) ? $weatherDataObjects['by_day']->getMovingAverageByDay(7) : [];

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