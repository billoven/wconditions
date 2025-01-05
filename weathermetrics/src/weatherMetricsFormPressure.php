<?php

   // Getting the current file name
    $currentFile = __FILE__;
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $configFilePath = '/etc/wconditions/db_config.php';

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

        // Check if each checkbox is set (checked) in the form submission
        // If checked, the value will be 'true', otherwise it will default to 'false'
        $by_day = isset($_POST['by_day']) ? true : false;
        $by_month = isset($_POST['by_month']) ? true : false;
        $by_year = isset($_POST['by_year']) ? true : false;
        $by_season = isset($_POST['by_season']) ? true : false;
        
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
                    WC_PressureAvg, 
                    WC_PressureHigh,
                     WC_PressureLow 
                FROM $tabledwc 
                WHERE WC_Date 
                BETWEEN '$start_date' AND '$end_date'";

        $result = $conn->query($sql);

        $sql1 = "SELECT 
                    DWC.WC_Date, 
                    NORM.DayOfYear, 
                    DWC.WC_PressureAvg, 
                    NORM.AvgPressureAvg as NormAvgPressureAvg, 
                    NORM.AvgPressureHigh as NormAvgPressureHigh, 
                    NORM.AvgPressureLow as NormAvgPressureLow 
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
        $AvgPressureAvgs = [] ;
        $AvgPressureHighs = [] ;
        $AvgPressureLows = [] ;

        while ($row = $result->fetch_assoc()) {
            $dates[] = $row['WC_Date'];
            $averages[] = $row['WC_PressureAvg'];
            $maximums[] = $row['WC_PressureHigh'];
            $minimums[] = $row['WC_PressureLow'];
        }

        while ($row1 = $result1->fetch_assoc()) {
            $AvgPressureAvgs[] = $row1['NormAvgPressureAvg'];
            $AvgPressureHighs[] = $row1['NormAvgPressureHigh'];
            $AvgPressureLows[] = $row1['NormAvgPressureLow'];
        }        

	    // Calculate moving average for the average Pressure
        $movingAverages = calculateMovingAverage($averages, 7);

        // Fetch monthly average Pressures for the given period
        $monthlyAvgData = [];
        $monthlyAvgLabels = [];
        $monthlyAvgQuery = "SELECT
                                DATE_FORMAT(WC_Date, '%Y-%m') AS Month,
                                AVG(WC_PressureAvg) AS AvgPressure,
                                AVG(WC_PressureHigh) AS MaxPressure,
                                AVG(WC_PressureLow) AS MinPressure
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
                'avg' => $row['AvgPressure'],
                'max' => $row['MaxPressure'],
                'min' => $row['MinPressure']
            ];
        }

        // Check if the 'by_year' checkbox is selected
        //if (isset($_POST['by_year']) && $_POST['by_year'] === 'true') {
            // Fetch annual average Pressures for the selected period
            $yearlyAvgData = [];
            $yearlyAvgLabels = [];
            
            $yearlyAvgQuery = "SELECT
                                DATE_FORMAT(WC_Date, '%Y') AS Year,
                                AVG(WC_PressureAvg) AS AvgPressure,
                                AVG(WC_PressureHigh) AS MaxPressure,
                                AVG(WC_PressureLow) AS MinPressure
                            FROM
                                $tabledwc
                            WHERE
                                WC_Date BETWEEN '$start_date' AND '$end_date'
                            GROUP BY
                                Year";

            $yearlyAvgResult = $conn->query($yearlyAvgQuery);

            while ($row = $yearlyAvgResult->fetch_assoc()) {
                $yearlyAvgLabels[] = $row['Year'];
                $yearlyAvgData[] = [
                    'avg' => $row['AvgPressure'],
                    'max' => $row['MaxPressure'],
                    'min' => $row['MinPressure']
                ];
            }
        //}

        // Check if the 'by_season' checkbox is selected
        //if (isset($_POST['by_season']) && $_POST['by_season'] === 'true') {
            // Fetch seasonal average Pressures for the selected period
            $seasonalAvgData = [];
            $seasonalAvgLabels = [];
            
            $seasonalAvgQuery = "SELECT
                                    CONCAT(
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
                                    ) AS Season,
                                    AVG(WC_PressureAvg) AS AvgPressure,
                                    AVG(WC_PressureHigh) AS MaxPressure,
                                    AVG(WC_PressureLow) AS MinPressure
                                FROM
                                    $tabledwc
                                WHERE
                                    WC_Date BETWEEN '$start_date' AND '$end_date'
                                GROUP BY
                                    Season
                                ORDER BY
                                    MIN(WC_Date)";  // Ensure seasons are ordered chronologically

            $seasonalAvgResult = $conn->query($seasonalAvgQuery);

            while ($row = $seasonalAvgResult->fetch_assoc()) {
                $seasonalAvgLabels[] = $row['Season'];
                $seasonalAvgData[] = [
                    'avg' => $row['AvgPressure'],
                    'max' => $row['MaxPressure'],
                    'min' => $row['MinPressure']
                ];
            }

        //}

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
        'start_date' => $start_date,
        'end_date' => $end_date,
        'dates' => $dates,
        'averages' => $averages,
        'maximums' => $maximums,
        'minimums' => $minimums,
        'AvgPressureAvgs' => $AvgPressureAvgs,
        'AvgPressureHighs' => $AvgPressureHighs,
        'AvgPressureLows' => $AvgPressureLows,
        'movingAverages' => $movingAverages,
        'monthlyAvgLabels' => $monthlyAvgLabels,
        'monthlyAvgData' => $monthlyAvgData,
        'yearlyAvgLabels' => $yearlyAvgLabels,
        'yearlyAvgData' => $yearlyAvgData,
        'seasonalAvgLabels' => $seasonalAvgLabels,
        'seasonalAvgData' => $seasonalAvgData

        
    );

    echo json_encode($responseData);

?>