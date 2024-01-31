<?php

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    //Connect to Data Base:
    //DB INFORMATION
    $servername = "localhost";
    $username = "id21538315_admin";
    $password = "_123Admin";
    $database = "id21538315_data";


    //Connect
    $mysqli = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }


    echo "<br><br>HELLO <br><br>";



    
    //Create temp_way_node_connections table
    //Set SQL COMMAND
    $sqlCreateTempWayNodeConnections = 'CREATE TABLE temp_way_node_connections (
                    node_id BIGINT,
                    connected_nodes TINYTEXT,
                    way_id BIGINT
                    );';

    //Call DB
    if ($mysqli->query($sqlCreateTempWayNodeConnections) === TRUE) {
        echo "Table temp_way_nodes created successfully<br><br>";
    } else {
        echo "Error creating table: " . $mysqli->error. "<br><br>";
    }


    //$sql = "SELECT temp_way_nodes.node_id, temp_way_nodes.way_index, temp_way_nodes.way_id, temp_ways.node_count FROM `temp_way_nodes` LEFT JOIN `temp_ways` ON temp_way_nodes.way_id = temp_ways.way_id ORDER BY temp_way_nodes.way_id;";
    $sql = "SELECT temp_way_nodes.node_id,
            temp_way_nodes.way_index,
            temp_way_nodes.way_id,
            temp_ways.node_count 
            FROM `temp_way_nodes` LEFT JOIN `temp_ways` 
            ON temp_way_nodes.way_id = temp_ways.way_id  
            ORDER BY temp_way_nodes.way_id ASC;";
    $result = $mysqli->query($sql);

    /*
    if ($mysqli->connect_errno) {
        die("Connection failed: " . $mysqli->connect_error . " (Error code: " . $mysqli->connect_errno . ")");
    }
    */

    // Check if the query was successful
    if ($result !== FALSE) {
        echo "Get Data successfully<br>";
        echo "RESULT: " . $result->num_rows . "<br><br>";

    
        //Find neighbouring Nodes and make a "connection" to them


        //$unsortedNodes is an array with every Node and its way neighbours
        //Nodes have also duplicates
        $unsortedNodes = array();
        //$uniqueNodeEdges is an array with every Node and the node it is connected to
        //The whole array is an graph
        $uniqueNodeEdges = array();

        $currentRow = 1;
        while ($row = $result->fetch_assoc()) {


            $node_id = $row['node_id'];
            $way_id = $row['way_id'];
            $way_index = $row['way_index'];
            $node_count = $row['node_count'];


            
            $nextNodeID = null;
            $previousNodeID = null;

            printf("Node_ID: " . $node_id . " | Way_ID: " . $way_id . " | current Row: " . $currentRow . "<br>");
            printf("Current Node Index: ". $way_index . " | Way Node Count: " . $node_count . "<br>");

            if(($way_index - 1) >= 0){

                if($currentRow != 0){
                    //Move a row down
                    $result->data_seek($currentRow - 1);
                    //Get array for the row
                    $previousRow = $result->fetch_row();
                    //get first column of the row which is node_id
                    $previousNodeID = $previousRow[0];
                    //Move back to initall row
                    $result->data_seek($currentRow);
                }

            }else{
                printf("Current Node Index - 1 is too small: " . ($way_index - 1) ."<br>");
            }
            
            if(($way_index + 1) < $node_count){


                if($currentRow != ($result->num_rows)){
                    //Move a row up
                    $result->data_seek($currentRow + 1);
                    //Get array for the row
                    $nextRow = $result->fetch_row();
                    //get first column of the row which is node_id
                    $nextNodeID = $nextRow[0];
                    //Move back to initall row
                    $result->data_seek($currentRow);
                }

            }else{
                printf("Current Node Index + 1 is too big: " . ($way_index + 1) ." | Node Count is: " . $node_count . "<br>");
            }

            printf("Node Connections: <br>");
            printf("--> Previous Node: " . $previousNodeID . "<br>");
            printf("--> Next Node: " . $nextNodeID . "<br>");

            printf("<br><br>");

            //$nodes[$currentRow - 1][0] is always the node_id, [1] is always the way_id, everything else are connected nodes
             //$nodes[$currentRow - 1] $currentRow gets normalized. $currentRow starts initially with 1 now in the array with 0
            $unsortedNodes[$currentRow - 1] = array($node_id, $way_id, $previousNodeID, $nextNodeID);

            $currentRow = $currentRow + 1;

           
        }

        //Sort array and make it to a graph structer

        foreach($unsortedNodes as $edge){
            $node_id = $edge[0];

            if(isset($uniqueNodeEdges[$node_id])){
                // Wenn die ID bereits vorhanden ist, fügen Sie die restlichen Werte hinzu
                $uniqueNodeEdges[$node_id] = array_merge($uniqueNodeEdges[ $node_id ], array_slice($edge,1));
            }else{
                // Wenn die ID noch nicht vorhanden ist, fügen Sie das gesamte Array hinzu
                $uniqueNodeEdges[$node_id] = $edge;
            }
        }

        //Clean array from empty values usw.

        function removeEmptyValues($array) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    // Wenn der Wert ein Array ist, rufen Sie die Funktion rekursiv auf
                    $array[$key] = removeEmptyValues($value);
        
                    // Wenn das resultierende Array nach der Bereinigung leer ist, entfernen Sie das Element
                    if (empty($array[$key])) {
                        unset($array[$key]);
                    }
                } else {
                    // Wenn der Wert leer ist, entfernen Sie das Element
                    if (empty($value)) {
                        unset($array[$key]);
                    }
                }
            }
        
            return array_values($array);
        }

        
        $uniqueNodeEdges = removeEmptyValues($uniqueNodeEdges);

        //Debug:
        //print_r(array_values($uniqueNodeEdges));


        print_r("<br><br><br><br>");

        //Package the Nodes/graph structure into an node_id, josn, way_id format. That is can be loaded to the DB

        $sql = 'INSERT INTO temp_way_node_connections VALUES ';

        for($a = 0; $a < sizeof($uniqueNodeEdges); $a++){
            $nodeConnection = $uniqueNodeEdges[$a];
            

            $node_id = $nodeConnection[0];
            $way_id = $nodeConnection[1];

            $sql = $sql . '(';

            $sql = $sql . $node_id . ', ';
            $sql = $sql . '\'[';

            for($i = 2; $i < count($nodeConnection);$i++){
                $connected_node_id = $nodeConnection[$i];

                if($i < count($nodeConnection)-1){
                    $sql = $sql . $connected_node_id. ', ';
                }else{
                    $sql = $sql . $connected_node_id. ''; 
                }
            }

            $sql = $sql . ']\', ';

            if($a < count($uniqueNodeEdges)-1){
                $sql = $sql . $way_id . '), ';
            }else{
                $sql = $sql . $way_id . ');';
            }
            
        }

        print_r($sql);

        if ($mysqli->query($sql) !== TRUE) {
            echo "Fehler beim Einfügen: " . $mysqli->error;
        }

    }else {
        echo "Error : " . $mysqli->error;
    }

    $mysqli->close();    
       
?>