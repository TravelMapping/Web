//
// CHM Data Viewer-related Javascript functions
//
// Load and view data files related to Clinched Highway Mapping (CHM)
// related academic data sets.
//
// Renamed as tmjsfuncs.js as part of the Travel Mapping project
//
// Author: Jim Teresco, Siena College, The College of Saint Rose
//
// Code developed based on examples from 
// http://cmap.m-plex.com/tools/wptedit/wptedit.html
// http://www.alecjacobson.com/weblog/?p=1645
//
// Modification History:
//
// 2011-06-20 JDT  Initial implementation
// 2011-06-21 JDT  Added .gra support and checkbox for hidden marker display
// 2011-06-23 JDT  Added .nmp file support (near-miss points)
// 2011-08-23 JDT  Added .pth file support (path)
// 2011-08-31 JDT  Added tabular graph data display
// 2013-08-14 JDT  Completed conversion to Google Maps API V3
// 2013-08-15 JDT  Added custom icon for intersections
// 2013-12-08 JDT  Fixed to handle DOS-style CRLF in uploaded files
// 2013-12-25 JDT  Click on GRA, PTH point label in table recenters map
// 2014-11-17 JDT  Added .wpl file support (waypoint list)
// 2015-06-10 JDT  Adapted for reading from database entries using PHP
// 2015-06-14 JDT  Clinched segment support
// 2015-06-17 JDT  All highways in region support
// 2015-08-19 JDT  Fixed a few bugs with infowindows
//
// $Id: chmviewerfunc3.js 2535 2015-01-28 18:46:22Z terescoj $
//

// global variable to hold the map, which will be assigned a google.maps.Map reference
var map;

// array of waypoints displayed
var waypoints = new Array();
// array of waypoint indices where route changes for region mapping
var newRouteIndices = new Array();
// tiers of each route included
var routeTier = new Array();
// color code for each route included
var routeColor = new Array();
// the markers at those waypoints
var markers = new Array();
// the info displayed when markers are clicked
var markerinfo = new Array();
// array of google.maps.LatLng representing the waypoint coordinates
var polypoints = new Array();
// array of connections on map as google.maps.Polyline overlays
var connections = new Array();
// array of graph edges (for graph data)
var graphEdges = new Array();
// array of segments and clinched for "clinched by traveler" mapping
var segments = new Array();
var clinched = new Array();
// boolean to say if we're doing this
var mapClinched = false;
// traveler name for clinched
var traveler;
// boolean to determine if graph edges should be generated automatically
var genEdges = false;

// array of objects that define color codes from names in the DB
var colorCodes = new Array();
colorCodes[0] = { name: "blue", unclinched: "rgb(100,100,255)", clinched: "rgb(0,0,255)" };
colorCodes[1] = { name: "brown", unclinched: "rgb(153,152,102)", clinched: "rgb(153,102,0)" };
colorCodes[2] = { name: "red", unclinched: "rgb(255,100,100)", clinched: "rgb(255,0,0)" };
colorCodes[3] = { name: "yellow", unclinched: "rgb(255,255,128)", clinched: "rgb(225,225,0)" };
colorCodes[4] = { name: "teal", unclinched: "rgb(100,200,200)", clinched: "rgb(0,200,200)" };
colorCodes[5] = { name: "green", unclinched: "rgb(100,255,100)", clinched: "rgb(0,255,0)" };
colorCodes[6] = { name: "magenta", unclinched: "rgb(255,100,255)", clinched: "rgb(255,0,255)" };

var infowindow = new google.maps.InfoWindow();

// some map options, from http://cmap.m-plex.com/hb/maptypes.js by Timothy Reichard

var MapnikOptions = { alt: "Show Mapnik road map tiles from OpenStreetMap.org",
		            getTileUrl: getMapnikTileURL,
		            maxZoom: 18,
		            minZoom: 0,
		            name: "Mapnik",
		            opacity: 1,
		            tileSize: new google.maps.Size(256, 256)
		          };

function getMapnikTileURL(point, zoom)
{
    return 'http://tile.openstreetmap.org/' + zoom + '/' + point.x + '/' + point.y + '.png';
}

var MQOpenMapOptions = { alt: "Show Mapquest Open Map road map tiles based on OpenStreetMap.org data",
			       getTileUrl: getMQOpenMapTileURL,
			       maxZoom: 18,
			       minZoom: 0,
			       name: "MQOpenMap",
			       opacity: 1,
			       tileSize: new google.maps.Size(256, 256)
			     };

function getMQOpenMapTileURL(point, zoom)
{
    return 'http://otile4.mqcdn.com/tiles/1.0.0/map/' + zoom + '/' + point.x + '/' + point.y + '.jpg';
    //return 'http://cmap.m-plex.com/hb/ymaptile.php?t=m&s=mq&x=' + point.x + '&y=' + point.y + '&z=' + zoom;
}



var MQOpenSatOptions = { alt: "Show Mapquest Open Map satellite imagery tiles based on OpenStreetMap.org data",
			       getTileUrl: getMQOpenSatTileURL,
			       maxZoom: 18,
			       minZoom: 0,
			       name: "MQOpenSat",
			       opacity: 1,
			       tileSize: new google.maps.Size(256, 256)
			     };

function getMQOpenSatTileURL(point, zoom)
{
    return 'http://otile4.mqcdn.com/tiles/1.0.0/sat/' + zoom + '/' + point.x + '/' + point.y + '.jpg';
    //return 'http://cmap.m-plex.com/hb/ymaptile.php?t=s&s=mq&x=' + point.x + '&y=' + point.y + '&z=' + zoom;
}



var intersectionimage = {
    url: 'smallintersection.png',
    // This marker is 16x16
    size: new google.maps.Size(16, 16),
    // The origin for this image is 0,0.
    origin: new google.maps.Point(0,0),
    // The anchor for this image is the center of the intersection
    anchor: new google.maps.Point(8, 8)
  };

// loadmap constructs and sets up the initial map
function loadmap() {
    var typeMQOpenMap = new google.maps.ImageMapType(MQOpenMapOptions);
    var typeMQOpenSat = new google.maps.ImageMapType(MQOpenSatOptions);
    var typeMapnik = new google.maps.ImageMapType(MapnikOptions);

    var maptypelist = ['MQOpenMap', 'MQOpenSat', 'Mapnik', google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE, google.maps.MapTypeId.HYBRID, google.maps.MapTypeId.TERRAIN];
    var maptypecontroloptions = {mapTypeIds: maptypelist, position: google.maps.TOP_RIGHT, style: google.maps.MapTypeControlStyle.DROPDOWN_MENU};
    //var mapopt = {center: new google.maps.LatLng(42.664529, -73.786470), zoom: 12, mapTypeId: 'Mapnik', mapTypeControl: true, mapTypeControlOptions: maptypecontroloptions, streetViewControl: true, disableDefaultUI: true, panControl: true, zoomControl: true, scaleControl: true, overviewMapControl: true, keyboardShortcuts: true, disableDoubleClickZoom: false};
    // coordinates are Albertus Hall room 400-2 at The College of Saint Rose
    var mapopt = {center: new google.maps.LatLng(42.664529, -73.786470), zoom: 16, mapTypeControl: true, mapTypeControlOptions: maptypecontroloptions};

    map = new google.maps.Map(document.getElementById("map"), mapopt);
    
    map.mapTypes.set('MQOpenMap', typeMQOpenMap);
    map.mapTypes.set('MQOpenSat', typeMQOpenSat);
    map.mapTypes.set('Mapnik', typeMapnik);

    //document.getElementById('showHidden').checked=false;
    //var showHidden = document.getElementById('showHidden').checked;
    //DBG.write("loadmap: showHidden is " + showHidden);

    // check for a load query string parameter
    //var qs = location.search.substring(1);
    //DBG.write("qs: " + qs);
    //var qsitems = qs.split('&');
    //for (var i = 0; i < qsitems.length; i++) {
	//DBG.write("qsitems[" + i + "] = " + qsitems[i]);
	//var qsitem = qsitems[i].split('=');
	//DBG.write("qsitem[0] = " + qsitem[0]);
	//if (qsitem[0] == "load") {
	    //var request = new XMLHttpRequest();
	    //DBG.write("qsitem[1] = " + qsitem[1]);
	    //document.getElementById('filename').innerHTML = qsitem[1];
	    //request.open("GET", qsitem[1], false);
	    //request.setRequestHeader("User-Agent", navigator.userAgent);
	    //request.send(null);
	    //if (request.status == 200) {
		//processContents(request.responseText);
	    //}
	//}
    //}
    waypointsFromSQL();  // function inserted by PHP in the index.php file
    updateMap();
}

// when a file is selected, this will be called
function startRead() {
    // first, retrieve the selected file (as a File object)
    var file = document.getElementById('file').files[0];
    if (file) {
	document.getElementById('filename').innerHTML = file.name;
	if ((file.name.indexOf(".wpt") == -1) &&
	    (file.name.indexOf(".pth") == -1) &&
	    (file.name.indexOf(".nmp") == -1) &&
	    (file.name.indexOf(".gra") == -1) &&
	    (file.name.indexOf(".wpl") == -1)) {
	    document.getElementById('pointbox').innerHTML = "<b>Unrecognized file type!</b>";
	    return;
	}
	document.getElementById('pointbox').innerHTML = 
	    "Loading... (" + file.size + " bytes)";
	var reader;
	try {
	    reader = new FileReader();
	}
	catch(e) {
	    document.getElementById('pointbox').innerHTML = 
		"<b>Error: unable to access file (Perhaps no browser support?  Try recent Firefox or Chrome releases.).</b>";
	    return;
	}
	reader.readAsText(file, "UTF-8");
	reader.onload = fileLoaded;
	//reader.onerror = fileLoadError;
    }
}


// when the FileReader created in startRead has finished, this will be called
// to process the contents of the file
function fileLoaded(event) {

    // file done loading, read the contents
    processContents(event.target.result);
}

// process the contents of a String which came from a file or elsewhere
function processContents(fileContents) {

    // place the contents into the file contents area (will improve later)
    document.getElementById('pointbox').innerHTML = "<pre>" + fileContents + "</pre>";

    var pointboxContents = "";

    // parse the file and process as appropriate
    var fileName = document.getElementById('filename').innerHTML;
    if (fileName.indexOf(".wpt") >= 0) {
	document.getElementById('filename').innerHTML = fileName + " (Waypoint File)";
	pointboxContents = parseWPTContents(fileContents);
    }
    else if (fileName.indexOf(".pth") >= 0) {
	document.getElementById('filename').innerHTML = fileName + " (Waypoint Path File)";
	pointboxContents = parsePTHContents(fileContents);
    }
    else if (fileName.indexOf(".nmp") >= 0) {
	document.getElementById('filename').innerHTML = fileName + " (Near-Miss Point File)";
	pointboxContents = parseNMPContents(fileContents);
    }
    else if (fileName.indexOf(".wpl") >= 0) {
	document.getElementById('filename').innerHTML = fileName + " (Waypoint List File)";
	pointboxContents = parseWPLContents(fileContents);
    }
    else if (fileName.indexOf(".gra") >= 0) {
	document.getElementById('filename').innerHTML = fileName + " (Highway Graph File)";
	pointboxContents = parseGRAContents(fileContents);
    }
    
    document.getElementById('pointbox').innerHTML = pointboxContents;
    updateMap();

}

// in case we get an error from the FileReader
function errorHandler(evt) {
    
    if (evt.target.error.code == evt.target.error.NOT_READABLE_ERR) {
	// The file could not be read
	document.getElementById('filecontents').innerHTML = "Error reading file...";
    }
}

// parse the contents of a .gra file
//
// First line specifies the number of vertices, numV, and the number
// of edges, numE
// Next numV lines are a waypoint name (a String) followed by two
// floating point numbers specifying the latitude and longitude
// Next numE lines are vertex numbers (based on order in the file)
// that are connected by an edge followed by a String listing the
// highway names that connect those points
function parseGRAContents(fileContents) {

    var lines = fileContents.replace(/\r\n/g,"\n").split('\n');
    var counts = lines[0].split(' ');
    var numV = parseInt(counts[0]);
    var numE = parseInt(counts[1]);
    var sideInfo = '<p style="font-size:12pt">' + numV + " waypoints, " + numE + " connections.</p>";

    var vTable = '<table class="gratable"><thead><tr><th colspan="3">Waypoints</th></tr><tr><th>#</th><th>Coordinates</th><th>Waypoint Name</th></tr></thead><tbody>';

    waypoints = new Array(numV);
    for (var i = 0; i < numV; i++) {
	var vertexInfo = lines[i+1].split(' ');
	waypoints[i] = new Waypoint(vertexInfo[0], vertexInfo[1], vertexInfo[2], "", "");
	vTable += '<tr><td>' + i + 
	    '</td><td>(' + parseFloat(vertexInfo[1]).toFixed(3) + ',' +
	    parseFloat(vertexInfo[2]).toFixed(3) + ')</td><td>'
	    + "<a onclick=\"javascript:LabelClick(" + i + ",'"
	    + waypoints[i].label + "\',"
	    + waypoints[i].lat + "," + waypoints[i].lon + ",0);\">"
	    + waypoints[i].label + "</a></td></tr>"
    }
    vTable += '</tbody></table>';

    var eTable = '<table class="gratable"><thead><tr><th colspan="3">Connections</th></tr><tr><th>#</th><th>Route Name(s)</th><th>Endpoints</th></tr></thead><tbody>';
    graphEdges = new Array(numE);
    for (var i = 0; i < numE; i++) {
	var edgeInfo = lines[i+numV+1].split(' ');
	graphEdges[i] = new GraphEdge(edgeInfo[0], edgeInfo[1], edgeInfo[2]);
	eTable += '<tr><td>' + i + '</td><td>' + edgeInfo[2] + '</td><td>'
	    + edgeInfo[0] + ':&nbsp;' + waypoints[graphEdges[i].v1].label + 
	    ' &harr; ' + edgeInfo[1] + ':&nbsp;' 
	    + waypoints[graphEdges[i].v2].label + '</td></tr>';
    }
    eTable += '</tbody></table>';
    genEdges = false;
    return sideInfo + vTable + '<p />' + eTable;
}

// parse the contents of a .wpt file
//
// Consists of a series of lines each containing a waypoint name
// and an OSM URL for that point's location:
//
/* 
YT1_S http://www.openstreetmap.org/?lat=60.684924&lon=-135.059652
MilCanRd http://www.openstreetmap.org/?lat=60.697199&lon=-135.047250
+5 http://www.openstreetmap.org/?lat=60.705383&lon=-135.054932
4thAve http://www.openstreetmap.org/?lat=60.712623&lon=-135.050619
*/
function parseWPTContents(fileContents) {

    var lines = fileContents.replace(/\r\n/g,"\n").split('\n');
    graphEdges = new Array();
    waypoints = new Array();
    for (var i = 0; i < lines.length; i++) {
	if (lines[i].length > 0) {
	    waypoints[waypoints.length] = WPTLine2Waypoint(lines[i]);
	}
    }
    genEdges = true;
    return "<h2>Raw file contents:</h2><pre>" + fileContents + "</pre>";
}

// parse the contents of a .pth file
//
// Consists of a series of lines each containing a waypoint name and a
// latitude and a longitude, and a route name, all space-separated, or
// a line containing a waypoint name followed by a lat,lng pair in
// parens, followed by a route name
//
/* 
START YT1_S 60.684924 135.059652
YT2 MilCanRd 60.697199 135.047250
YT2 +5 60.705383 135.054932
YT2 4thAve 60.712623 135.050619

or

START YT1_S (60.684924,135.059652)
YT2 MilCanRd (60.697199,135.047250)
YT2 +5 (60.705383,135.054932)
YT2 4thAve (60.712623,135.050619)

*/
function parsePTHContents(fileContents) {

    var table = '<table class="pthtable"><thead><tr><th>Route</th><th>To Point</th><th>Seg.<br>Miles</th><th>Cumul.<br>Miles</th></tr></thead><tbody>';
    var lines = fileContents.replace(/\r\n/g,"\n").split('\n');
    graphEdges = new Array();
    waypoints = new Array();
    var totalMiles = 0.0;
    var segmentMiles = 0.0;
    for (var i = 0; i < lines.length; i++) {
	if (lines[i].length > 0) {
	    waypoints[waypoints.length] = PTHLine2Waypoint(lines[i]);
	    if (waypoints.length > 1) { // make sure we are not at the first
		segmentMiles = Mileage(waypoints[waypoints.length-2].lat,
				       waypoints[waypoints.length-2].lon,
				       waypoints[waypoints.length-1].lat,
				       waypoints[waypoints.length-1].lon);
		totalMiles += segmentMiles;
	    }
	    table += '<tr><td>' + waypoints[waypoints.length-1].elabel +
		"</td><td><a onclick=\"javascript:LabelClick(" + 0 + ",\'"
	        + waypoints[waypoints.length-1].label + "\',"
	        + waypoints[waypoints.length-1].lat + "," + waypoints[waypoints.length-1].lon +
		",0);\">" + waypoints[waypoints.length-1].label +
		'</a></td><td style="text-align:right">' + segmentMiles.toFixed(2) +
		'</td><td style="text-align:right">' + totalMiles.toFixed(2) +
		'</td></tr>';
	}
    }
    table += '</tbody></table>';
    genEdges = true;
    return table;
}

// parse the contents of a .nmp file
//
// Consists of a series of lines, each containing a waypoint name
// followed by two floating point numbers representing the point's
// latitude and longitude
//
// Entries are paired as "near-miss" points, and a graph edge is
// added between each pair for viewing.
//
function parseNMPContents(fileContents) {

    var table = '<table class="nmptable"><thead /><tbody>';
    // all lines describe waypoints
    var lines = fileContents.replace(/\r\n/g,"\n").split('\n');
    waypoints = new Array();
    for (var i = 0; i < lines.length; i++) {
	if (lines[i].length > 0) {
	    var xline = lines[i].split(' ');
	    if (xline.length == 3) {
		waypoints[waypoints.length] = new Waypoint(xline[0], xline[1], xline[2], "", "");
	    }
	}
    }
    // graph edges between pairs, will be drawn as connections
    var numE = waypoints.length/2;
    graphEdges = new Array(numE);
    for (var i = 0; i < numE; i++) {
	// add the edge
	graphEdges[i] = new GraphEdge(2*i, 2*i+1, "");

	// add an entry to the table to be drawn in the pointbox
	var miles = Mileage(waypoints[2*i].lat, waypoints[2*i].lon, waypoints[2*i+1].lat, waypoints[2*i+1].lon).toFixed(4);
	var feet = Feet(waypoints[2*i].lat, waypoints[2*i].lon, waypoints[2*i+1].lat, waypoints[2*i+1].lon).toFixed(2);
	table += "<tr><td><table class=\"nmptable2\"><thead /><tbody><tr><td>"
	    + "<a onclick=\"javascript:LabelClick(" + 2*i + ",\'"
	    + waypoints[2*i].label + "\',"
	    + waypoints[2*i].lat + "," + waypoints[2*i].lon + ",0);\">"
	    + waypoints[2*i].label + "</a></td><td>("
	    + waypoints[2*i].lat + ","
	    + waypoints[2*i].lon + ")</td></tr><tr><td>"
	    + "<a onclick=\"javascript:LabelClick(" + 2*i+1 + ",\'"
	    + waypoints[2*i+1].label + "\',"
	    + waypoints[2*i+1].lat + "," + waypoints[2*i+1].lon + ",0);\">"
	    + waypoints[2*i+1].label + "</a></td><td>("
	    + waypoints[2*i+1].lat + ","
	    + waypoints[2*i+1].lon + ")</td></tr>"
	    + "</tbody></table></td><td>"
	    + miles  + " mi/"
	    + feet + " ft</td></tr>";
    }

    table += "</tbody></table>";
    genEdges = false;
    return table;
}

// parse the contents of a .wpl file
//
// Consists of a series of lines, each containing a waypoint name
// followed by two floating point numbers representing the point's
// latitude and longitude
//
function parseWPLContents(fileContents) {

    var table = '<table class="nmptable"><thead /><tbody>';
    // all lines describe waypoints
    var lines = fileContents.replace(/\r\n/g,"\n").split('\n');
    waypoints = new Array();
    for (var i = 0; i < lines.length; i++) {
	if (lines[i].length > 0) {
	    var xline = lines[i].split(' ');
	    if (xline.length == 3) {
		waypoints[waypoints.length] = new Waypoint(xline[0], xline[1], xline[2], "", "");
	    }
	}
    }
    // no edges here
    graphEdges = new Array();
    genEdges = false;
    return "<h2>Raw file contents:</h2><pre>" + fileContents + "</pre>";
}

// construct a new Waypoint object (based on similar function by Tim Reichard)
function Waypoint (label, lat, lon, errors, elabel) {
    this.label = label;
    this.lat = parseFloat(lat).toFixed(6);
    this.lon = parseFloat(lon).toFixed(6);
    this.visible = true;
    if (label.indexOf("+") >= 0) {
	this.visible = false;
    }
    this.errors = 0;
    this.elabel = elabel;
    return this;
}

function CopyWaypoint(wpt) {
    
    return new Waypoint(wpt.label, wpt.lat, wpt.lon, wpt.errors, wpt.elabel);
}

function WPTLine2Waypoint(line) {
    
    // remove extraneous spaces in the line
    line = line.replace('  ', ' ');
    line = line.replace('  ', ' ');
    line = line.replace('  ', ' ');
    line = line.replace('  ', ' ');
    
    var xline = line.split(' ');
    if (xline.length < 2) {
	return Waypoint('bad-line', 0, 0);
    }
    var label = xline[0];
    var url = xline[1];
    var latlon = Url2LatLon(url);
    return new Waypoint(label, latlon[0], latlon[1], 0, "");
}

// convert an openstreetmap URL to a latitude/longitude
function Url2LatLon(url) {

    var latlon = new Array(0., 0.);
    var floatpattern = '([-+]?[0-9]*\.?[0-9]+)';
    var latpattern = 'lat=' + floatpattern;
    var lonpattern = 'lon=' + floatpattern;

    //search for lat
    var matches = url.match(latpattern);
    if (matches != null) {
	latlon[0] = parseFloat(matches[1]).toFixed(6);
    }
    
    //search for lon
    matches = url.match(lonpattern);
    if (matches != null) {
	latlon[1] = parseFloat(matches[1]).toFixed(6);
    }

    return latlon;
}

function PTHLine2Waypoint(line) {
    
    // remove any extraneous spaces in the line
    line = line.replace('  ', ' ');
    line = line.replace('  ', ' ');
    line = line.replace('  ', ' ');
    line = line.replace('  ', ' ');

    var xline = line.split(' ');
    // check for and convert a (lat,lng) format
    if ((xline.length == 3) &&
	(xline[2].charAt(0) == '(') &&
	(xline[2].indexOf(',') > 0) &&
	(xline[2].charAt(xline[2].length-1) == ')')) {
	newlatlng = xline[2].replace('(', '');
	newlatlng = newlatlng.replace(',', ' ');
	newlatlng = newlatlng.replace(')', '');
	return PTHLine2Waypoint(xline[0] + " " + xline[1] + " " + newlatlng);
    }
    if (xline.length < 4) {
	return Waypoint('bad-line', 0, 0);
    }
    return new Waypoint(xline[1], xline[2], xline[3], 0, xline[0]);
}


function GraphEdge(v1, v2, label) {

    this.v1 = parseInt(v1);
    this.v2 = parseInt(v2);
    this.label = label;
    return this;
}

// update the map to the current set of waypoints and connections
function updateMap()
{
    // remove any existing google.maps.Polyline connections shown
    for (var i = 0; i < connections.length; i++) {
	connections[i].setMap(null);
    }
    connections = new Array();

    var minlat = 999;
    var maxlat = -999;
    var minlon = 999;
    var maxlon = -999;

    polypoints = new Array();
    for (var i = 0; i < markers.length; i++) {
	markers[i].setMap(null);
    }

    var showHidden = false;  // document.getElementById('showHidden').checked;
    var showMarkers = document.getElementById('showMarkers').checked;

    markers = new Array();
    markerinfo = new Array();
    var bounds = new google.maps.LatLngBounds();
    for (var i = 0; i < waypoints.length; i++) {
	minlat = Math.min(minlat, waypoints[i].lat);
	maxlat = Math.max(maxlat, waypoints[i].lat);
	minlon = Math.min(minlon, waypoints[i].lon);
	maxlon = Math.max(maxlon, waypoints[i].lon);
	
	polypoints[i] = new google.maps.LatLng(waypoints[i].lat, waypoints[i].lon);
	
	markerinfo[i] = MarkerInfo(i, waypoints[i]);
	markers[i] = new google.maps.Marker({
	    position: polypoints[i],
	    //map: map,
	    title: waypoints[i].label,
	    icon: intersectionimage
	});
	if (showMarkers && (showHidden || waypoints[i].visible)) {
	    AddMarker(markers[i], markerinfo[i], i);
	}
	bounds.extend(polypoints[i]);
    }
    
    var midlat = (minlat + maxlat)/2;
    var midlon = (minlon + maxlon)/2;

    var nsdist = Mileage(minlat, midlon, maxlat, midlon);
    var ewdist = Mileage(midlat, minlon, midlat, maxlon);
    var maxdist = Math.max(nsdist, ewdist);
  
    //var zoom = 17 - (12 + Math.floor(Math.log(maxdist/800)/Math.log(2.0)));
    //zoom = Math.max(zoom, 0);
    //zoom = Math.min(zoom, 17);
    //map.setZoom(zoom);
    map.fitBounds(bounds);

    // if  this is a graph, we draw edges as connections,
    // otherwise we may be connecting waypoints in order
    // to plot a path
    if (graphEdges.length > 0) {
	for (var i = 0; i < graphEdges.length; i++) {
	    var edgePoints = new Array(2);
	    var v1 = graphEdges[i].v1;
	    var v2 = graphEdges[i].v2;
	    //	    DBG.write("Adding edge " + i + " from " + v1 + "(" + waypoints[v1].lat + "," + waypoints[v1].lon + ") to " + v2 + "(" + waypoints[v2].lat + "," + waypoints[v2].lon + ")");
	    edgePoints[0] = new google.maps.LatLng(waypoints[v1].lat, waypoints[v1].lon);
	    edgePoints[1] = new google.maps.LatLng(waypoints[v2].lat, waypoints[v2].lon);
	    connections[i] = new google.maps.Polyline({path: edgePoints, strokeColor: "#0000FF", strokeWeight: 10, strokeOpacity: 0.4, map: map});
	    //map.addOverlay(connections[i]);
	}
    }
    else if (mapClinched) {
	// clinched vs unclinched segments mapped with different colors
	var nextClinchedCheck = 0;
	var totalMiles = 0.0;
	var clinchedMiles = 0.0;
	var level = map.getZoom();
	var weight = 2;
	if (newRouteIndices.length > 0) {
	    // if newRouteIndices is not empty, we're plotting multiple routes
	    //DBG.write("Multiple clinched routes!");
	    var nextSegment = 0;
	    for (var route = 0; route < newRouteIndices.length; route++) {
		var start = newRouteIndices[route];
		var end;
		if (route == newRouteIndices.length-1) {
		    end = waypoints.length-1;
		}
		else {
		    end = newRouteIndices[route+1]-1;
		}
		//DBG.write("route = " + route + ", start = " + start + ", end = " + end);
		// support for clinch colors from systems.csv
		var unclinchedColor = "rgb(200,200,200)"; //"#cccccc";
		var clinchedColor = "rgb(255,128,128)"; //"#ff8080";
		for (var c = 0; c<colorCodes.length; c++) {
		    if (colorCodes[c].name == routeColor[route]) {
			unclinchedColor = colorCodes[c].unclinched;
			clinchedColor = colorCodes[c].clinched;
		    }
		}
		for (var i=start; i<end; i++) {
		    var zIndex = 10 - routeTier[route];
		    var edgePoints = new Array(2);
		    edgePoints[0] = new google.maps.LatLng(waypoints[i].lat, waypoints[i].lon);
		    edgePoints[1] = new google.maps.LatLng(waypoints[i+1].lat, waypoints[i+1].lon);
		    var segmentLength = Mileage(waypoints[i].lat,
						waypoints[i].lon,
						waypoints[i+1].lat,
						waypoints[i+1].lon);
		    totalMiles += segmentLength;
		    //DBG.write("i = " + i);
		    var color = unclinchedColor;
		    var opacity = 0.3;
		    if (segments[nextSegment] == clinched[nextClinchedCheck]) {
			//DBG.write("Clinched!");
			color = clinchedColor;
			zIndex = zIndex + 10;
			nextClinchedCheck++;
			clinchedMiles += segmentLength;
			opacity = 0.85;
		    }
		    connections[nextSegment] = new google.maps.Polyline(
			{path: edgePoints, strokeColor: color, strokeWeight: weight, strokeOpacity: opacity,
			 zIndex : zIndex, map: map});
		    nextSegment++;
		}	
	    }
	    // set up listener for changes to zoom level and adjust strokeWeight in response
	    //DBG.write("Setting up zoom_changed");
	    google.maps.event.clearListeners(map, 'zoom_changed');
	    google.maps.event.addListener(map, 'zoom_changed', zoomChange); 
//	    google.maps.event.addListener(map, 'zoom_changed', function() {
//		var level = map.getZoom();
//		var weight = Math.floor(level);
//		DBG.write("Zoom level " + level + ", weight = " + weight);
//		for (var i=0; i<connections.length; i++) {
//		    connections[i].setOptions({strokeWeight: weight});
//		}
//	    });
	}
	else {
	    // single route
	    for (var i=0; i<segments.length; i++) {
		var edgePoints = new Array(2);
		edgePoints[0] = new google.maps.LatLng(waypoints[i].lat, waypoints[i].lon);
		edgePoints[1] = new google.maps.LatLng(waypoints[i+1].lat, waypoints[i+1].lon);
		var segmentLength = Mileage(waypoints[i].lat,
					    waypoints[i].lon,
					    waypoints[i+1].lat,
					    waypoints[i+1].lon);
		totalMiles += segmentLength;
		var color = "#cccccc";
		if (segments[i] == clinched[nextClinchedCheck]) {
		    color = "#ff8080";
		    nextClinchedCheck++;
		    clinchedMiles += segmentLength;
		}
		connections[i] = new google.maps.Polyline({path: edgePoints, strokeColor: color, strokeWeight: 10, strokeOpacity: 0.75, map: map});
	    }
	}
	document.getElementById('controlboxinfo').innerHTML = clinchedMiles.toFixed(2) + " of " + totalMiles.toFixed(2) + " miles (" + (clinchedMiles/totalMiles*100).toFixed(1) + "%) clinched by " + traveler + ".";				    
    }
    else if (genEdges) {
	connections[0] = new google.maps.Polyline({path: polypoints, strokeColor: "#0000FF", strokeWeight: 10, strokeOpacity: 0.75, map: map});
	//map.addOverlay(connections[0]);
    }
    // don't think this should not be needed, but an attempt to get hidden waypoints
    // to be hidden when first created
    showHiddenClicked();
}

function zoomChange() {

    var level = map.getZoom();
    var newWeight;
    if (level < 9) newWeight = 2;
    else if (level < 12) newWeight = 6;
    else if (level < 15) newWeight = 10;
    else newWeight = 16;
    //DBG.write("zoomChange: Zoom level " + level + ", newWeight = " + newWeight);
    for (var i=0; i<connections.length; i++) {
	//connections[i].setMap(null);
	connections[i].setOptions({strokeWeight: newWeight});
    }
}

function AddMarker(marker, markerinfo, i) {

    marker.setMap(map);
    google.maps.event.addListener(marker, 'click', function() {
	infowindow.setContent(markerinfo);
	infowindow.open(map, marker);
	});
}

function LabelClick(i, label, lat, lon, errors) {

    //var info = MarkerInfo(i, new Waypoint(label, lat, lon, errors, ""));
    map.panTo(new google.maps.LatLng(lat, lon)); 
    //infowindow.setContent(info);
    infowindow.setContent(markerinfo[i]);
    infowindow.open(map, markers[i]);
}

function MarkerInfo(i, wpt) {

    return '<p style="line-height:160%;"><span style="font-size:24pt;">' + wpt.label + '</span><br><b>Waypoint ' + (i+1) + '<\/b><br><b>Coords.:<\/b> ' + wpt.lat + '&deg;, ' + wpt.lon + '&deg;<\/p>';

}

// compute distance in miles between two lat/lon points
function Mileage(lat1, lon1, lat2, lon2) {
    if(lat1 == lat2 && lon1 == lon2)
	return 0.;
    
    var rad = 3963.;
    var deg2rad = Math.PI/180.;
    var ang = Math.cos(lat1 * deg2rad) * Math.cos(lat2 * deg2rad) * Math.cos((lon1 - lon2)*deg2rad) + Math.sin(lat1 * deg2rad) * Math.sin(lat2 * deg2rad);
    return Math.acos(ang) * 1.02112 * rad;
}

// compute distance in feet between two lat/lon points
function Feet(lat1, lon1, lat2, lon2) {
    if(lat1 == lat2 && lon1 == lon2)
	return 0.;
    
    var rad = 3963.;
    var deg2rad = Math.PI/180.;
    var ang = Math.cos(lat1 * deg2rad) * Math.cos(lat2 * deg2rad) * Math.cos((lon1 - lon2)*deg2rad) + Math.sin(lat1 * deg2rad) * Math.sin(lat2 * deg2rad);
    return Math.acos(ang) * 1.02112 * rad * 5280;
}

// callback for when the showHidden checkbox is clicked
function showHiddenClicked() {

    var showHidden = false; //document.getElementById('showHidden').checked;
    //DBG.write("showHiddenClicked: showHidden is " + showHidden);
    if (showHidden) {
	// add in the hidden markers
	for (var i = 0; i < waypoints.length; i++) {
	    if (!waypoints[i].visible) {
		AddMarker(markers[i], markerinfo[i], i);
	    }
	}
    }
    else {
	// hide the ones that should no longer be visible
	for (var i = 0; i < waypoints.length; i++) {
	    if (!waypoints[i].visible) {
		markers[i].setMap(null);
	    }
	}
    }
}

// callback for when the hideMarkers checkbox is clicked
function showMarkersClicked() {

    var showThem = document.getElementById('showMarkers').checked;
    if (showThem) {
	for (var i = 0; i < waypoints.length; i++) {
	    if (waypoints[i].visible) {
		AddMarker(markers[i], markerinfo[i], i);
	    }
	}
    }
    else {
	for (var i = 0; i < waypoints.length; i++) {
	    markers[i].setMap(null);
	}
    }
}

// JS debug window by Mike Maddox from
// http://javascript-today.blogspot.com/2008/07/how-about-quick-debug-output-window.html
var DBG = {
    write : function(txt){
	if (!window.dbgwnd){
	    window.dbgwnd = window.open("","debug","status=0,toolbar=0,location=0,menubar=0,directories=0,resizable=0,scrollbars=1,width=600,height=250");
	    window.dbgwnd.document.write('<html><head></head><body style="background-color:black"><div id="main" style="color:green;font-size:12px;font-family:Courier New;"></div></body></html>');
	}
	var x = window.dbgwnd.document.getElementById("main");
	this.line=(this.line==null)?1:this.line+=1;
	txt=this.line+': '+txt;
	if (x.innerHTML == ""){
	    x.innerHTML = txt;
	}
	else {
	    x.innerHTML = txt + "<br/>" + x.innerHTML;
	}
    }
}
