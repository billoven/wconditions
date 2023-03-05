<?php
try {
// Souvent on identifie cet objet par la variable $conn ou $db
// Mot de passe à changer
$mysqlConnection = new PDO(
    'mysql:host=192.168.17.10;dbname=VillebonWeatherReport;charset=utf8',
    'admin',
    'xxxxxxxx'
);
}
catch (Exception $e)
{
        die('Erreur : ' . $e->getMessage());
}


$WeatherRain = $mysqlConnection->prepare('SELECT WC_Date,WC_PrecipitationSum FROM DayWeatherConditions');
$WeatherRain->execute();
$rainvalues = $WeatherRain->fetchAll();
$i=0;
$NoRain = [[]];
foreach ($rainvalues as $rainvalue) {
    // printf ("[%s]   [%2.1f]\n",$rainvalue['WC_Date'],$rainvalue['WC_PrecipitationSum']) ; 
    if ( $rainvalue['WC_PrecipitationSum'] < 1 ) {
        //echo " Une hournée sans pluie détectéé !\n";
        $NoRain[$i]['WC_Date'] = $rainvalue['WC_Date'];
        $NoRain[$i]['WC_PrecipitationSum'] = $rainvalue['WC_PrecipitationSum'] ;
        //printf ("[%s]   [%2.1f]\n",$NoRain[$i]['WC_Date'],$NoRain[$i]['WC_PrecipitationSum']) ;
        $i++;
    } else {
        if ( $i > 18 ) {
            printf (" %d jours sans pluie ==> %s ",$i+1,$NoRain[0]['WC_Date']);
            for ($j = 0 ; $j <= $i ; $j++ ) {
                printf ("*",$j);
            }
            printf (" <== %s \n",$NoRain[$i-1]['WC_Date']);
        }
        $NoRain=[[]];
        $i=0;
    }
    
}
if ( $i > 8 ) {
    printf (" %d jours sans pluie  (En cours ...) ==> %s ",$i+1,$NoRain[0]['WC_Date']);
    for ($j = 0 ; $j <= $i ; $j++ ) {
        printf ("*",$j);
    }
    printf (" <== %s \n",$NoRain[$i-1]['WC_Date']);
}
// foreach($rainvalues as $key => $rainvalue){
//    $prev = $rainvalues[$key-1];
//    printf ("previous value %s \n",$prev['WC_Date']);
// }



?>