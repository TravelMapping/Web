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
// 2016-05-25 JDT  Consolidated some changes from copies of chmviewerfunc3.js
// 2016-06-27 JDT  Removed code not needed by TM
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
// system for each route included
var routeSystem = new Array();
// the markers at those waypoints
var markers = new Array();
// the info displayed when markers are clicked
var markerinfo = new Array();
// array of google.maps.LatLng representing the waypoint coordinates
var polypoints = new Array();
// array of connections on map as google.maps.Polyline overlays
var connections = new Array();
// array of graph edges (for graph data, used by HDX, which imports this code)
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
// boolean to determine if graph edges are in vertex adjacency lists
var usingAdjacencyLists = false;

// array of objects that define color codes from names in the DB
var colorCodes = new Array();
colorCodes[0] = { name: "blue", unclinched: "rgb(100,100,255)", clinched: "rgb(0,0,255)" };
colorCodes[1] = { name: "brown", unclinched: "rgb(153,152,102)", clinched: "rgb(153,102,0)" };
colorCodes[2] = { name: "red", unclinched: "rgb(255,100,100)", clinched: "rgb(255,0,0)" };
colorCodes[3] = { name: "yellow", unclinched: "rgb(255,255,128)", clinched: "rgb(225,225,0)" };
colorCodes[4] = { name: "teal", unclinched: "rgb(100,200,200)", clinched: "rgb(0,200,200)" };
colorCodes[5] = { name: "green", unclinched: "rgb(100,255,100)", clinched: "rgb(0,255,0)" };
colorCodes[6] = { name: "magenta", unclinched: "rgb(255,100,255)", clinched: "rgb(255,0,255)" };
colorCodes[7] = { name: "lightsalmon", unclinched: "rgb(255,200,200)", clinched: "rgb(255,160,122)" };

// array of custom color codes to be pulled from query string parameter "colors="
var customColorCodes = new Array();

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

var OpenStreetMapDEOptions = {
    alt: "Show OpenStreetMapDE road map tiles from OpenStreetMap.org",
    getTileUrl: getOpenStreetMapDEURL,
    maxZoom: 18,
    minZoom: 0,
    name: "DE",
    opacity: 1,
    tileSize: new google.maps.Size(256, 256)
};

function getOpenStreetMapDEURL(point, zoom) {
    return 'http://tile.openstreetmap.de/tiles/osmde/' + zoom + '/' + point.x + '/' + point.y + '.png';
}

var EsriOptions = {
    alt: "Show Esri road map tiles from OpenStreetMap.org",
    getTileUrl: EsriURL,
    maxZoom: 18,
    minZoom: 0,
    name: "Esri",
    opacity: 1,
    tileSize: new google.maps.Size(256, 256)
};

function EsriURL(point, zoom) {
    return 'http://server.arcgisonline.com/ArcGIS/rest/services/NatGeo_World_Map/MapServer/tile/' + zoom + '/' + point.y + '/' + point.x;
}

var OpenStreetMapDEOptions = {
    alt: "Show OpenStreetMapDE road map tiles from OpenStreetMap.org",
    getTileUrl: getOpenStreetMapDEURL,
    maxZoom: 18,
    minZoom: 0,
    name: "DE",
    opacity: 1,
    tileSize: new google.maps.Size(256, 256)
};

function getOpenStreetMapDEURL(point, zoom) {
    return 'http://tile.openstreetmap.de/tiles/osmde/' + zoom + '/' + point.x + '/' + point.y + '.png';
}

//HERE map tiles
var HEREOptions = {
    alt: "Show wego HERE road map tiles from https://wego.here.com",
    getTileUrl: HEREURL,
    maxZoom: 18,
    minZoom: 0,
    name: "HERE",
    opacity: 1,
    tileSize: new google.maps.Size(256, 256)
};

function HEREURL(point, zoom) {
    return 'https://1.base.maps.cit.api.here.com/maptile/2.1/maptile/newest/normal.day/' + zoom + '/' + point.x + '/' + point.y + '/256/png8?app_id=VX6plk5zCW0wzrNcN64O&app_code=LcZFksQAhfg7rvZvcZ1lqw';
}

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
    var subdomain = Math.floor( Math.random() * (4 - 1 + 1) ) + 1; // Request tile from random subdomain.
    return 'http://otile' + subdomain + '.mqcdn.com/tiles/1.0.0/map/' + zoom + '/' + point.x + '/' + point.y + '.jpg';
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
    var subdomain = Math.floor( Math.random() * (4 - 1 + 1) ) + 1; // Request tile from random subdomain.
    return 'http://otile' + subdomain + '.mqcdn.com/tiles/1.0.0/sat/' + zoom + '/' + point.x + '/' + point.y + '.jpg';
    //return 'http://cmap.m-plex.com/hb/ymaptile.php?t=s&s=mq&x=' + point.x + '&y=' + point.y + '&z=' + zoom;
}

var BlankOptions = { alt: "Show a blank background",
				getTileUrl: getBlankURL,
				maxZoom: 18,
				minZoom: 0,
				name: "Blank",
				opacity: 1,
				tileSize: new google.maps.Size(256, 256)
				};

function getBlankURL() {
	return '/empty.gif';
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
    var typeBlank = new google.maps.ImageMapType(BlankOptions);
    var typeOpenStreetMapDE = new google.maps.ImageMapType(OpenStreetMapDEOptions);
    var typeEsri = new google.maps.ImageMapType(EsriOptions);
    var typeHERE = new google.maps.ImageMapType(HEREOptions);

    var maptypelist = ['Mapnik', google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.SATELLITE, google.maps.MapTypeId.HYBRID, google.maps.MapTypeId.TERRAIN, 'Blank', 'DE', 'Esri', 'HERE'];
    var maptypecontroloptions = {mapTypeIds: maptypelist, position: google.maps.TOP_RIGHT, style: google.maps.MapTypeControlStyle.DROPDOWN_MENU};
    //var mapopt = {center: new google.maps.LatLng(42.664529, -73.786470), zoom: 12, mapTypeId: 'Mapnik', mapTypeControl: true, mapTypeControlOptions: maptypecontroloptions, streetViewControl: true, disableDefaultUI: true, panControl: true, zoomControl: true, scaleControl: true, overviewMapControl: true, keyboardShortcuts: true, disableDoubleClickZoom: false};
    // OLD coordinates are Albertus Hall room 400-2 at The College of Saint Rose
    //var mapopt = {center: new google.maps.LatLng(42.664529, -73.786470), zoom: 16, mapTypeControl: true, mapTypeControlOptions: maptypecontroloptions};

    // coordinates are Roger Bacon 321 at Siena College
    var mapopt = {center: new google.maps.LatLng(42.719450, -73.752063), zoom: 16, mapTypeControl: true, mapTypeControlOptions: maptypecontroloptions};

    map = new google.maps.Map(document.getElementById("map"), mapopt);
    
    map.mapTypes.set('MQOpenMap', typeMQOpenMap);
    map.mapTypes.set('MQOpenSat', typeMQOpenSat);
    map.mapTypes.set('Mapnik', typeMapnik);
    map.mapTypes.set('Blank', typeBlank);
    map.mapTypes.set('DE', typeOpenStreetMapDE);
    map.mapTypes.set('Esri', typeEsri);
    map.mapTypes.set('HERE', typeHERE);
}

// construct a new Waypoint object (based on similar function by Tim Reichard)
// now supporting edge adjacency lists
// these sometimes have a field "intersecting" added to them which is
// an array of intersecting Routes (defined below)
function Waypoint(label, lat, lon, elabel, edgeList) {
    this.label = label;
    this.lat = parseFloat(lat).toFixed(6);
    this.lon = parseFloat(lon).toFixed(6);
    this.visible = true;
    if (label.indexOf("+") >= 0) {
	this.visible = false;
    }
    this.elabel = elabel;
    this.edgeList = edgeList;
    return this;
}

// construct a Route object to encapsulate information needed for intersecting
// routes
function Route(root, route, region, banner, abbrev, city) {

    this.root = root;
    this.route = route;
    this.region = region;
    this.banner = banner;
    this.abbrev = abbrev;
    this.city = city;
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

    var showHidden = false;
    if (document.getElementById('showHidden') != null) {
       showHidden = document.getElementById('showHidden').checked;
    }
    var showMarkers = true;
    if (document.getElementById('showMarkers') != null) {
      showMarkers = document.getElementById('showMarkers').checked;
    }

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

    // if this is a graph in HDX, we draw edges as connections,
    // otherwise we may be connecting waypoints in order to plot a
    // path
    if (graphEdges.length > 0) {
	for (var i = 0; i < graphEdges.length; i++) {
	    var numPoints;
	    if (graphEdges[i].via == null) {
		numPoints = 2;
	    }
	    else {
		numPoints = graphEdges[i].via.length/2 + 2;
	    }
	    var edgePoints = new Array(numPoints);
	    var v1 = graphEdges[i].v1;
	    var v2 = graphEdges[i].v2;
	    //	    DBG.write("Adding edge " + i + " from " + v1 + "(" + waypoints[v1].lat + "," + waypoints[v1].lon + ") to " + v2 + "(" + waypoints[v2].lat + "," + waypoints[v2].lon + ")");
	    edgePoints[0] = new google.maps.LatLng(waypoints[v1].lat, waypoints[v1].lon);
	    nextPoint = 1;
	    if (graphEdges[i].via != null) {
		for (var j = 0; j < graphEdges[i].via.length; j+=2) {
		    edgePoints[nextPoint] = new google.maps.LatLng(graphEdges[i].via[j], graphEdges[i].via[j+1]);
		    nextPoint++;
		}
	    }
	    edgePoints[nextPoint] = new google.maps.LatLng(waypoints[v2].lat, waypoints[v2].lon);
	    connections[i] = new google.maps.Polyline({path: edgePoints, strokeColor: "#0000FF", strokeWeight: 10, strokeOpacity: 0.4, map: map});
	    //map.addOverlay(connections[i]);
	}
    }
    else if (usingAdjacencyLists) {
	var edgeNum = 0;
	for (var i = 0; i < waypoints.length; i++) {
	    for (var j = 0; j < waypoints[i].edgeList.length; j++) {
		var thisEdge = waypoints[i].edgeList[j];
		// avoid double plot by only plotting those with v1 as i
		if (thisEdge.v1 == i) {
		    var numPoints;
		    if (thisEdge.via == null) {
			numPoints = 2;
		    }
		    else {
			numPoints = thisEdge.via.length/2 + 2;
		    }
		    var edgePoints = new Array(numPoints);
		    edgePoints[0] = new google.maps.LatLng(waypoints[thisEdge.v1].lat, waypoints[thisEdge.v1].lon);
		    nextPoint = 1;
		    if (thisEdge.via != null) {
			for (var p = 0; p < thisEdge.via.length; p+=2) {
			    edgePoints[nextPoint] = new google.maps.LatLng(thisEdge.via[p], thisEdge.via[p+1]);
			    nextPoint++;
			}
		    }
		    edgePoints[nextPoint] = new google.maps.LatLng(waypoints[thisEdge.v2].lat, waypoints[thisEdge.v2].lon);
		    // count the commas, which tell us how many
		    // concurrent routes are represented, as they will
		    // be comma-separated, then use that to choose a
		    // color to indicate the number of routes
		    // following the edge
		    concurrent = thisEdge.label.split(",").length;
		    color = "";
		    switch (concurrent) {
		    case 1:
			color = "#0000FF";
			break;
		    case 2:
			color = "#00FF00";
			break;
		    case 3:
			color = "#FF00FF";
			break;
		    case 4:
			color = "#FFFF00";
			break;
		    default:
			color = "#FF0000";
			break;
		    }
		    connections[edgeNum] = new google.maps.Polyline({path: edgePoints, strokeColor: color, strokeWeight: 10, strokeOpacity: 0.4, map: map});
		    edgeNum++;
		}
	    }
	}
    }
    // connecting waypoints in order to plot a path
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
		// override with tier or system colors given in query string if they match
		for (var c = 0; c<customColorCodes.length; c++) {
		    if (customColorCodes[c].name == ("tier"+routeTier[route])) {
			unclinchedColor = customColorCodes[c].unclinched;
			clinchedColor = customColorCodes[c].clinched;
		    }
		    if (customColorCodes[c].name == routeSystem[route]) {
			unclinchedColor = customColorCodes[c].unclinched;
			clinchedColor = customColorCodes[c].clinched;
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
        if (document.getElementById('controlboxinfo') != null) {
  	    document.getElementById('controlboxinfo').innerHTML = ""; //clinchedMiles.toFixed(2) + " of " + totalMiles.toFixed(2) + " miles (" + (clinchedMiles/totalMiles*100).toFixed(1) + "%) clinched by " + traveler + ".";
        }
    }
    else if (genEdges) {
	connections[0] = new google.maps.Polyline({path: polypoints, strokeColor: "#0000FF", strokeWeight: 10, strokeOpacity: 0.75, map: map});
	//map.addOverlay(connections[0]);
    }
    // don't think this should not be needed, but an attempt to get
    // hidden waypoints to be hidden when first created
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

    map.panTo(new google.maps.LatLng(lat, lon)); 
    //infowindow.setContent(info);
    infowindow.setContent(markerinfo[i]);
    infowindow.open(map, markers[i]);
}

function MarkerInfo(i, wpt) {

    var intersections = "";
    if (wpt.hasOwnProperty('intersecting')) {
	intersections = "<p>Intersecting/Concurrent Routes:<br />";
	for (var j = 0; j < wpt.intersecting.length; j++) {
	    r = wpt.intersecting[j];
	    intersections+="<a href=\"/hb/?r=" + r.root + "\">" + r.region + " " + r.route + " " + r.banner;
	    if (r.city != "") {
		intersections+="(" + r.city + ")";
	    }
	    intersections += "</a><br />";
	}
	intersections += "</p>";
    }
    return '<p style="line-height:160%;"><span style="font-size:24pt;">' + wpt.label + '</span><br><b>Waypoint ' + (i+1) + '<\/b><br><b>Coords.:<\/b> ' + wpt.lat + '&deg;, ' + wpt.lon + '&deg;<\/p>' + intersections;

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

    var showHidden = false; 
    if (document.getElementById('showHidden') != null) {
	showHidden = document.getElementById('showHidden').checked;
    }
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

function redirect(url) {
	var win = window.open(url);
	win.focus();
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
