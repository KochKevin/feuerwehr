<?php
/*
    header('Content-type: Application/JSON');

    // Fehlerberichterstattung aktivieren
    error_reporting(E_ALL);

    // Fehler direkt auf dem Bildschirm anzeigen
    ini_set('display_errors', 1);
    */

    function findNode($elementsData, $nodeIDToFind ){
        
        $nodeLat = 0;
        $nodeLon = 0;

        for($i = 0; $i < count($elementsData); $i++){

            $currentJsonElement = $elementsData[$i];

            if($currentJsonElement['type'] != 'node'){
                continue;
            }

            if($currentJsonElement['id'] == $nodeIDToFind){
                $nodeLat = $currentJsonElement['lat'];
                $nodeLon = $currentJsonElement['lon'];
                break;
            }

        }
        return array('lat' => $nodeLat, 'lon' => $nodeLon);

    }


// Fehlerberichterstattung aktivieren
error_reporting(E_ALL);

// Fehler direkt auf dem Bildschirm anzeigen
ini_set('display_errors', 1);

//Load Temp folder
$tempFolder = "temp";

//Save file
$savePath =  "/v2/serverSide/data/"; 
$saveFileName = "nodeConnections.txt";



$osmStreetDataPath = $tempFolder . "/tempStreetData.txt";

$fileContent = file_get_contents($osmStreetDataPath);

// Prüfe, ob das Lesen erfolgreich war
if ($fileContent == false) {
    die("Can not read file");
}


// JSON-Zeichenkette in ein PHP-Array umwandeln
$data = json_decode($fileContent, true);


$nodeData = array();


$elementsData = $data['elements'];

for($i = 0; $i < count($elementsData); $i++){

    $currentJsonObject = $elementsData[$i];

    if($currentJsonObject['type'] != 'way'){
        continue;
    }

    //Calculate Travele Cost:
    //When smaller, the node is more considerd
    $travelCost = 0;
    $streetTyp = $currentJsonObject['tags']['highway'];

    //See https://wiki.openstreetmap.org/wiki/DE:Key:highway
    switch($streetTyp){

        //Autobahn
        case 'motorway':
            $travelCost = 1;
        break;

        //Autobahn auffahrt
        case 'motorway_link':
            $travelCost = 0;
        break;

        //Autobahn ähnliche Bundestraße
        case 'trunk':
            $travelCost = 2;
        break;

        //Autobahn ähnliche Bundesstraße aufffahrt
        case 'trunk_link':
            $travelCost = 0;
        break;

        //Bundesstraße
        case 'primary':
            $travelCost = 4;
        break;

         //Bundestraße Auffahrt
         case 'primary_link':
            $travelCost = 0;
        break;

        //Große Landstraßen/Kreisstraßen
        case 'secondary':
            $travelCost = 6;
        break;

        //Große Landstraße/Kreistraße auffahrt
        case 'secondary_link':
            $travelCost = 0;
        break;

        //Normale Straße
        case 'tertiary':
            $travelCost = 6;
        break;

        // Verbindung zwischen Normale Straßen
        case 'tertiary_link':
            $travelCost = 0;
        break;

        //Nachberschaftsstraße
        case 'residential':
            $travelCost = 30;
        break;

        //Zufahrts Straßen in Industrie, Parkplätze usw
        case 'service':
            $travelCost = 70;
        break;

        //Fußgängerstraße
        case 'pedestrian':
            $travelCost = 70;
        break;

        //Unbekannter Straßen typ
        case 'road':
            $travelCost = 15;
        break;

        case 'living_street':
            $travelCost = 70;
        break;

        //Feldweg
        case 'track':
            $travelCost = 70;
        break;


        default:
            $travelCost = 50;
        break;

    }

    

    //Check if street is a one way or roundabout
    $isOneWay = false;
    //Check if oneway
    if(isset($currentJsonObject['tags']['oneway'])){
        if($currentJsonObject['tags']['oneway'] == 'yes'){
            $isOneWay = true;
        }
    }

    //Check if roundabout
    if(isset($currentJsonObject['tags']['junction'])){
        if($currentJsonObject['tags']['junction'] == 'roundabout'){
            $isOneWay = true;
        }
    }


    for($a = 0; $a < count($currentJsonObject['nodes']); $a++){

        $nodes = $currentJsonObject['nodes'];

        $currentNodeID = $nodes[$a];
        $previousNodeID = null;
        $nextNodeID = null;

        $currentNodeInfo = findNode($elementsData, $currentNodeID);

        if($a != 0 && $isOneWay == false){
            $previousNodeID = $nodes[$a - 1];
            $previousNodeInfo = findNode($elementsData, $previousNodeID);
        }

        if($a != count($nodes) - 1){
            $nextNodeID = $nodes[$a + 1];
            $nextNodeInfo = findNode($elementsData, $nextNodeID);
        }

       if (!array_key_exists($currentNodeID, $nodeData)){

        $nodeData[strval($currentNodeID)] = array(
            "lat" => $currentNodeInfo['lat'],
            "lon" => $currentNodeInfo['lon'],
            "travelCost" => $travelCost,
            "connections" => array()
           );
       }

       if(isset($previousNodeID)){
            $nodeData[strval($currentNodeID)]['connections'][] = $previousNodeID;
        }

        if(isset($nextNodeID)){
            $nodeData[strval($currentNodeID)]['connections'][] = $nextNodeID;
        }
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



//HTML OUTPUT:

echo "<h1>CREATE NODE CONNECTIONS<h2>";
echo "<hr>";






?>