<!DOCTYPE html>
<html>
<head>
    <title>Temperature and Rainfall Graphs</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Your existing CSS styles for the body and containers go here... */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
	    /*  background-color: #f2f2f2; */ 
	        background-color: transparent;
    	    background-image: url('cloud.jpg');
	    background-size: cover;
        }

       .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
	    overflow: hidden;
        }
	
        #DailyTempGraphContainer,
        #MonthlyTempGraphContainer,
        #precipitationGraphContainer {
            width: 100%;
            height: 400px;
            margin: 20px 0;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            color: #555;
            text-align: center;
        }
        h2 {
            text-align: center;
        }
        p {
            color: #333;
            line-height: 1.6;
        }
    </style>
</head>
<body>
<?php
class WeatherData
{
    private $host = '192.168.17.10';
    private $username = 'admin';
    private $password = 'Z0uZ0u0!';
    private $database = 'VillebonWeatherReport';
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        if ($this->conn->connect_error) {
            die("Connection failed: connection to the database" . $this->conn->connect_error);
        }
    }

    public function getWeatherData($startDate, $endDate)
    {
        $data = [
            'dates' => [],
            'averages' => [],
            'maximums' => [],
            'minimums' => [],
            'precipitations' => [],
            'cumulativePrecipitations' => [],
            'movingAverages' => [],
            'monthlyAvgData' => [],
            'monthlyAvgLabels' => [],
        ];

        // Fetch data for the selected date range from the database
        $sql = "SELECT WC_Date, WC_TempAvg, WC_TempHigh, WC_TempLow, WC_PrecipitationSum FROM DayWeatherConditions WHERE WC_Date BETWEEN '$startDate' AND '$endDate'";
        $result = $this->conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $data['dates'][] = $row['WC_Date'];
            $data['averages'][] = $row['WC_TempAvg'];
            $data['maximums'][] = $row['WC_TempHigh'];
            $data['minimums'][] = $row['WC_TempLow'];
            $data['precipitations'][] = $row['WC_PrecipitationSum'];
            $cumulativeSum = end($data['cumulativePrecipitations']) + $row['WC_PrecipitationSum'];
            $data['cumulativePrecipitations'][] = $cumulativeSum;
        }

        // Calculate moving average for the average temperature
        $data['movingAverages'] = $this->calculateMovingAverage($data['averages'], 7);

        // Fetch monthly average temperatures for the given period
        $monthlyAvgQuery = "SELECT
                                DATE_FORMAT(WC_Date, '%Y-%m') AS Month,
                                AVG(WC_TempAvg) AS AvgTemp,
                                AVG(WC_TempHigh) AS MaxTemp,
                                AVG(WC_TempLow) AS MinTemp
                            FROM
                                DayWeatherConditions
                            WHERE
                                WC_Date BETWEEN '$startDate' AND '$endDate'
                            GROUP BY
                                Month";

        $monthlyAvgResult = $this->conn->query($monthlyAvgQuery);

        while ($row = $monthlyAvgResult->fetch_assoc()) {
            $data['monthlyAvgLabels'][] = $row['Month'];
            $data['monthlyAvgData'][] = [
                'avg' => $row['AvgTemp'],
                'max' => $row['MaxTemp'],
                'min' => $row['MinTemp']
            ];
        }

        return $data;
    }

    public function closeConnection()
    {
        $this->conn->close();
    }

    private function calculateMovingAverage($data, $windowSize)
    {
        // Your existing implementation of the calculateMovingAverage function...
        /**
         * Calculates the moving average of an array.
         *
         * @param array $data The input data array
         * @param int $windowSize The size of the moving window
        * @return array The array of moving averages
        */

        $movingAverages = [];
        $dataSize = count($data);

        for ($i = 0; $i < $dataSize; $i++) {
            $startIndex = max(0, $i - $windowSize + 1);
            $endIndex = $i + 1;
            $window = array_slice($data, $startIndex, $endIndex - $startIndex);
            $average = array_sum($window) / count($window);
            $movingAverages[] = $average;

        return $movingAverages;
    }

    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    $weatherData = new WeatherData();
    $data = $weatherData->getWeatherData($startDate, $endDate);
    $weatherData->closeConnection();
}
?>
    <div class="container">
        <h2>Select Date Range</h2>
        <form method="POST">
            Start Date: <input type="date" name="start_date" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">
            End Date: <input type="date" name="end_date" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
            <input type="submit" value="Generate Graphs">
        </form>
    </div>

    <div class="container">
        <h2>Daily Temperature Graph</h2>
        <div class="graph-container" id="DailyTempGraphContainer">
            <canvas id="DailyTempChart" width="800" height="400"></canvas>
        </div>
    </div>

    <div class="container">
        <h2>Monthly Temperature Graph</h2>
        <div class="graph-container" id="MonthlyTempGraphContainer">
            <canvas id="MonthlyTempChart" width="800" height="400"></canvas>
        </div>
    </div>

    <div class="container">
        <h2>Precipitation Graph</h2>
        <div class="graph-container" id="precipitationGraphContainer">
            <canvas id="precipitationChart" width="800" height="400"></canvas>
        </div>
    </div>

    <script>
        // Your existing JavaScript code for Chart.js and graph configurations...
          // Function to check if data is available for the charts
    function isDataAvailable(data) {
        return data !== null && data.length > 0;
    }

    // Daily Temperature Graph
    var dailyTempCtx = document.getElementById('DailyTempChart').getContext('2d');
    var dailyTempData = <?php echo isDataAvailable($data['dates']) ? json_encode($data['dates']) : '[]'; ?>;
    var dailyTempAvg = <?php echo isDataAvailable($data['averages']) ? json_encode($data['averages']) : '[]'; ?>;
    var dailyTempMax = <?php echo isDataAvailable($data['maximums']) ? json_encode($data['maximums']) : '[]'; ?>;
    var dailyTempMin = <?php echo isDataAvailable($data['minimums']) ? json_encode($data['minimums']) : '[]'; ?>;
    var movingAvg = <?php echo isDataAvailable($data['movingAverages']) ? json_encode($data['movingAverages']) : '[]'; ?>;

    var dailyTempChart = new Chart(dailyTempCtx, {
        type: 'line',
        data: {
            labels: dailyTempData,
            datasets: [
                {
                    label: 'Average Temperature',
                    data: dailyTempAvg,
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0, 0, 255, 0.1)',
                    fill: true
                },
                {
                    label: 'Maximum Temperature',
                    data: dailyTempMax,
                    borderColor: 'red',
                    backgroundColor: 'rgba(255, 0, 0, 0.1)',
                    fill: true
                },
                {
                    label: 'Minimum Temperature',
                    data: dailyTempMin,
                    borderColor: 'green',
                    backgroundColor: 'rgba(0, 255, 0, 0.1)',
                    fill: true
                },
                {
                    label: 'Moving Average',
                    data: movingAvg,
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
            }
        }
    });

    // Monthly Temperature Graph
    var monthlyTempCtx = document.getElementById('MonthlyTempChart').getContext('2d');
    var monthlyTempLabels = <?php echo isDataAvailable($data['monthlyAvgLabels']) ? json_encode($data['monthlyAvgLabels']) : '[]'; ?>;
    var monthlyTempData = <?php echo isDataAvailable($data['monthlyAvgData']) ? json_encode($data['monthlyAvgData']) : '[]'; ?>;
    var monthlyTempChart = new Chart(monthlyTempCtx, {
        type: 'bar',
        data: {
            labels: monthlyTempLabels,
            datasets: [
                {
                    label: 'Average Temperature',
                    data: monthlyTempData.map(data => data.avg),
                    backgroundColor: 'blue'
                },
                {
                    label: 'Maximum Temperature',
                    data: monthlyTempData.map(data => data.max),
                    backgroundColor: 'red'
                },
                {
                    label: 'Minimum Temperature',
                    data: monthlyTempData.map(data => data.min),
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
            }
        }
    });

    // Precipitation Graph
    var precipitationCtx = document.getElementById('precipitationChart').getContext('2d');
    var precipitationDates = <?php echo isDataAvailable($data['dates']) ? json_encode($data['dates']) : '[]'; ?>;
    var precipitationData = <?php echo isDataAvailable($data['precipitations']) ? json_encode($data['precipitations']) : '[]'; ?>;
    var cumulativePrecipitations = <?php echo isDataAvailable($data['cumulativePrecipitations']) ? json_encode($data['cumulativePrecipitations']) : '[]'; ?>;
    var precipitationChart = new Chart(precipitationCtx, {
        type: 'bar',
        data: {
            labels: precipitationDates,
            datasets: [
                {
                    label: 'Daily Precipitation (mm)',
                    data: precipitationData,
                    backgroundColor: 'rgba(0, 123, 255, 0.7)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 1,
                },
                {
                    label: 'Cumulative Precipitation (mm)',
                    data: cumulativePrecipitations,
                    type: 'line',
                    borderColor: 'orange',
                    borderWidth: 2,
                    fill: false,
                    yAxisID: 'cumulative-y-axis',
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
            }
        }
    });
</script>

</body>
</html>
