//
// Travel Mapping (TM) JavaScript functions related to showroute functionality
//
// Primary author: Jim Teresco
//
// Note: this file should include only functionality specific to
// hb/showroute.php.  Generally-applicable code should continue to
// be placed in tmjsfuncs.js.  This file assumes tmjsfuncs.js
// will be loaded before itself.

// functions to be called at the start/end of a data loading process
// to display/hide the loading message
var dataLoadingHandlersInProgress;

function startDataLoading(msg) {

    document.getElementById("loadingMsgText").innerHTML = msg;
    document.getElementById("loadingMsg").style.display = "";
}

function endDataLoading() {

    document.getElementById("loadingMsg").style.display = "none";
}

/***

.list entry toolbox functionality

***/

var lastClickedConnIndex = -1;
var lastClickedSelecting = false;

// callback to hide/show the draggable .list entry toolbox
function hideShowToolbox() {

    if (document.getElementById('showToolbox').checked) {
	document.getElementById("listToolbox").style.display = "";
	// if we have any selections, show them
	connections.forEach(c => {if (c.selected) createListSelectedOverlay(c);});
	// create clipboard.js object for .list entry toolbox
	var clipboard = new ClipboardJS('.listtoolbox');
	clipboard.on('success', function(e) {
	    e.clearSelection();
	});
    }
    else {
	document.getElementById("listToolbox").style.display = "none";
	// if we have any selections, remove their overlay
	connections.forEach(c => {if (c.selected) removeListSelectedOverlay(c);});
    }
}

// functions based on those at
// https://www.w3schools.com/howto/howto_js_draggable.asp
// to support the draggable .list entry toolbox
function makeListToolboxDraggable() {
    var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    var toolboxDiv = document.getElementById("listToolbox");
    document.getElementById("listToolboxHeader").onmousedown = dragMouseDown;

    function dragMouseDown(e) {
	e = e || window.event;
	e.preventDefault();
	// get the mouse cursor position at startup:
	pos3 = e.clientX;
	pos4 = e.clientY;
	document.onmouseup = closeDragElement;
	// call a function whenever the cursor moves:
	document.onmousemove = elementDrag;
    }
    
    function elementDrag(e) {
	e = e || window.event;
	e.preventDefault();
	// calculate the new cursor position:
	pos1 = pos3 - e.clientX;
	pos2 = pos4 - e.clientY;
	pos3 = e.clientX;
	pos4 = e.clientY;
	// set the element's new position:
	toolboxDiv.style.top = (toolboxDiv.offsetTop - pos2) + "px";
	toolboxDiv.style.left = (toolboxDiv.offsetLeft - pos1) + "px";
    }
    
    function closeDragElement() {
	// stop moving when mouse button is released:
	document.onmouseup = null;
	document.onmousemove = null;
    }
}

// event handler for a MouseEvent e because of a click on Polyline c
function showrouteConnectionClick(e, c) {

    if (document.getElementById('showToolbox').checked) {
	if (e.originalEvent.shiftKey) {
	    if (lastClickedConnIndex >= 0) {
		// select/unselect all from lastClickedConnIndex to here
		if (lastClickedConnIndex > c.connIndex) {
		    for (let i = c.connIndex; i <= lastClickedConnIndex; i++) {
			connections[i].selected = lastClickedSelecting;
		    }
		}
		else {
		    for (let i = lastClickedConnIndex; i <= c.connIndex; i++) {
			connections[i].selected = lastClickedSelecting;
		    }
		}
	    }
	}
	else {
	    // switch selection for this one
	    lastClickedConnIndex = c.connIndex;
	    c.selected = !c.selected;
	    lastClickedSelecting = c.selected;
	}	
	updateListToolboxSelection();
    }
}

// event handler for a MouseEvent e to handle a click on marker m
function showrouteMarkerClick(e, m) {

    m.unbindPopup();
    let coords = m.getLatLng();
    map.panTo(coords);

    // lat, lng in the link should be at least 6 digits (padded with
    // 0 if necessary) but we do not want to trim off additional
    // digits if they exist
    let linklat = "" + coords.lat;
    let fixedlat = coords.lat.toFixed(6);
    if (fixedlat.length > linklat.length) linklat = fixedlat;
    let linklng = "" + coords.lng;
    let fixedlng = coords.lng.toFixed(6);
    if (fixedlng.length > linklng.length) linklng = fixedlng;
    
    let markerinfo = '<p style="line-height:160%;">' +
	'<span id="srmctext" style="font-size:18pt;color:black;">' + m.labels +
	'</span>&nbsp;<button class="srmc" data-clipboard-target="#srmctext">' +
	'Copy</button><br />' +
	'<b><a target="_blank" href="http://www.openstreetmap.org/?lat=' +
	linklat + '&lon=' + linklng +
	'">Coords.:</b> ' + coords.lat +
	'&deg;, ' + coords.lng + '&deg;</a></p>';

    // create clipboard.js object
    var clipboard = new ClipboardJS('.srmc');
    clipboard.on('success', function(e) {
	e.clearSelection();
    });

    if (m.intersects != null) {
	// intersects is an array of 2-element arrays, where
	// each element of the "outer" array is the root and
	// .list name of an intersecting or concurrent route here
	markerinfo += "<p>Intersecting/Concurrent Routes:<br />";
	for (let r = 0; r < m.intersects.length; r++) {
	    markerinfo +=
		"<a href=\"/hb/showroute.php?r=" + m.intersects[r][0] +
		"&lat=" + map.getCenter().lat.toFixed(6) + "&lon=" +
		map.getCenter().lng.toFixed(6) + "&zoom=" + map.getZoom() +
		"\">" + m.intersects[r][1] + "</a><br />";
	}
	markerinfo += "</p>";
    }
    m.bindPopup(markerinfo);
    m.openPopup();
}

// add the selected overlay to a connection
function createListSelectedOverlay(c) {
    
    c.selectedOverlay = L.polyline(c.getLatLngs(), {
	color: "#000",
	weight: 2*polylineWeight,
	opacity: 0.5
    });
    c.selectedOverlay.on('click',
			 function(e) {
			     showrouteConnectionClick(e, c);
			 });
    c.selectedOverlay.addTo(map);
}

// remove the selected overlay for a connection
function removeListSelectedOverlay(c) {

    c.selectedOverlay.remove();
    c.selectedOverlay = null;
}

// show/hide the overlay Polylines and text box for selected segments
function updateListToolboxSelection() {

    let listEntries = "";
    let startSegment = null;
    let endSegment = null;
    
    for (let i = 0; i < connections.length; i++) {
	let c = connections[i];

	// update overlay Polylines
	if (c.selected && c.selectedOverlay == null) {
	    createListSelectedOverlay(c);
	}
	if (!c.selected && c.selectedOverlay != null) {
	    removeListSelectedOverlay(c);
	}

	// build list entries for the toolbox text area
	if (c.selected) {
	    if (startSegment == null) {
		// start of a new segment
		startSegment = c.startLabel;
	    }
	    endSegment = c.endLabel;
	}
	else {
	    if (startSegment != null) {
		listEntries += startSegment + " " + endSegment + "\n";
		startSegment = null;
	    }
	}
    }
    if (startSegment != null) {
	listEntries += startSegment + " " + endSegment + "\n";
    }
    document.getElementById("listToolboxLines").innerHTML = listEntries;
}
    
function hideShowMarkers() {

    let showThem = document.getElementById('showMarkers').checked;
    if (showThem) {
	markers.forEach(m => m.addTo(map));
    }
    else {
	markers.forEach(m => m.remove());
    }
}

// function to update the hidden (behind the map) div that has a span
// containing a URL to the current view at all times, for the "Copy Link"
// button functionality
function updateLinkHereText() {
    
    let loc = window.location;
    let center = map.getCenter();
    let link = 	loc.protocol + "//" + loc.hostname + loc.pathname + "?r=" +
	showrouteParams.roots[0];
    if (showrouteParams.connected) {
	link += "&cr";
    }
    link += "&lat=" + center.lat + "&lon=" + center.lng +
	"&zoom=" + map.getZoom();

    document.getElementById("linkheretext").innerHTML = link;
}

// function to perform the sequence of actions when showroute pages are
// first loaded
function showrouteStartup(lat, lon, zoom) {

    mapStatus = mapStates.HB_ROUTE;
    
    // create clipboard.js object for copy link functionality
    var clipboard = new ClipboardJS('.linkhere');
    clipboard.on('success', function(e) {
	e.clearSelection();
    });
    
    loadmap();
    makeListToolboxDraggable();
    // what colors to use when we draw Polylines?
    showrouteParams.colors = lookupColors(showrouteParams.color,
					  showrouteParams.tier,
					  showrouteParams.system);
    
    startDataLoading("Loading Data...");
    dataLoadingHandlersInProgress = 2;

    // are we fitting to the data or using specified coords/zoom?
    if (lat == null || lon == null || zoom == null) {
   	nextMapPosUpdate = mapPosUpdates.FIT_TO_DATA;
    }
    else {
   	nextMapPosUpdate = mapPosUpdates.USE_LAT_LON_ZOOM;
	setlat = lat;
	setlon = lon;
	setzoom = zoom;
    }

    // route stats
    let routeParams = {
	roots: showrouteParams.roots,
	traveler: traveler,
    };
    let jsonParams = JSON.stringify(routeParams);
    $.ajax({
	type: "POST",
	url: "/lib/getRouteStats.php",
	datatype: "json",
	data: { "params" : jsonParams },
	success: parseRouteStats
    });

    // waypoint tables(s)
    $.ajax({
	type: "POST",
	url: "/lib/getRouteData.php",
	datatype: "json",
	data: { "params" : jsonParams },
	success: parseRouteData
    });
}

// process returned information for the Route Stats table
function parseRouteStats(data) {

    startDataLoading("Processing Route Stats Data...");
    let responses = $.parseJSON(data);

    // compute total miles for all chopped routes
    let totalMileage = 0.0;
    for (let i = 0; i < showrouteParams.roots.length; i++) {
	totalMileage += parseFloat(responses['mileage'][i]);
    }
    let trs = "<tr><td class=\"important\">Total Length</td><td>" +
	length_in_current_units(totalMileage) + "</td></tr>";

    // build up lists of unique drivers and clinchers
    let drivers = new Set();
    let clinchers = new Set();
    if (showrouteParams.roots.length > 1) {
	// get the initial list of clinchers from chopped route 0
	if (responses['clinchers'][0] != null) {
	    let choppedClinchers = responses['clinchers'][0].split(',');
	    choppedClinchers.forEach(c => clinchers.add(c));
	}
	for (let i = 0; i < showrouteParams.roots.length; i++) {
	    if (responses['drivers'][i] != null) {
		let choppedDrivers = responses['drivers'][i].split(',');
		choppedDrivers.forEach(d => drivers.add(d));
	    }
	    // only clinchers who have all previous chopped routes clinched
	    // plus this chopped route remain clinchers
	    let oldClinchers = clinchers;
	    clinchers = new Set();
	    if (responses['clinchers'][i] != null) {
		let choppedClinchers = responses['clinchers'][i].split(',');
		choppedClinchers.forEach(c => {
		    if (oldClinchers.has(c)) clinchers.add(c);
		});
	    }
	    trs += "<tr><td class=\"important\">" +
		'<a href="/hb/showroute.php?r=' +
		showrouteParams.roots[i] + '">' +
		responses['listNames'][i] + "</a></td><td>" +
		length_in_current_units(parseFloat(responses['mileage'][i])) +
		"</td></tr>";
	}	
    }
    else {
	if (responses['drivers'][0] != null) {
	    let choppedDrivers = responses['drivers'][0].split(',');
	    choppedDrivers.forEach(d => drivers.add(d));
	}
	if (responses['clinchers'][0] != null) {
	    let choppedClinchers = responses['clinchers'][0].split(',');
	    choppedClinchers.forEach(c => clinchers.add(c));
	}
    }

    let style = 'style="background-color: ' +
	colorForAmountTraveled(drivers.size, responses['numUsers']) + ';"';
    trs += '<tr title="' + [...drivers] + '"><td>Total Drivers</td>' +
	'<td ' + style + '>' + drivers.size + " (" +
	(100.0 * drivers.size / responses['numUsers']).toFixed(2) +
	"%)</td></tr>";

    // only show the rest of this table for routes with at least one driver
    if (drivers.size > 0) {
	style = 'style="background-color: ' +
	    colorForAmountTraveled(clinchers.size, responses['numUsers']) + ';"';
	trs += '<tr title="' + [...clinchers] +
	    '"><td rowspan=\"2\">Total Clinched</td>' +
	    '<td ' + style + '>' + clinchers.size + " (" +
	    (100.0 * clinchers.size / responses['numUsers']).toFixed(2) +
	    "%)</td></tr>";
	style = 'style="background-color: ' +
	    colorForAmountTraveled(clinchers.size, drivers.size) + ';"';
	trs += '<tr title="' + [...clinchers] +
	    '"><td ' + style + '>' +
	    (100.0 * clinchers.size / drivers.size).toFixed(2) +
	    "% of drivers</td></tr>";

	// average mileage can only be computed for chopped routes
	// without adding some more DB queries
	if (!showrouteParams.connected) {
	    style = 'style="background-color: ' +
		colorForAmountTraveled(responses['avgMileage'][0],
				       responses['mileage'][0]) + ';"';
	    trs += '<tr><td>Average Traveled</td><td ' + style + '>' +
		length_in_current_units(parseFloat(responses['avgMileage'][0])) +
		" (" + (100.0 * responses['avgMileage'][0] /
			responses['mileage'][0]).toFixed(2) +
		"%)</td></tr>";
	}

	// if we have a user, show traveled amount
	if (traveler != "null") {
	    let clinchedMileage = 0.0;
	    for (let i = 0; i < showrouteParams.roots.length; i++) {
		clinchedMileage += parseFloat(responses['clinchedMileage'][i]);
	    }
	    style = 'style="background-color: ' +
		colorForAmountTraveled(clinchedMileage, totalMileage) + ';"';
	    trs += '<tr><td>' + traveler + ' Traveled</td><td ' + style + '>' +
		length_in_current_units(clinchedMileage) +
		" (" + (100.0 * clinchedMileage / totalMileage).toFixed(2) +
		"%)</td></tr>";
	}
    }
    document.getElementById("routeInfoTBody").innerHTML = trs;
    dataLoadingHandlersInProgress--;
    if (dataLoadingHandlersInProgress == 0) {
	endDataLoading();
    }
}

// helper function to see if two 2-element arrays contain equal values
function pairMatch(p1, p2) {

    return p1[0] == p2[0] && p1[1] == p2[1];
}

// parse the waypoint data to be plotted on the map and shown in the Waypoints
// table
function parseRouteData(data) {
    
    startDataLoading("Processing Route Waypoint Data...");
    let responses = $.parseJSON(data);
    
    // set up to find bounding box of points we plot
    let minlat = 999;
    let maxlat = -999;
    let minlon = 999;
    let maxlon = -999;

    // first, if this is a connected route, we might need to reverse the order
    // of some chopped route that it contains to make ends line up
    let reversed = [];
    if (showrouteParams.connected && showrouteParams.roots.length > 1) {
	// to get started, get the first two lined up, and others will need to
	// follow from there
	let r0start = [ responses['latitudes'][0][0],
			responses['longitudes'][0][0]];
	let r0end = [ responses['latitudes'][0][responses['latitudes'][0].length-1],
		      responses['longitudes'][0][responses['longitudes'][0].length-1]];
	let r1start = [ responses['latitudes'][1][0],
			responses['longitudes'][1][0]];
	let r1end = [ responses['latitudes'][1][responses['latitudes'][1].length-1],
		      responses['longitudes'][1][responses['longitudes'][1].length-1]];
	let lastEnd;
	if (pairMatch(r0end, r1start)) {
	    reversed = [ false, false ];
	}
	else if (pairMatch(r0end, r1end)) {
	    reversed = [ false, true ];
	}
	else if (pairMatch(r0start, r1end)) {
	    reversed = [ true, true ];
	}
	else if (pairMatch(r0start, r1start)) {
	    reversed = [ true, false ];
	}
	else {
	    console.log("Chopped routes " + showrouteParams.roots[0] + " and " +
			showrouteParams.roots[1] + " cannot be connected!");
	}
	// which point from r1 does the next need to match?
	if (reversed[1]) {
	    lastEnd = r1start;
	}
	else {
	    lastEnd = r1end;
	}
	// check remaining routes
	for (let i = 2; i < showrouteParams.roots.length; i++) {
	    let ristart = [ responses['latitudes'][i][0],
			    responses['longitudes'][i][0]];
	    let riend = [ responses['latitudes'][i][responses['latitudes'][i].length-1],
			  responses['longitudes'][i][responses['longitudes'][i].length-1]];
	    if (pairMatch(lastEnd, ristart)) {
		reversed.push(false);
		lastEnd = riend;
	    }
	    else if (pairMatch(lastEnd, riend)) {
		reversed.push(true);
		lastEnd = ristart;
	    }
	    else {
		// assume no swap when no match
		reversed.push(false);
		lastEnd = riend;
		console.log("Chopped routes " + showrouteParams.roots[i-1] + " and " +
			    showrouteParams.roots[i] + " cannot be connected!");
		console.log("End of " + showrouteParams.roots[i-1] + " at " +
			    lastEnd + " does not match either endpoint of " +
			    showrouteParams.roots[i] + " at " + ristart +
			    " or " + riend);
	    }
	}

	// reverse data where needed before continuing processing
	for (let i = 0; i < reversed.length; i++) {
	    if (reversed[i]) {
		responses['pointNames'][i].reverse();
		responses['latitudes'][i].reverse();
		responses['longitudes'][i].reverse();
		// segment-based have "dummy" entries at the end
		responses['driverCounts'][i].pop();
		responses['driverCounts'][i].reverse();
		responses['driverCounts'][i].push("0");
		responses['segmentIds'][i].pop();
		responses['segmentIds'][i].reverse();
		responses['segmentIds'][i].push(null);
		responses['clinched'][i].pop();
		responses['clinched'][i].reverse();
		responses['clinched'][i].push("0");
	    }
	}
    }

    startDataLoading("Building Waypoint Table...");
    // build waypoints array for the map and waypoints table
    let trs = "";

    // since markers on the map are added after the table is built,
    // we do not yet have the marker itself during table creation,
    // but this variable will keep track of which marker will later
    // correspond to each table entry to be able to show its popup
    let markernum = 0;
    
    for (let i = 0; i < showrouteParams.roots.length; i++) {
	// route header for connected routes
	if (showrouteParams.connected) {
	    trs += '<tr><td colspan="2" style="text-align: center; background-color: rgb(200,200,200);">' +
		'.list Name: <a href="/hb/showroute.php?r=' +
		showrouteParams.roots[i] + '">' +
		responses['listNames'][i] + '</a></td></tr>';
	}

	// loop over waypoints for this chopped route
	let pointNames = responses['pointNames'][i];
	let clinched = responses['clinched'][i];
	let driverCounts = responses['driverCounts'][i];
	let lastColor = 'rgb(255,255,255)';
	
	for (let j = 0 ; j < pointNames.length; j++) {

	    // table entries only for visible points
	    if (pointNames[j][0] != '+') {
		let thisColor = (clinched[j] == "1" ? 'rgb(255,167,167)' :
				 'rgb(255,255,255)');
		let style1 = 'style="text-align: center; background-image: ' +
		    'linear-gradient(180deg, ' + lastColor +
		    ' 0 50%, ' + thisColor + ' 50% 100%)";';
		lastColor = thisColor;
		let style2 = 'style="text-align: center; background-color: ' +
		    colorForAmountTraveled(driverCounts[j],
					   responses['numUsers']) + '";';
		
		// tr's labelClick functionality here to be replaced with
		// updated implementation
		trs += '<tr onclick="showrouteMarkerClick(null, markers[' +
		    markernum + ']);"><td ' + style1 + '>' + pointNames[j] +
		    '</td>';

		// will this have been an actual new marker?
		if ((i == 0) || (j > 0)) markernum++;
		
		if (j == pointNames.length - 1) {
		    // no segment corresponding to last waypoint
		    trs += '<td></td></tr>';
		}
		else {
		    trs += '<td ' + style2 + '>' +
			(100.0 * driverCounts[j] / responses['numUsers']).toFixed(2) +
			'%</td></tr>';
		}
	    }
	}
    }
    // display the table
    document.getElementById("waypointsTBody").innerHTML = trs;

    startDataLoading("Adding Data to Map...");
    // add markers and polylines to the map
    for (let i = 0; i < showrouteParams.roots.length; i++) {

	let pointNames = responses['pointNames'][i];
	let latitudes = responses['latitudes'][i];
	let longitudes = responses['longitudes'][i];
	let clinched = responses['clinched'][i];
	let intersects = responses['intersects'][i];

	// list name text to add when processing connected routes
	let listName = "";
	if (showrouteParams.connected) {
	    listName = responses['listNames'][i] + ' ';
	}

	// points for polyline to the next visible waypoint
	let path = [];
	let pathLength = 0.0;
	let pathStart = "";
	
	// loop over waypoints for this chopped route
	for (let j = 0 ; j < pointNames.length; j++) {

	    // extend our bounds to include this point?
	    minlat = Math.min(minlat, latitudes[j]);
	    maxlat = Math.max(maxlat, latitudes[j]);
	    minlon = Math.min(minlon, longitudes[j]);
	    maxlon = Math.max(maxlon, longitudes[j]);

	    // add point to the path
	    path.push([latitudes[j], longitudes[j]]);
	    if (path.length == 1) {
		pathStart = pointNames[j];
	    }
	    else {
		pathLength += distanceInMiles(latitudes[j],
					      longitudes[j],
					      latitudes[j-1],
					      longitudes[j-1]);
	    }
	    
	    // hidden points otherwise ignored here
	    if ((pointNames[j][0] != '+') ||
		pointNames[j].startsWith("+DIV") ||
		pointNames[j].startsWith("+SKIP")) {

		// we don't need a new marker for the first point in a chopped
		// route for connected routes (except the first chopped route)
		if ((i == 0) || (j > 0)) {
		    let m = L.marker([latitudes[j], longitudes[j]], {
			title: listName + pointNames[j],
			icon: intersectionimage
		    });
		    markers.push(m);
		    m.addTo(map);
		    
		    // build info for this marker's popup
		    let labels = listName + pointNames[j];
		    
		    // if this is the last point in a non-last chopped
		    // route within a connected route
		    if ((j == pointNames.length - 1) &&
			(i < showrouteParams.roots.length - 1)) {
			labels += '<br />' + responses['listNames'][i+1] + ' ' +
			    responses['pointNames'][i+1][0];
		    }

		    // save labels and intersecting route info with
		    // the marker for popups later (even if null)
		    m.labels = labels;
		    m.intersects = intersects[j];
		    m.on('click', function(e) {
			showrouteMarkerClick(e, m);
		    });
		    
		    // unless this is the first point, we can draw a polyline
		    // to here from the previous visible point
		    if (j > 0) {
			let color = showrouteParams.colors[0];
			let opacity = 0.3;
			if (clinched[j-1] == "1" || traveler == "null") {
			    color = showrouteParams.colors[1];
			    opacity = 0.85;
			}
			let c = L.polyline(path, {
			    color: color,
			    weight: polylineWeight,
			    opacity: opacity
			});
			c.clinched = clinched[j-1] == "1";
			c.addTo(map);

			// so the click callback can find this Polyline
			c.connIndex = connections.length;

			// for .list entry toolbox functionality
			c.selected = false;
			c.selectedOverlay = null;
			connections.push(c);
			c.on('click',
			     function(e) {
				 showrouteConnectionClick(e, c);
			     });
			let listPrefix = "";
			if (listName.length > 0) {
			    listPrefix = listName + "<br />";
			}
			c.startLabel = responses['listNames'][i] + ' ' +
			    pathStart;
			if (showrouteParams.connected) {
			    c.endLabel = listName + pointNames[j];
			}
			else {
			    c.endLabel = pointNames[j];
			}
			c.bindPopup(listPrefix + pathStart + " <-> " +
				    pointNames[j] + "<br />Length: " +
				    length_in_current_units(pathLength));
			path = [ [latitudes[j], longitudes[j]] ];
			pathLength = 0.0;
			pathStart = pointNames[j];
		    }
		}
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

    updateLinkHereText();
    map.on('resize', updateLinkHereText);
    map.on('moveend', updateLinkHereText);
    map.on('zoomend', updateLinkHereText);
    
    dataLoadingHandlersInProgress--;
    if (dataLoadingHandlersInProgress == 0) {
	endDataLoading();
    }
}

// handle change in the "similar routes" dropdown
function jumpToSimilarRoute() {

    let routeSelector = document.getElementById("similarroute");
    // index 0 is not a route
    if (routeSelector.selectedIndex > 0) {
        let root = routeSelector.options[routeSelector.selectedIndex].value;
        redirect("/hb/showroute.php?r=" + root);
    }
}
