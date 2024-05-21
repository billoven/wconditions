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

        // Close the database connection
        $conn->close();

    }


    // Return the JSON response with the necessary data for each graph
    $responseData = array(
        'dates' => $dates,
        'AvgPrecipitationSums' => $AvgPrecipitationSums,
        'precipitations' => $precipitations,
        'cumulativePrecipitations' => $cumulativePrecipitations
    );

    echo json_encode($responseData);

?>