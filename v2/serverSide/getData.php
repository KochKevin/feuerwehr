<?php
//Wenn aufgerufen sendet es eine rohe datei

// Setze CORS-Header
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");


function sendData($filePath){
    // Versuche, den Inhalt der Datei zu lesen
    $fileContent = file_get_contents($filePath);

    // Prüfe, ob das Lesen erfolgreich war
    if ($fileContent !== false) {
        // Antwort als Text setzen
        echo $fileContent;
    } else {
        // Fehlerantwort, falls das Lesen fehlschlägt
        http_response_code(500); // Internal Server Error
        echo "Fehler beim Lesen der Datei.";
    }
}


$command = htmlspecialchars($_GET["command"]);

switch($command){

    case "nodes":
        sendData("data/nodeConnections.txt");
    break;

    case "emergency":
        sendData("data/emergency.txt");
    break;

}

?>
