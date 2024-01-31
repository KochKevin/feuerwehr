//Only returns a JSON string which needs to be prased
function callDB(request, parameter1 = "", parameter2 = ""){
    //Construct URL
    var url = new URL('https://equipollent-particl.000webhostapp.com/serverSide/db_request.php');
    url.searchParams.append("command", request);
    url.searchParams.append("parameter1", parameter1);
    url.searchParams.append("parameter2", parameter2);

    var url_string = url.toString();

   /*
    alert("start fetching with:");
    alert(request + "  " + parameter1 + "  " + parameter2);
    alert("to: " + url);
    */

    //Start rewuest with fetch
    return fetch(url, {
    	method: "GET",
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    })
    .then((response) => response.json())
    .then((responseData) => {
        //alert(responseData);
        return responseData;
    })
    .catch(error => alert("FETCHING ERROR:  " + error + " WITH   " + responseData));
  


    
}





