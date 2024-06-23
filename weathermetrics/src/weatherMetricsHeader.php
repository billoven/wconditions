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
        .navbar-brand-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        #version-image-container {
            display: flex;
            justify-content: center;
            margin-top: 5px; /* Adjust the margin as needed */
        }
        #version-image {
            width: auto; /* Maintain aspect ratio */
            height: 20px; /* Adjust the height as needed */
        }
        .release-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 5px; /* Adjust as needed */
        }

    </style>  

    <style type="text/css">
        /* ============ desktop view necessary for normals selection with sub-menu ============ */
        @media all and (min-width: 992px) {

            .dropdown-menu li{
                position: relative;
            }
            .dropdown-menu .submenu{ 
                display: none;
                position: absolute;
                left:100%; top:-7px;
            }
            .dropdown-menu .submenu-left{         //window.location.href = window.location.href;
            .dropdown-submenu .dropdown-menu {
               width: auto;
            }
        }	
        /* ============ desktop view .end// ============ */

        /* ============ small devices ============ */ 
        @media (max-width: 991px) {
            .dropdown-menu .dropdown-menu{
                    margin-left:0.7rem; margin-right:0.7rem; margin-bottom: .5rem;
            }
        } 	
        /* ============ small devices .end// ============ */
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

        // Store the selected theme in a cookie
        document.cookie = `theme=${themeName}; path=/; SameSite=None`;

        $('#dropdownMenuButton').text(themeName);

        // Log the selected theme
        console.log("Theme stored:", themeName);
    }


    // Function to change the selected normals
    function changeNormals(city, selectedPeriod) {
        // Check if the site is using HTTPS
        const isSecure = location.protocol === 'https:';

        // Store the selected Period in a cookie if it is not empty
        if (selectedPeriod) {
            document.cookie = `selectedNormals=${selectedPeriod}; path=/; SameSite=None;${isSecure ? ' Secure;' : ''}`;
        }

        // Store the selected City in a cookie if it is not empty
        if (city) {
            document.cookie = `selectedNormalsCity=${city}; path=/; SameSite=None;${isSecure ? ' Secure;' : ''}`;
        }

        // Log the stored selected Normals and City
        const storedCity = getCookie('selectedNormalsCity');
        console.log("Normals City Stored:", storedCity);
        const storedNormals = getCookie('selectedNormals');
        console.log("Normals Period Stored:", storedNormals);

        // Update the text of the "Select Normals" button with the abbreviated city name and period
        const abbreviatedCity = city.substring(0, 2); // Get the first two letters of the city name
        const buttonLabel = `${abbreviatedCity}-${selectedPeriod}`; // Concatenate abbreviated city name and period
        console.log("Dans changeNormals buttonLabel:", buttonLabel);
        $('#dropdownNormalsButton').text(buttonLabel);

        // Log the selected city and period
        console.log("Selected city:", city);
        console.log("Selected period:", selectedPeriod);

        // Using window.location.href
        location.reload();
    }
    

    // Function to retrieve a specific cookie by name
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    // Function to change the selected database
    function changeDb(dbid, station) {
        // Check if the site is using HTTPS
        const isSecure = location.protocol === 'https:';

        // Store the selected database in a cookie
        document.cookie = `selectedDb=${dbid}; path=/; SameSite=None;${isSecure ? ' Secure;' : ''}`;
        document.cookie = `selectedStation=${station}; path=/; SameSite=None;${isSecure ? ' Secure;' : ''}`;

        // Update the text of the "Select Db" button with the station name 
        $('#dropdownDbButton').text(station);

        // Log the selected database
        console.log("Selected DBid stored:", dbid);
        console.log("Selected DB station stored:", station);

        // Using window.location.href to reload the page
        location.reload();
    }


    </script>

    <?php
        // Read a Normals stats json file associated to a City
        function readNormalsJsonFile($city, $period) {
            $filename = "./normals/StatsNormals_" . str_replace(' ', '', $city) . "_" . $period . ".json";
            
            if(file_exists($filename)) {
                $json_data = file_get_contents($filename);
                $data = json_decode($json_data, true);
                
                if($data === null) {
                    // JSON decoding failed
                    return null;
                } else {
                    // JSON decoding successful
                    //echo "Normals Json Filename read: [$filename]";
                    return $data;
                }
            } else {
                // File does not exist
                return null;
            }
        }

        // Set the selectedDb cookie to "db1" if it hasn't been set
        if (!isset($_COOKIE['selectedDb'])) {
            setcookie('selectedDb', 'db1', 0, '/');
        }

        // Retrieve the DB value from the cookie
        $selectedDb = $_COOKIE['selectedDb'] ?? "db1";

        // Able to use $selectedDb in PHP code
        //echo "SelectedDB received from Cookie: " . htmlspecialchars($selectedDb) . "<BR>";

        // Database configuration
        require_once('/etc/wconditions/db_config.php'); // Adjust the path accordingly

        if (isset($dbConfigs[$selectedDb])) {
            $dbConfig = $dbConfigs[$selectedDb];
            $conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);
            if ($conn->connect_error) {
                die("Database Connection failed: " . $conn->connect_error);
            }

            // Echo the host, username, and database
            //echo "Weather Station   : " . $dbConfig['weatherStation'] . "<br>";
            //echo "Host              : " . $dbConfig['host'] . "<br>";
            //echo "Username          : " . $dbConfig['username'] . "<br>";
            //echo "Database          : " . $dbConfig['database'] . "<br>";
            //echo "TableDwc          : " . $dbConfig['tabledwc'] . "<br>";
            //echo "DefaultNormals    : " . $dbConfig['DefaultNormals'] . "<br>";
            //echo "DefaultNormalsCity: " . $dbConfig['DefaultNormalsCity'] . "<br>";
            ?>
            <script>
                // Retrieve the value of the selectedNormalsCity cookie
                const selectedNormalsCity = getCookie('selectedNormalsCity');

                // Check if the selectedNormalsCity cookie is not set
                if (!selectedNormalsCity) {
                    // If the cookie is not set, set it to the default value from $dbConfig
                    document.cookie = `selectedNormalsCity=<?php echo $dbConfig['DefaultNormalsCity']; ?>; path=/; SameSite=None`;
                }
                // Check if the selectedNormals cookie exists
                const selectedNormalsCookie = getCookie('selectedNormals');

                // If the selectedNormals cookie doesn't exist, set it to the default value from PHP
                if (!selectedNormalsCookie) {
                    const defaultNormals = "<?php echo $dbConfig['DefaultNormals']; ?>";
                    document.cookie = `selectedNormals=${defaultNormals}; path=/; SameSite=None`;
                }
            
            </script>
            <?php

            // Retrieve the city and period values from the cookie
            $selectedCity = $_COOKIE['selectedNormalsCity'] ?? $dbConfig['DefaultNormalsCity'];
            $selectedPeriod = $_COOKIE['selectedNormals'] ?? $dbConfig['DefaultNormals'];

            // Display the cookie values
            //echo "Cookie NormalsCity: " . $selectedCity . "<br>";
            //echo "Cookie Selected NormalsPeriod: " . $selectedPeriod . "<br>";
            //echo "Cookie Selected DB: " . $selectedDb . "<br>";

            // Fetch available years from the database
            $years = array();
            $yearQuery = "SELECT DISTINCT YEAR(WC_Date) AS Year FROM DayWeatherConditions ORDER by Year DESC";
            $yearResult = $conn->query($yearQuery);
            while ($row = $yearResult->fetch_assoc()) {
                $years[] = $row['Year'];
            }
        
            // TableNormals name for the selected Normals period
            $selectedPeriodTable = "Normals_" . $selectedPeriod;

            // SQL query to check if the table exists
            $sql = "SHOW TABLES LIKE '" . $selectedPeriodTable . "'";
            $result = $conn->query($sql);

            // If the table exists, initialize $TableNormals variable
            if ($result->num_rows > 0) {
                $TableNormals = $selectedPeriodTable;
            } else {
                // If the table doesn't exist, handle the error accordingly
                echo "Error: Normals table for the selected period does not exist.";
            }

            // Close the database connection
            $conn->close();
        }

        // Read Normals Json File of Stats for a city
        $normalsData = readNormalsJsonFile($selectedCity, $selectedPeriod);

        if ($normalsData !== null) {
            // Normals data loaded successfully
            //echo json_encode($normalsData, JSON_PRETTY_PRINT);
        } else {
            // Failed to load normals data
            echo "Failed to load normals data for $selectedCity - $selectedPeriod.";
        }

  
    ?>


    <!-- Navigation bar -->
    <nav class="navbar navbar-expand-md navbar-dark">
        <div class="container">
            <div class="navbar-brand-wrapper">
                <a class="navbar-brand" href="#">
                    <img src="images/WeatherCondtions.png" alt="Logo" width="90" height="50" class="d-inline-block align-top">
                </a>
                <div class="release-container" id="version-image-container">
                    <!-- Release version will be inserted here as an image -->
                </div>
            </div>
            <!-- Navbar toggle button for small screens -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <!-- Navigation links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="weatherMetricsTemp.php" class="nav-link btn btn-light fs-6 text-dark" role="button">Temperatures</a>
                    </li>
                    <li class="nav-item">
                        <a href="weatherMetricsRain.php" class="nav-link btn btn-light fs-6 text-dark" role="button">Rainfall</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link btn btn-light fs-6 text-dark" role="button">Pressure</a>
                    </li>
                    <li class="nav-item">
                        <a href="weatherMetricsComp.php" class="nav-link btn btn-light fs-6 text-dark" role="button">Comparison</a>
                    </li>
                    <li class="nav-item">
                        <a href="weatherMetricsByYear.php" class="nav-link btn btn-light fs-6 text-dark" role="button">Climatologic</a>
                    </li>
                    <!-- Add more navigation links as needed -->
                </ul>
                 <!-- Dropdowns for Normals, database, theme selections -->
                <div class="ms-auto d-flex">
                    <div class="dropdown me-3">
                        <!-- Normals selection dropdown toggle button -->
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownNormalsButton" data-bs-toggle="dropdown" aria-expanded="false">
                            Select Normals
                        </button>
                    
                        <!-- Normals selection options --> 
                        <ul class="dropdown-menu dropdown-menu-right" id="normals-selector" aria-labelledby="dropdownNormalsButton">

                        <?php
                            // Read the JSON file
                            $jsonData = file_get_contents('normals/NormalsFilesList.json');

                            // Parse JSON data
                            $data = json_decode($jsonData, true);

                            // Iterate over WeatherStations
                            foreach ($data['WeatherStations'] as $weatherStation => $periods) {
                                // Output the dropdown submenu for each WeatherStation
                                echo '<li class="dropdown-submenu">';
                                echo '<a class="dropdown-item dropdown-toggle" href="#">' . $weatherStation . '</a>';
                                echo '<ul class="submenu submenu-left dropdown-menu">';
                                
                                // Output the Normals Files for the WeatherStation
                                foreach ($periods as $period) {
                                    echo '<li><a class="dropdown-item" href="#" onclick="changeNormals(\'' . $weatherStation . '\', \'' . $period . '\')">' . str_replace('_', '-', $period) . '</a></li>';
                                }
                                
                                echo '</ul>';
                                echo '</li>';
                            }
                        ?>
                        </ul>
                    </div>
                </div>

                <!-- JavaScript to handle the dropdown -->
                <script>
                // JavaScript to handle the dropdown
                $(document).ready(function () {
                    // Handle mouse enter on Weather Station to show sub-menu
                    $('.dropdown-submenu').on('mouseenter', function () {
                        $(this).find('.submenu').show();
                    }).on('mouseleave', function () {
                        $(this).find('.submenu').hide();
                    });

                    // Event handler for when a sub-menu item (period) is clicked
                    $('.dropdown-submenu a.dropdown-item').on('click', function (e) {
                        // Get the selected city from the parent menu
                        const selectedCity = $(this).closest('.dropdown-submenu').find('a.dropdown-toggle').text();
                        // Get the selected period from the clicked sub-menu item
                        const selectedPeriod = $(this).text();

                        // Hide both parent and sub-menu
                        $(this).closest('.dropdown').removeClass('show').find('.dropdown-menu').removeClass('show');

                        // Prevent further propagation of the current event 
                        // in the capturing and bubbling phases
                        e.stopPropagation();
                        // Cancel the event if it is cancelable, meaning that 
                        // the default action that belongs to the event will not occur
                        e.preventDefault();
                    });
                });
                </script>
                <div class="dropdown me-3">
                    <!-- Database selection dropdown toggle button -->
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownDbButton" data-bs-toggle="dropdown" aria-expanded="false">  
                        <?php 
                            // Display default weatherStation name
                            echo $dbConfig['weatherStation']; 
                        ?>
                    </button>

                        <!-- Database selection options -->
                        <ul class="dropdown-menu" id="db-selector" aria-labelledby="dropdownDbButton">
                            <?php foreach ($dbConfigs as $dbid => $dbConfig): ?>
                            <?php 
                                // Check if this is the currently selected database and set the class accordingly
                                $isActive = ($selectedDbCookie == $dbid) ? 'active' : ''; 
                            ?>
                            <li><a class="dropdown-item <?php echo $isActive; ?>" href="#" onclick="changeDb('<?php echo $dbid; ?>','<?php echo $dbConfig['weatherStation']; ?>')"><?php echo $dbConfig['weatherStation']; ?></a></li>
                            <?php endforeach; ?>
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

        // Get the theme value from the cookie
        const themeCookie = getCookie('theme');

        // Check if a theme is stored in a cookie
        if (!themeCookie) {
            // If no theme is stored, set the default theme (darkly).
            changeTheme("darkly");
        } else {
            // Set the theme
            changeTheme(themeCookie);
        }
        
        document.addEventListener('DOMContentLoaded', function () {
                const storedNormalsCity = getCookie('selectedNormalsCity');
                const storedNormalsPeriod = getCookie('selectedNormals');
                
                // Get the default values from PHP
                const defaultNormalsCity = "<?php echo $dbConfig['DefaultNormalsCity']; ?>";
                const defaultNormalsPeriod = "<?php echo $dbConfig['DefaultNormals']; ?>";

                // Set the button text to default values if stored values are null
                const cityToDisplay = storedNormalsCity ? storedNormalsCity : defaultNormalsCity;
                const periodToDisplay = storedNormalsPeriod ? storedNormalsPeriod : defaultNormalsPeriod;

                const abbreviatedCity = cityToDisplay.substring(0, 2); // Get the first two letters of the city name
                const buttonLabel = `${abbreviatedCity}-${periodToDisplay}`; // Concatenate abbreviated city name and period
                console.log("Normals Button:", document.buttonLabel);
                $('#dropdownNormalsButton').text(buttonLabel);
                
                // Debugging: Log the cookies
                console.log("All Cookies:", document.cookie);

                // Additional code for other functionalities
                const storedStation = getCookie('selectedStation');
                console.log("Stored Station:", storedStation); // Debugging: Log the stored station
                $('#dropdownDbButton').text(storedStation);
            });

        $(document).ready(function () {
            // Function to fetch release version from file
            function fetchReleaseVersion() {
                return fetch('release_installed.txt')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to fetch release version');
                        }
                        return response.text();
                    })
                    .then(text => {
                        // Extract release version from the text
                        const releaseMatch = text.match(/^RELEASE=wconditions_(.+)$/m);
                        if (releaseMatch) {
                            return releaseMatch[1];
                        } else {
                            throw new Error('Release version not found in file');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching release version:', error);
                    });
            }

            // Function to create an image from a string
            function createImageFromString(versionString) {
                // Create a canvas element
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');

                // Set the canvas dimensions
                const text = `v${versionString}`;
                context.font = '20px Verdana';
                const textWidth = context.measureText(text).width;
                canvas.width = textWidth + 20; // add some padding
                canvas.height = 40; // height of the text

                // Draw the text onto the canvas
                context.font = '20px Verdana';
                context.fillStyle = 'blue';
                context.fillText(text,10,30); // draw the text with some padding

                // Create an image element
                const img = new Image();
                img.src = canvas.toDataURL();
                img.id = 'version-image';

                // Append the image to the container
                document.getElementById('version-image-container').appendChild(img);
            }

            // Fetch release version and generate image
            fetchReleaseVersion().then(createImageFromString);
        });
    </script>
