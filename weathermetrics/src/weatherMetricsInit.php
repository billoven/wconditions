<?php
/*
 * This PHP script is included in the header of multiple pages to provide common functionalities:
 * 1. Defines constants for paths to JSON files and the database configuration.
 * 2. Loads the database configuration from an external file.
 * 3. Provides a function (`showAlert()`) to display JavaScript-based alert messages on the page.
 * 4. Reads and decodes a JSON file containing statistical normal data for a selected city and period using the `readNormalsJsonFile()` function.
 * 5. Manages cookies to store user-selected database configurations and normals city/period preferences.
 * 6. Connects to the weather database to retrieve available years of weather data and sets up connections to the "Normals" database.
 * 7. Checks if the necessary database tables for weather normals data exist and retrieves relevant data for the selected city and period.
 * 8. Provides error handling with JavaScript alerts in case of missing files or failed database connections.
 * 
 * The script sets cookies, performs database operations, and handles JSON file loading for dynamic page content rendering.
 */

    // Constante définissant le chemin de base pour les fichiers JSON
    define('NORMALS_JSON_PATH', './normals/');
    
    // Define a constant for the database configuration file path
    define('DB_CONFIG_PATH', '/etc/wconditions/db_config.php');

    // Database configuration
    if (file_exists(DB_CONFIG_PATH)) {
        require_once(DB_CONFIG_PATH);
    } else {
        die("Error: Database configuration file not found at " . DB_CONFIG_PATH);
    }

    function showAlert($title, $message, $type, $draggable, $timeout) {
        // Échappe les caractères spéciaux pour éviter les erreurs JavaScript
        $escapedTitle = addslashes($title);
        $escapedMessage = addslashes($message);
        $escapedType = addslashes($type);
        $escapedDraggable = $draggable ? 'true' : 'false';
    
        // Génère le script JavaScript
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showAlert('$escapedTitle', '$escapedMessage', '$escapedType', $escapedDraggable, $timeout);
            });
        </script>";
    }

    // Read a Normals stats json file associated to a City
    // Check if the file exists
    function readNormalsJsonFile($city, $period) {            
        $filename = NORMALS_JSON_PATH . "StatsNormals_" . str_replace(' ', '', $city) . "_" . $period . ".json";

        if (!file_exists($filename)) {
            // File does not exist, show an error alert and return null
            showAlert("Internal Error", "File does not exist " . $filename, "error", false, "");
            return null;
        }

        // Read the contents of the file
        $json_data = file_get_contents($filename);

        // Decode the JSON data into an associative array
        $data = json_decode($json_data, true);

        // Check if JSON decoding failed
        if ($data === null) {
            // Return null if the JSON data is invalid or cannot be decoded
            return null;
        }

        // Return the decoded JSON data
        return $data;
    }

    // Set the selectedDb cookie to "db1" if it hasn't been set
    if (!isset($_COOKIE['selectedDb'])) {
        setcookie('selectedDb', 'db1', 0, '/');
    }

    // Retrieve the DB value from the cookie
    $selectedDb = $_COOKIE['selectedDb'] ?? "db1";

    // Check configuration available for selectedDb id
    if (isset($dbConfigs[$selectedDb])) {
        $dbConfig = $dbConfigs[$selectedDb];

        // Enable exception handling for mysqli
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Try to connect to the database
        try {
            $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
        } catch (mysqli_sql_exception $e) {
            /// If an error occurs, display an alert and terminate the script execution
            showAlert("Internal Error", "Weather Database connection failed for " . $dbConfig['weatherStation'], "error", false, "");
            exit;
        }
    
        // Echo the complete config for the DB selected (WeatherStation location)
        //echo "Weather Station   : " . $dbConfig['weatherStation'] . "<br>";
        //echo "Host              : " . $dbConfig['host'] . "<br>";
        //echo "Username          : " . $dbConfig['username'] . "<br>";
        //echo "Database          : " . $dbConfig['database'] . "<br>";
        //echo "TableDwc          : " . $dbConfig['tabledwc'] . "<br>";
        //echo "DefaultNormals    : " . $dbConfig['DefaultNormals'] . "<br>";
        //echo "DefaultNormalsCity: " . $dbConfig['DefaultNormalsCity'] . "<br>";
        //echo "NormalsDB         : " . $dbConfig['NormalsDB'] . "<br>";
        ?>
        <script>
            // Retrieve the value of the selectedNormalsCity cookie
            const selectedNormalsCity = getCookie('selectedNormalsCity');

            // Check if the selectedNormalsCity cookie is not set
            if (!selectedNormalsCity) {
                // If the cookie is not set, set it to the default value from $dbConfig
                document.cookie = `selectedNormalsCity=<?php echo $dbConfig['DefaultNormalsCity']; ?>; path=/; SameSite=Lax`;
            }
            // Check if the selectedNormals cookie exists
            const selectedNormalsCookie = getCookie('selectedNormals');

            // If the selectedNormals cookie doesn't exist, set it to the default value from PHP
            if (!selectedNormalsCookie) {
                const defaultNormals = "<?php echo $dbConfig['DefaultNormals']; ?>";
                document.cookie = `selectedNormals=${defaultNormals}; path=/; SameSite=Lax`;
            }
        
        </script>
        <?php

        // Retrieve the city and period values from the cookie
        $selectedCity = $_COOKIE['selectedNormalsCity'] ?? $dbConfig['DefaultNormalsCity'];
        $selectedPeriod = $_COOKIE['selectedNormals'] ?? $dbConfig['DefaultNormals'];
        $selectedStation = $_COOKIE['selectedStation'] ?? $dbConfig['weatherStation'];

        // Fetch available years of weather data from the database
        // Assurez-vous que la variable $dbconfig['tabledwc'] contient bien un nom de table valide.
        $tableDwc = $dbConfig['tabledwc'];

        // Construire la requête en intégrant la variable
        $yearQuery = "SELECT DISTINCT YEAR(WC_Date) AS Year FROM `$tableDwc` ORDER BY Year DESC";

        $years = array();
        $yearResult = $conn->query($yearQuery);
        while ($row = $yearResult->fetch_assoc()) {
            $years[] = $row['Year'];
        }

        // Close the Weather database connection
        $conn->close();

        // Try to connect to the Normals database $dbConfig['NormalsDB']
        try {
            $conndbnorm = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['NormalsDB']);

        } catch (mysqli_sql_exception $e) {
            /// If an error occurs, display an alert and terminate the script execution
            showAlert("Internal Error", "Weather Normals Database connection failed", "error", false, "");
            exit;
        }             
        
        // Build TableNormals name for the selected Normals period
        // <City location>_Normals_<ThirtyYearsPeriod> ex: "ParisMontsouris_Normals_1991_2020"
        $selectedPeriodNormalsTable = $selectedCity."_Normals_" . $selectedPeriod;

        // SQL query to check if the table exists
        $sql = "SHOW TABLES LIKE '" . $selectedPeriodNormalsTable . "'";
        $result = $conndbnorm->query($sql);

        // If the table exists, initialize $TableNormals variable
        if ($result->num_rows > 0) {
            $TableNormals = $selectedPeriodNormalsTable;
        } else {
            // If the table doesn't exist, handle the error accordingly
            showAlert("Internal Warning", "Normals DB table for the selected period does not exist", "warning", false, "");

        }

        // Close the Normals database connection
        $conndbnorm->close();
    }

    // Attempt to read the normals data from the JSON file for the selected city and period
    $normalsData = readNormalsJsonFile($selectedCity, $selectedPeriod);

    // Check if the normals data was loaded successfully
    if ($normalsData) {
        // Normals data loaded successfully, can be used or printed
        // Uncomment the line below to output the data in a formatted JSON format
        // echo json_encode($normalsData, JSON_PRETTY_PRINT);
    } else {
        // Failed to load normals data, show an error message
        // Provide detailed information about the failure (city and period)
        showAlert("Internal Warning", "Failed to load normals summary for city :".$selectedCity ." - Period: ".$selectedPeriod, "warning", false, "");

    }   
?>