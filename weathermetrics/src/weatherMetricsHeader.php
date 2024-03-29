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
        // Function to change the theme
        function changeTheme(themeName) {
        
            // Get the link element for the theme stylesheet
            const themeLink = document.getElementById('bootstrap-theme');
        
            // Update the href attribute with the selected theme
            themeLink.href = `https://cdn.jsdelivr.net/npm/bootswatch@5.3.1/dist/${themeName}/bootstrap.min.css`;

            // Store the selected theme in session storage
            sessionStorage.setItem('theme', themeName);

            // Log the selected theme
            const storedTheme = sessionStorage.getItem('theme');
            console.log("Theme stored:", storedTheme);
        }

        // Get the theme value from the session storage
        const theme = sessionStorage.getItem('theme');

         // Check if a theme is stored in session storage
         if (theme == undefined || theme == null) {
             // If no theme is stored, set the default theme (darkly).
            theme = "darkly"
        }
        
        // Display the theme value in the console
        console.log("Theme=[", theme, "]");
       
        // Set the theme or change it
        changeTheme(theme)

    </script>
    <?php
        // Retrieve the DB value from the URL parameter or put db1 by default
        $selectedDb = $_GET['selectedDb'] ?? "db1";

        // Able to use $selectedDb in PHP code
        echo "SelectedDB received from JavaScript: " . htmlspecialchars($selectedDb) . "<BR>";

        // Database configuration
        require_once('/etc/weathermetrics/db_config.php'); // Adjust the path accordingly

        if (isset($dbConfigs[$selectedDb])) {
            $dbConfig = $dbConfigs[$selectedDb];
            $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Echo the host, username, and database
            echo "Host: " . $dbConfig['host'] . "<br>";
            echo "Username: " . $dbConfig['username'] . "<br>";
            echo "Database: " . $dbConfig['database'] . "<br>";
            echo "TableDwc: " . $dbConfig['tabledwc'] . "<br>";
            echo "LabelNormals1: " . $dbConfig['LabelNormals1'] . "<br>";
            echo "LabelNormals2: " . $dbConfig['LabelNormals2'] . "<br>";
    
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

    <!-- Navigation bar -->
    <nav class="navbar navbar-expand-md navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="images/WeatherCondtions.png" alt="Logo" width="94" height="53" class="d-inline-block align-top">
            </a>
            <!-- Navbar toggle button for small screens -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <!-- Navigation links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="weatherMetrics.php" class="nav-link btn btn-light fs-6 text-dark" role="button">Temperatures</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link btn btn-light fs-6 text-dark" role="button">Rainfall</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link btn btn-light fs-6 text-dark" role="button">Pressure</a>
                    </li>
                    <li class="nav-item">
                        <a href="weatherMetricsByYear.php" class="nav-link btn btn-light fs-6 text-dark" role="button">Climatologic</a>
                    </li>
                    <!-- Add more navigation links as needed -->
                </ul>
                <!-- Dropdowns for database and theme selection -->
                <div class="ms-auto d-flex">
                    <div class="dropdown me-3">
                        <!-- Database selection dropdown toggle button -->
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownDbButton" data-bs-toggle="dropdown" aria-expanded="false">
                            Select DB
                        </button>
                        <!-- Database selection options -->
                        <ul class="dropdown-menu" id="db-selector" aria-labelledby="dropdownDbButton">
                            <li><a class="dropdown-item" href="#" onclick="changeDb('db1')">Villebon</a></li>
                            <li><a class="dropdown-item" href="#" onclick="changeDb('db2')">Bethune</a></li>
                            <!-- Add more database options as needed -->
                        </ul>
                    </div>
                    <div class="dropdown ms-3">
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        Select Theme
                        </button>
                        <ul class="dropdown-menu" id="theme-selector" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('cerulean')">cerulean</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('darkly')">Dark</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('cosmo')">Cosmo</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('flatly')">Flatly</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('journal')">Journal</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('litera')">Litera</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('lux')">Lux</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('materia')">Materia</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('minty')">Minty</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('pulse')">Pulse</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('sandstone')">Sandstone</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('sketchy')">Sketchy</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('slate')">Slate</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('solar')">Solar</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('spacelab')">Spacelab</a></li>
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('united')">United</a></li>	
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('spacelab')">Spacelab</a></li>	
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('vapor')">Vapor</a></li>	
                        <li><a class="dropdown-item" href="#" onclick="changeTheme('yeti')">Yeti</a></li>				
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Script section for JavaScript code -->
    <script>
        // Get the theme selector element
        const themeSelector = document.getElementById('theme-selector');

        // Add event listener to the theme selector
        themeSelector.addEventListener('change', function () {
            // Get the selected theme from the dropdown
            const selectedTheme = this.value;
            // Call the function to change the theme
            changeTheme(selectedTheme);
        });

        // Function to update the text of the dropdown toggle button
        function updateDropdownButtonText(dbName) {
            const dropdownButton = document.getElementById('dropdownDbButton');
            if (dbName === 'db1') {
                dropdownButton.textContent = 'Villebon';
            } else if (dbName === 'db2') {
                dropdownButton.textContent = 'Bethune';
            } else {
                dropdownButton.textContent = 'Select DB';
            }
        }

        // Function to change the selected database
        function changeDb(dbName) {
            // Store the selected database in session storage
            sessionStorage.setItem('selectedDb', dbName);

            // Update the text of the dropdown toggle button
            updateDropdownButtonText(dbName);

            // Get the current URL
            const currentUrl = window.location.href;

                // Check if there is already a query string in the URL
            const hasQueryString = currentUrl.indexOf('?') !== -1;

            // Build the new URL with the selectedDb as a GET parameter
            const newUrl = hasQueryString
                ? `${currentUrl.split('?')[0]}?selectedDb=${encodeURIComponent(dbName)}`
                : `${currentUrl}?selectedDb=${encodeURIComponent(dbName)}`;

            // Navigate to the new URL
            window.location.href = newUrl;

            // Log the selected database
            console.log("Selected DB stored:", dbName);

            // Log the New Url
            console.log("NewUrl:", newUrl);
        }

        // Event listener for the database selector
        const dbSelectors = document.querySelectorAll('.dropdownDbButton');
        dbSelectors.forEach(function (dbSelector) {
            dbSelector.addEventListener('click', function (event) {
                // Trigger the changeDb function with the selected database
                const selectedDb = event.target.innerText;
                changeDb(selectedDb);

                // Manually navigate to the new URL
                window.location.href = newUrl;
            });
        });

        // Initial setup: Check if a database is already selected and update the button text
        document.addEventListener('DOMContentLoaded', function () {
            const storedDb = sessionStorage.getItem('selectedDb');
            updateDropdownButtonText(storedDb);
        });

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

