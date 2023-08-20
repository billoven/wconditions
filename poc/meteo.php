<!DOCTYPE html>
<html>
<head>
    <title>Web 2.0-like Body Style</title>
    <title>Temperature Graph</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="styles.css">

</head>
<body>
    <div class="container">
      <h2>Select Date Range</h2>
      <form method="POST">
        Start Date: <input type="date" name="start_date" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">
        End Date: <input type="date" name="end_date" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
          <input type="submit" value="Generate Graph">
      </form>
   </div>
    <div class="container">
        <h2>Daily Temperatures Graph</h2>
        <div id="DailyTempGraphContainer">
            <canvas id="DailyTempChart" width="800" height="400"></canvas>
        </div>
    </div>
    <div class="container">
        <h2>Monthly Temperatures Graph</h2>
        <div id="MonthlyTempGraphContainer">
    	   <canvas id="MonthlyTempChart" width="800" height="400"></canvas>
        </div>
    </div>

    <div class="container">
        <h2>Daily rain fall and cumulative of the period</h2>
        <div id="precipitationGraphContainer">
    	   <canvas id="precipitationChart" width="800" height="400"></canvas>
        </div>
    </div>
            <!-- New container for the comparison graph -->
            <div class="container">
            <h2>Comparison Graph</h2>
            <div id="comparisonGraphContainer">
                <canvas id="comparisonChart" width="800" height="400"></canvas>
            </div>
        </div>

        <!-- Form for selecting periods and data type for comparison -->
        <div class="container">
            <h2>Compare Two Periods</h2>
            <form method="POST" class="comparison-form">
                <label for="start_date_1">Start Date (Period 1):</label>
                <input type="date" name="start_date_1" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['start_date_1']) ? $_POST['start_date_1'] : ''; ?>">
                
                <label for="end_date_1">End Date (Period 1):</label>
                <input type="date" name="end_date_1" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['end_date_1']) ? $_POST['end_date_1'] : ''; ?>">
                
                <label for="start_date_2">Start Date (Period 2):</label>
                <input type="date" name="start_date_2" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['start_date_2']) ? $_POST['start_date_2'] : ''; ?>">
                
                <label for="end_date_2">End Date (Period 2):</label>
                <input type="date" name="end_date_2" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['end_date_2']) ? $_POST['end_date_2'] : ''; ?>">
                
                <label for="data_type">Data Type:</label>
                <select name="data_type" required>
                    <option value="average">Average Temperature</option>
                    <option value="maximum">Maximum Temperature</option>
                    <option value="minimum">Minimum Temperature</option>
                    <option value="precipitation">Rainfall</option>
                </select>

                <input type="submit" value="Generate Comparison Graph">
            </form>
        </div>

    </div>

    <?php
    // Database configuration
    $host = '192.168.17.10';
    $username = 'admin';
    $password = 'Z0uZ0u0!';
    $database = 'VillebonWeatherReport';

    // Connect to the database
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: connection to the database" . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the selected date range
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Debugging: Display the selected date range
        echo "Selected Date Range: $start_date - $end_date<br>";

        // Fetch data for the selected date range from the database
        $sql = "SELECT WC_Date, WC_TempAvg, WC_TempHigh, WC_TempLow, WC_PrecipitationSum FROM DayWeatherConditions WHERE WC_Date BETWEEN '$start_date' AND '$end_date'";
        $result = $conn->query($sql);
	
	// Debugging: Display the generated SQL query
        echo "SQL Query: $sql<br>";

        echo "Result: "; print_r($result); echo "<br>";

        $dates = [];
        $averages = [];
        $maximums = [];
        $minimums = [];
	$precipitations = [];
        $cumulativePrecipitations = [];
        $cumulativeSum = 0;

        while ($row = $result->fetch_assoc()) {
            $dates[] = $row['WC_Date'];
            $averages[] = $row['WC_TempAvg'];
            $maximums[] = $row['WC_TempHigh'];
            $minimums[] = $row['WC_TempLow'];
	    $precipitations[] = $row['WC_PrecipitationSum'];
            $cumulativeSum += $row['WC_PrecipitationSum'];
            $cumulativePrecipitations[] = $cumulativeSum;
        }
	// Calculate moving average for the average temperature
        $movingAverages = calculateMovingAverage($averages, 7);

        // Fetch monthly average temperatures for the given period
        $monthlyAvgData = [];
        $monthlyAvgLabels = [];
        $monthlyAvgQuery = "SELECT
                                DATE_FORMAT(WC_Date, '%Y-%m') AS Month,
                                AVG(WC_TempAvg) AS AvgTemp,
                                AVG(WC_TempHigh) AS MaxTemp,
                                AVG(WC_TempLow) AS MinTemp
                            FROM
                                DayWeatherConditions
                            WHERE
                                WC_Date BETWEEN '$start_date' AND '$end_date'
                            GROUP BY
                                Month";

        $monthlyAvgResult = $conn->query($monthlyAvgQuery);

        while ($row = $monthlyAvgResult->fetch_assoc()) {
            $monthlyAvgLabels[] = $row['Month'];
            $monthlyAvgData[] = [
                'avg' => $row['AvgTemp'],
                'max' => $row['MaxTemp'],
                'min' => $row['MinTemp']
            ];
        }

        // Close the database connection
        $conn->close();

        // Debugging: Display the fetched data
        echo "Fetched Data:<br>";
        echo "Dates: "; print_r($dates); echo "<br>";
        echo "Averages: "; print_r($averages); echo "<br>";
        echo "Maximums: "; print_r($maximums); echo "<br>";
        echo "Minimums: "; print_r($minimums); echo "<br>";
    }

    // Close the database connection
    $conn->close();


    /**
     * Calculates the moving average of an array.
     *
     * @param array $data The input data array
     * @param int $windowSize The size of the moving window
     * @return array The array of moving averages
     */
    function calculateMovingAverage($data, $windowSize) {
        $movingAverages = [];
        $dataSize = count($data);

        for ($i = 0; $i < $dataSize; $i++) {
            $startIndex = max(0, $i - $windowSize + 1);
            $endIndex = $i + 1;
            $window = array_slice($data, $startIndex, $endIndex - $startIndex);
            $average = array_sum($window) / count($window);
            $movingAverages[] = $average;
        }

        return $movingAverages;
    }

    ?>
    <script>
    
    // Function to handle form submission
    document.querySelector('.comparison-form').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission behavior

        // Get the form input values
        const startDate1 = document.querySelector('input[name="start_date_1"]').value;
        const endDate1 = document.querySelector('input[name="end_date_1"]').value;
        const startDate2 = document.querySelector('input[name="start_date_2"]').value;
        const endDate2 = document.querySelector('input[name="end_date_2"]').value;
        const dataType = document.querySelector('select[name="data_type"]').value;

        // Fetch data for Period 1 from the database
        fetchAndPlotGraph(startDate1, endDate1, dataType, 'Period 1');

        // Fetch data for Period 2 from the database
        fetchAndPlotGraph(startDate2, endDate2, dataType, 'Period 2');
    });

    // Function to fetch data from the database and plot the graph
    function fetchAndPlotGraph(startDate, endDate, dataType, periodLabel) {
        // Modify the URL to the PHP script that fetches data based on the selected dates and data type
        const url = `fetch_data.php?start_date=${startDate}&end_date=${endDate}&data_type=${dataType}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Process the fetched data and update the comparison graph
                updateComparisonGraph(data, periodLabel);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }

    // Function to update the comparison graph
    function updateComparisonGraph(data, periodLabel) {
        const chartData = data.map(entry => entry[dataType]);

        // Update the comparisonChart data and labels
        comparisonChart.data.labels = data.map(entry => entry.WC_Date);
        comparisonChart.data.datasets.push({
            label: `${periodLabel} - ${dataType}`,
            data: chartData,
            borderColor: getRandomColor(), // A function to get a random color (optional)
            backgroundColor: 'rgba(0, 0, 0, 0)', // Set the background color to transparent for lines
            borderWidth: 2,
            fill: false,
            pointRadius: 0 // Hide data points
        });

        // Update the chart and display the new data
        comparisonChart.update();
    }

    // Optional function to generate a random color
    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    var ctx = document.getElementById('DailyTempChart').getContext('2d');
    var temperatureChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates ?? []); ?>,
            datasets: [
                {
                    label: 'Average Temperature',
                    data: <?php echo json_encode($averages ?? []); ?>,
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0, 0, 255, 0.1)',
                    fill: true
                },
                {
                    label: 'Maximum Temperature',
                    data: <?php echo json_encode($maximums ?? []); ?>,
                    borderColor: 'red',
                    backgroundColor: 'rgba(255, 0, 0, 0.1)',
                    fill: true
                },
                {
                    label: 'Minimum Temperature',
                    data: <?php echo json_encode($minimums ?? []); ?>,
                    borderColor: 'green',
                    backgroundColor: 'rgba(0, 255, 0, 0.1)',
                    fill: true
                },
	            {
                    label: 'Moving Average',
                    data: <?php echo json_encode($movingAverages ?? []); ?>,
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

    var monthlyAvgCtx = document.getElementById('MonthlyTempChart').getContext('2d');
    var monthlyAvgChart = new Chart(monthlyAvgCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($monthlyAvgLabels ?? []); ?>,
            datasets: [
                {
                    label: 'Average Temperature',
                    data: <?php echo json_encode(array_column($monthlyAvgData, 'avg')); ?>,
                    backgroundColor: 'blue'
                },
                {
                    label: 'Maximum Temperature',
                    data: <?php echo json_encode(array_column($monthlyAvgData, 'max')); ?>,
                    backgroundColor: 'red'
                },
                {
                    label: 'Minimum Temperature',
                    data: <?php echo json_encode(array_column($monthlyAvgData, 'min')); ?>,
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

    var ctxPrecipitation = document.getElementById('precipitationChart').getContext('2d');
    var precipitationChart = new Chart(ctxPrecipitation, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($dates ?? []); ?>,
            datasets: [
                {
                    label: 'Daily Precipitation (mm)',
                    data: <?php echo json_encode($precipitations ?? []); ?>,
                    backgroundColor: 'rgba(0, 123, 255, 0.7)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 1,
                },
                {
                    label: 'Cumulative Precipitation (mm)',
                    data: <?php echo json_encode($cumulativePrecipitations ?? []); ?>,
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
