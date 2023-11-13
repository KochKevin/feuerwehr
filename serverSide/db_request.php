<?php

    //DB INFORMATION
    $servername = "localhost";
    $username = "id21538315_admin";
    $password = "_123Admin";
    $database = "id21538315_data";


    //This file contains mainly the code for requests to the DB and the output in json

    $request = $_REQUEST["q"];
    $output = "";



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
            echo( json_encode($jsonData));
            

            //close the db connection
            mysqli_close($connection);

        break;

        default:
            $output = "error - Undefined request";
        break;
    }

    

?>