<?php
header('Content-Type: application/json');

// Define a constant for the database configuration file path
define('DB_CONFIG_PATH', '/etc/wconditions/db_config.php');

// Database configuration
if (file_exists(DB_CONFIG_PATH)) {
    require_once(DB_CONFIG_PATH);
} else {
    die("Error: Database configuration file not found at " . DB_CONFIG_PATH);
}

// Retrieve the DB value from the cookie
$selectedDb = $_COOKIE['selectedDb'] ?? "db1";

    // Check configuration available for selectedDb id
    if (isset($dbConfigs[$selectedDb])) {
        $dbConfig = $dbConfigs[$selectedDb];

        // Enable exception handling for mysqli
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Retrieve the city and period values from the cookie
        $selectedCity = $_COOKIE['selectedNormalsCity'] ?? $dbConfig['DefaultNormalsCity'];
        $selectedPeriod = $_COOKIE['selectedNormals'] ?? $dbConfig['DefaultNormals'];
        $selectedStation = $_COOKIE['selectedStation'] ?? $dbConfig['weatherStation'];

        // Database Configuration
        $host = $dbConfig['host'];
        $city = $dbConfig['weatherStation'];
        $dbname = $dbConfig['database'];
        $username = $dbConfig['username']; 
        $password = $dbConfig['password'];    
        $latitude = $dbConfig['latitude'];
        $longitude = $dbConfig['longitude'];
        $tablewc = $dbConfig['tablewc'];
        $timezone = $dbConfig['timezone'];

        try {
            // Step 1: Fetch Weather Metrics from Database
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Query the latest weather data
            $stmt = $pdo->prepare("SELECT * FROM `$tablewc` ORDER BY WC_Datetime DESC LIMIT 1");
            $stmt->execute();
            $latestData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$latestData) {
                echo json_encode(['error' => 'No weather data found in the database.']);
                exit;
            }

            // Compute Sunrise and Sunset for the selected weather Station
            // Coordonnées géographiques
            // Date actuelle
            $date = new DateTime('now', new DateTimeZone($timezone));

            // Calcul des informations solaires pour la date et position données
            $sun_info = date_sun_info($date->getTimestamp(), $latitude, $longitude);

            // Convertir les heures UTC en heures locales au format HH:MM
            $sunrise = (new DateTime('@' . $sun_info['sunrise']))->setTimezone(new DateTimeZone($timezone))->format('H:i');
            $sunset = (new DateTime('@' . $sun_info['sunset']))->setTimezone(new DateTimeZone($timezone))->format('H:i');

            // Debugging: Output the retrieved data (remove in production)
            //error_log('Fetched weather data: ' . print_r($latestData, true));
            /* Step 2: Fetch Weather Condition from Weather Underground
            $wundergroundUrl = "https://www.wunderground.com/weather/fr/villebon-sur-yvette/48.70,2.23";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $wundergroundUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $htmlContent = curl_exec($ch);
            curl_close($ch);

            $weatherConditionHTML = '<p>Error fetching weather condition.</p>';
            if ($htmlContent !== false) {
                $dom = new DOMDocument();
                @$dom->loadHTML($htmlContent);
                $xpath = new DOMXPath($dom);
                $conditionDiv = $xpath->query('//div[contains(@class, "condition-icon")]');
                if ($conditionDiv->length > 0) {
                    $weatherConditionHTML = $dom->saveHTML($conditionDiv->item(0));
                }
            } */

            // Step 3: Combine Data into a JSON Response
            $response = [
                'datetime' => $latestData['WC_Datetime'],
                'city' => $city,
                'temp' => $latestData['WC_temp'],
                'humidity' => $latestData['WC_humidity'],
                'precipRate' => $latestData['WC_precipRate'],
                'precipTotal' => $latestData['WC_precipTotal'],
                'pressure' => $latestData['WC_pressure'],
                'windSpeed' => $latestData['WC_windSpeed'],
                'windGust' => $latestData['WC_windGust'],
                'windChill' => $latestData['WC_windChill'],
                'sunrise' => $sunrise,
                'sunset' => $sunset
                //'condition' => $weatherConditionHTML // Weather icon and condition from Weather Underground
            ];

            echo json_encode($response);

        } catch (PDOException $e) {
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['error' => 'Unexpected error: ' . $e->getMessage()]);
            exit;
        }
    }
?>


