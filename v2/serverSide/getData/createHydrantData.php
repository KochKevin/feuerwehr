<?php

// Fehlerberichterstattung aktivieren
error_reporting(E_ALL);

// Fehler direkt auf dem Bildschirm anzeigen
ini_set('display_errors', 1);

//Load Temp folder
$tempFolder = "temp";

//Save file
$savePath =  "/v2/serverSide/data/"; 
$saveFileName = "emergency.txt";


$osmHydrantDataPath = $tempFolder . "/tempHydrantData.txt";

$fileContent = file_get_contents($osmHydrantDataPath);

// Prüfe, ob das Lesen erfolgreich war
if ($fileContent == false) {
    die("Can not read file");
}

// JSON-Zeichenkette in ein PHP-Array umwandeln
$data = json_decode($fileContent, true);

$nodeData = array();
$elementsData = $data['elements'];


foreach ($elementsData as $element) {

    switch($element["tags"]["emergency"]){

        case "fire_hydrant":

            //https://wiki.openstreetmap.org/wiki/DE:Tag:emergency%3Dfire_hydrant

            $currentNodeID = $element['id'];

            $nodeData[strval($currentNodeID)] = array(
                "lat" => $element['lat'],
                "lon" => $element['lon'],
                "emergency" => "fire_hydrant"
            );
            
            //Typ: pipe, pillar, wall, underground 
            if (array_key_exists("fire_hydrant:type", $element['tags'])){
                $nodeData[strval($currentNodeID)]['type'] = $element['tags']['fire_hydrant:type'];
            }
            //Durchmesser: in mm
            if (array_key_exists("fire_hydrant:diameter", $element['tags'])){
                $nodeData[strval($currentNodeID)]['diameter'] = $element['tags']['fire_hydrant:diameter'];
            }
            //Durchflussrate: in liter / minute
            if (array_key_exists("fire_hydrant:flow_rate", $element['tags'])){
                $nodeData[strval($currentNodeID)]['flow_rate'] = $element['tags']['fire_hydrant:flow_rate'];
            }
            //Position: lane (Fahrbahn), parking_lot (Parkbucht), sidewalk (Bürgersteig), green (Wiese) 
            if (array_key_exists("fire_hydrant:position", $element['tags'])){
                $nodeData[strval($currentNodeID)]['position'] = $element['tags']['fire_hydrant:position'];
            }

            break;

        case "suction_point":
            //Saugstelle https://wiki.openstreetmap.org/wiki/DE:Tag:emergency%3Dsuction_point
            $nodeData[strval($currentNodeID)] = array(
                "lat" => $element['lat'],
                "lon" => $element['lon'],
                "emergency" => "suction_point"
            );

            break;

    }

}

$finalData = array(
    "overpass" => $data["generator"] . " Version: " . $data["version"],
    "osm3s" => array(
        "timestamp_osm_base" => $data["osm3s"]["timestamp_osm_base"],
        "timestamp_areas_base" => $data["osm3s"]["timestamp_areas_base"],
    ),
    "copyright" => $data["osm3s"]["copyright"],
    "nodes" => $nodeData
);

// PHP-Array in JSON umwandeln
$jsonData = json_encode($finalData);


$saveFilePath = $_SERVER['DOCUMENT_ROOT'] .$savePath . $saveFileName;
file_put_contents($saveFilePath, $jsonData);

?>