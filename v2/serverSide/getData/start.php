<?php

 function callOverpass($overpassQL){

    $overpassURL = 'http://overpass-api.de/api/interpreter?data=';

    $overpassQuery = $overpassURL . $overpassQL;

    echo "<br> <hr> <br>";
    echo $overpassQuery;
    echo "<br> <br> <br>";

    $response = file_get_contents($overpassQuery);

    if ($response === FALSE) {
        $error = error_get_last();
        die('Fehler beim Abrufen der Daten: ' . $error['message']);
    }

    echo $response;

    return $response;
 }


$tempFolder = "temp";

// Überprüfen Sie den Pfad zur Datei und führen Sie ggf. URL-Kodierung durch
$overpassQLForStreet = file_get_contents("overpassQLForStreet.txt");
$overpassQLForStreet = urlencode($overpassQLForStreet);

$overpassQLForHydrant = file_get_contents("overpassQLForHydrant.txt");
$overpassQLForHydrant = urlencode($overpassQLForHydrant);


$filePath = $tempFolder . "/tempStreetData.txt";
file_put_contents($filePath, callOverpass($overpassQLForStreet));

$filePath = $tempFolder . "/tempHydrantData.txt";
file_put_contents($filePath, callOverpass($overpassQLForHydrant));


?>
