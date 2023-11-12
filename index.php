<?php

  $servername = "localhost";
  $username = "id21538315_admin";
  $password = "_123Admin";
  $database = "id21538315_data";



  //Connect
  $mysqli = new mysqli($servername, $username, $password, $database);

  if ($result = $mysqli->query("SELECT * FROM hydrants")) {

    printf("Datenbank enthält %d Einträge.\n", $result->num_rows);
  
    echo "<br>";

    //Table Head
    echo "<table>";
    echo "
    <tr>
      <th>NODE ID</th>
      <th>POSITION</th>
      <th>DIAMETER</th>
      <th>GROUND</th>
      <th>TYPE</th>
      <th>PRESSURE</th>
      <th>WATER SOURCE</th>
    </tr>";

    while ($row = $result->fetch_assoc()) {

      //Table Row
      echo "<tr>";
      
        echo "<th>".$row["node_id"]."</th>";
        echo "<th>".$row["lon"] . " lon   " . $row["lat"] . " lat" . "</th>";
        echo "<th>".$row["diameter"]."</th>";
        echo "<th>".$row["position"]."</th>";
        echo "<th>".$row["type"]."</th>";
        echo "<th>".$row["pressure"]."</th>";
        echo "<th>".$row["water_source"]."</th>";

      echo "</tr>";
      
    }
//End Table
    echo "</table>";

    /* free result set */
    $result->free();
}
  
?>

<DOCTYPE html>
<html lang="de">

  <head>
    <meta charset="utf-8">
    <title>Welcome</title>
  </head>

  <body>

    <h1>Hello</h1>




  </body>  

 
</html>
