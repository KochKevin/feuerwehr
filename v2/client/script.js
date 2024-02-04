class PriorityQueue{
    constructor(){
        this.elements = [];
    }

    enqueue(element, priority) {
        this.elements.push({ element, priority });
        this.elements.sort((a, b) => a.priority - b.priority);
    }
    
    dequeue() {
        return this.elements.shift().element;
    }
    
    isEmpty() {
        return this.elements.length === 0;
    }
}

//vector calculations:
function vectorSubtraction(vector1, vector2) {
    return [vector1[0] - vector2[0], vector1[1] - vector2[1]];
}

function dotProduct(vector1, vector2) {
    return vector1[0] * vector2[0] + vector1[1] * vector2[1];
}

function vectorLength(vector) {
    return Math.sqrt(vector[0]**2 + vector[1]**2);
}

function orthogonalDistance(position, lineStart, lineEnd) {
    // Vektor zwischen den beiden Nodes (Linienvektor)
    const lineVector = vectorSubtraction(lineEnd, lineStart);

    // Vektor vom Startpunkt der Linie zum Benutzer
    const vectorToPosition = vectorSubtraction(position, lineStart);

    // Berechne die Projektion des Benutzer-Vektors auf die Linie
    const projection = dotProduct(vectorToPosition, lineVector) / vectorLength(lineVector);

    // Berechne den orthogonalen Abstand
    const distance = vectorLength(vectorToPosition) - projection;

    return distance;
}


// Beispielanwendung:
const userLocation = [37.7749, -122.4194];  // Benutzerstandort
const lineStart = [37.7750, -122.4190];  // Startpunkt der Linie
const lineEnd = [37.7760, -122.4180];  // Endpunkt der Linie

const distance = orthogonalDistance(userLocation, lineStart, lineEnd);
console.log("Orthogonaler Abstand des Benutzers zur Linie:", distance);

//Change Distance Function to inbuilt leaflet function
//See https://leafletjs.com/reference.html#latlng-distanceto
function calculateDistance(lat1, lon1, lat2, lon2) {
    // Konvertiere Grad in Radian
    const deg2rad = (deg) => deg * (Math.PI / 180);

    // Berechne Differenzen
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);

    // Anwenden der euklidischen Distanzformel
    const distance = Math.sqrt(dLat * dLat + dLon * dLon);

    // Der resultierende Wert ist in Radian, konvertiere ihn in Kilometer (oder eine andere Einheit)
    const earthRadius = 6371; // Radius der Erde in Kilometern
    const distanceInKm = earthRadius * distance;

    return distanceInKm;
}


/*
class Locate
{

    options;

    constructor()
    {
        this.options = {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        };




    }


    Start()
    {

    }

}

*/

class MapSystem
{
    //TODO
    //Add access function for nodeMap

    nodeMap = new Map();
    leafletMap = null;


    //array of node ids of the calulated path
    #pathData;
    //array of all polylines for path
    #routeLines;
    //int count of how many nodes are passed
    #routeProgress;
    //Polyline from current position to next node on path
    #nextNodeLine;
    constructor(nodeNetworkJson, leafletMap){
        //First Step is to convert the JSON to a JS Map
        this.#createNodeMap(nodeNetworkJson);
        this.leafletMap = leafletMap;

    }

    startRouting(startNode, goalNode){
        var cameFrom = this.#dijkstra(startNode, goalNode);
        var fullPath = this.#reconstructPath2(cameFrom, goalNode, startNode);
        this.#drawPath(fullPath, 'red');
    }

    
    startRouting2(startLat, startLon, endLat, endLon)
    {


        var startNode = this.findNearestStreetNode(startLat, startLon);
        var endNode = this.findNearestStreetNode(endLat, endLon);

        console.log("Start Node: " + startNode + " | End Node: " + endNode);

        var camFrom = this.#dijkstra(startNode, endNode);
        var fullPath = this.#reconstructPath2(camFrom, endNode, startNode);
        this.#drawPath(fullPath, 'red');


        L.polyline([[startLat, startLon],[this.nodeMap.get(String(startNode)).lat, this.nodeMap.get(String(startNode)).lon]], {color: 'red'}).addTo(this.leafletMap);
        L.polyline([[endLat, endLon],[this.nodeMap.get(String(endNode)).lat, this.nodeMap.get(String(endNode)).lon]], {color: 'red'}).addTo(this.leafletMap);

        //fullPath[0] ist die End Node
        console.log("Node: " + fullPath[0] + " LAT: " + this.nodeMap.get(String(fullPath[0])).lat + " LON: " + this.nodeMap.get(String(fullPath[0])).lon)

        //this.#currentPathToGo = fullPath;
        //this.#routeIsCalculated = true;
        /*
        
        var pathAlreadyCome;

        var nextNode;
        previousNode = nextNode;
        nextNode = fullPath.pop();
        pathAlreadyCome.push(previousNode);

        */
    }

    

    startRouting3(startNode, goalNode){
        this.#routeProgress = 0;
        

        var cameFrom = this.#dijkstra(startNode, goalNode);
        this.#pathData = this.#reconstructPath2(cameFrom, goalNode, startNode).reverse();
        this.builtRoute(this.#pathData);
        this.#nextNodeLine =  L.polyline([[0, 0],[0, 0]], {color: 'red'}).addTo(this.leafletMap);
    }


    routingUpdate(currentPositionLat, currentPositionLon){

        if (typeof this.#routeProgress == 'undefined') {
            return;
        }

        var currentPosition = L.latLng(currentPositionLat, currentPositionLon);

        console.log("Route Progress: " + this.#routeProgress);

        var currentNodePositon = L.latLng(this.nodeMap.get(String(this.#pathData[this.#routeProgress])).lat, this.nodeMap.get(String(this.#pathData[this.#routeProgress])).lon);

        this.#nextNodeLine.setLatLngs([currentPosition,currentNodePositon]);

        console.log("Distance: " + currentPosition.distanceTo(currentNodePositon));

        //10 guter wert um auf der Straße zu bleiben
        if(currentPosition.distanceTo(currentNodePositon) < 10){


            if(this.#routeProgress == this.#pathData.length - 1){
                console.warn("END OF PATH REACHED");
                return;
            }

            this.#routeLines[this.#routeProgress].setStyle({color: 'grey'});
            
            this.#routeProgress++;

        }

    }

    builtRoute(nodePath)
    {

        this.#routeLines = [];

        for(let i = 0; i < nodePath.length; i++){



            var currentNodeLat = this.nodeMap.get(String(nodePath[i])).lat;
            var currentNodeLon = this.nodeMap.get(String(nodePath[i])).lon;


            if(i == nodePath.length-1){     
                //console.log("Route Size: " + this.#routeLines.length + " vs Node Path Size: " + nodePath.length);
                return;
            }

            
            var nextNodeLat = this.nodeMap.get(String(nodePath[i + 1])).lat;
            var nextNodeLon = this.nodeMap.get(String(nodePath[i + 1])).lon;

            var r = Math.floor(Math.random() * 255);
            var g = Math.floor(Math.random() * 255);
            var b = Math.floor(Math.random() * 255);
            

            var color = "rgb("+r+" ,"+g+","+ b+")"; 

            color = "red";

            this.#routeLines.push(L.polyline([[currentNodeLat, currentNodeLon],[nextNodeLat, nextNodeLon]], {color: color}).addTo(this.leafletMap));
            
        }

        
    }



    findNearestStreetNode(startLat, startLon){

        var shortestDistance = Infinity;
        var nearestNode  = null;
    
    
        this.nodeMap.forEach((value, current) => {
    
            var currentLat = this.nodeMap.get(current).lat;
            var currentLon = this.nodeMap.get(current).lon;
    
            var distance = calculateDistance(startLat, startLon, currentLat, currentLon);
    
            if(distance < shortestDistance) {
                shortestDistance = distance;
                nearestNode = current;
            }
            
        });  
        nearestNode = Number(nearestNode);
        return nearestNode;
    }



    #createNodeMap(nodeNetworkJson){

        for (var nodeId in nodeNetworkJson.nodes) {
            if (nodeNetworkJson.nodes.hasOwnProperty(nodeId)) {
                var node = nodeNetworkJson.nodes[nodeId];
                this.nodeMap.set(nodeId, {
                    lat: node.lat,
                    lon: node.lon,
                    travelCost: node.travelCost,
                    connections: node.connections
                });
            }
        }
    }

    //TODO dijkstra -> A*?
    //Add for better Algo:
    //Speed limits
    //Priority Roads
    //Surface?
    //Tracktypes?
    //Traffic Lights?
    #dijkstra(startNode, goalNode){
        var queue = new PriorityQueue();
        var cameFrom = new Map();
        var costSoFar = new Map();

        queue.enqueue(startNode, 0);
        cameFrom.set(startNode, null);
        costSoFar.set(startNode, 0);

        while(queue.elements.length > 0){
            const currentNode = queue.dequeue();
            const connections = this.nodeMap.get(String(currentNode))?.connections || [];


            if(currentNode == goalNode){
                break;
            }

            for(var connection of connections){
                var newCost

                connection = Number(connection);
                newCost = costSoFar.get(currentNode) + (this.nodeMap.get(String(currentNode)).travelCost + (this.nodeMap.get(String(connection)).travelCost * 2)) / 2;

                if(!costSoFar.has(connection) || newCost < costSoFar.get(connection)){
                    var priority

                    costSoFar.set(connection, newCost);
                    priority = newCost + calculateDistance(this.nodeMap.get(String(connection)).lat, this.nodeMap.get(String(connection)).lon, this.nodeMap.get(String(currentNode)).lat, this.nodeMap.get(String(currentNode)).lon);
                    queue.enqueue(connection, priority);
                    cameFrom.set(connection, currentNode);
                }
            }
        }
        return cameFrom;

    }   

    #reconstructPath2(cameFrom, startNode, goalNode){
        //Reconstruct starts at the inital goalNode and goes back to the inital startNode
        var current = startNode;
        var nodePath = [];

        while(current !== goalNode){
            nodePath.push(current);

            //nodeLat = this.nodeMap.get(String(current)).lat;
            //nodeLon = this.nodeMap.get(String(current)).lon;

            current = cameFrom.get(current);

            if(current == null){
                return nodePath;
            }
            //nextNodeLat = this.nodeMap.get(String(current)).lat;
            //nextNodeLon = this.nodeMap.get(String(current)).lon;
        }
        nodePath.push(goalNode);

        return nodePath;
    }

    
    #drawPath(nodePath, pathColor){
       
        for(let i = 0; i < nodePath.length; i++){

            var currentNodeLat = this.nodeMap.get(String(nodePath[i])).lat;
            var currentNodeLon = this.nodeMap.get(String(nodePath[i])).lon;

            if(i == 0){
                //Mark first Marker
                L.marker([currentNodeLat, currentNodeLon]).addTo(this.leafletMap);
            }

            if(i == nodePath.length-1){
                //Mark last Marker
                L.marker([currentNodeLat, currentNodeLon]).addTo(this.leafletMap);
                return;
            }

            var nextNodeLat = this.nodeMap.get(String(nodePath[i + 1])).lat;
            var nextNodeLon = this.nodeMap.get(String(nodePath[i + 1])).lon;
            //console.log(this.leafletMap);
            L.polyline([[currentNodeLat, currentNodeLon],[nextNodeLat, nextNodeLon]], {color: pathColor}).addTo(this.leafletMap);

        }

    }


}



// Das ist die richtige Ladereinfolge
// Später sollen alle abfragen an den Server, um daten zu laden in der load funktion passieren der rest, der dann passiert, kommt in load().then()
//Wenn load().them() anfängt soll auch der Bildschirm vom ladescreen zum MapMode wechselen

let nodeNetwork;
let nodeEmergency;


async function fetchFile(url){
    try {
        const response = await fetch(url, {method: 'GET', cache: "no-store"});

        if(!response.ok){
            throw new Error('Network response was not ok');
        }

        const data = await response.text();
        console.log("File loaded");

        return JSON.parse(data);
    }
    catch(error){
        console.log('Fetch fehlgeschlagen', error);
        throw error;
    }
}


async function loadFiles(){
    let nodeURL = 'https://equipollent-particl.000webhostapp.com/v2/serverSide/getData.php/?command=nodes';
    let emergencyURL = 'https://equipollent-particl.000webhostapp.com/v2/serverSide/getData.php/?command=emergency';

    try {
        [nodeNetwork, nodeEmergency] = await Promise.all([
            fetchFile(nodeURL),
            fetchFile(emergencyURL)
        ]);

    } catch (error) {
        console.error('Fehler beim Laden der Dateien', error);
    }
}

async function loadSite(){
    //Alle Steps die benötigt werden, dass die website geladen ist
    await loadFiles();
}


loadSite().then(()=>{

    //Nach dem Fetch und Async
    console.log(nodeNetwork);
    console.log(nodeEmergency);
    console.log(nodeNetwork.overpass);

        // Warte darauf, dass das DOM geladen ist
    document.addEventListener('DOMContentLoaded', function () {
    // Hier kannst du Leaflet verwenden, da das DOM geladen wurde

   

    });

    var map = L.map('map');
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'  
    }).addTo(map);

    map.setView([52.41037, 10.17706], 15);

  /*
    Debug function
    Need to moved into MapSystem


    function mapQuery()
    {
        console.log("Map Query");
        nodeMap.forEach((value, current) => {
            console.log(nodeMap.get(String(current)).travelCost);

            currentLat = nodeMap.get(String(current)).lat;
            currentLon = nodeMap.get(String(current)).lon;

            L.marker([currentLat, currentLon]).addTo(map);
            const connections = nodeMap.get(String(current))?.connections || [];
            for(connection of connections){

                connectionLat = nodeMap.get(String(connection)).lat;
                connectionLon = nodeMap.get(String(connection)).lon;

                L.polyline([[currentLat, currentLon],[connectionLat, connectionLon]], {color: 'green'}).addTo(map);
            }
        });
    }
*/

    const mapSys = new MapSystem(nodeNetwork, map);
    //Clear nodeNetwork
    nodeNetwork = null;

    function onMapClick(e){
        pos = e.latlng;
        console.log("Clicked Map at " + pos.lat + " | " + pos.lng);
        var goalNode = mapSys.findNearestStreetNode(pos.lat, pos.lng);
        var startNode = 3605228749


        mapSys.startRouting(startNode, goalNode);

        /*
        var pathToGo;
        //var pathAlreadyCome;  


        var cameFrom = dijkstra(3605228749,goalNode);
        pathToGo = reconstructPath2(cameFrom, goalNode, startNode);
        drawPath(pathToGo, 'red');
        */


    }



    function onMapClick2(e){


        //mapSys.startRouting2(currentPos.latitude, currentPos.longitude, e.latlng.lat, e.latlng.lng);
        //StartNode, GoalNode
        //mapSys.startRouting3(3605228749, 3615504064);
    }
    
    mapSys.startRouting3(3605228749, 3615504064);
    var mouseLatLon;
    var mouseLine =  L.polyline([[0, 0],[0, 0]], {color: 'green'}).addTo(map);

    function onMouseMove(e){

        mouseLatLon = e.latlng;
        //console.log("Mouse Lat Lon: " + mouseLatLon.toString());
        mouseLine.setLatLngs([[52.409928, 10.183599],mouseLatLon]);
        mapSys.routingUpdate(mouseLatLon.lat, mouseLatLon.lng);
    }
//
    

    map.on('click', onMapClick2);
    map.on('mousemove', onMouseMove);





    //Testing Locating:


    let locateId;
    let locateOptions;
    var currentPos;
    var currentPosMarker = L.marker([51.5, -0.09]).addTo(map);

    function onLocateSuccess(pos){
        console.log("New Location Position: " + pos.coords.latitude + " lat " + pos.coords.longitude + " lon | Accuracy: " + pos.coords.accuracy + " | Heading: " + pos.coords.heading + " | Speed: " + pos.coords.speed + " | TIMESTAMP : " + pos.timestamp);
        var date = new Date(pos.timestamp);
        document.getElementById('text').textContent = "Position: " + pos.coords.latitude + " lat " + pos.coords.longitude + " lon | Accuracy: " + pos.coords.accuracy + " | Heading: " + pos.coords.heading + " | Speed: " + pos.coords.speed + " | TIMESTAMP : " + date.getHours() + ":" + date.getMinutes()+":"+ date.getSeconds();
        currentPosMarker.setLatLng(L.latLng(pos.coords.latitude, pos.coords.longitude));

        currentPos = pos.coords;
        //mapSys.startRouting2(pos.coords.latitude, pos.coords.longitude, mouseClickLat, mouseClickLon);
    }

    function onLocateError(err){
        console.warn(`ERROR(${err.code}): ${err.message}`);
    }

    locateOptions = {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 0,
    };

    locateId = navigator.geolocation.watchPosition(onLocateSuccess, onLocateError, locateOptions);


    


});