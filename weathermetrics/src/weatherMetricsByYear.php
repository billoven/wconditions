<?php
    // Common Header for all the weatherMetrics files
    include "weatherMetricsHeader.php";

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

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
                    AVG({$conditions['TempAvg']}) AS AvgTemp,
                    MAX({$conditions['TempAvg']}) AS MaxAvgTemp,
                    MIN({$conditions['TempAvg']}) AS MinAvgTemp,
                    (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM $tableName WHERE YEAR(WC_Date) = $yearField ORDER BY {$conditions['TempAvg']} ASC LIMIT 1) AS DateMinAvgTemp,
                    (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM $tableName WHERE YEAR(WC_Date) = $yearField ORDER BY {$conditions['TempAvg']} DESC LIMIT 1) AS DateMaxAvgTemp,
                    SUM(CASE WHEN {$conditions['TempAvg']} <= 0 THEN 1 ELSE 0 END) AS DaysLessThanEqualTo0,
                    SUM(CASE WHEN {$conditions['TempAvg']} >= 25 THEN 1 ELSE 0 END) AS DaysGreaterThanOrEqualTo25,
                    SUM(CASE WHEN {$conditions['TempAvg']} <= 0 THEN 1 ELSE 0 END) AS DaysLessThanOrEqualTo0,
                    SUM(CASE WHEN {$conditions['TempAvg']} > 0 AND {$conditions['TempAvg']} < 5 THEN 1 ELSE 0 END) AS DaysGreater0AndLess5,
                    SUM(CASE WHEN {$conditions['TempAvg']} >= 5 AND {$conditions['TempAvg']} < 10 THEN 1 ELSE 0 END) AS DaysGreaterOrEqual5AndLess10,
                    SUM(CASE WHEN {$conditions['TempAvg']} >= 10 AND {$conditions['TempAvg']} < 15 THEN 1 ELSE 0 END) AS DaysGreaterOrEqual10AndLess15,
                    SUM(CASE WHEN {$conditions['TempAvg']} >= 15 AND {$conditions['TempAvg']} < 20 THEN 1 ELSE 0 END) AS DaysGreaterOrEqual15AndLess20,
                    SUM(CASE WHEN {$conditions['TempAvg']} >= 20 THEN 1 ELSE 0 END) AS DaysGreaterThanOrEqualTo20,
                    AVG({$conditions['TempLow']}) AS AvgLowTemp,
                    MAX({$conditions['TempLow']}) AS MaxLowTemp,
                    MIN({$conditions['TempLow']}) AS MinLowTemp,
                    (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM $tableName WHERE YEAR(WC_Date) = $yearField ORDER BY {$conditions['TempLow']} ASC LIMIT 1) AS DateMinLowTemp,
                    (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM $tableName WHERE YEAR(WC_Date) = $yearField ORDER BY {$conditions['TempLow']} DESC LIMIT 1) AS DateMaxLowTemp,
                    SUM(CASE WHEN {$conditions['TempLow']} <= -5 THEN 1 ELSE 0 END) AS DaysLowLessThanEqualToMinus5,
                    SUM(CASE WHEN {$conditions['TempLow']} >= 20 THEN 1 ELSE 0 END) AS DaysLowGreaterThanOrEqualTo20,
                    SUM(CASE WHEN {$conditions['TempLow']} <= 0 THEN 1 ELSE 0 END) AS DaysLowLessThanOrEqualTo0,
                    SUM(CASE WHEN {$conditions['TempLow']} > 0 AND {$conditions['TempLow']} < 5 THEN 1 ELSE 0 END) AS DaysLowGreater0AndLess5,
                    SUM(CASE WHEN {$conditions['TempLow']} >= 5 AND {$conditions['TempLow']} < 10 THEN 1 ELSE 0 END) AS DaysLowGreaterOrEqual5AndLess10,
                    SUM(CASE WHEN {$conditions['TempLow']} >= 10 AND {$conditions['TempLow']} < 15 THEN 1 ELSE 0 END) AS DaysLowGreaterOrEqual10AndLess15,
                    SUM(CASE WHEN {$conditions['TempLow']} >= 15 AND {$conditions['TempLow']} < 20 THEN 1 ELSE 0 END) AS DaysLowGreaterOrEqual15AndLess20,
                    SUM(CASE WHEN {$conditions['TempLow']} >= 20 THEN 1 ELSE 0 END) AS DaysLowGreaterThanOrEqualTo20,
                    AVG({$conditions['TempHigh']}) AS AvgHighTemp,
                    MAX({$conditions['TempHigh']}) AS MaxHighTemp,
                    MIN({$conditions['TempHigh']}) AS MinHighTemp,
                    (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM $tableName WHERE YEAR(WC_Date) = $yearField ORDER BY {$conditions['TempHigh']} ASC LIMIT 1) AS DateMinHighTemp,
                    (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM $tableName WHERE YEAR(WC_Date) = $yearField ORDER BY {$conditions['TempHigh']} DESC LIMIT 1) AS DateMaxHighTemp,
                    SUM(CASE WHEN {$conditions['TempHigh']} <= 0 THEN 1 ELSE 0 END) AS DaysHighLessThanEqual0,
                    SUM(CASE WHEN {$conditions['TempHigh']} >= 30 THEN 1 ELSE 0 END) AS DaysHighGreaterThanOrEqualTo30,
                    SUM(CASE WHEN {$conditions['TempHigh']} <= 0 THEN 1 ELSE 0 END) AS DaysHighLessThanOrEqualTo0,
                    SUM(CASE WHEN {$conditions['TempHigh']} > 0 AND {$conditions['TempHigh']} < 5 THEN 1 ELSE 0 END) AS DaysHighGreater0AndLess5,
                    SUM(CASE WHEN {$conditions['TempHigh']} >= 5 AND {$conditions['TempHigh']} < 10 THEN 1 ELSE 0 END) AS DaysHighGreaterOrEqual5AndLess10,
                    SUM(CASE WHEN {$conditions['TempHigh']} >= 10 AND {$conditions['TempHigh']} < 15 THEN 1 ELSE 0 END) AS DaysHighGreaterOrEqual10AndLess15,
                    SUM(CASE WHEN {$conditions['TempHigh']} >= 15 AND {$conditions['TempHigh']} < 20 THEN 1 ELSE 0 END) AS DaysHighGreaterOrEqual15AndLess20,
                    SUM(CASE WHEN {$conditions['TempHigh']} >= 20 THEN 1 ELSE 0 END) AS DaysHighGreaterThanOrEqualTo20,
                    SUM({$conditions['PrecipitationSum']}) AS YearTotalPrecipit,
                    MAX({$conditions['PrecipitationSum']}) AS DayMaxPrecipit,
                    SUM(CASE WHEN {$conditions['PrecipitationSum']} >= 20 THEN 1 ELSE 0 END) AS DaysPrecipitGreaterOrEqual20,
                    SUM(CASE WHEN {$conditions['PrecipitationSum']} > 0 AND {$conditions['PrecipitationSum']} < 1 THEN 1 ELSE 0 END) AS DaysPrecipitLess1,
                    SUM(CASE WHEN {$conditions['PrecipitationSum']} >= 1 AND {$conditions['PrecipitationSum']} < 5 THEN 1 ELSE 0 END) AS DaysPrecipitGreaterOrEqual1AndLess5,
                    SUM(CASE WHEN {$conditions['PrecipitationSum']} >= 5 AND {$conditions['PrecipitationSum']} < 10 THEN 1 ELSE 0 END) AS DaysPrecipitGreaterOrEqual5AndLess10,
                    SUM(CASE WHEN {$conditions['PrecipitationSum']} >= 10 THEN 1 ELSE 0 END) AS DaysPrecipitGreaterOrEqual10,
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
                    // Add more fields as needed
                ];

                $result = calculateClimateStatistics($conn, 'DayWeatherConditions', $year, $conditions);

                if ($result !== null) {
                    $statistics[$year] = $result;
                }
            }

            // Close the database connection
            $conn->close();
        }
    }
?>
<div class="container mt-5">
    <h2 class="mb-4">Climatologic Statistics by Year</h2>
    <form method="GET" action="#statistics" id="year-form">
        <!-- Add a hidden input field to store the selectedDb value -->
        <input type="hidden" name="selectedDb" value="<?php echo isset($_GET['selectedDb']) ? htmlspecialchars($_GET['selectedDb']) : 'db1'; ?>">

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
    // Function to generate rows for statistics
    function generateStatisticRow($label1, $statisticsArray, $selected_years, $statisticKey, $datekey, $precision, $unit, $norm1) {
        if (isset($statisticsArray[$selected_years[0]][$statisticKey])) {
            echo "<tr>";
            echo "<th class='small'>$label1</th>";
            //echo "<td class='small'>{$norm1}</td><td class='small'>N/A</td>";
            echo "<td class='small'>{$norm1} {$unit}</td>";
            foreach ($selected_years as $year) {
                $value = isset($statisticsArray[$year][$statisticKey]) ? $statisticsArray[$year][$statisticKey] : null;
                $datevalue = isset($statisticsArray[$year][$datekey]) ? $statisticsArray[$year][$datekey] : null;                

                if ($value === null && $datevalue === null) {
                    echo "<td colspan='2'>N/A</td>";
                } elseif ($value !== null && $datevalue !== null) {
                    echo "<td class='small'><center>" . number_format($value, $precision) . " {$unit}</center></td>";
                    echo "<td class='small'><center>" . $datevalue . "</center></td>";
                } elseif ($value === null) {
                    echo "<td colspan='2' class='small'><center>" . $datevalue . "</center></td>";
                } else {
                    echo "<td colspan='2' class='small'><center>" . number_format($value, $precision) . " {$unit}</center></td>"; 
                }
                }
            echo "</tr>";
        }
    }
    function generateStatCatHeader($title, $nbOfYears ) {
        $nbCols= 3 + $nbOfYears * 2;
        echo "<tr><th colspan='{$nbCols}' class='small'>$title</th></tr>";
    }

    function generateStatTable($type, $statistics, $selected_years) {

        // Use global variable for Slected Normals period and city
        global $selectedPeriod;
        global $selectedCity;

        // Use global variable for using the Normals data associated to the period and city of normals
        global $normalsData;
        
        // Generate HTML Header of a Statistic Data Table
        //typeOfTable is a lowercase of the type of the table example : "average"
        $typeOfTable = strtolower($type);
        echo "      <div class='table-responsive'>";
        echo "      <div class='card-header' id='{$typeOfTable}-heading'>";
        echo '          <p class="mb-0">';
        echo "          <button class='btn btn-sm btn-secondary' type='button' data-bs-toggle='collapse' data-bs-target='#{$typeOfTable}-collapse' aria-expanded='false' aria-controls='#{$typeOfTable}-collapse'>";
        echo "          {$type} Climatologic Results";
        echo '          </button>';
        echo '          </p>';
        echo '      </div>';
        echo "      <div id='{$typeOfTable}-collapse' class='collapse show multi-collapse' aria-labelledby='{$typeOfTable}-heading' data-parent='#climatologic-tables'>";
        echo '          <div class="card card-body">';         
        echo "              <table class='table table-bordered table-valign-middle table-hover'";
        echo "                  <thead class='text-center'>";
        echo "                      <tr><th></th>";
        //echo "                      <th><b style='word-wrap: break-word;'>$selectedPeriod<br>$selectedCity<br>Normals</b></th>";
        echo "                        <th style='text-align: center;'><b style='word-wrap: break-word;'>$selectedPeriod<br>$selectedCity<br>Normals</b></th>";
        //echo "                      <th><b style='word-wrap: break-word;'>2016-2020<br>Normals</b></th>";

        foreach ($selected_years as $year) {
            echo "                      <th colspan='2'>$year</th>";
        }
        $numberOfYears = count($selected_years);

        echo "                      </tr>";
        echo "                  </thead>";
        echo "                  <tbody>";

        switch ($type) {
            case "Average":
                // ==============================================================================
                // Parameters of the generateStatisticRow function
                // 1 = Label First Column
                // 2 = Label 2nd Column
                // 3 = Label 3rd Column
                // 4 = Array of Statistics collected in the DataBase for the Selected years
                // 5 = Array of Selected Years
                // 6 = Statistic Key of the Statistics array
                // 7 = Date of the weather data reported as an extreme measurement
                // 8 = Format of decimals, number of decimal behing comma
                // 9 = Unity
                //10 = 1971-2000 Normals or extremes values of the 30 years
                // ==============================================================================
                // Call the function to generate rows for average temperatures
                generateStatCatHeader('Average Temperatures Year Global Metrics', $numberOfYears);
                generateStatisticRow('Avg', $statistics, $selected_years, 'AvgTemp', '', 1, '°C',$normalsData['Avg_TempAvg']);
                generateStatisticRow('Min', $statistics, $selected_years, 'MinAvgTemp', 'DateMinAvgTemp', 1, '°C', $normalsData['Min_TempAvg'][0]['Value']);
                generateStatisticRow('Max', $statistics, $selected_years, 'MaxAvgTemp','DateMaxAvgTemp', 1, '°C', $normalsData['Max_TempAvg'][0]['Value']);
                generateStatCatHeader('Average Extreme Temperatures', $numberOfYears);
                generateStatisticRow('≤0°', $statistics, $selected_years, 'DaysLessThanEqualTo0', '', 0, 'd', $normalsData['Avg_Days_TempAvg_0']);
                generateStatisticRow('≥25°', $statistics, $selected_years, 'DaysGreaterThanOrEqualTo25', '', 0, 'd', $normalsData['Avg_Days_TempAvg_25']);
                generateStatCatHeader('Average Temperatures Distribution', $numberOfYears);
                generateStatisticRow('≤0°', $statistics, $selected_years, 'DaysLessThanEqualTo0', '', 0, 'd', $normalsData['Avg_Days_TempAvg_0']);
                generateStatisticRow('>0°And<5°', $statistics, $selected_years, 'DaysGreater0AndLess5', '', 0, 'd', $normalsData['Avg_Days_TempAvg_0_5']);
                generateStatisticRow('≥5°And<10°', $statistics, $selected_years, 'DaysGreaterOrEqual5AndLess10', '', 0, 'd', $normalsData['Avg_Days_TempAvg_5_10']);
                generateStatisticRow('≥10°And<15°', $statistics, $selected_years, 'DaysGreaterOrEqual10AndLess15', '', 0, 'd', $normalsData['Avg_Days_TempAvg_10_15']);
                generateStatisticRow('≥15°And<20°', $statistics, $selected_years, 'DaysGreaterOrEqual15AndLess20', '', 0, 'd', $normalsData['Avg_Days_TempAvg_15_20']);
                generateStatisticRow('≥20°', $statistics, $selected_years, 'DaysGreaterThanOrEqualTo20', '', 0, 'd', $normalsData['Avg_Days_TempAvg_20']);

            break;
            case "Low":
                // Call the function to generate rows for Low temperatures
                generateStatCatHeader('Low Temperatures Year Global Metrics', $numberOfYears);
                generateStatisticRow('Avg', $statistics, $selected_years, 'AvgLowTemp', '', 1, '°C', $normalsData['Avg_TempLow']);
                generateStatisticRow('Max', $statistics, $selected_years, 'MaxLowTemp', 'DateMaxLowTemp', 1, '°C', $normalsData['Max_TempLow'][0]['Value']);
                generateStatisticRow('Min', $statistics, $selected_years, 'MinLowTemp', 'DateMinLowTemp', 1, '°C', $normalsData['Min_TempLow'][0]['Value']);
                generateStatCatHeader('Low Extreme Temperatures', $numberOfYears);
                generateStatisticRow('≤ -5°', $statistics, $selected_years, 'DaysLowLessThanEqualToMinus5', '', 0, 'd',  $normalsData['Avg_Days_TempLow_-5']);
                generateStatisticRow('≥ 20', $statistics, $selected_years, 'DaysLowGreaterThanOrEqualTo20', '', 0, 'd', $normalsData['Avg_Days_TempLow_20']);
                generateStatCatHeader('Low Temperatures Distribution', $numberOfYears);
                generateStatisticRow('≤ 0°', $statistics, $selected_years, 'DaysLowLessThanOrEqualTo0', '', 0, 'd', $normalsData['Avg_Days_TempLow_0']);
                generateStatisticRow('> 0° And < 5°', $statistics, $selected_years, 'DaysLowGreater0AndLess5', '', 0, 'd', $normalsData['Avg_Days_TempLow_0_5']);
                generateStatisticRow('≥ 5° And < 10°', $statistics, $selected_years, 'DaysLowGreaterOrEqual5AndLess10', '', 0, 'd', $normalsData['Avg_Days_TempLow_5_10']);
                generateStatisticRow('≥ 10° And < 15°', $statistics, $selected_years, 'DaysLowGreaterOrEqual10AndLess15', '', 0, 'd', $normalsData['Avg_Days_TempLow_10_15']);
                generateStatisticRow('≥ 15° And < 20°', $statistics, $selected_years, 'DaysLowGreaterOrEqual15AndLess20', '', 0, 'd', $normalsData['Avg_Days_TempLow_15_20']);
                generateStatisticRow('≥ 20°', $statistics, $selected_years, 'DaysLowGreaterThanOrEqualTo20', '', 0, 'd', $normalsData['Avg_Days_TempLow_20']);

            break;
            case "High":
                /// Call the function to generate rows for Low temperatures
                generateStatCatHeader('High Temperatures Year Global Metrics', $numberOfYears);
                generateStatisticRow('Avg', $statistics, $selected_years, 'AvgHighTemp', '', 1, '°C', $normalsData['Avg_TempHigh']);
                generateStatisticRow('Max', $statistics, $selected_years, 'MaxHighTemp', 'DateMaxHighTemp', 1, '°C', $normalsData['Max_TempHigh'][0]['Value']);
                generateStatisticRow('Min', $statistics, $selected_years, 'MinHighTemp', 'DateMinHighTemp', 1, '°C', $normalsData['Min_TempHigh'][0]['Value']);
                generateStatCatHeader('High Extreme Temperatures', $numberOfYears);
                generateStatisticRow('≤ 0°', $statistics, $selected_years, 'DaysHighLessThanEqual0', '', 0, 'd', $normalsData['Avg_Days_TempHigh_0']);
                generateStatisticRow('≥ 30°', $statistics, $selected_years, 'DaysHighGreaterThanOrEqualTo30', '', 0, 'd', $normalsData['Avg_Days_TempHigh_30']);
                generateStatCatHeader('High Temperatures Distribution', $numberOfYears);
                generateStatisticRow('≤ 0°', $statistics, $selected_years, 'DaysHighGreater0AndLess5', '', 0, 'd', $normalsData['Avg_Days_TempHigh_0']);
                generateStatisticRow('> 0° And < 5°', $statistics, $selected_years, 'DaysHighGreater0AndLess5', '', 0, 'd', $normalsData['Avg_Days_TempHigh_0_5']);
                generateStatisticRow('≥ 5° And < 10°', $statistics, $selected_years, 'DaysHighGreaterOrEqual5AndLess10', '', 0, 'd', $normalsData['Avg_Days_TempHigh_5_10']);
                generateStatisticRow('≥ 10° And < 15°', $statistics, $selected_years, 'DaysHighGreaterOrEqual10AndLess15', '', 0, 'd', $normalsData['Avg_Days_TempHigh_10_15']);
                generateStatisticRow('≥ 15° And < 20°', $statistics, $selected_years, 'DaysHighGreaterOrEqual15AndLess20', '', 0, 'd', $normalsData['Avg_Days_TempHigh_15_20']);
                generateStatisticRow('≥ 20°', $statistics, $selected_years, 'DaysHighGreaterThanOrEqualTo20', '', 0, 'd', $normalsData['Avg_Days_TempHigh_20']);
            break;
            case "Rainfall":
                // Call the function to generate rows for Precipiatations
                generateStatCatHeader('Rainfall Year Global Metrics', $numberOfYears);
                generateStatisticRow('Sum', $statistics, $selected_years, 'YearTotalPrecipit', '', 1, 'mm', $normalsData['Yearly_Avg_Precipitation']);
                generateStatisticRow('Max', $statistics, $selected_years, 'DayMaxPrecipit', '', 1, 'mm',  $normalsData['Max_Daily_Precipitation'][0]['Value'] );
                generateStatCatHeader('Rainfall Extreme', $numberOfYears);
                generateStatisticRow('≥ 20mm', $statistics, $selected_years, 'DaysPrecipitGreaterOrEqual20', '', 0, 'd', $normalsData['Avg_Days_Precipitation_20']);
                generateStatCatHeader('Rainfall Year Distribution (Number of days per year)', $numberOfYears);
                generateStatisticRow('< 1mm', $statistics, $selected_years, 'DaysPrecipitLess1', '', 0, 'd', $normalsData['Avg_Days_Precipitation_0']-$normalsData['Avg_Days_Precipitation_1']);
                generateStatisticRow('≥ 1 And < 5mm', $statistics, $selected_years, 'DaysPrecipitGreaterOrEqual1AndLess5', '', 0, 'd', $normalsData['Avg_Days_Precipitation_1_5']);
                generateStatisticRow('≥ 5 And < 10mm', $statistics, $selected_years, 'DaysPrecipitGreaterOrEqual5AndLess10', '', 0, 'd', $normalsData['Avg_Days_Precipitation_5_10']);
                generateStatisticRow('≥ 10mm', $statistics, $selected_years, 'DaysPrecipitGreaterOrEqual10', '', 0, 'd', $normalsData['Avg_Days_Precipitation_10']);

            break;

            default:
                echo "This is normaly an impossible case!";
        }
                        
        echo "                  </tbody>";
        echo "              </table>";
        echo "          </div>";
        echo "      </div></div>" ;
    }
    ?>
    
    <div class="m-4">
        <div id="statistics" class="card">        
            <?php !empty($statistics) ? generateStatTable('Average', $statistics, $selected_years) : '<p>No data available for the selected years.</p>'; ?>
            <?php !empty($statistics) ? generateStatTable('Low', $statistics, $selected_years) : '<p>No data available for the selected years.</p>'; ?>    
           <?php !empty($statistics) ? generateStatTable('High', $statistics, $selected_years) : '<p>No data available for the selected years.</p>'; ?>
            <?php !empty($statistics) ? generateStatTable('Rainfall', $statistics, $selected_years) : '<p>No data available for the selected years.</p>'; ?>
        </div>
    </div>
</div>
</body>
</html>