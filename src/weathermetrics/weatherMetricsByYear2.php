<?php
    // Common Header for all the weatherMetrics files
    include "weatherMetricsHeader.php";

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Database configuration
    require_once('/etc/weathermetrics/db_config.php'); // Adjust the path accordingly
    $selectedDb = 'db1'; // Change this to the database you want to connect to

if (isset($dbConfigs[$selectedDb])) {
    $dbConfig = $dbConfigs[$selectedDb];
    $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch available years from the database
    $years = array();
    $yearQuery = "SELECT DISTINCT YEAR(WC_Date) AS Year FROM DayWeatherConditions ORDER by Year DESC";
    $yearResult = $conn->query($yearQuery);
    while ($row = $yearResult->fetch_assoc()) {
        $years[] = $row['Year'];
    }

    // Close the database connection
    $conn->close();
}

// Check if the form was submitted
$selected_years = array(); // Initialize the variable

// Check if the form was submitted
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['selected_years'])) {
    $selected_years = $_GET['selected_years'];

    // Database configuration
    require_once('/etc/weathermetrics/db_config.php'); // Adjust the path accordingly
    $selectedDb = 'db1'; // Change this to the database you want to connect to

    if (isset($dbConfigs[$selectedDb])) {
        $dbConfig = $dbConfigs[$selectedDb];
        $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        foreach ($selected_years as $year) {
            // Fetch statistics from the database
            $sql = "SELECT
            AVG(WC_TempAvg) AS AvgTemp,
            MAX(WC_TempAvg) AS MaxAvgTemp,
            MIN(WC_TempAvg) AS MinAvgTemp,
            (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM DayWeatherConditions WHERE YEAR(WC_Date) = '$year' ORDER BY WC_TempAvg ASC LIMIT 1) AS DateMinAvgTemp,
            (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM DayWeatherConditions WHERE YEAR(WC_Date) = '$year' ORDER BY WC_TempAvg DESC LIMIT 1) AS DateMaxAvgTemp,
            SUM(CASE WHEN WC_TempAvg <= 0 THEN 1 ELSE 0 END) AS DaysLessThanEqualTo0,
            SUM(CASE WHEN WC_TempAvg >= 25 THEN 1 ELSE 0 END) AS DaysGreaterThanOrEqualTo25,
            SUM(CASE WHEN WC_TempAvg <= 0 THEN 1 ELSE 0 END) AS DaysLessThanOrEqualTo0,
            SUM(CASE WHEN WC_TempAvg > 0 AND WC_TempAvg < 5 THEN 1 ELSE 0 END) AS DaysGreater0AndLess5,
            SUM(CASE WHEN WC_TempAvg >= 5 AND WC_TempAvg < 10 THEN 1 ELSE 0 END) AS DaysGreaterOrEqual5AndLess10,
            SUM(CASE WHEN WC_TempAvg >= 10 AND WC_TempAvg < 15 THEN 1 ELSE 0 END) AS DaysGreaterOrEqual10AndLess15,
            SUM(CASE WHEN WC_TempAvg >= 15 AND WC_TempAvg < 20 THEN 1 ELSE 0 END) AS DaysGreaterOrEqual15AndLess20,
            SUM(CASE WHEN WC_TempAvg >= 20 THEN 1 ELSE 0 END) AS DaysGreaterThanOrEqualTo20,
            -- Add more similar cases for Minimal temperatures
            AVG(WC_TempLow) AS AvgLowTemp,
            MAX(WC_TempLow) AS MaxLowTemp,
            MIN(WC_TempLow) AS MinLowTemp,
            (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM DayWeatherConditions WHERE YEAR(WC_Date) = '$year' ORDER BY WC_TempLow ASC LIMIT 1) AS DateMinLowTemp,
            (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM DayWeatherConditions WHERE YEAR(WC_Date) = '$year' ORDER BY WC_TempLow DESC LIMIT 1) AS DateMaxLowTemp,
            SUM(CASE WHEN WC_TempLow <= -5 THEN 1 ELSE 0 END) AS DaysLowLessThanEqualToMinus5,
            SUM(CASE WHEN WC_TempLow >= 20 THEN 1 ELSE 0 END) AS DaysLowGreaterThanOrEqualTo20,
            SUM(CASE WHEN WC_TempLow <= 0 THEN 1 ELSE 0 END) AS DaysLowLessThanOrEqualTo0,
            SUM(CASE WHEN WC_TempLow > 0 AND WC_TempLow < 5 THEN 1 ELSE 0 END) AS DaysLowGreater0AndLess5,
            SUM(CASE WHEN WC_TempLow >= 5 AND WC_TempLow < 10 THEN 1 ELSE 0 END) AS DaysLowGreaterOrEqual5AndLess10,
            SUM(CASE WHEN WC_TempLow >= 10 AND WC_TempLow < 15 THEN 1 ELSE 0 END) AS DaysLowGreaterOrEqual10AndLess15,
            SUM(CASE WHEN WC_TempLow >= 15 AND WC_TempLow < 20 THEN 1 ELSE 0 END) AS DaysLowGreaterOrEqual15AndLess20,
            SUM(CASE WHEN WC_TempLow >= 20 THEN 1 ELSE 0 END) AS DaysLowGreaterThanOrEqualTo20,
                                        -- Add more similar cases for Minimal temperatures
            AVG(WC_TempHigh) AS AvgHighTemp,
            MAX(WC_TempHigh) AS MaxHighTemp,
            MIN(WC_TempHigh) AS MinHighTemp,
            (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM DayWeatherConditions WHERE YEAR(WC_Date) = '$year' ORDER BY WC_TempHigh ASC LIMIT 1) AS DateMinHighTemp,
            (SELECT DATE_FORMAT(WC_Date, '%d/%m') FROM DayWeatherConditions WHERE YEAR(WC_Date) = '$year' ORDER BY WC_TempHigh DESC LIMIT 1) AS DateMaxHighTemp,
            SUM(CASE WHEN WC_TempHigh <= 0 THEN 1 ELSE 0 END) AS DaysHighLessThanEqual0,
            SUM(CASE WHEN WC_TempHigh >= 30 THEN 1 ELSE 0 END) AS DaysHighGreaterThanOrEqualTo30,
            SUM(CASE WHEN WC_TempHigh <= 0 THEN 1 ELSE 0 END) AS DaysHighLessThanOrEqualTo0,
            SUM(CASE WHEN WC_TempHigh > 0 AND WC_TempHigh < 5 THEN 1 ELSE 0 END) AS DaysHighGreater0AndLess5,
            SUM(CASE WHEN WC_TempHigh >= 5 AND WC_TempHigh < 10 THEN 1 ELSE 0 END) AS DaysHighGreaterOrEqual5AndLess10,
            SUM(CASE WHEN WC_TempHigh >= 10 AND WC_TempHigh < 15 THEN 1 ELSE 0 END) AS DaysHighGreaterOrEqual10AndLess15,
            SUM(CASE WHEN WC_TempHigh >= 15 AND WC_TempHigh < 20 THEN 1 ELSE 0 END) AS DaysHighGreaterOrEqual15AndLess20,
            SUM(CASE WHEN WC_TempHigh >= 20 THEN 1 ELSE 0 END) AS DaysHighGreaterThanOrEqualTo20,
            SUM(WC_PrecipitationSum) AS YearTotalPrecipit,
            MAX(WC_PrecipitationSum) AS DayMaxPrecipit,
            SUM(CASE WHEN WC_PrecipitationSum >= 20 THEN 1 ELSE 0 END) AS DaysPrecipitGreaterOrEqual20,
            SUM(CASE WHEN WC_PrecipitationSum > 0 AND WC_PrecipitationSum < 1 THEN 1 ELSE 0 END) AS DaysPrecipitLess1,
            SUM(CASE WHEN WC_PrecipitationSum >= 1 AND WC_PrecipitationSum < 5 THEN 1 ELSE 0 END) AS DaysPrecipitGreaterOrEqual1AndLess5,
            SUM(CASE WHEN WC_PrecipitationSum >= 5 AND WC_PrecipitationSum < 10 THEN 1 ELSE 0 END) AS DaysPrecipitGreaterOrEqual5AndLess10,
            SUM(CASE WHEN WC_PrecipitationSum >= 10 THEN 1 ELSE 0 END) AS DaysPrecipitGreaterOrEqual10,
            COUNT(*) AS TotalDays
        FROM DayWeatherConditions
        WHERE YEAR(WC_Date) = '$year'";

            $result = $conn->query($sql);         

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $statistics[$year] = $row;
            }
        }

        // Close the database connection
        $conn->close();
    }
}
?>
<div class="container mt-5">
    <h2 class="mb-4">Climatologic Statistics by Year</h2>
    <div class="dropdown float-end">
      <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
        Select Theme
      </button>
      <ul class="dropdown-menu" id="theme-selector" aria-labelledby="dropdownMenuButton">
        <li><a class="dropdown-item" href="#" onclick="changeTheme('cerulean')">cerulean</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('darkly')">Dark</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('cosmo')">Cosmo</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('cyborg')">Cyborg</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('flatly')">Flatly</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('journal')">Journal</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('litera')">Litera</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('lumen')">Lumen</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('lux')">Lux</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('materia')">Materia</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('minty')">Minty</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('morph')">Morph</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('pulse')">Pulse</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('quartz')">Quartz</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('sandstone')">Sandstone</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('simplex')">Simplex</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('sketchy')">Sketchy</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('slate')">Slate</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('solar')">Solar</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('spacelab')">Spacelab</a></li>
        <li><a class="dropdown-item" href="#" onclick="changeTheme('superhero')">Superhero</a></li>	
        <li><a class="dropdown-item" href="#" onclick="changeTheme('united')">United</a></li>	
        <li><a class="dropdown-item" href="#" onclick="changeTheme('spacelab')">Spacelab</a></li>	
        <li><a class="dropdown-item" href="#" onclick="changeTheme('vapor')">Vapor</a></li>	
        <li><a class="dropdown-item" href="#" onclick="changeTheme('yeti')">Yeti</a></li>		
        <li><a class="dropdown-item" href="#" onclick="changeTheme('zephir')">Zephir</a></li>		
      </ul>
    </div>

    <!-- Rest of your form and table -->
    <form method="GET" action="#statistics" id="year-form">
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
            echo "<td class='small'>{$norm1}</td><td class='small'>N/A</td>";
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
        echo "                      <th><b style='word-wrap: break-word;'>1971-2000<br>Normals</b></th>";
        echo "                      <th><b style='word-wrap: break-word;'>2016-2020<br>Normals</b></th>";

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
                generateStatisticRow('Avg', $statistics, $selected_years, 'AvgTemp', '', 1, '°C',12.1);
                generateStatisticRow('Min', $statistics, $selected_years, 'MinAvgTemp', 'DateMinAvgTemp', 1, '°C', -11.4);
                generateStatisticRow('Max', $statistics, $selected_years, 'MaxAvgTemp','DateMaxAvgTemp', 1, '°C', 29.9);
                generateStatCatHeader('Average Extreme Temperatures', $numberOfYears);
                generateStatisticRow('≤0°', $statistics, $selected_years, 'DaysLessThanEqualTo0', '', 0, '', 9);
                generateStatisticRow('≥25°', $statistics, $selected_years, 'DaysGreaterThanOrEqualTo25', '', 0, '', 5);
                generateStatCatHeader('Average Temperatures Distribution', $numberOfYears);
                generateStatisticRow('≤0°', $statistics, $selected_years, 'DaysLessThanEqualTo0', '', 0, '', 9);
                generateStatisticRow('>0°And<5°', $statistics, $selected_years, 'DaysGreater0AndLess5', '', 0, '', 42);
                generateStatisticRow('≥5°And<10°', $statistics, $selected_years, 'DaysGreaterOrEqual5AndLess10', '', 0, '', 94);
                generateStatisticRow('≥10°And<15°', $statistics, $selected_years, 'DaysGreaterOrEqual10AndLess15', '', 0, '', 89);
                generateStatisticRow('≥15°And<20°', $statistics, $selected_years, 'DaysGreaterOrEqual15AndLess20', '', 0, '', 87);
                generateStatisticRow('≥20°', $statistics, $selected_years, 'DaysGreaterThanOrEqualTo20', '', 0, '', 43);

            break;
            case "Low":
                // Call the function to generate rows for Low temperatures
                generateStatCatHeader('Low Temperatures Year Global Metrics', $numberOfYears);
                generateStatisticRow('Avg', $statistics, $selected_years, 'AvgLowTemp', '', 1, '°C', 8);
                generateStatisticRow('Max', $statistics, $selected_years, 'MaxLowTemp', 'DateMaxLowTemp', 1, '°C', 24);
                generateStatisticRow('Min', $statistics, $selected_years, 'MinLowTemp', 'DateMinLowTemp', 1, '°C', -13.9);
                generateStatCatHeader('Low Extreme Temperatures', $numberOfYears);
                generateStatisticRow('≤ -5°', $statistics, $selected_years, 'DaysLowLessThanEqualToMinus5', '', 0, '', 3);
                generateStatisticRow('≥ 20', $statistics, $selected_years, 'DaysLowGreaterThanOrEqualTo20', '', 0, '', 4);
                generateStatCatHeader('Low Temperatures Distribution', $numberOfYears);
                generateStatisticRow('≤ 0°', $statistics, $selected_years, 'DaysLowLessThanOrEqualTo0', '', 0, '', 25);
                generateStatisticRow('> 0° And < 5°', $statistics, $selected_years, 'DaysLowGreater0AndLess5', '', 0, '', 77);
                generateStatisticRow('≥ 5° And < 10°', $statistics, $selected_years, 'DaysLowGreaterOrEqual5AndLess10', '', 0, '', 106);
                generateStatisticRow('≥ 10° And < 15°', $statistics, $selected_years, 'DaysLowGreaterOrEqual10AndLess15', '', 0, '', 105);
                generateStatisticRow('≥ 15° And < 20°', $statistics, $selected_years, 'DaysLowGreaterOrEqual15AndLess20', '', 0, '', 48);
                generateStatisticRow('≥ 20°', $statistics, $selected_years, 'DaysLowGreaterThanOrEqualTo20', '', 0, '', 4);

            break;
            case "High":
                /// Call the function to generate rows for Low temperatures
                generateStatCatHeader('High Temperatures Year Global Metrics', $numberOfYears);
                generateStatisticRow('Avg', $statistics, $selected_years, 'AvgHighTemp', '', 1, '°C', 15.6);
                generateStatisticRow('Max', $statistics, $selected_years, 'MaxHighTemp', 'DateMaxHighTemp', 1, '°C', 37.3);
                generateStatisticRow('Min', $statistics, $selected_years, 'MinHighTemp', 'DateMinHighTemp', 1, '°C', -10);
                generateStatCatHeader('High Extreme Temperatures', $numberOfYears);
                generateStatisticRow('≤ 0°', $statistics, $selected_years, 'DaysHighLessThanEqual0', '', 0, '', 4);
                generateStatisticRow('≥ 30°', $statistics, $selected_years, 'DaysHighGreaterThanOrEqualTo30', '', 0, '', 9);
                generateStatCatHeader('High Temperatures Distribution', $numberOfYears);
                generateStatisticRow('≤ 0°', $statistics, $selected_years, 'DaysHighGreater0AndLess5', '', 0, '', 4);
                generateStatisticRow('> 0° And < 5°', $statistics, $selected_years, 'DaysHighGreater0AndLess5', '', 0, '', 23);
                generateStatisticRow('≥ 5° And < 10°', $statistics, $selected_years, 'DaysHighGreaterOrEqual5AndLess10', '', 0, '', 64);
                generateStatisticRow('≥ 10° And < 15°', $statistics, $selected_years, 'DaysHighGreaterOrEqual10AndLess15', '', 0, '', 88);
                generateStatisticRow('≥ 15° And < 20°', $statistics, $selected_years, 'DaysHighGreaterOrEqual15AndLess20', '', 0, '', 75);
                generateStatisticRow('≥ 20°', $statistics, $selected_years, 'DaysHighGreaterThanOrEqualTo20', '', 0, '', 111);
            break;
            case "Rainfall":
                // Call the function to generate rows for Precipiatations
                generateStatCatHeader('Rainfall Year Global Metrics', $numberOfYears);
                generateStatisticRow('Sum', $statistics, $selected_years, 'YearTotalPrecipit', '', 1, 'mm', 637);
                generateStatisticRow('Max', $statistics, $selected_years, 'DayMaxPrecipit', '', 1, 'mm', null );
                generateStatCatHeader('Rainfall Extreme', $numberOfYears);
                generateStatisticRow('≥ 20mm', $statistics, $selected_years, 'DaysPrecipitGreaterOrEqual20', '', 0, '', null);
                generateStatCatHeader('Rainfall Year Distribution', $numberOfYears);
                generateStatisticRow('< 1mm', $statistics, $selected_years, 'DaysPrecipitLess1', '', 0, '', null);
                generateStatisticRow('≥ 1 And < 5mm', $statistics, $selected_years, 'DaysPrecipitGreaterOrEqual1AndLess5', '', 0, '', null);
                generateStatisticRow('≥ 5 And < 10mm', $statistics, $selected_years, 'DaysPrecipitGreaterOrEqual5AndLess10', '', 0, '', null);
                generateStatisticRow('≥ 10mm', $statistics, $selected_years, 'DaysPrecipitGreaterOrEqual10', '', 0, '', null);

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

<script>
   // Function to change the theme
   function changeTheme(themeName) {
        const themeLink = document.getElementById('bootstrap-theme');
        themeLink.href = `https://cdn.jsdelivr.net/npm/bootswatch@5.3.1/dist/${themeName}/bootstrap.min.css`;


        sessionStorage.setItem('theme', themeName);

        const Storedtheme = sessionStorage.getItem('theme');
        console.log("Theme stocké:",Storedtheme);
    }

    // Get the theme selector element
    const themeSelector = document.getElementById('theme-selector');

    // Add event listener to the theme selector
    themeSelector.addEventListener('change', function() {
        const selectedTheme = this.value;
        changeTheme(selectedTheme);

    });
</script>
</body>
</html>
