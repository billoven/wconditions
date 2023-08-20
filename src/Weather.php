<?php
require_once 'jpgraph/src/jpgraph.php';
require_once 'jpgraph/src/jpgraph_line.php';
require_once 'jpgraph/src/jpgraph_bar.php';

class DatabaseConnection
{
    private $host;
    private $username;
    private $password;
    private $database;

    public function __construct($host, $username, $password, $database)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }

    public function connect()
    {
        $conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
}

class TemperatureDataFetcher
{
    private $databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    public function fetchTemperatureData($startDate, $endDate)
    {
        $conn = $this->databaseConnection->connect();

        $sql = "SELECT WC_Date, WC_TempAvg, WC_TempHigh, WC_TempLow FROM DayWeatherConditions WHERE WC_Date BETWEEN '$startDate' AND '$endDate'";
        $result = $conn->query($sql);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = new TemperatureRecord($row['WC_Date'], $row['WC_TempAvg'], $row['WC_TempHigh'], $row['WC_TempLow']);
        }

        $conn->close();

        return $data;
    }

    public function fetchMonthlyAverageTemperature($startDate, $endDate)
    {
        $conn = $this->databaseConnection->connect();

        $sql = "SELECT 
                    DATE_FORMAT(WC_Date, '%Y-%m') AS Month, 
                    AVG(WC_TempAvg) AS AvgTemp, 
                    MAX(WC_TempHigh) AS MaxTemp, 
                    MIN(WC_TempLow) AS MinTemp 
                FROM 
                    DayWeatherConditions 
                WHERE 
                    WC_Date BETWEEN '$startDate' AND '$endDate' 
                GROUP BY 
                    Month";

        $result = $conn->query($sql);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = new MonthlyAverageTemperature($row['Month'], $row['AvgTemp'], $row['MaxTemp'], $row['MinTemp']);
        }

        $conn->close();

        return $data;
    }
}

class TemperatureRecord
{
    private $date;
    private $average;
    private $maximum;
    private $minimum;

    public function __construct($date, $average, $maximum, $minimum)
    {
        $this->date = $date;
        $this->average = $average;
        $this->maximum = $maximum;
        $this->minimum = $minimum;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getAverage()
    {
        return $this->average;
    }

    public function getMaximum()
    {
        return $this->maximum;
    }

    public function getMinimum()
    {
        return $this->minimum;
    }
}

class MonthlyAverageTemperature
{
    private $month;
    private $average;
    private $maximum;
    private $minimum;

    public function __construct($month, $average, $maximum, $minimum)
    {
        $this->month = $month;
        $this->average = $average;
        $this->maximum = $maximum;
        $this->minimum = $minimum;
    }

    public function getMonth()
    {
        return $this->month;
    }

    public function getAverage()
    {
        return $this->average;
    }

    public function getMaximum()
    {
        return $this->maximum;
    }

    public function getMinimum()
    {
        return $this->minimum;
    }
}

class ChartGenerator
{
    public function generateLineChart($canvasId, $temperatureData)
    {
        $graph = new Graph(800, 400);
        $graph->SetScale('textlin');
        $graph->title->Set('Temperature Data');
        $graph->xaxis->title->Set('Date');
        $graph->yaxis->title->Set('Temperature');

        $temperatureLinePlot = new LinePlot();
        $temperatureLinePlot->SetColor('blue');
        $temperatureLinePlot->SetLegend('Average Temperature');

        $maximumLinePlot = new LinePlot();
        $maximumLinePlot->SetColor('red');
        $maximumLinePlot->SetLegend('Maximum Temperature');

        $minimumLinePlot = new LinePlot();
        $minimumLinePlot->SetColor('green');
        $minimumLinePlot->SetLegend('Minimum Temperature');

        foreach ($temperatureData as $record) {
            $temperatureLinePlot->addData($record->getDate(), $record->getAverage());
            $maximumLinePlot->addData($record->getDate(), $record->getMaximum());
            $minimumLinePlot->addData($record->getDate(), $record->getMinimum());
        }

        $graph->Add($temperatureLinePlot);
        $graph->Add($maximumLinePlot);
        $graph->Add($minimumLinePlot);
        $graph->legend->SetPos(0.1, 0.1);

        $graph->Stroke($canvasId);
    }

    public function generateBarChart($canvasId, $monthlyAvgTemperatureData)
    {
        $graph = new Graph(800, 400);
        $graph->SetScale('textlin');
        $graph->title->Set('Monthly Average Temperature');
        $graph->xaxis->title->Set('Month');
        $graph->yaxis->title->Set('Temperature');

        $avgBarPlot = new BarPlot();
        $avgBarPlot->SetFillColor('blue');
        $avgBarPlot->SetLegend('Average Temperature');

        $maxBarPlot = new BarPlot();
        $maxBarPlot->SetFillColor('red');
        $maxBarPlot->SetLegend('Maximum Temperature');

        $minBarPlot = new BarPlot();
        $minBarPlot->SetFillColor('green');
        $minBarPlot->SetLegend('Minimum Temperature');

        foreach ($monthlyAvgTemperatureData as $record) {
            $avgBarPlot->addData($record->getAverage());
            $maxBarPlot->addData($record->getMaximum());
            $minBarPlot->addData($record->getMinimum());
        }

        $graph->Add($avgBarPlot);
        $graph->Add($maxBarPlot);
        $graph->Add($minBarPlot);
        $graph->legend->SetPos(0.1, 0.1);

        $graph->Stroke($canvasId);
    }
}

// Usage example:

// Database configuration
$host = '192.168.17.10';
$username = 'admin';
$password = 'Z0uZ0u0!';
$database = 'VillebonWeatherReport';

// Get the selected start date and end date from the page
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Create the database connection object
$databaseConnection = new DatabaseConnection($host, $username, $password, $database);

// Create the temperature data fetcher object
$temperatureDataFetcher = new TemperatureDataFetcher($databaseConnection);

// Fetch temperature data for the selected date range
$temperatureData = $temperatureDataFetcher->fetchTemperatureData($startDate, $endDate);

// Fetch monthly average temperature data for the selected date range
$monthlyAvgTemperatureData = $temperatureDataFetcher->fetchMonthlyAverageTemperature($startDate, $endDate);

// Create the chart generator object
$chartGenerator = new ChartGenerator();

// Generate line chart for temperature data
$temperatureChartId = 'temperature_chart';
$chartGenerator->generateLineChart($temperatureChartId, $temperatureData);

// Generate bar chart for monthly average temperature data
$monthlyAvgChartId = 'monthly_average_chart';
$chartGenerator->generateBarChart($monthlyAvgChartId, $monthlyAvgTemperatureData);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Temperature Data Visualization</title>
</head>
<body>
    <h1>Temperature Data Visualization</h1>

    <h2>Temperature Chart</h2>
    <img src="<?php echo $temperatureChartId; ?>.png" alt="Temperature Chart">

    <h2>Monthly Average Chart</h2>
    <img src="<?php echo $monthlyAvgChartId; ?>.png" alt="Monthly Average Chart">

    <!-- Provide the form for selecting start and end dates -->
    <h2>Select Date Range</h2>
    <form method="GET" action="">
        Start Date: <input type="date" name="start_date" value="<?php echo $startDate; ?>"><br>
        End Date: <input type="date" name="end_date" value="<?php echo $endDate; ?>"><br>
        <input type="submit" value="Submit">
    </form>
</body>
</html>

