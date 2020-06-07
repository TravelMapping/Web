//
// Travel Mapping (TM) JavaScript functions related to Mapview functionality
//
// Primary author: Jim Teresco
//
// Much of this code was moved from tmjsfuncs.js
//
// Note: this file should include only functionality specific to
// user/mapview.php.  Generally-applicable code should continue to
// be placed in tmjsfuncs.js.  This file assumes tmjsfuncs.js
// will be loaded before itself.

// Note: the scrollableMapviewDialog global variable is set in mapview.php,
// as it depends on PHP to generate part of its content.

// Also the mapviewParams object is constructed and populated in mapview.php
// to use PHP to parse the QS params

// highlight hover route in mapview
var mapviewHoverRoute = null;

// what should be done for map positioning by the next callback that
// parses JSON output to display segments on the map and routes in the
// table?
// 
var mapPosUpdates = {
    NONE: 1,
    USE_LAT_LON_ZOOM: 2,
    FIT_TO_DATA: 3
};

var nextMapPosUpdate = mapPosUpdates.NONE;

// max travelers on any segment in view
var maxTravelers = 0;

function showDataLoadingMsg() {

    document.getElementById("loadingMsg").style.display = "";
}

function hideDataLoadingMsg() {

    document.getElementById("loadingMsg").style.display = "none";
}

function showHideRouteTable() {

    let check = document.getElementById("showRoutesCheckbox");
    let table = document.getElementById("routes");
    if (check.checked) {
        table.style.display = "";
    }
    else {
        table.style.display = "none";
    }
}

function updateCheckboxChanged() {

    showAllInView = document.getElementById("updateCheckbox").checked;
    // if we just turned on updating, launch an update
    if (showAllInView) {
	nextMapPosUpdate = mapPosUpdates.NONE;
        updateVisibleData();
    }
}

// event handler for changes in segment highlighting
function updateConnectionColors() {

    let colorScheme = document.getElementById("coloring").value;
    let highlightScheme = document.getElementById("highlighting").value;
    connections.forEach(function(value) {
	let color = "#202020";
	let highlight = 1;
	if ((highlightScheme == "traveled" && value.TMclinched == 0) ||
	    (highlightScheme == "untraveled" && value.TMclinched == 1) ||
	    (highlightScheme == "none")) {
	    highlight = 0;
	}
	let opacity = 0.3 + 0.55 * highlight;
	if (colorScheme == "system") {
	    let colors = lookupColors(value.TMminTierColor,
				      value.TMminTier,
				      value.TMsystemshere[0]);
	    color = colors[highlight];
	}
	else if (colorScheme == "concurrent") {
	    color = concurrencyColors[value.TMrouteshere.length];
	}
	else if (colorScheme == "travelers") {
	    color = segmentColorForAmountTraveled(value.TMtravelers, maxTravelers);
	}
	value.setStyle({
	    color: color,
	    opacity: opacity
	});
    });
}

// highlight route segments for all routes
// in the given system when that system's header entry is hovered over
// in the table of routes
function mapviewRouteHoverSystem(system) {

    connections.forEach(function(value) {
	if (value.TMsystemshere.includes(system)) {
	    value.TMoverlay = L.polyline(value.getLatLngs(), {
		color: "black",
		weight: polylineWeight*2,
		opacity: 0.4
	    }).addTo(map);
	}
    });
}

// highlight route segments when route is hovered over in the table of
// routes (here, we have no routeInfo or waypoints array
function mapviewRouteHoverRoot(root) {

    // Note: unclear if it's likely more efficient to attach a reference
    // to the overlay polyline to each one in the connections array, or
    // maintain a list that we traverse on remove.  The former seems better
    // for maps with not too many routes, the latter for maps with lots of
    // routes, but doing the former to start
    connections.forEach(function(value) {
	if (value.TMrouteshere.includes(root)) {
	    value.TMoverlay = L.polyline(value.getLatLngs(), {
		color: "black",
		weight: polylineWeight*2,
		opacity: 0.4
	    }).addTo(map);
	}
    });
}

// remove any overlays added by mapviewRouteEndHover
function mapviewRouteEndHoverRoot() {
    connections.forEach(function(value) {
	if (value.hasOwnProperty("TMoverlay")) {
	    value.TMoverlay.remove();
	    delete value.TMoverlay;
	}
    });
}

// highlight route segments when route is hovered over
// in the table of routes
function mapviewRouteHover(root) {

    // find the route in the routeInfo array
    let firstWaypoint = 0;
    let lastWaypoint = waypoints.length-1;
    for (let routeIndex = 0; routeIndex < routeInfo.length; routeIndex++) {
	if (routeInfo[routeIndex].root == root) {
	    firstWaypoint = routeInfo[routeIndex].firstWaypoint;
	    if (routeIndex != routeInfo.length - 1) {
		lastWaypoint = routeInfo[routeIndex+1].firstWaypoint - 1;
	    }
	    break;
	}
    }
    let pointList = new Array();
    for (let i = firstWaypoint; i <= lastWaypoint; i++) {
	pointList.push([waypoints[i].lat, waypoints[i].lon]);
    }
    mapviewHoverRoute = L.polyline(pointList, {
	color: "black",
	weight: polylineWeight*2,
	opacity: 0.4
    }).addTo(map);
}

function mapviewRouteEndHover() {

    mapviewHoverRoute.remove();
    mapviewHoverRoute = null;
}

// function to perform the sequence of actions when mapview pages are
// first loaded
function mapviewStartup(lat, lon, zoom) {

    mapStatus = mapStates.MAPVIEW;
    
    // honor v QS param
    document.getElementById("updateCheckbox").checked = showAllInView;
    loadmap();

    // find initial data based on mapviewParams
    // ignoring country for now - it will eventually just extend regions
    // either here or in mapview.php before it gets here

    // if this gets a value, it means we need to use it for an AJAX request
    let clause = '';
    // a number of cases arise based on the QS params
    if (mapviewParams.regions.length > 0) {
	clause = "where (routes.region='" + mapviewParams.regions[0] + "'";
	for (let i = 1; i < mapviewParams.regions.length; i++) {
	    clause += " or routes.region='" + mapviewParams.regions[i] + "'";
	}
	clause += ")";
	// if we have regions, we could also have systems within
	if (mapviewParams.systems.length > 0) {
	    clause += " and (routes.systemName='" + mapviewParams.systems[0] + "'";
	    for (let i = 1; i < mapviewParams.systems.length; i++) {
		clause += " or routes.systemName='" + mapviewParams.systems[i] + "'";
	    }
	    clause += ")";
	}
    }
    else if (mapviewParams.systems.length > 0) {
	clause = "where (routes.systemName='" + mapviewParams.systems[0] + "'";
	for (let i = 1; i < mapviewParams.systems.length; i++) {
	    clause += " or routes.systemName='" + mapviewParams.systems[i] + "'";
	}
	clause += ")";
    }
    else if (mapviewParams.routePattern != '') {
	console.log("Load routes matching pattern " + mapviewParams.routePattern);
    }
    else {
	// initial data will be loaded on "OK" of the popup, and no
	// data will be loaded if cancelled
	// setting showAllInView to true even if v was not specified
	document.getElementById("updateCheckbox").checked = true;
	showAllInView = true;
	showScrollableMapviewPopup();
    }

    // if we set the "clause" we need to launch an AJAX query
    // request all segments that have at least one end waypoint in view
    if (clause != "") {
	showDataLoadingMsg();
	let params = {
	    clause: clause,
	    traveler: traveler
	};
	let jsonParams = JSON.stringify(params);
	if (lat == null || lon == null || zoom == null) {
   	    nextMapPosUpdate = mapPosUpdates.FIT_TO_DATA;
	}
	else {
   	    nextMapPosUpdate = mapPosUpdates.USE_LAT_LON_ZOOM;
	    setlat = lat;
	    setlon = lon;
	    setzoom = zoom;
	}
	$.ajax({
	    type: "POST",
	    url: "/lib/getRegionSystemSegments.php",
	    datatype: "json",
	    data: { "params" : jsonParams },
	    success: parseSegmentAndRouteData
	});
    }
    
    // we will add these unconditionally since we might turn on scrollable
    // functionality later even if it wasn't specified initially
    map.on('moveend', zoomChange);
    map.on('resize', zoomChange);
}


// when showing visible data, changes to the visible map will result
// in a call to this function, which is responsible for updating
// the data on the map and in tables
function updateVisibleData() {

    showDataLoadingMsg();
    
    // get current map extents
    let bounds = map.getBounds();

    // request all segments that have at least one end waypoint in view
    let params = {
	minLat: bounds.getSouth(),
        maxLat: bounds.getNorth(),
	minLng: bounds.getWest(),
	maxLng: bounds.getEast(),
	traveler: traveler
    };
    let jsonParams = JSON.stringify(params);
    nextMapPosUpdate = mapPosUpdates.NONE;
    $.ajax({
	type: "POST",
	url: "/lib/getVisibleSegments.php",
	datatype: "json",
	data: { "params" : jsonParams },
	success: parseSegmentAndRouteData
    });
}

// debounced version of updateVisibleData
var mapviewUpdateVisibleDataDebounced = debounce(updateVisibleData, 1000);

// the callback function for when the AJAX data is returned by
// updateVisibleData's call, so this is really the second half
// of the functionality of updateVisibleData, also used for
// return from loading segments and routes by region/system/etc.
function parseSegmentAndRouteData(data) {

    let responses = $.parseJSON(data);
    // what we get back here is several parallel arrays:
    // responses['roots'] is the root of each segment
    // responses['w1name'] name of one end waypoint
    // responses['w1lat'] latitude of one end waypoint
    // responses['w1lng'] longitude of one end waypoint
    // responses['w2name'] name of other end waypoint
    // responses['w2lat'] latitude of other end waypoint
    // responses['w2lng'] longitude of other end waypoint
    // responses['clinched'] is this segment clinched by the traveler (0 or 1)
    // responses['travelers'] number of TM travelers of this segment
    // responses['routeroots'] the list of route roots, one per route
    // responses['routelistnames'] the list names of routes
    // responses['routemileages'] the overall mileages of routes
    // responses['routeclinchedmileages'] clinched mileages of routes
    //     by the traveler
    // responses['routeclinched'] is route completely clinched by the
    //     traveler (0 or 1)
    // responses['routecolors'] the colors for plotting of routes
    // responses['routetiers'] the tiers for routes
    // responses['routesystemnames'] the systems (human readable) for routes  
    // responses['routesystemcodes'] the systems (code) for routes  
    // responses['routelevels'] the levels (active or preview) for routes
    // responses['routetiers'] the tiers for routes
    //
    // the elements here are ordered by tier then csv order (first
    // order of systems, then order of routes within system)
    let roots = responses['roots'];
    let w1name = responses['w1name'];
    let w1lat = responses['w1lat'];
    let w1lng = responses['w1lng'];
    let w2name = responses['w2name'];
    let w2lat = responses['w2lat'];
    let w2lng = responses['w2lng'];
    let clinched = responses['clinched'];
    let travelers = responses['travelers'];
    let routeroots = responses['routeroots'];

    // build route info object with detailed "indexed" by a property
    // equal to the root string (e.g., ny.i090)
    let routes = [];
    for (let i = 0; i < routeroots.length; i++) {
	routes[routeroots[i]] = {
	    listname: responses['routelistnames'][i],
	    mileage: responses['routemileages'][i],
	    clinchedmileage: responses['routeclinchedmileages'][i],
	    clinched: responses['routeclinched'][i],
	    color: responses['routecolors'][i],
	    systemname: responses['routesystemnames'][i],
	    systemcode: responses['routesystemcodes'][i],
	    level: responses['routelevels'][i],
	    tier: responses['routetiers'][i],
	    visiblemileage: 0.0,
	    visibleclinched: 0.0
	}
    }

    // compute clinched and total mileage of visible portions of routes,
    // and maxTravelers for color selections later
    maxTravelers = 0;
    for (let i = 0; i < roots.length; i++) {
	let d = distanceInMiles(w1lat[i], w1lng[i], w2lat[i], w2lng[i]);

	routes[roots[i]].visiblemileage += d;
	if (clinched[i] == 1) {
	    routes[roots[i]].visibleclinched += d;
	}   
	if (parseInt(travelers[i]) > maxTravelers) {
	    maxTravelers = parseInt(travelers[i]);
	}
    }
    
    // remove Polyline connections previously shown
    connections.forEach(function(value) { value.remove(); });
    connections = [];

    // remove Markers previously shown
    markers.forEach(function(value) { value.remove(); });
    markers = [];
    markerinfo = [];

    // replace the entries in the body of the routes table
    let table = document.getElementById("routesTable");
    let thead = table.getElementsByTagName("thead")[0];
    thead.innerHTML = '<tr><th rowspan="2">Route</th><th colspan="2">Traveled by ' + traveler + ' ('+ distanceUnits + ')</th></tr><tr><th>Visible Segments</th><th>Route Overall</th></tr>';
    let tbody = table.getElementsByTagName("tbody")[0];
    let tablerows = ''
    let currentSystem = "";
    if (routeroots.length == 0) {
	tablerows += '<tr><td colspan="3" style="text-align: center;">No Routes on Map</td></tr>';
    }

    for (let i = 0; i < routeroots.length; i++) {
	let root = routeroots[i];
	// system "header" row needed?
	if (routes[root].systemcode != currentSystem) {
	    currentSystem = routes[root].systemcode;
	    let bgcolor = "#CCFFCC";  // active
	    if (routes[root].level == "preview") {
		bgcolor = "#FFFFCC";
	    }
	    tablerows += '<tr onmouseover="mapviewRouteHoverSystem(\'' + currentSystem + '\')" onmouseout="mapviewRouteEndHoverRoot()"><td colspan="3" style="text-align: center; background-color: ' + bgcolor + '">' + routes[root].systemname + " (" + currentSystem + ')</td></tr>';
	}
	let percent = routes[root].clinchedmileage/routes[root].mileage * 100.0;
	let vispercent = routes[root].visibleclinched/routes[root].visiblemileage * 100.0;
	if (routes[root].clinched == 1) {
	    percent = 100.0;
	}
	let link = "/hb/?r=" + root;
	tablerows += '<tr onclick="window.open(\'' + link + '\')" onmouseover="mapviewRouteHoverRoot(\'' + root + '\')" onmouseout="mapviewRouteEndHoverRoot()"><td>' + routes[root].listname + '</td><td style="background-color: ' + colorForAmountTraveled(routes[root].visibleclinched, routes[root].visiblemileage) + '">' + convertToCurrentUnits(routes[root].visibleclinched).toFixed(2) + ' of ' + convertToCurrentUnits(routes[root].visiblemileage).toFixed(2) + ' (' + vispercent.toFixed(1) + '%)</td><td style="background-color: ' + colorForAmountTraveled(routes[root].clinchedmileage, routes[root].mileage) + ';">' + convertToCurrentUnits(routes[root].clinchedmileage).toFixed(2) + ' of ' + convertToCurrentUnits(routes[root].mileage).toFixed(2) + ' (' + percent.toFixed(1) + '%)</td></tr>';
    }
    tbody.innerHTML = tablerows;
    
    // make sure we have something visible
    if (roots.length == 0) return;
    
    // loop through all segments and build the appropriate connections

    // track where the current route starts in the array of
    // connections (not the full lists of segment info like roots)
    let currentRouteStart = 0;
    let currentRoot = "";
    //let colors = lookupColors(routes[roots[0]].color,
    //			      routes[roots[0]].tier,
    //			      routes[roots[0]].systemcode);
    
    // set up to find bounding box of points we plot
    let minlat = 999;
    let maxlat = -999;
    let minlon = 999;
    let maxlon = -999;

    for (let i = 0; i < roots.length; i++) {

	// while we're here, update bounding box just in case
	if (w1lat[i] < minlat) minlat = w1lat[i];
	if (w1lat[i] > maxlat) maxlat = w1lat[i];
	if (w1lng[i] < minlon) minlon = w1lng[i];
	if (w1lat[i] > maxlon) maxlon = w1lng[i];
	if (w2lat[i] < minlat) minlat = w2lat[i];
	if (w2lat[i] > maxlat) maxlat = w2lat[i];
	if (w2lng[i] < minlon) minlon = w2lng[i];
	if (w2lat[i] > maxlon) maxlon = w2lng[i];
	
	// when we need an index into the segments arrays, we can use i,
	// and when we need the root at this index, we can use root
	let root = roots[i];
	
	// if we have any hidden endpoints, try to tack onto each end of
	// segments so far of current route in progress
	let extended = false;
	if (root == currentRoot) {

	    // TO CHECK: make sure we can never have the case of
	    // segments in the route already from V1 to +H1 and from
	    // +H2 to V2, followed by a segment from +H1 to +H2 being
	    // processed which would require that this segment not
	    // only be tacked onto an existing connection, but that
	    // another connection would need to be tacked on and then
	    // removed from the list..  The DB order should prevent
	    // this case, as well as the 3 of the 4 possible "normal"
	    // cases that are commented out below
	    
	    if (w1name[i][0] == '+') {
		for (other = connections.length - 1; other >= currentRouteStart; other--) {
		    /*
                    if (connections[other].TMend1 == w1name[i]) {
			console.log("MATCH NOT HANDLED end1 to w1name with " + connections[other].TMend1 + " in " + connections[other].TMrouteshere[0]);
		    }
		    else */
		    if (connections[other].TMend2 == w1name[i]) {
			// attach w2 to the end of this connection
			let currPoints = connections[other].getLatLngs();
			currPoints.push([w2lat[i], w2lng[i]]);
			connections[other].setLatLngs(currPoints);
			connections[other].TMend2 = w2name[i];
			extended = true;
		    }
		}
	    }
/* 
	    if (w2name[i][0] == '+') {
		console.log("Hidden w2name[" + i + "] is " + w2name[i] + " in " + roots[i]);
		for (other = currentRouteStart; other < connections.length; other++) {
		    if (connections[other].TMend1 == w2name[i]) {
			console.log("MATCH NOT HANDLED end1 to w2name with " + connections[other].TMend1 + " in " + connections[other].TMrouteshere[0]);
		    }
		    else if (connections[other].TMend2 == w2name[i]) {
			console.log("MATCH NOT HANDLED end2 to w2name with " + connections[other].TMend2 + " in " + connections[other].TMrouteshere[0]);
		    }
		}
	    }
*/
	}
	// if this was tacked onto an existing connection, move on to the next
	if (extended) continue;
	
	// check if this segment is concurrent with any we already processed
	let match = -1;
	for (let j = 0; j < connections.length; j++) {
	    let points = connections[j].getLatLngs();
	    for (let p = 0; p < points.length; p++) {
		if (points[p].lat == w1lat[i] && points[p].lng == w1lng[i]) {
		    if (p > 0) {
			if (points[p-1].lat == w2lat[i] && points[p-1].lng == w2lng[i]) {
			    match = j;
			    break;
			}
		    }
		    if (p < points.length-1) {
			if (points[p+1].lat == w2lat[i] && points[p+1].lng == w2lng[i]) {
			    match = j;
			    break;
			}
		    }
		}
		if (match >= 0) break;
	    }
	}
	if (match >= 0) {
	    let matched = connections[match];
	    if (!matched.TMrouteshere.includes(root)) {
		// add to system list, if necessary
		if (!matched.TMsystemshere.includes(routes[root].systemcode)) {
		    matched.TMsystemshere.push(routes[root].systemcode);
		}
		// are we at a "smaller" tier than previous?
		if (routes[root].tier < matched.TMminTier) {
		    // our new min tier
		    matched.TMminTier = routes[root].tier;
		    // update color based on this route
		    //matched.setStyle({
		//	color: lookupColors(routes[root].color,
		//			    routes[root].tier,
		//			    routes[root].systemcode)[matched.TMclinched],
		    //  });
		    matched.TMminTierColor = routes[root].color;
		    // place at the beginning so this is a higher priority
		    // in the concurrencies listed in popups
		    matched.TMrouteshere.unshift(root);
		    // also need to make sure we inherit any extensions
		    // across hidden points that were made in the previous
		    // 0 entry, and update waypoint labels to match
		    // so do this based on first and last LatLng of the
		    // connection
		    let points = matched.getLatLngs();
		    let oldw1loc = points[0];
		    let oldw2loc = points[points.length-1];
		    let checkNext = currentRouteStart;
		    let foundw1 = false;
		    let foundw2 = false;
		    while (!foundw1 || !foundw2) {
			let w1latlng = L.latLng([w1lat[checkNext],
						 w1lng[checkNext]]);
			if (oldw1loc.equals(w1latlng)) {
			    foundw1 = true;
			    matched.TMend1 = w1name[checkNext];
			}
			if (oldw2loc.equals(w1latlng)) {
			    foundw2 = true;
			    matched.TMend2 = w1name[checkNext];
			}
			let w2latlng = L.latLng([w2lat[checkNext],
						 w2lng[checkNext]]);
			if (oldw2loc.equals(w2latlng)) {
			    foundw2 = true;
			    matched.TMend2 = w2name[checkNext];
			}
			if (oldw1loc.equals(w2latlng)) {
			    foundw1 = true;
			    matched.TMend1 = w2name[checkNext];
			}
			checkNext++;
		    }
		}
		else {
		    matched.TMrouteshere.push(root);
		}
	    }
	    continue; // done processing this one
	}
	
	// check if we switched routes to simplify collapse of segments
	// across hidden waypoints
	if (root != currentRoot) {
	    // we will be adding a new connections entry for sure,
	    // so it will have the first connection of this route
	    // that's not concurrent with a previously-processed route
	    currentRouteStart = connections.length;
	    currentRoot = root;
	    //colors = lookupColors(routes[root].color,
	//			  routes[root].tier,
	//			  routes[root].systemcode);
	}

	let edgePoints = new Array(2);
	edgePoints[0] = [w1lat[i], w1lng[i]];
	edgePoints[1] = [w2lat[i], w2lng[i]];
	let con = L.polyline(edgePoints, {
	    //color: colors[clinched[i]],
	    color: "black",
	    weight: polylineWeight,
	    //opacity: 0.3 + 0.55 * clinched[i]
	    opacity: 1
	});

	connections.push(con);
	con.TMend1 = w1name[i];
	con.TMend2 = w2name[i];
	con.TMrouteshere = [ root ];
	con.TMsystemshere = [ routes[root].systemcode ];
	con.TMminTier = routes[root].tier;
	con.TMminTierColor = routes[root].color;
	con.TMclinched = clinched[i];
	con.TMtravelers = travelers[i];
    }
    
    // build TMpopupinfo for each connection and add connectionListener
    for (let i = 0; i < connections.length; i++) {
	let c = connections[i];
	
	// start with list name for the first connected route here,
	// linked to its HB page, plus its endpoint labels
	c.TMpopupinfo = '<span style="font-size:18pt; color:black">' +
	    '<a target="_blank" href="/hb?r=' + c.TMrouteshere[0] +
	    '">' + routes[c.TMrouteshere[0]].listname + '</a></span><br />' +
	    c.TMend1 + " <-> " + c.TMend2 + "<br /><b>Length: </b>" +
	    length_in_current_units(pathLengthInMiles(c.getLatLngs())) +
	    "<br />" + c.TMtravelers + " traveler";
	if (c.TMtravelers != 1) c.TMpopupinfo += "s";
	c.TMpopupinfo += "<br />";
	// append the routes for this segment's additional roots
	if (c.TMrouteshere.length > 1) {
	    c.TMpopupinfo += '<b>Concurrent with:</b>'
	    for (let others = 1; others < c.TMrouteshere.length; others++) {
		c.TMpopupinfo += ' <a target="_blank" href="/hb?r=' +
		    c.TMrouteshere[others] + '">'
		    + routes[c.TMrouteshere[others]].listname + "</a>";
	    }
	}
	connectionListener({
	    connIndex: i
	});
    }

    // set the colors of the connections according to the current
    // state of the drop-downs
    updateConnectionColors();
    
    // add connections to map, high-numbered tiers to low
    for (let tier = 5; tier >= 1; tier--) {
	for (let i = 0; i < connections.length; i++) {
	    let c = connections[i];
	    if (c.TMminTier == tier) {
		c.addTo(map);
	    }
	}
    }

    // fit to data, if we're supposed to
    if (nextMapPosUpdate == mapPosUpdates.FIT_TO_DATA) {
        map.fitBounds([[minlat, minlon],[maxlat, maxlon]]);
    }
    else if (nextMapPosUpdate == mapPosUpdates.USE_LAT_LON_ZOOM) {
	map.setView([setlat, setlon], setzoom);
    }

    hideDataLoadingMsg();
}


// Geocoding lookup with Nominatim
function nominatimLookup(elementId) {

    let field = document.getElementById(elementId);
    let url = "https://nominatim.openstreetmap.org/search?q=%22" +
	field.value + "%22&format=json&limit=1";
    $.getJSON(url, function(data) {
	// data[0] will have the info
	document.getElementById("latvalinput").value = data[0].lat;
	document.getElementById("lonvalinput").value = data[0].lon;
    });
}

// remember the scrollableMapviewPopup when we create it
var scrollableMapviewPopup = null;

function showScrollableMapviewPopup() {
    // dialog box uses code from
    // https://github.com/mapshakers/leaflet-control-window
    if (scrollableMapviewPopup == null) {
	scrollableMapviewPopup = L.control.window(map, {
	    title: "TM Scrollable Mapview",
	    closeButton: false,
	    content: scrollableMapviewDialog,
	    prompt: {
		buttonAction: "OK",
		buttonOK: "Cancel",
		action: function() {
		    showDataLoadingMsg();
		    let latinput = document.getElementById("latvalinput");
		    let loninput = document.getElementById("lonvalinput");
		    let zoominput = document.getElementById("zoomvalinput");
		    let usersel = document.getElementById("tmuserselect");
		    let unitssel = document.getElementById("tmunitsselect");
		    setlat = latinput.value;
		    setlon = loninput.value;
		    setzoom = zoominput.value;
		    traveler = usersel.value;
		    distanceUnits = unitssel.value;
		    set_traveler_cookie();
		    set_units_cookie();
		    map.setView([setlat, setlon], setzoom);
		},
		callback: function() {
		    // don't update from default or previous position/zoom
		    //    map.setView([setlat, setlon], setzoom);
		}
	    },
	    modal: true,
	    visible: true
	});
    }
    else {
	// we created it earlier, just update it with the current
	// visible situation and show it
	let placeinput = document.getElementById("placeinput");
	let latinput = document.getElementById("latvalinput");
	let loninput = document.getElementById("lonvalinput");
	let zoominput = document.getElementById("zoomvalinput");
	placeinput.value = "";
	let center = map.getCenter();
	latinput.value = center.lat;
	loninput.value = center.lng;
	zoominput.value = map.getZoom();
	scrollableMapviewPopup.show();
    }
}
