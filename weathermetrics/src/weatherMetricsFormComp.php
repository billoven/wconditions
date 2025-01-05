<?php

    // Getting the current file name
    $currentFile = __FILE__;

    // Function to check if two dates have the same number of days
    function isSameNumberOfDays($start_date1, $end_date1, $start_date2, $end_date2)
    {
        $num_days_period1 = floor((strtotime($end_date1) - strtotime($start_date1)) / (60 * 60 * 24));
        $num_days_period2 = floor((strtotime($end_date2) - strtotime($start_date2)) / (60 * 60 * 24));

        return $num_days_period1 === $num_days_period2;
    }
    
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

        // Get the selected periods range
        $start_date1 = $_POST['start_date_1'];
        $end_date1 = $_POST['end_date_1'];
        $start_date2 = $_POST['start_date_2'];
        $end_date2 = $_POST['end_date_2'];
        $selected_data_type = $_POST['weather_data_type'];
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

        // Associative array to map the weather_data_type selected in the form to the real names in the database
        $weather_data_labels = [
            'WC_TempAvg' => 'Average Temperature',
            'WC_TempHigh' => 'Maximum Temperature',
            'WC_TempLow' => 'Minimum Temperature',
            'WC_PrecipitationSum' => 'Precipitation Sum'
        ];

        // Validate the selected weather_data_type
        if (!array_key_exists($selected_data_type, $weather_data_labels)) {
            die("Invalid weather data type selected.");
        }

        // Use the associated label for the selected weather_data_type
        $selected_data_type_label = $weather_data_labels[$selected_data_type];

        // Check if the two periods have the same number of days
        if (!isSameNumberOfDays($start_date1, $end_date1, $start_date2, $end_date2)) {
            die("The two selected periods must have the same number of days.");
        }

        // Fetch data1 for the selected date range from the database
        $sql1 = "SELECT WC_Date, $selected_data_type FROM DayWeatherConditions WHERE WC_Date BETWEEN '$start_date1' AND '$end_date1'";
        $result1 = $conn->query($sql1);

        $dates1 = [];
        $weatherdata1 = [];

        while ($row = $result1->fetch_assoc()) {
            $dates1[] = $row['WC_Date'];
            $weatherdata1[] = $row[$selected_data_type];
        }

        // Fetch data2 for the selected date range from the database
        $sql2 = "SELECT WC_Date, $selected_data_type FROM DayWeatherConditions WHERE WC_Date BETWEEN '$start_date2' AND '$end_date2'";
        $result2 = $conn->query($sql2);

        $dates2 = [];
        $weatherdata2 = [];

        while ($row = $result2->fetch_assoc()) {
            $dates2[] = $row['WC_Date'];
            $weatherdata2[] = $row[$selected_data_type];
        }

        // Close the database connection
        $conn->close();
    }

    $DataType = array($selected_data_type_label);

    //Index of the Days for the 2 periods "0" = first day of period 1 & 2
    $labels = array_keys($weatherdata1);

    // Return the JSON response with the necessary data for each graph
    $responseData = array(
        'IndexOfDay' => $labels,
        'DatesPeriod1' => $dates1,
        'DatesPeriod2' => $dates2,
        'WeatherData1' => $weatherdata1,
        'WeatherData2' => $weatherdata2,
        'DataType' => $DataType
    );
    //console.log($responseData);
    echo json_encode($responseData);
?>