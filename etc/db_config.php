<?php
// Database configurations
$dbConfigs = array(
    'db1' => array(
        'weatherStation' => 'Villebon-Sur-Yvette',
	'timezone' => 'Europe/Paris', 
        'latitude' => 48.7000,
        'longitude' => 2.2325,
        'host' => '192.168.17.30',
        'username' => '%%DB1_USER%%',
        'password' => '%%DB1_PASSWORD%%',
        'database' => 'VillebonWeatherReport',
        'tabledwc' => 'DayWeatherConditions',
        'tablewc' => 'WeatherConditions',
        'NormalsDB' => 'ClimateNormals',
        'DefaultNormals' => '1991_2020',
        'DefaultNormalsCity' => 'ParisMontsouris'

    ),
    'db2' => array(
        'weatherStation' => 'Béthune',
	'timezone' => 'Europe/Paris', 
        'latitude' => 50.5346,
        'longitude' => 2.6307,
        'host' => '192.168.17.30',
        'username' => '%%DB2_USER%%',
        'password' => '%%DB2_PASSWORD%%',
        'database' => 'BethuneWeatherReport',
        'normalsdb' => 'ClimateNormals',
        'tabledwc' => "DayWeatherConditions",
        'tablewc' => 'WeatherConditions',
        'NormalsDB' => 'ClimateNormals',
        'DefaultNormals' => '1991_2020',
        'DefaultNormalsCity' => 'LilleLesquin'
    )
    // Add more database configurations as needed
);
?>
