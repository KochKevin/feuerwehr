<?php

    //DB INFORMATION
    $servername = "localhost";
    $username = "id21538315_admin";
    $password = "_123Admin";
    $database = "id21538315_data";


    //This file contains mainly the code for requests to the DB and the output in json

    $request = $_REQUEST["command"];
    $parameter1 = $_REQUEST["parameter1"];
    $parameter2 = $_REQUEST["parameter2"];
    $parameter3 = $_REQUEST["parameter3"];
    $parameter4 = $_REQUEST["parameter4"];
    
    $output = "";

    $request = "getWayNodes";


    switch($request){

        case "getIDsandPos":

            //Connect
            $mysqli = new mysqli($servername, $username, $password, $database);

            //Set SQL COMMAND
            $sqlCommand = "SELECT node_id, lon, lat FROM `hydrants`;";

            //Gather Data from DB
            $result = $mysqli->query($sqlCommand);
        
            //go trough each row and add it to json
            while($row = $result->fetch_array(MYSQLI_ASSOC))
            {
                $node_id = $row["node_id"];
                $lon = $row["lon"];
                $lat = $row["lat"];

                $jsonData[] = array("node_id"=> $node_id, "lon"=> $lon, "lat"=> $lat);
            }

            //make it to json code and output it
            echo(json_encode($jsonData));
            

            //close the db connection
            mysqli_close($connection);

        break;

        
        case "getInfo":

            //Connect
            $mysqli = new mysqli($servername, $username, $password, $database);

            //Set SQL COMMAND
            $sqlCommand = "";

            //IF parameter1 is empty than get all hydrant infos if not get the specify hydrant
            if(empty($parameter1)){
                $sqlCommand = "SELECT diameter, position, type, pressure, water_source FROM `hydrants`; ";
            }else{
                $sqlCommand = "SELECT diameter, position, type, pressure, water_source FROM `hydrants` WHERE node_id=".$parameter1."; ";
            }

            //Gather Data from DB
            $result = $mysqli->query($sqlCommand);
        
            //go trough each row and add it to json
            while($row = $result->fetch_array(MYSQLI_ASSOC))
            {
                $diameter = $row["diameter"];
                $position = $row["position"];
                $type = $row["type"];
                $pressure = $row["pressure"];
                $water_source = $row["water_source"];

                $jsonData[] = array("diameter"=> $diameter, "position"=> $position, "type"=> $type, "pressure"=> $pressure, "water_source"=> $water_source);
            }

            //make it to json code and output it
            echo( json_encode($jsonData));
            

            //close the db connection
            mysqli_close($connection);

        break;

        case "getWayNodes":

            $mysqli = new mysqli($servername, $username, $password, $database);

            $sqlCommand =  "SELECT
                            temp_way_node_connections.node_id,
                            temp_way_nodes.lat,
                            temp_way_nodes.lon,
                            temp_way_node_connections.connected_nodes
                            FROM `temp_way_node_connections` LEFT JOIN `temp_way_nodes` 
                            ON temp_way_node_connections.way_id = temp_way_nodes.way_id;";

            $queryTime = microtime(true);
            //Gather Data from DB
            $result = $mysqli->query($sqlCommand);

            echo("Query Time: " . microtime(true) - $queryTime . "<br><br>");

            $jsonCreateTime = microtime(true);

            //go trough each row and add it to json
            while($row = $result->fetch_array(MYSQLI_ASSOC))
            {
                $node_id = $row["node_id"];
                $lat = $row["lat"];
                $lon = $row["lon"];
                $connected_nodes = $row["connected_nodes"];
                
                //Mache die daten zu zu einem array die den namen von node_id haben das man die daten über node_id bekommt
                $jsonData[] = array("node_id"=> $node_id, "lon"=> $lon, "lat"=> $lat, "connected_nodes"=> json_decode($connected_nodes, true));
            }

            echo("JSON Creation Time: " . microtime(true) - $jsonCreateTime . "<br><br>");

            //make it to json code and output it

            $jsonEncodeTime = microtime(true);

            echo("<br><br>");

            echo(json_encode($jsonData));

            echo("<br><br>");

            //json_encode($jsonData);
            
            $myfile = fopen("jsonData.txt", "w") or die("Unable to open file!");
            fwrite($myfile, json_encode($jsonData));
            fclose($myfile);

            echo("JSON Finshed <br>");

            echo("JSON Progress Time: " . microtime(true) - $jsonEncodeTime . "<br>");

            

            $getPosNextToTime = microtime(true);

            //Get Next Node which is near to the start Position:
            //Start Pos:
            $startPosLat = $parameter1;
            $startPosLon = $parameter2;

            $sql =  "SELECT `node_id`,`lat`,`lon`"
                    . "FROM temp_way_nodes"
                    . "ORDER BY ACOS(SIN(RADIANS(temp_way_nodes.lat)) * SIN(RADIANS(" . $startPosLat . ")) +"
                    . "             COS(RADIANS(temp_way_nodes.lat)) * COS(RADIANS(" . $startPosLat. ")) *"
                    . "             COS(RADIANS(temp_way_nodes.lon - " . $startPosLon . "))) * 6371"
                    . "LIMIT 1;";


            $result = $mysqli->query($sqlCommand);

            $row = $result->fetch_array(MYSQLI_ASSOC);

            $firstNodeID = $row["node_id"];
            $firstNodeLat = $row["lat"];
            $firstNodeLon = $row["lon"];

            //Get Next Node which is near to the destionation Position:
            //Start Pos:
            $destinationPosLat = $parameter2;
            $destinationPosLon = $parameter3;

            $sql =  "SELECT `node_id`,`lat`,`lon`"
                    . "FROM temp_way_nodes"
                    . "ORDER BY ACOS(SIN(RADIANS(temp_way_nodes.lat)) * SIN(RADIANS(" . $destinationPosLat . ")) +"
                    . "             COS(RADIANS(temp_way_nodes.lat)) * COS(RADIANS(" . $destinationPosLat. ")) *"
                    . "             COS(RADIANS(temp_way_nodes.lon - " . $destinationPosLon . "))) * 6371"
                    . "LIMIT 1;";


            $result = $mysqli->query($sqlCommand);

            $row = $result->fetch_array(MYSQLI_ASSOC);

            $destinationNodeID = $row["node_id"];
            $destinationNodeLat = $row["lat"];
            $destinationNodeLon = $row["lon"];

            echo("Getting Position in ther near: " . microtime(true) - $getPosNextToTime . "<br><br>");

            //Path finding BFS



             //close the db connection
             mysqli_close($connection);






        break;

        default:
            $output = "error - Undefined request";
        break;
    }

    

?>