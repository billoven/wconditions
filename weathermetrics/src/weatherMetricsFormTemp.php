<?php

   // Getting the current file name
    $currentFile = __FILE__;
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $configFilePath = '/etc/weathermetrics/db_config.php';

        // Check if the file exists
        if (file_exists($configFilePath)) {
            // Include the file if it exists
            require_once($configFilePath);
        } else {
            // Display an error message and terminate the script
            die("File: $currentFile - Error: Configuration file '$configFilePath' not found.");
        }

        // Get the selected date range 
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        
        // Retrieve the DB value from the cookie
        $selectedDb = $_COOKIE['selectedDb'] ?? "db1";

        // Database configuration
        if (isset($dbConfigs[$selectedDb])) {
            $dbConfig = $dbConfigs[$selectedDb];
            $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
            if ($conn->connect_error) {

                die("File: $currentFile - Connection failed: " . $conn->connect_error);
            }
        } else {
            die("File: $currentFile - Invalid database selection.");
        }

        // Use placeholders for the table names
        $tabledwc = $dbConfig['tabledwc'];

        // Retrieve the city and period values from the cookie
        $selectedPeriod = $_COOKIE['selectedNormals'] ?? $dbConfig['DefaultNormals'];

        // TableNormals name for the selected Normals period
        $selectedPeriodTable = "Normals_" . $selectedPeriod;

        // Fetch data for the selected date range from the database
        $sql = "SELECT 
                    WC_Date, 
                    WC_TempAvg, 
                    WC_TempHigh,
                     WC_TempLow 
                FROM $tabledwc 
                WHERE WC_Date 
                BETWEEN '$start_date' AND '$end_date'";

        $result = $conn->query($sql);

        $sql1 = "SELECT 
                    DWC.WC_Date, 
                    NORM.DayOfYear, 
                    DWC.WC_TempAvg, 
                    NORM.AvgTempAvg as NormAvgTempAvg, 
                    NORM.AvgTempHigh as NormAvgTempHigh, 
                    NORM.AvgTempLow as NormAvgTempLow 
                FROM $tabledwc DWC 
                JOIN $selectedPeriodTable NORM 
                ON DATE_FORMAT(DWC.WC_Date, '%m-%d') = NORM.DayOfYear 
                WHERE DWC.WC_Date 
                BETWEEN '$start_date' AND '$end_date'" ;
        
        $result1 = $conn->query($sql1);
	
        $dates = [];
        $averages = [];
        $maximums = [];
        $minimums = [];
	    $precipitations = [];
        $AvgTempAvgs = [] ;
        $AvgTempHighs = [] ;
        $AvgTempLows = [] ;

        while ($row = $result->fetch_assoc()) {
            $dates[] = $row['WC_Date'];
            $averages[] = $row['WC_TempAvg'];
            $maximums[] = $row['WC_TempHigh'];
            $minimums[] = $row['WC_TempLow'];
        }

        while ($row1 = $result1->fetch_assoc()) {
            $AvgTempAvgs[] = $row1['NormAvgTempAvg'];
            $AvgTempHighs[] = $row1['NormAvgTempHigh'];
            $AvgTempLows[] = $row1['NormAvgTempLow'];
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
                                $tabledwc
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
        'AvgTempAvgs' => $AvgTempAvgs,
        'AvgTempHighs' => $AvgTempHighs,
        'AvgTempLows' => $AvgTempLows,
        'movingAverages' => $movingAverages,
        'monthlyAvgLabels' => $monthlyAvgLabels,
        'monthlyAvgData' => $monthlyAvgData
    );

    echo json_encode($responseData);

?>