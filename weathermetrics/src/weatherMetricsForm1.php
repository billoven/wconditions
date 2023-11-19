<?php

    // Database configuration
    require_once('/etc/weathermetrics/db_config.php'); // Adjust the path accordingly
    $selectedDb = 'db1'; // Change this to the database you want to connect to
    if (isset($dbConfigs[$selectedDb])) {
        $dbConfig = $dbConfigs[$selectedDb];
        $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    } else {
        die("Invalid database selection.");
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the selected date range
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Fetch data for the selected date range from the database
        $sql = "SELECT WC_Date, WC_TempAvg, WC_TempHigh, WC_TempLow, WC_PrecipitationSum FROM DayWeatherConditions WHERE WC_Date BETWEEN '$start_date' AND '$end_date'";
        $result = $conn->query($sql);
	
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


    // Return the JSON response with the necessary data for each graph
    $responseData = array(
        'dates' => $dates,
        'averages' => $averages,
        'maximums' => $maximums,
        'minimums' => $minimums,
        'movingAverages' => $movingAverages,
        'monthlyAvgLabels' => $monthlyAvgLabels,
        'monthlyAvgData' => $monthlyAvgData,
        'precipitations' => $precipitations,
        'cumulativePrecipitations' => $cumulativePrecipitations
    );

    echo json_encode($responseData);

?>