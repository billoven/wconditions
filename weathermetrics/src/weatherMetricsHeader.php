<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weather Conditions & Climate Statistics</title>
    <link id="bootstrap-theme" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.1/dist/united/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-gradient"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="scripts/weatherMetrics.js"></script>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        .table th:first-child,
        .table td:first-child {
        position: sticky;
        left: 0;
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

        /* ============ desktop view necessary for normals selection with sub-menu ============ */
        @media all and (min-width: 992px) {

            .dropdown-menu li{
                position: relative;
            }

            /* Align main dropdown menu to the left of the button */
            .dropleft .dropdown-menu {
                right: 100%; /* Position on the left of the button */
                top: 0; /* Align with the top of the button */
                transform: translateX(-10px); /* Optional offset for spacing */
          }

            /* Submenu alignment */
            .dropleft .submenu-left {
                position: absolute;
                left: -100%; /* Place submenu to the left of the parent */
                top: 0;
            }

            /* Ensure submenu displays on hover */
            .dropdown-submenu:hover .dropdown-menu {
                display: block;
                min-width: auto;
                max-width: 300px; /* Ajustez cette valeur si nécessaire */
                width: auto;
                white-space: nowrap;             
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
        .metrics-table {
            width: 90%;
            border-collapse: collapse;
            margin: 20px auto; /* Center the table horizontally */
            font-size: 16px;
            text-align: left;
        }
        .metrics-table th, .metrics-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .metrics-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .metrics-header {
            background-color: #e0e0e0;
        }
        .normals-header {
            background-color: #d9edf7;
        }
        .year-header {
            background-color: #f9f9f9;
        }
        .metrics-cell {
            background-color: #e0e0e0;
            font-weight: bold;
        }
        .normals-cell {
            background-color: #d9edf7;
        }
        .data-cell {
            background-color: #f9f9f9;
        }
        .variation {
            font-size: 0.9em;
            color: #333;
        }
        .icon-up {
            color: darkgreen;
            font-weight: bold;
        }
        .icon-up-oblique {
            color: green;
            font-weight: bold;
        }
        .icon-horizontal {
            color: blue;
            font-weight: bold;
        }
        .icon-down {
            color: darkred;
            font-weight: bold;
        }
        .icon-down-oblique {
            color: orange;
            font-weight: bold;
        }
        .navbar-custom {
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #5a189a;
        }
        .navbar-brand span {
            color: #4a90e2;
        }
        .dropdown-menu {
            min-width: 180px;
            background-color:rgb(157, 161, 165) ;
        }
        .dropdown-header {
            font-weight: bold;
        }
        .navbar .form-select {
            max-width: 250px;
            margin-left: 1rem;
        }
        .navbar-text {
            font-size: 0.9rem;
            color: #6c757d;
        }

        /* Weather Condition Container */
        /* General Card Styling */
        .card {
            border: 1px ; 
            background-color: #fff;
            margin-bottom: 1rem;
        }

        .card-body {
            padding: 5px;
        }

        /* Typography */
        h6 {
            margin: 0;
            font-size: 1rem;
        }

        .container-fluid {
            /* Ajustez cette valeur selon le besoin */
        }
</style>


</head>
<body class="p-3 m-0 border-0 bd-example">
 
    <?php include 'alertBox.php'; ?>
    <script>
    // Function to change the selected normals
    function changeNormals(city, selectedPeriod) {
        // Check if the site is using HTTPS
        const isSecure = location.protocol === 'https:';

        // Store the selected Period in a cookie if it is not empty
        if (selectedPeriod) {
            document.cookie = `selectedNormals=${selectedPeriod}; path=/; SameSite=Lax;${isSecure ? ' Secure;' : ''}`;
        }

        // Store the selected City in a cookie if it is not empty
        if (city) {
            document.cookie = `selectedNormalsCity=${city}; path=/; SameSite=Lax;${isSecure ? ' Secure;' : ''}`;
        }

        // Log the stored selected Normals and City
        const storedCity = getCookie('selectedNormalsCity');
        const storedNormals = getCookie('selectedNormals');

        console.log("Normals City Stored:", storedCity);
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
        document.cookie = `selectedDb=${dbid}; path=/; SameSite=Lax;${isSecure ? ' Secure;' : ''}`;
        document.cookie = `selectedStation=${station}; path=/; SameSite=Lax;${isSecure ? ' Secure;' : ''}`;

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

        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        // Make various "standard" initialization for all page with this standard header like:
        // - Various parameters for accessing to the weatherStation DataBase
        // - Various paramenters for acessing to the selected Climate Normals data
        require('weatherMetricsInit.php');

    ?>

    <!-- Navigation bar -->  
    <div class="container-fluid" style="padding: 1px;">
        <nav class="navbar navbar-expand-lg navbar-custom px-3 mb-1">
            <!-- Logo and Title -->
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="bi bi-cloud-sun-fill" style="font-size: 1.8rem; color: #4a90e2; margin-right: 0.5rem;"></i>
                Weather <span>Conditions</span>
            </a>
            <div class="release-container" id="version-image-container">
                <!-- Release version will be inserted here as an image -->
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
                        <a href="weatherMetricsPressure.php" class="nav-link btn btn-light fs-6 text-dark" role="button">Pressure</a>
                    </li>
                    <li class="nav-item">
                        <a href="weatherMetricsComp.php" class="nav-link btn btn-light fs-6 text-dark" role="button">Comparison</a>
                    </li>
                    <li class="nav-item">
                        <a href="weatherMetricsByYear.php" class="nav-link btn btn-light fs-6 text-dark" role="button">Climate-Stats</a>
                    </li>
                    <!-- Add more navigation links as needed -->
                </ul>
                 <!-- Dropdowns for Normals, database, selections -->
                <!-- Default dropleft button -->
                <div class="ms-auto d-flex">
                    <div class="btn-group dropleft me-2"> <!-- dropleft added here -->
                        <!-- Normals selection dropdown toggle button -->
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownNormalsButton" data-bs-toggle="dropdown" aria-expanded="false">
                            Select Normals
                        </button>

                        <!-- Normals selection options -->
                        <ul class="dropdown-menu" id="normals-selector" aria-labelledby="dropdownNormalsButton">
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
                <div class="dropdown me-2">
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
                            $isActive = ($selectedDb == $dbid) ? 'active' : ''; 
                            // $selectedDb = $_COOKIE['selectedDb'] ?? ''; // Utilise la valeur du cookie, ou une chaîne vide par défaut

                        ?>
                        <li><a class="dropdown-item <?php echo $isActive; ?>" href="#" onclick="changeDb('<?php echo $dbid; ?>','<?php echo $dbConfig['weatherStation']; ?>')"><?php echo $dbConfig['weatherStation']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                 </div>
            </div>   
        </nav>
    </div>
        
    <div class="container py-1">
        <div class="card text-body mb-1 mx-auto" style="max-width: 1000px; padding-top: 1px;"">
            <div class="card-body d-flex justify-content-between align-items-center p-2">
                <!-- Column 1: City, Time, Sunrise, and Sunset -->
                <div class="text-start" style="flex: 1;">
                    <!-- City Name -->
                    <h6 id="live-city" class="mb-0" style="font-size: 1rem;">--</h6>
                    <!-- Date and Time -->
                    <div>
                        <!-- Date -->
                        <span id="live-date" class="d-block" style="font-size: 0.9rem;">--</span>
                        <!-- Time with "Updated" prefix -->
                        <span id="live-time" class="d-block" style="font-size: 0.6rem;">Updated --:--</span>
                    </div>
                    <!-- Sunrise and Sunset -->
                    <div class="mt-2" style="font-size: 0.8rem;">
                        <i class="fas fa-sun fa-fw" style="color: #FFD700;"></i> 
                        <span id="live-sunrise">--:--</span>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <i class="fas fa-moon fa-fw" style="color: #1E90FF;"></i> 
                        <span id="live-sunset">--:--</span>
                    </div>
                </div>

                <!-- Column 2: Temperature -->
                <div class="text-center" style="flex: 1;">
                    <div style="font-size: 1.5rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <i class="fas fa-thermometer-half" style="color: #FF6347;"></i>
                        <h6 id="live-temp" class="mb-0" style="display: inline;">--°C</h6>
                    </div>
                    <p style="font-size: 0.7rem;"><em>Feels like <span id="live-windchill" class="small text-muted">--°</span></em></p>
                </div>

                <!-- Column 3: Pressure, Rainfall Rate, and Cumul -->
                <div class="text-start" style="flex: 1; font-size: 0.9rem;">
                    <div><i class="fas fa-tachometer-alt fa-fw" style="color: #868B94;"></i> <span id="live-pressure">-- hPa</span></div>
                    <div><i class="fas fa-cloud-rain fa-fw" style="color: #868B94;"></i> <span id="live-rain-rate">-- mm/h</span></div>
                    <div><i class="fas fa-water fa-fw" style="color: #868B94;"></i> <span id="live-rain-cumul">-- mm</span></div>
                </div>

                <!-- Column 4: Humidity and Wind Speed -->
                <div class="text-start" style="flex: 1; font-size: 0.9rem;">
                    <div><i class="fas fa-tint fa-fw" style="color: #868B94;"></i> <span id="live-humidity">--%</span></div>
                    <div><i class="fas fa-wind fa-fw" style="color: #868B94;"></i> <span id="live-wind">-- km/h</span></div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Safely updates the text content of an HTML element if it exists.
        function safeUpdate(elementId, value) {
            // Retrieve the HTML element by its ID
            const element = document.getElementById(elementId);
            if (!element) {
                // Log a warning if the element with the given ID is not found
                console.warn(`Element with ID "${elementId}" not found.`);
            } else {
                // Update the text content of the element with the provided value
                element.textContent = value;
            }
        }

        // Fetches live weather data and updates the corresponding elements on the page.
        function updateLiveWeather() {
            fetch('weatherMetricsGetLiveData.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error("Error in data:", data.error);
                    } else {
                        console.log('data =', data);

                        // Split datetime into date and time
                        const [date, time] = data.datetime ? data.datetime.split(' ') : ["--", "--"];

                        // Update elements with the fetched data
                        safeUpdate('live-city', data.city || "--");
                        safeUpdate('live-date', date || "--"); // Update the date
                        safeUpdate('live-time', `Updated ${time}` || "Updated --:--"); // Update the time with prefix
                        safeUpdate('live-sunrise', data.sunrise || "--:--");
                        safeUpdate('live-sunset', data.sunset || "--:--");
                        safeUpdate('live-temp', `${parseFloat(data.temp).toFixed(1)}°C` || "--°C");
                        safeUpdate('live-windchill', `${parseFloat(data.windChill).toFixed(1)}°C` || "--°C");
                        safeUpdate('live-humidity', `${Math.round(data.humidity)}%` || "--%");
                        safeUpdate('live-rain-rate', `${parseFloat(data.precipRate).toFixed(2)} mm/h` || "-- mm/h");
                        safeUpdate('live-rain-cumul', `${parseFloat(data.precipTotal).toFixed(2)} mm` || "-- mm");
                        safeUpdate('live-pressure', `${Math.round(data.pressure)} hPa` || "-- hPa");
                        safeUpdate('live-wind', `${Math.round(data.windSpeed)} km/h` || "-- km/h");
                    }
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                });
        }

        // Call the function on page load and set periodic updates
        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(() => {
                updateLiveWeather(); // Initial call after a small delay
            }, 500); // Delay of 500ms
            setInterval(updateLiveWeather, 60000); // Update every minute
        });
  
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
            $('#dropdownNormalsButton').text(buttonLabel);
 
            // Additional code for other functionalities
            const storedStation = getCookie('selectedStation');
            $('#dropdownDbButton').text(storedStation);
           
            // Debugging: Log the cookies
            console.log("All Cookies:", document.cookie);

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