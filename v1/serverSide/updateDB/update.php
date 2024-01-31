<?php


/* To DO:

Move vizualiing into another file, that when looking at data doesent refresh data set


*/






//Get every path Nodes in Gemeinde Uetze
$overpassGetPathNodes = 'https://overpass-api.de/api/interpreter?data=%5Bout%3Ajson%5D%5Btimeout%3A25%5D%3Barea%283601177827%29%2D%3E%2EsearchArea%3B%28nwr%5B%22highway%22%3D%22motorway%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22trunk%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22primary%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22secondary%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22tertiary%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22unclassified%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22motorway%5Flink%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22trunk%5Flink%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22primary%5Flink%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22secondary%5Flink%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22tertiary%5Flink%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22residential%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22service%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22road%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22living%5Fstreet%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22junction%22%3D%22roundabout%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22pedestrian%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22track%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22path%22%5D%28area%2EsearchArea%29%3Bnwr%5B%22highway%22%3D%22cycleway%22%5D%28area%2EsearchArea%29%3B%29%3Bout%20geom%3B%0A';

print("WaysCall: <br>");
print($overpassGetPathNodes . "<br><br>");

// collecting results in JSON format
$html = file_get_contents($overpassGetPathNodes);
$wayResult = json_decode($html, true); // "true" to get PHP array instead of an object



//Visulize Way Data
print("<br>");
print("<h1>Ways:</h1>");
//Some Info:
print("Data from: " . $wayResult["generator"] . "  v." . $wayResult["version"] . "<br>");
print("Timestamp OSM: " . $wayResult["osm3s"]["timestamp_osm_base"] . "   Timestamp Area: " . $wayResult["osm3s"]["timestamp_osm_base"] . "<br>");
print("Copyright: " . $wayResult["osm3s"]["copyright"] . "<br>");

print("<br><br>");

// elements key contains the array of all required elements
$wayData = $wayResult['elements'];

foreach($wayData as $way){
    //Roadname
    if(isset($way["tags"]["name"])){
        print("<h2>" . $way["tags"]["name"] . "</h2>");
    }
    //Referenz Name (For example: B213, L387)
    if(isset($way["tags"]["ref"])){
        
        print("<h3>" . $way["tags"]["ref"] . "</h3>");
    }
    //Type is way
    if(isset($way["type"])){
            
            print("Type: " . $way["type"]);
            print("<br>");
    }
    //Way ID
    if(isset($way["id"])){
        
        print("ID: " . $way["id"]);
        print("<br>");
    }
    //Table of Nodes of the road
    if(isset($way["nodes"]) && isset($way["geometry"])){

        print("<table>");

            print("<tr>");
                print("<th>NUM</th>");
                print("<th>Node ID</th>");
                print("<th>LAT</th>");
                print("<th>LON</th>");
            print("</tr>");

            
                for ($i = 0; $i < sizeof($way["nodes"]); $i++) {
                    print("<tr>");
                        //Count
                        print("<td>". $i ."</td>");
                        //Node ID
                        print("<td>". $way["nodes"][$i] ."</td>");
                        //Positions
                        print("<td>". $way["geometry"][$i]["lat"] ."</td>");
                        print("<td>". $way["geometry"][$i]["lon"] ."</td>");
                    print("</tr>");
                }
            

        print("</table>");

    }

    print("<br><br>");

}

//Connect to Data Base:
    //DB INFORMATION
    $servername = "localhost";
    $username = "id21538315_admin";
    $password = "_123Admin";
    $database = "id21538315_data";

    //Connect
    $mysqli = new mysqli($servername, $username, $password, $database);


    //Create temp_way_nodes table
    //Set SQL COMMAND
    $sqlCreateTempWayNodes = 'CREATE TABLE temp_way_nodes (
                    node_id BIGINT,
                    way_id BIGINT,
                    lat decimal(9,7),
                    lon decimal(9,7),
                    way_index INT
                    );';



   //Call DB
    if ($mysqli->query($sqlCreateTempWayNodes) === TRUE) {
        echo "Table temp_way_nodes created successfully";
    } else {
        echo "Error creating table: " . $mysqli->error;
    }

    echo "<br>";

    $sql = 'INSERT INTO temp_way_nodes VALUES';



//Insert Way Nodes in temp_way_nodes

    for($a = 0; $a < sizeof($wayData); $a++){
        $way = $wayData[ $a ];


        for ($i = 0; $i < sizeof($way["nodes"]); $i++) {

            if($a === 0 && $i === 0){
            }else{
                $sql = $sql . ',';
            }

            $sql = $sql . '(' . $way["nodes"][$i]. ',' . $way["id"] . ',' . $way["geometry"][$i]["lat"]. ',' . $way["geometry"][$i]["lon"] . ',' . $i . ')';

            
            
        }

        if($a === (sizeof($wayData) - 1)){
            $sql = $sql . ';';
        }
    }

    echo $sql;
    if ($mysqli->query($sql) !== TRUE) {
        echo "Fehler beim Einfügen: " . $mysqli->error;
    }

    echo "<br><br>TEMP_WAYS<br><br>";


//Create temp_ways table

 //Set Create Table SQL COMMAND
    $sqlCreateTempWays = 'CREATE TABLE temp_ways(
                            way_id BIGINT,
                            way_name VARCHAR(255),
                            ref VARCHAR(255),
                            node_count INT,
                            note TINYTEXT
                            );';



    //Create Table
    if ($mysqli->query($sqlCreateTempWays) === TRUE) {
    echo "Table temp_ways created successfully";
    } else {
    echo "Error creating table: " . $mysqli->error;
    }
    
    //Insert way data in temp_ways table

    echo "<br><br><br>";


    $sql = "INSERT INTO temp_ways VALUES";

    for($i = 0; $i < sizeof($wayData); $i++){
        $way = $wayData[ $i ];


        //Get Data of the way

        $wayID = $way["id"];
        echo "ID: " . $wayID . "<br>";

        $wayNodeCount = sizeof($way["nodes"]);

        echo "NODE COUNT: " . $wayNodeCount . "<br>";

        if(isset($way["tags"]["name"])){
            $wayName = $way["tags"]["name"];

            echo "NAME: " . $wayName . "<br>";

        }else{
            $wayName = "NULL";
        }

        if(isset($way["tags"]["ref"])){
            $wayRef = $way["tags"]["ref"];

            echo "REF: " . $wayRef . "<br>";
        }else{
            $wayRef = "NULL";
        }

        if(isset($way["tags"]["note"])){
            $wayNote = $way["tags"]["note"];

            echo "NOTE: " . $wayNote . "<br>";
        }else{
            $wayNote = "NULL";
        }

        //Remove double and single quotation marks
        $wayName = str_replace('"', " ", $wayName);
        $wayName = str_replace("'", " ", $wayName);

        $wayRef = str_replace('"', " ", $wayRef);
        $wayRef = str_replace("'", " ", $wayRef);

        $wayNote = str_replace('"', " ", $wayNote);
        $wayNote = str_replace("'", " ", $wayNote);
      
        
        echo "<br><br><br>";

        if($i === 0){
        }else{
            $sql = $sql . ',';
        }
        
        $sql = $sql . '(' . $wayID . ', "' . $wayName . '", "' . $wayRef . '", ' . $wayNodeCount . ', "'. $wayNote .  '")';

        if($i === (sizeof($wayData) - 1)){
            $sql = $sql . ';';
        }
    }

    echo "<br><br>Ways:<br>";

    echo $sql;
    echo "<br><br><br>";
    if ($mysqli->query($sql) !== TRUE) {
        echo "Fehler beim Einfügen: " . $mysqli->error;
    }



//Insert hydrant nodes in temp_nodes


//Create temp_hydrants table


//Insert hydrants data in temp_hydrants


//Insert railway milestone nodes in temp_nodes 


//Create temp_railywayMilestones table


//Insert railway milestone data in temp_railywayMilestones


//Update main_ tables with temp_ tabels
    
    //$mysqli->close;

/*

Get next Node to lat and lon

SET @targetLat = 52.4101238; 
SET @targetLon = 10.1834857;

-- SQL-Abfrage zur Berechnung und Auswahl des nächstgelegenen Ortes
SELECT node_id, lat, lon,
       6371 * 2 * ASIN(SQRT(POWER(SIN((@targetLat - lat) * pi()/180 / 2), 2) +
       COS(@targetLat * pi()/180) * COS(lat * pi()/180) *
       POWER(SIN((@targetLon - lon) * pi()/180 / 2), 2))) AS distance
FROM temp_nodes
ORDER BY distance
LIMIT 1;
*/

?>