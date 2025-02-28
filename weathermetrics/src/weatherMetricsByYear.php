<?php

    // Common Header for all the weatherMetrics files
    include "weatherMetricsHeader.php";

    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);

    if (isset($dbConfigs[$selectedDb])) {
        $dbConfig = $dbConfigs[$selectedDb];
        $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Fetch available years from the database
        $years = array();
        $yearQuery = "SELECT DISTINCT 
                            YEAR(WC_Date) AS Year 
                        FROM DayWeatherConditions 
                        ORDER by Year DESC";
        $yearResult = $conn->query($yearQuery);
        while ($row = $yearResult->fetch_assoc()) {
            $years[] = $row['Year'];
        }

        // Close the database connection
        $conn->close();
    }

    $selected_years = array(); // Initialize the variable

    /**
    * Calculates climate statistics for a given table and year.
    *
    * @param mysqli $conn The MySQLi database connection
    * @param string $tableName The name of the table
    * @param int $yearField The field representing the year
    * @param array $conditions An associative array mapping fields in the table to their corresponding names in the statistics calculation
    * @return array|null An associative array containing climate statistics or null if no data is found
    */
    function calculateClimateStatistics($conn, $tableName, $yearField, $conditions) {
        // Fetch statistics from the database
        $sql = "SELECT
                    AVG({$conditions['TempAvg']}) AS Avg_TempAvg,
                    MAX({$conditions['TempAvg']}) AS Max_TempAvg,
                    MIN({$conditions['TempAvg']}) AS Min_TempAvg,
                    (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM $tableName WHERE YEAR(WC_Date) = $yearField ORDER BY {$conditions['TempAvg']} ASC LIMIT 1) AS Date_Min_TempAvg,
                    (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM $tableName WHERE YEAR(WC_Date) = $yearField ORDER BY {$conditions['TempAvg']} DESC LIMIT 1) AS Date_Max_TempAvg,
                    SUM(CASE WHEN {$conditions['TempAvg']} <= 0 THEN 1 ELSE 0 END) AS Avg_Days_TempAvg_0,
                    SUM(CASE WHEN {$conditions['TempAvg']} >= 25 THEN 1 ELSE 0 END) AS Avg_Days_TempAvg_25,
                    SUM(CASE WHEN {$conditions['TempAvg']} > 0 AND {$conditions['TempAvg']} < 5 THEN 1 ELSE 0 END) AS Avg_Days_TempAvg_0_5,
                    SUM(CASE WHEN {$conditions['TempAvg']} >= 5 AND {$conditions['TempAvg']} < 10 THEN 1 ELSE 0 END) AS Avg_Days_TempAvg_5_10,
                    SUM(CASE WHEN {$conditions['TempAvg']} >= 10 AND {$conditions['TempAvg']} < 15 THEN 1 ELSE 0 END) AS Avg_Days_TempAvg_10_15,
                    SUM(CASE WHEN {$conditions['TempAvg']} >= 15 AND {$conditions['TempAvg']} < 20 THEN 1 ELSE 0 END) AS Avg_Days_TempAvg_15_20,
                    SUM(CASE WHEN {$conditions['TempAvg']} >= 20 THEN 1 ELSE 0 END) AS Avg_Days_TempAvg_20,
                    AVG({$conditions['TempLow']}) AS Avg_TempLow,
                    MAX({$conditions['TempLow']}) AS Max_TempLow,
                    MIN({$conditions['TempLow']}) AS Min_TempLow,
                    (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM $tableName WHERE YEAR(WC_Date) = $yearField ORDER BY {$conditions['TempLow']} ASC LIMIT 1) AS Date_Min_TempLow,
                    (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM $tableName WHERE YEAR(WC_Date) = $yearField ORDER BY {$conditions['TempLow']} DESC LIMIT 1) AS Date_Max_TempLow,
                    SUM(CASE WHEN {$conditions['TempLow']} <= -5 THEN 1 ELSE 0 END) AS Avg_Days_TempLow_minus5,
                    SUM(CASE WHEN {$conditions['TempLow']} <= 0 THEN 1 ELSE 0 END) AS Avg_Days_TempLow_0,
                    SUM(CASE WHEN {$conditions['TempLow']} > 0 AND {$conditions['TempLow']} < 5 THEN 1 ELSE 0 END) AS Avg_Days_TempLow_0_5,
                    SUM(CASE WHEN {$conditions['TempLow']} >= 5 AND {$conditions['TempLow']} < 10 THEN 1 ELSE 0 END) AS Avg_Days_TempLow_5_10,
                    SUM(CASE WHEN {$conditions['TempLow']} >= 10 AND {$conditions['TempLow']} < 15 THEN 1 ELSE 0 END) AS Avg_Days_TempLow_10_15,
                    SUM(CASE WHEN {$conditions['TempLow']} >= 15 AND {$conditions['TempLow']} < 20 THEN 1 ELSE 0 END) AS Avg_Days_TempLow_15_20,
                    SUM(CASE WHEN {$conditions['TempLow']} >= 20 THEN 1 ELSE 0 END) AS Avg_Days_TempLow_20,  
                    AVG({$conditions['TempHigh']}) AS Avg_TempHigh,
                    MAX({$conditions['TempHigh']}) AS Max_TempHigh,
                    MIN({$conditions['TempHigh']}) AS Min_TempHigh,
                    (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM $tableName WHERE YEAR(WC_Date) = $yearField ORDER BY {$conditions['TempHigh']} ASC LIMIT 1) AS Date_Min_TempHigh,
                    (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM $tableName WHERE YEAR(WC_Date) = $yearField ORDER BY {$conditions['TempHigh']} DESC LIMIT 1) AS Date_Max_TempHigh,
                    SUM(CASE WHEN {$conditions['TempHigh']} <= 0 THEN 1 ELSE 0 END) AS Avg_Days_TempHigh_0,
                    SUM(CASE WHEN {$conditions['TempHigh']} >= 30 THEN 1 ELSE 0 END) AS Avg_Days_TempHigh_30,
                    SUM(CASE WHEN {$conditions['TempHigh']} > 0 AND {$conditions['TempHigh']} < 5 THEN 1 ELSE 0 END) AS Avg_Days_TempHigh_0_5,
                    SUM(CASE WHEN {$conditions['TempHigh']} >= 5 AND {$conditions['TempHigh']} < 10 THEN 1 ELSE 0 END) AS Avg_Days_TempHigh_5_10,
                    SUM(CASE WHEN {$conditions['TempHigh']} >= 10 AND {$conditions['TempHigh']} < 15 THEN 1 ELSE 0 END) AS Avg_Days_TempHigh_10_15,
                    SUM(CASE WHEN {$conditions['TempHigh']} >= 15 AND {$conditions['TempHigh']} < 20 THEN 1 ELSE 0 END) AS Avg_Days_TempHigh_15_20,
                    SUM(CASE WHEN {$conditions['TempHigh']} >= 20 THEN 1 ELSE 0 END) AS Avg_Days_TempHigh_20,
                    SUM({$conditions['PrecipitationSum']}) AS Yearly_Avg_Precipitation,
                    MAX({$conditions['PrecipitationSum']}) AS Max_Daily_Precipitation,
                    (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM $tableName WHERE YEAR(WC_Date) = $yearField ORDER BY {$conditions['PrecipitationSum']} DESC LIMIT 1) AS Date_Max_Daily_Precipitation,
                    SUM(CASE WHEN {$conditions['PrecipitationSum']} >= 20 THEN 1 ELSE 0 END) AS Avg_Days_Precipitation_20,
                    SUM(CASE WHEN {$conditions['PrecipitationSum']} > 0 AND {$conditions['PrecipitationSum']} < 1 THEN 1 ELSE 0 END) AS Avg_Days_Precipitation_1,
                    SUM(CASE WHEN {$conditions['PrecipitationSum']} >= 1 AND {$conditions['PrecipitationSum']} < 5 THEN 1 ELSE 0 END) AS Avg_Days_Precipitation_1_5,
                    SUM(CASE WHEN {$conditions['PrecipitationSum']} >= 5 AND {$conditions['PrecipitationSum']} < 10 THEN 1 ELSE 0 END) AS Avg_Days_Precipitation_5_10,
                    SUM(CASE WHEN {$conditions['PrecipitationSum']} >= 10 THEN 1 ELSE 0 END) AS Avg_Days_Precipitation_10,
                    COUNT(*) AS TotalDays
                FROM $tableName
                WHERE YEAR(WC_Date) = $yearField";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row;
        } else {
            return null;
        }
    }

    // Fonction pour restructurer les résultats par année
    function restructureResultsByYear($year, $results) {
        $restructured = [
            $year => []
        ];

        foreach ($results as $key => $value) {
            if (strpos($key, 'Max_') === 0 || strpos($key, 'Min_') === 0) {
                // Identifier la clé associée à la date
                $dateKey = 'Date_' . $key;

                // Ajouter avec le format demandé
                $restructured[$year][$key] = [
                    'Value' => $value,
                    'Date' => isset($results[$dateKey]) ? $results[$dateKey] : null,
                ];
            } elseif (strpos($key, 'Date_') === false) {
                // Ajouter les autres clés telles quelles
                $restructured[$year][$key] = $value;
            }
            
        }

        return $restructured; // Retourner le tableau restructuré complet
    }

    // Check if the form was submitted
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['selected_years'])) {
        $selected_years = $_GET['selected_years'];


        if (isset($dbConfigs[$selectedDb])) {
            $dbConfig = $dbConfigs[$selectedDb];
            $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $tableDwc = $dbConfig['tabledwc'];
            $statistics = [];

            foreach ($selected_years as $year) {
                $conditions = [
                    'TempAvg' => 'WC_TempAvg',
                    'TempLow' => 'WC_TempLow',
                    'TempHigh' => 'WC_TempHigh',
                    'PrecipitationSum' => 'WC_PrecipitationSum'
                    // Ajoutez d'autres champs si nécessaire
                ];
        
                // Appel de la fonction de calcul des statistiques
                $result = calculateClimateStatistics($conn, $tableDwc, $year, $conditions);
        
                if ($result !== null) {
                    // Réorganiser les résultats pour l'année
                    $restructured = restructureResultsByYear($year, $result);
        
                    // Stocker les résultats réorganisés dans $statistics
                    $statistics[$year] = $restructured[$year];
                }
            }

            // Close the database connection
            $conn->close();
        }
    }

?>

<div class="container mt-5">
    <h5 class="mb-4">Climate Statistics by Year : <?php global $selectedStation; echo $selectedStation?></h5>
    <form method="GET" action="#statistics" id="year-form">
        <!-- Add a hidden input field to store the selectedDb value -->
        <input type="hidden" name="selectedDb" value="<?php echo isset($_GET['selectedDb']) ? htmlspecialchars($_GET['selectedDb']) : 'db1'; ?>">
        <input type="hidden" name="metric" value="<?php echo htmlspecialchars($selectedMetric, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="form-group">
            <label for="selected_years">Select Years:  </label>
            <button type="button" class="btn btn-sm btn-secondary" id="select-all-btn">Select All</button>
            <button type="button" class="btn btn-sm btn-secondary" id="unselect-all-btn">Unselect All</button>
            <br>
            <?php          
            // Generate checkboxes for each available year
            foreach ($years as $year) {
                $isChecked = in_array($year, $selected_years) ? 'checked' : '';
                echo "<div class='form-check form-check-inline'>";
                echo "<input class='form-check-input' type='checkbox' name='selected_years[]' value='$year' $isChecked>";
                echo "<label class='form-check-label'>$year</label>";
                echo "</div>";
            }
            ?>

            <br><br>
            
            <button type="submit" class="btn btn-sm btn-primary">Show Statistics</button>
            
            <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target=".multi-collapse" aria-expanded="false" aria-controls="average-collapse low-collapse high-collapse rainfall-collapse">Toggle All Tables</button>
                      
        </div>
    </form>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const selectAllBtn = document.getElementById("select-all-btn");
            const unselectAllBtn = document.getElementById("unselect-all-btn");
            const yearForm = document.getElementById("year-form");

            selectAllBtn.addEventListener("click", function () {
                const checkboxes = yearForm.querySelectorAll("input[type='checkbox']");
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = true;
                });
            });

            unselectAllBtn.addEventListener("click", function () {
                const checkboxes = yearForm.querySelectorAll("input[type='checkbox']");
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });
            });
        });
    </script>

<?php
    function calculateVariationIcon($percentage) {
        if ($percentage > 20) {
            return '<span class="icon-up">&#x2191;</span>';
        } elseif ($percentage > 5) {
            return '<span class="icon-up-oblique">&#x2197;</span>';
        } elseif ($percentage < -20) {
            return '<span class="icon-down">&#x2193;</span>';
        } elseif ($percentage < -5) {
            return '<span class="icon-down-oblique">&#x2198;</span>';
        } else {
            return '<span class="icon-horizontal">&#x2192;</span>';
        }
    }

    function renderMetricsTable($title, $metrics) {

        // Use global variable for Slected Normals period and city
        global $selectedPeriod;
        global $selectedCity;
        
        // Use global variable for using the Normals data associated to the period and city of normals
        global $normalsData;
        global $statistics;

        $typeOfStat = strtolower(explode(' ', $title)[0]);

        //echo "<h3>{$title}</h3>";
        echo "  <div class='table-responsive'>";
        echo "      <div class='card-header' id='{$typeOfStat}-heading'>";
        echo '          <p class="mb-0">';
        echo "          <button class='btn btn-sm btn-secondary' type='button' data-bs-toggle='collapse' data-bs-target='#{$typeOfStat}-collapse' aria-expanded='false' aria-controls='#{$typeOfStat}-collapse'>";
        echo "          {$title} <i class='bi bi-chevron-down'></i>";
        echo '          </button>';
        echo '          </p>';              
        echo '      </div>';
        echo "      <div id='{$typeOfStat}-collapse' class='collapse show multi-collapse' aria-labelledby='{$typeOfStat}-heading' data-parent='#climatologic-tables'>";
        echo '          <div class="card card-body">';         
        echo "              <table class='metrics-table table-bordered table-valign-middle table-hover'";
        echo "                  <thead class='text-center'>";
        echo "                      <tr><th style='text-align: center;'>Metrics</th>";
        echo "                        <th style='text-align: center;'><b style='word-wrap: break-word;'>$selectedPeriod<br>$selectedCity<br>Normals</b></th>";
        if (!empty($statistics)) {
            foreach (array_keys($statistics) as $year) {
                echo "<th class='year-header text-center'>{$year}</th>";
            }
        } 

        echo '</tr></thead>';

        // Table Body
        echo '<tbody>';
        foreach ($metrics as $metricKey => $metricLabel) {
            $showVariation = isset($metricLabel['show_variation']) ? $metricLabel['show_variation'] : true;
            echo '<tr>';
            echo "<td class='metrics-cell'>{$metricLabel['label']}</td>";

            // Handling normal data (normalsData)
            if (isset($normalsData[$metricKey])) {
                $normalValue = $normalsData[$metricKey];

                // If the data is an array containing 'Value' and 'Date' keys
                if (is_array($normalValue) && isset($normalValue[0]['Value']) && isset($normalValue[0]['Date'])) {
                    echo "<td class='normals-cell'>";
                    echo "{$normalValue[0]['Value']} {$metricLabel['unit']} <span style='font-size: smaller; font-style: italic;'>({$normalValue[0]['Date']})</span>";
                    echo "</td>";
                } elseif (is_scalar($normalValue)) {
                    // If the data is a simple number or string
                    echo "<td class='normals-cell'>{$normalValue} {$metricLabel['unit']}</td>";
                } else {
                    // If the structure is unknown
                    echo "<td class='normals-cell'>Unknown</td>";
                }
            } else {
                // If no normal data is available
                echo "<td class='normals-cell'>N/A</td>";
            }

            // Ensure $statistics is a valid array before iterating
            if (is_array($statistics)) {
                foreach ($statistics as $year => $data) {
                    echo "<td class='data-cell'>";

                    // Vérification si la clé existe et est un tableau
                    if (is_array($data[$metricKey]) && isset($data[$metricKey]['Value'])) {
                        $value = round((float)$data[$metricKey]['Value'], 1); // S'assurer que 'Value' est bien un nombre
                        $date = $data[$metricKey]['Date'] ?: 'N/A'; // Si 'Date' est vide, afficher 'N/A'
                        echo "{$value} {$metricLabel['unit']} <span style='font-size: smaller; font-style: italic;'>({$date})</span>";
                    } elseif (is_numeric($data[$metricKey])) {
                        // Si la donnée est un simple nombre
                        $value = round($data[$metricKey], 1); // Arrondir à 1 décimale
                        echo "{$value} {$metricLabel['unit']}";
                    } else {
                        // Si la donnée n'est pas un tableau ou un nombre
                        echo "N/A";
                    }

                    // Calculate and display variation if applicable
                    if (isset($normalsData[$metricKey]) && is_scalar($normalsData[$metricKey])) {
                        if ($normalsData[$metricKey] != 0) { // Avoid division by zero
                            $variation = $showVariation ? round((($value - $normalsData[$metricKey]) / $normalsData[$metricKey]) * 100, 1) : null;
                        } else {
                            $variation = null; // Set variation to null if normal is 0
                        }

                        if ($showVariation && $variation !== null) {
                            echo " <span class='variation'>(" . ($variation > 0 ? '+' : '') . "{$variation}%)</span>";
                            echo calculateVariationIcon($variation);
                        }
                    }
                }
            }
        }

        echo "                  </tbody>";
        echo "              </table>";
        echo "          </div>";
        echo "      </div></div>";
    }

    // Example usage
    $rainfallMetrics = [
        'Yearly_Avg_Precipitation' => ['label' => 'Sum', 'unit' => 'mm', 'show_variation' => true],
        'Max_Daily_Precipitation' => ['label' => 'Max', 'unit' => 'mm', 'show_variation' => false],
        'Avg_Days_Precipitation_20' => ['label' => 'Rainfall Extreme (≥ 20mm)', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_Precipitation_1' => ['label' => 'Rainfall < 1mm', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_Precipitation_1_5' => ['label' => 'Rainfall ≥ 1 And < 5mm', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_Precipitation_5_10' => ['label' => 'Rainfall ≥ 5 And < 10mm', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_Precipitation_10' => ['label' => 'Rainfall ≥ 10mm', 'unit' => 'd', 'show_variation' => true],
    ];

    $tempAvgMetrics = [
        'Avg_TempAvg' => ['label' => 'Avg. Daily Temperature', 'unit' => '°C', 'show_variation' => true],
        'Min_TempAvg' => ['label' => 'Min. daily Average temperature', 'unit' => '°C', 'show_variation' => false],
        'Max_TempAvg' => ['label' => 'Max. daily Average temperature', 'unit' => '°C', 'show_variation' => false],
        'Avg_Days_TempAvg_0' => ['label' => 'Days ≤ 0°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempAvg_25' => ['label' => 'Days ≥ 25°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempAvg_0_5' => ['label' => 'Days > 0°C And < 5°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempAvg_5_10' => ['label' => 'Days ≥ 5°C And < 10°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempAvg_10_15' => ['label' => 'Days ≥ 10°C And < 15°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempAvg_15_20' => ['label' => 'Days ≥ 15°C And < 20°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempAvg_20' => ['label' => 'Days ≥ 20°C', 'unit' => 'd', 'show_variation' => true],
    ];

    $tempLowMetrics = [
        'Avg_TempLow' => ['label' => 'Avg. Daily Low Temperature', 'unit' => '°C', 'show_variation' => true],
        'Min_TempLow' => ['label' => 'Min. Daily low temperature', 'unit' => '°C', 'show_variation' => false],
        'Max_TempLow' => ['label' => 'Max. Daily Low temperature', 'unit' => '°C', 'show_variation' => false],
        'Avg_Days_TempLow_minus5' => ['label' => 'Days low temp ≤ -5°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempLow_20' => ['label' => 'Days low temp ≥ 20°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempLow_0' => ['label' => 'Days low temp ≤ 0°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempLow_0_5' => ['label' => 'Days low temp ≥ 0°C And < 5°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempLow_5_10' => ['label' => 'Days low temp ≥ 5°C And < 10°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempLow_10_15' => ['label' => 'Days low temp ≥ 10°C And < 15°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempLow_15_20' => ['label' => 'Days low temp ≥ 15°C And < 20°C', 'unit' => 'd', 'show_variation' => true]
    ];

    $tempHighMetrics = [
        'Avg_TempHigh' => ['label' => 'Avg. Daily High Temperature', 'unit' => '°C', 'show_variation' => true],
        'Min_TempHigh' => ['label' => 'Min. Daily High temperature', 'unit' => '°C', 'show_variation' => false],
        'Max_TempHigh' => ['label' => 'Max. Daily High temperature', 'unit' => '°C', 'show_variation' => false],
        'Avg_Days_TempHigh_0' => ['label' => 'Days high temp ≤ 0°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempHigh_30' => ['label' => 'Days high temp ≥ 30°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempHigh_0_5' => ['label' => 'Days high temp ≥ 0 °C And < 5°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempHigh_5_10' => ['label' => 'Days high temp ≥ 5°C And < 10°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempHigh_10_15' => ['label' => 'Days high temp ≥ 10°C And < 15°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempHigh_15_20' => ['label' => 'Days high temp ≥ 15°C And < 20°C', 'unit' => 'd', 'show_variation' => true],
        'Avg_Days_TempHigh_20' => ['label' => 'Days high temp ≥ 20°C', 'unit' => 'd', 'show_variation' => true],
    ];

?>
    <div class="m-4">
        <div id="statistics" class="card">       
            <?php renderMetricsTable('Average Daily Temperatures Statistics', $tempAvgMetrics);?>
            <?php renderMetricsTable('Low Daily Temperatures Statistics', $tempLowMetrics);?>
            <?php renderMetricsTable('High Daily Temperatures Statistics', $tempHighMetrics);?>
            <?php renderMetricsTable('Rainfall Statistics', $rainfallMetrics);?>
        </div>
    </div>
</div>
</body>
</html>