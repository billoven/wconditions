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
                     WC_PrecipitationSum 
                FROM $tabledwc 
                WHERE WC_Date 
                BETWEEN '$start_date' AND '$end_date'";

        $result = $conn->query($sql);

        $sql1 = "SELECT 
                    DWC.WC_Date, 
                    NORM.DayOfYear, 
                    NORM.AvgPrecipitationSum as NormAvgPrecipSum 
                FROM $tabledwc DWC 
                JOIN $selectedPeriodTable NORM 
                ON DATE_FORMAT(DWC.WC_Date, '%m-%d') = NORM.DayOfYear 
                WHERE DWC.WC_Date 
                BETWEEN '$start_date' AND '$end_date'" ;
        
        $result1 = $conn->query($sql1);
	
        $dates = [];
	    $precipitations = [];
        $cumulativePrecipitations = [];
        $AvgPrecipitationSums = [] ;
        $cumulativeSum = 0;

        while ($row = $result->fetch_assoc()) {
            $dates[] = $row['WC_Date'];
	        $precipitations[] = $row['WC_PrecipitationSum'];
            $cumulativeSum += $row['WC_PrecipitationSum'];

            $cumulativePrecipitations[] = $cumulativeSum;
        }

        while ($row1 = $result1->fetch_assoc()) {
            $AvgPrecipitationSums[] = $row1['NormAvgPrecipSum'];
        }        

        // Fetch monthly average temperatures for the given period
        $row=[];
        $monthlyAvgData = [];
        $monthlyAvgLabels = [];
        $monthlyAvgQuery = "SELECT
            DATE_FORMAT(WC_Date, '%Y-%m') AS Month, 
            SUM(WC_PrecipitationSum) AS TotalRainfall 
            FROM $tabledwc 
            WHERE WC_Date BETWEEN '$start_date' AND '$end_date' 
            GROUP BY DATE_FORMAT(WC_Date, '%Y-%m') 
            ORDER BY Month";

        $monthlyAvgResult = $conn->query($monthlyAvgQuery);

        while ($row = $monthlyAvgResult->fetch_assoc()) {
            $monthlyAvgLabels[] = $row['Month'];
            $monthlyAvgData[] = [
                'MonthlyRainFall' => $row['TotalRainfall']
            ];
        }

        // Fetch yearly total rainfall for the given period
        $row = [];
        $yearlyAvgData = [];
        $yearlyAvgLabels = [];
        $yearlyAvgQuery = "SELECT
            DATE_FORMAT(WC_Date, '%Y') AS Year, 
            SUM(WC_PrecipitationSum) AS TotalRainfall 
            FROM $tabledwc 
            WHERE WC_Date BETWEEN '$start_date' AND '$end_date' 
            GROUP BY DATE_FORMAT(WC_Date, '%Y') 
            ORDER BY Year";

        $yearlyAvgResult = $conn->query($yearlyAvgQuery);

        while ($row = $yearlyAvgResult->fetch_assoc()) {
            $yearlyAvgLabels[] = $row['Year'];
            $yearlyAvgData[] = [
                'YearlyRainFall' => $row['TotalRainfall']
            ];
        }

        // Fetch seasonal precipitation data
        $seasonalAvgData = [];
        $seasonalAvgLabels = [];
        $seasonalData = []; // Temporary storage to consolidate seasons

        $seasonalAvgQuery = "SELECT 
            MIN(WC_Date) AS RepresentativeDate, 
            SUM(WC_PrecipitationSum) AS TotalRainfall
        FROM $tabledwc
        WHERE WC_Date BETWEEN '$start_date' AND '$end_date'
        GROUP BY YEAR(WC_Date), MONTH(WC_Date)
        ORDER BY RepresentativeDate";

        $seasonalAvgResult = $conn->query($seasonalAvgQuery);

        while ($row = $seasonalAvgResult->fetch_assoc()) {
            $date = $row['RepresentativeDate'];
            $rainfall = $row['TotalRainfall'];

            // Determine season based on the date
            $month = (int)date('m', strtotime($date));
            $year = (int)date('Y', strtotime($date));
            $season = '';

            if ($month == 12) {
                $season = "Winter " . $year + 1; // December belongs to the next year's winter
            } elseif (in_array($month, [1, 2])) {
                $season = "Winter " . $year;
            } elseif (in_array($month, [3, 4, 5])) {
                $season = "Spring " . $year;
            } elseif (in_array($month, [6, 7, 8])) {
                $season = "Summer " . $year;
            } elseif (in_array($month, [9, 10, 11])) {
                $season = "Autumn " . $year;
            }

            // Consolidate seasonal data
            if (!isset($seasonalData[$season])) {
                $seasonalData[$season] = 0;
            }
            $seasonalData[$season] += $rainfall; // Sum rainfall for the same season
        }

        // Prepare data for the graph
        foreach ($seasonalData as $season => $rainfall) {
            $seasonalAvgLabels[] = $season;
            $seasonalAvgData[] = [
                'SeasonalRainFall' => $rainfall
            ];
        }

        // Debugging output
        error_log("Processed seasonal labels: " . print_r($seasonalAvgLabels, true));
        error_log("Processed seasonal rainfall data: " . print_r($seasonalAvgData, true));


        // Close the database connection
        $conn->close();

    }


    // Return the JSON response with the necessary data for each graph
    $responseData = array(
        'start_date' => $start_date,
        'end_date' => $end_date,
        'dates' => $dates,
        'AvgPrecipitationSums' => $AvgPrecipitationSums,
        'precipitations' => $precipitations,
        'cumulativePrecipitations' => $cumulativePrecipitations,
        'monthlyAvgLabels' => $monthlyAvgLabels,
        'monthlyAvgData' => $monthlyAvgData,
        'yearlyAvgLabels' => $yearlyAvgLabels,
        'yearlyAvgData' => $yearlyAvgData,
        'seasonalAvgLabels' => $seasonalAvgLabels,
        'seasonalAvgData' => $seasonalAvgData
    );

    echo json_encode($responseData);

?>