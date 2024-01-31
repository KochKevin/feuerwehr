const airports = "PHX BKK OKC JFK LAX MEX EZE HEL LOS LAP LIM".split(' ');

const routes = [
    ['PHX', 'LAX'],
    ['PHX', 'JFK'],
    ['JFK', 'OKC'],
    ['JFK', 'HEL'],
    ['JFK', 'LOS'],
    ['MEX', 'LAX'],
    ['MEX', 'BKK'],
    ['MEX', 'LIM'],
    ['MEX', 'EZE'],
    ['LIM', 'BKK']

];




const adjacencyList = new Map();

function addNode(airport){
    adjacencyList.set(airport, [])
}

function addEdge(orign, destination){
    adjacencyList.get(orign).push(destination);
    adjacencyList.get(destination).push(orign);
}

//create Graph
airports.forEach(addNode);
routes.forEach(routes => addEdge(...routes));

console.log(adjacencyList);


function bfs(start) {

    const visited = new Set();

    const queue = [start];


    while (queue.length > 0) {

        const airport = queue.shift(); // mutates the queue

        const destinations = adjacencyList.get(airport);


        for (const destination of destinations) {

            if (destination === 'BKK')  {
                console.log(`BFS found Bangkok!`)
                console.log(airport + ' --- ' + destination);
            }

            if (!visited.has(destination)) {
                visited.add(destination);
                queue.push(destination);
            }
           
        }

        
    }

}

bfs('PHX')
