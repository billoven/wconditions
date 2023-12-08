<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Climatologic Statistics with bootstra5.3.1</title>
  <link id="bootstrap-theme" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.1/dist/darkly/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-gradient"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="scripts/weatherMetrics.js"></script>
  <link rel="stylesheet" href="styles/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
 

    <style>
        .table th:first-child,
        .table td:first-child {
        position: sticky;
        left: 0;
        }
    </style>   
</head>
<body class="p-3 m-0 border-0 bd-example m-0 border-0">
    <script>
        // Get the theme value from the session storage
        const theme = sessionStorage.getItem('theme');
        
        // Display the theme value in the console
        console.log("Theme=[", theme, "]");
        
        // Check if a theme is stored in session storage
        if (theme !== undefined && theme !== null) {
            // If a theme is stored, set the theme to the value of the `theme` variable.
            const themeLink = document.getElementById('bootstrap-theme');
            themeLink.href = `https://cdn.jsdelivr.net/npm/bootswatch@5.3.1/dist/${theme}/bootstrap.min.css`;
        }
        else {
            // If no theme is stored, set the default theme (darkly).
            const themeLink = document.getElementById('bootstrap-theme');
            themeLink.href = 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.1/dist/darkly/bootstrap.min.css';
        }
    </script>
    <?php
        // Retrieve the theme value from the URL parameter
        $selectedDb = $_GET['selectedDb'];

        // Now you can use $theme in your PHP code
        echo "SelectedDB received from JavaScript: " . htmlspecialchars($selectedDb);

        // Database configuration
        require_once('/etc/weathermetrics/db_config.php'); // Adjust the path accordingly
        // $selectedDb = 'db1'; // Change this to the database you want to connect to

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
    ?>
    <!-- Release Container -->
    <div class="release-container" id="releaseContainer">
        <!-- Release version will be inserted here -->
    </div>
    <?php
        include "weatherMetricsNavBar.html";
    ?>
       <!-- Your custom JavaScript to update the release version -->
    <script>
        $(document).ready(function () {
            // Fetch release version from release_installed.txt
            $.get('release_installed.txt')
                .done(function (data) {
                    // Update the release container with the release version
                    var releaseContainer = document.getElementById('releaseContainer');
                    releaseContainer.innerHTML = 'Release ' + data.trim();
                })
                .fail(function () {
                    console.error('Error loading release version from release_installed.txt');
                });
        });
    </script>

