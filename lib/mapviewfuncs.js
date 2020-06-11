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

// mapview legend, and object where we'll keep the legends for each
// display option
var mapviewLegend;
var mapviewLegends = Object();

function mapviewLegendEntry(color, text) {

    return '<i style="background:' + color + '"></i>&nbsp ' + text + '<br />';
}

// functions to be called at the start/end of a data loading process
// to display/hide the loading message and do other cleanup (like
// removing any hovering overlay segments)
function startDataLoading() {

    mapviewRouteEndHoverRoot();
    document.getElementById("loadingMsg").style.display = "";
}

function endDataLoading() {

    mapviewRouteEndHoverRoot();
    document.getElementById("loadingMsg").style.display = "none";
}

function showHideRouteTable() {

    let check = document.getElementById("showRoutesCheckbox");
    let table = document.getElementById("routes");
    if (check.checked) {
        table.style.display = "";
	setTMCookie("mapviewRT", "checked");
    }
    else {
        table.style.display = "none";
	setTMCookie("mapviewRT", "unchecked");
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

// show/hide legend
function legendCheckboxChanged() {

    if (document.getElementById("legendCheckbox").checked) {
	mapviewLegend.addTo(map);
	setTMCookie("mapviewLegend", "checked");
    }
    else {
	mapviewLegend.remove();
	setTMCookie("mapviewLegend", "unchecked");
    }
}

// get the current mapview legend contents
function mapviewLegendContents() {

    let colorScheme = document.getElementById("coloring").value;
    return mapviewLegends[colorScheme];
}

// event handler for changes in segment highlighting
function updateConnectionColors() {

    let colorScheme = document.getElementById("coloring").value;
    let highlightScheme = document.getElementById("highlighting").value;

    // update legend
    if (document.getElementById("legendCheckbox").checked) {
	mapviewLegend.remove();
	mapviewLegend.addTo(map);
    }
    
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

    // set toggles based on cookies if they exist
    document.getElementById("showRoutesCheckbox").checked = 
	getTMCookie("mapviewRT") != "unchecked";
    showHideRouteTable();
    document.getElementById("legendCheckbox").checked = 
	getTMCookie("mapviewLegend") != "unchecked";

    // create empty legend to start
    mapviewLegend = L.control({position: 'bottomleft'});
    mapviewLegend.onAdd = function(map) {
	this._div = L.DomUtil.create('div', 'mapviewLegend');
	this._div.style.padding = "6px 8px";
	this._div.style.font = "14px/16px Arial, Helvetica, sans-serif";
	this._div.style.background = "rgba(255,255,255,0.8)";
	this._div.style.boxShadow = "0 0 15px rgba(0,0,0,0.2)";
	this._div.style.borderRadius = "5px";
	this._div.style.lineHeight ="18px";
	this._div.innerHTML = mapviewLegendContents();
	return this._div;
    };

    // find initial data based on mapviewParams
    // ignoring country for now - it will eventually just extend regions
    // either here or in mapview.php before it gets here

    // if this gets a value, it means we need to use it for an AJAX request
    let clause = '';
    // a number of cases arise based on the QS params
    if (mapviewParams.roots.length > 0) {
	clause = "where (routes.root='" + mapviewParams.roots[0] + "'";
	for (let i = 1; i < mapviewParams.roots.length; i++) {
	    clause += " or routes.root='" + mapviewParams.roots[i] + "'";
	}
	clause += ")";
    }
    else if (mapviewParams.regions.length > 0) {
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
    else if (mapviewParams.routePattern != '') {
	clause = "where routes.route='" + mapviewParams.routePattern + "'";
	// if we have rte=, we could also have systems restrictions
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
	startDataLoading();
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

    startDataLoading();
    
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
    let segmentids = responses['segmentids'];
    let w1name = responses['w1name'];
    let w1lat = responses['w1lat'];
    let w1lng = responses['w1lng'];
    let w2name = responses['w2name'];
    let w2lat = responses['w2lat'];
    let w2lng = responses['w2lng'];
    let clinched = responses['clinched'];
    let travelers = responses['travelers'];
    let routeroots = responses['routeroots'];

    // build route info object with details "indexed" by a property
    // equal to the root string (e.g., ny.i090)
    let routes = [];
    for (let i = 0; i < routeroots.length; i++) {
	routes[routeroots[i]] = {
	    listname: responses['routelistnames'][i],
	    mileage: parseFloat(responses['routemileages'][i]),
	    clinchedmileage: parseFloat(responses['routeclinchedmileages'][i]),
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

    // set up to build legends
    mapviewLegends.system = "<h4>Systems</h4>";
    mapviewLegends.travelers = "<h4># Travelers</h4>";
    mapviewLegends.concurrent = "<h4>Concurrencies</h4>";
    mapviewLegends.plain = mapviewLegendEntry("black", "All Routes");
    let maxConcurrency = 1;

    // compute clinched and total mileage of visible portions of routes,
    // and maxTravelers (and related) for color selections later
    let haveUntraveled = false;
    let haveSingleTraveler = false;
    let minTravelers = Number.MAX_VALUE;
    maxTravelers = 0;
    for (let i = 0; i < roots.length; i++) {
	let d = distanceInMiles(w1lat[i], w1lng[i], w2lat[i], w2lng[i]);

	routes[roots[i]].visiblemileage += d;
	if (clinched[i] == 1) {
	    routes[roots[i]].visibleclinched += d;
	}
	let t = parseInt(travelers[i]);
	if (t == 0) haveUntraveled = true;
	if (t == 1) haveSingleTraveler = true;
	if (t > maxTravelers) {
	    maxTravelers = t;
	}
	if ((t >= 2) && (t < minTravelers)) {
	    minTravelers = t;
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
    let sysTotals = new Object();
    sysTotals.visTrav = 0.0;
    sysTotals.visTotal = 0.0;
    sysTotals.allTrav = 0.0;
    sysTotals.allTotal = 0.0;
    sysTotals.count = 0;
    sysTotals.clinchCount = 0;
    let totals = new Object();
    totals.visTrav = 0.0;
    totals.visTotal = 0.0;
    totals.allTrav = 0.0;
    totals.allTotal = 0.0;
    totals.count = 0;
    totals.clinchCount = 0;
    totals.sysCount = 0;

    let haveRoutes = false;
    for (let i = 0; i < routeroots.length; i++) {
	let root = routeroots[i];
	if (routes[root].level == "devel") continue;
	haveRoutes = true;
	// system "header" row and new legend entry needed?
	if (routes[root].systemcode != currentSystem) {
	    // check if a previous system total is needed
	    if (sysTotals.count > 1) {
		let percent = sysTotals.allTrav/sysTotals.allTotal * 100.0;
		let vispercent = sysTotals.visTrav/sysTotals.visTotal * 100.0;
		if (sysTotals.clinched == sysTotals.count) {
		    percent = 100.0;
		}
		tablerows += '<tr><td><b>' + currentSystem + ' Total</b></td><td style="background-color: ' + colorForAmountTraveled(sysTotals.visTrav, sysTotals.visTotal) + '">' + convertToCurrentUnits(sysTotals.visTrav).toFixed(2) + ' of ' + convertToCurrentUnits(sysTotals.visTotal).toFixed(2) + ' (' + vispercent.toFixed(1) + '%)</td><td style="background-color: ' + colorForAmountTraveled(sysTotals.allTrav, sysTotals.allTotal) + ';">' + convertToCurrentUnits(sysTotals.allTrav).toFixed(2) + ' of ' + convertToCurrentUnits(sysTotals.allTotal).toFixed(2) + ' (' + percent.toFixed(1) + '%)</td></tr>'; 
	    }
	    currentSystem = routes[root].systemcode;

	    totals.visTrav += sysTotals.visTrav;
	    totals.visTotal += sysTotals.visTotal;
	    totals.allTrav += sysTotals.allTrav;
	    totals.allTotal += sysTotals.allTotal;
	    totals.count += sysTotals.count;
	    totals.clinchCount += sysTotals.clinchCount;
	    totals.sysCount += 1;
	    
	    sysTotals.visTrav = 0.0;
	    sysTotals.visTotal = 0.0;
	    sysTotals.allTrav = 0.0;
	    sysTotals.allTotal = 0.0;
	    sysTotals.count = 0;
	    sysTotals.clinchCount = 0;
	    let bgcolor = "#CCFFCC";  // active
	    if (routes[root].level == "preview") {
		bgcolor = "#FFFFCC";
	    }
	    tablerows += '<tr onmouseover="mapviewRouteHoverSystem(\'' + currentSystem + '\')" onmouseout="mapviewRouteEndHoverRoot()"><td colspan="3" style="text-align: center; background-color: ' + bgcolor + '">' + routes[root].systemname + " (" + currentSystem + ')</td></tr>';
	    // new system for legend
	    mapviewLegends.system += mapviewLegendEntry(
		lookupColors(routes[root].color, "", currentSystem)[1],
		currentSystem);
	}
	let percent = routes[root].clinchedmileage/routes[root].mileage * 100.0;
	let vispercent = routes[root].visibleclinched/routes[root].visiblemileage * 100.0;
	if (routes[root].clinched == 1) {
	    percent = 100.0;
	}
	let link = "/hb/?r=" + root;
	tablerows += '<tr onclick="window.open(\'' + link + '\')" onmouseover="mapviewRouteHoverRoot(\'' + root + '\')" onmouseout="mapviewRouteEndHoverRoot()"><td>' + routes[root].listname + '</td><td style="background-color: ' + colorForAmountTraveled(routes[root].visibleclinched, routes[root].visiblemileage) + '">' + convertToCurrentUnits(routes[root].visibleclinched).toFixed(2) + ' of ' + convertToCurrentUnits(routes[root].visiblemileage).toFixed(2) + ' (' + vispercent.toFixed(1) + '%)</td><td style="background-color: ' + colorForAmountTraveled(routes[root].clinchedmileage, routes[root].mileage) + ';">' + convertToCurrentUnits(routes[root].clinchedmileage).toFixed(2) + ' of ' + convertToCurrentUnits(routes[root].mileage).toFixed(2) + ' (' + percent.toFixed(1) + '%)</td></tr>';
	sysTotals.visTrav += routes[root].visibleclinched;
	sysTotals.visTotal += routes[root].visiblemileage;
	sysTotals.allTrav += routes[root].clinchedmileage;
	sysTotals.allTotal += routes[root].mileage;
	sysTotals.count += 1;
	if (routes[root].clinched == 1) {
	    sysTotals.clinchCount += 1;
	}
    }

    // add the last system to the grand totals
    totals.visTrav += sysTotals.visTrav;
    totals.visTotal += sysTotals.visTotal;
    totals.allTrav += sysTotals.allTrav;
    totals.allTotal += sysTotals.allTotal;
    totals.count += sysTotals.count;
    totals.clinchCount += sysTotals.clinchCount;
    totals.sysCount += 1;
	    
    // check if the last system total is needed
    if (sysTotals.count > 1) {
	let percent = sysTotals.allTrav/sysTotals.allTotal * 100.0;
	let vispercent = sysTotals.visTrav/sysTotals.visTotal * 100.0;
	if (sysTotals.clinched == sysTotals.count) {
	    percent = 100.0;
	}
	tablerows += '<tr><td><b>' + currentSystem + ' Total</b></td><td style="background-color: ' + colorForAmountTraveled(sysTotals.visTrav, sysTotals.visTotal) + '">' + convertToCurrentUnits(sysTotals.visTrav).toFixed(2) + ' of ' + convertToCurrentUnits(sysTotals.visTotal).toFixed(2) + ' (' + vispercent.toFixed(1) + '%)</td><td style="background-color: ' + colorForAmountTraveled(sysTotals.allTrav, sysTotals.allTotal) + ';">' + convertToCurrentUnits(sysTotals.allTrav).toFixed(2) + ' of ' + convertToCurrentUnits(sysTotals.allTotal).toFixed(2) + ' (' + percent.toFixed(1) + '%)</td></tr>'; 
    }
    
    // check if a grand total is needed (note count will be one off)
    if (totals.sysCount > 2) {
	let percent = totals.allTrav/totals.allTotal * 100.0;
	let vispercent = totals.visTrav/totals.visTotal * 100.0;
	if (totals.clinched == totals.count) {
	    percent = 100.0;
	}
	tablerows += '<tr><td><b>Grand Total</b></td><td style="background-color: ' + colorForAmountTraveled(totals.visTrav, totals.visTotal) + '">' + convertToCurrentUnits(totals.visTrav).toFixed(2) + ' of ' + convertToCurrentUnits(totals.visTotal).toFixed(2) + ' (' + vispercent.toFixed(1) + '%)</td><td style="background-color: ' + colorForAmountTraveled(totals.allTrav, totals.allTotal) + ';">' + convertToCurrentUnits(totals.allTrav).toFixed(2) + ' of ' + convertToCurrentUnits(totals.allTotal).toFixed(2) + ' (' + percent.toFixed(1) + '%)</td></tr>'; 
    }
    
    // make sure we have something visible
    if (!haveRoutes) {
	tablerows += '<tr><td colspan="3" style="text-align: center;">No Routes on Map</td></tr>';
	tbody.innerHTML = tablerows;
	mapviewLegend.remove();
	endDataLoading();
	return;
    }
    tbody.innerHTML = tablerows;
    
    // loop through all segments and build the appropriate connections

    // track where the current route starts in the array of
    // connections (not the full lists of segment info like roots)
    let currentRouteStart = 0;
    let currentRoot = "";
    
    // set up to find bounding box of points we plot
    let minlat = 999;
    let maxlat = -999;
    let minlon = 999;
    let maxlon = -999;

    for (let i = 0; i < roots.length; i++) {

	// skip devel routes
	if (routes[roots[i]].level == "devel") continue;

	// while we're here, update bounding box just in case
	if (w1lat[i] < minlat) minlat = w1lat[i];
	if (w1lat[i] > maxlat) maxlat = w1lat[i];
	if (w1lng[i] < minlon) minlon = w1lng[i];
	if (w1lng[i] > maxlon) maxlon = w1lng[i];
	if (w2lat[i] < minlat) minlat = w2lat[i];
	if (w2lat[i] > maxlat) maxlat = w2lat[i];
	if (w2lng[i] < minlon) minlon = w2lng[i];
	if (w2lng[i] > maxlon) maxlon = w2lng[i];
	
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
		    if (matched.TMrouteshere.length > maxConcurrency) {
			maxConcurrency = matched.TMrouteshere.length;
		    }
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
	}

	let edgePoints = new Array(2);
	edgePoints[0] = [w1lat[i], w1lng[i]];
	edgePoints[1] = [w2lat[i], w2lng[i]];
	// note: color and opacity will be set according to UI settings
	// before these connections are added to the map
	let con = L.polyline(edgePoints, {
	    color: "black",
	    weight: polylineWeight,
	    opacity: 1
	});

	connections.push(con);
	con.TMend1 = w1name[i];
	con.TMend2 = w2name[i];
	con.TMsegmentid = segmentids[i];
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
	    '<br /><span id="popup' + c.TMsegmentid +
	    '" onclick="getTravelers(\'' + c.TMsegmentid + '\');"';
	if (c.TMtravelers > 0) {
	    c.TMpopupinfo += ' title="Click then hover for list"';
	}
	c.TMpopupinfo += ">" + c.TMtravelers + " traveler";
	if (c.TMtravelers != 1) c.TMpopupinfo += "s";
	c.TMpopupinfo += '</span><br />';
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

    // finish creating legend entries (for concurrencies and num travelers)
    for (let i = 1; i <= maxConcurrency; i++) {
	mapviewLegends.concurrent += mapviewLegendEntry(
	    concurrencyColors[i], i);
    }
    if (haveUntraveled) {
	mapviewLegends.travelers +=
	    mapviewLegendEntry(segmentColorForAmountTraveled(0, 1), "None");
    }
    if (haveSingleTraveler) {
	mapviewLegends.travelers +=
	    mapviewLegendEntry(segmentColorForAmountTraveled(1, 1), "1");
    }

    // only need more if any routes traveled by at least 2
    if (minTravelers < Number.MAX_VALUE) {
	let rangeSize = maxTravelers - minTravelers;

	// only 1 value 2 and up, so display it
	if (rangeSize == 0) {
	    mapviewLegends.travelers +=
		mapviewLegendEntry(segmentColorForAmountTraveled(minTravelers, maxTravelers), minTravelers);
	}
	// otherwise at least two values to show, so we make a gradient
	else {
	    // we'll use 4 boxes, each with 1/4 of the gradient, and each
	    // of those with 3 intermediate values, so the range is broken
	    // into 16 total intervals, the colors of which are computed here
	    colorVals = [];
	    colorVals.push(segmentColorForAmountTraveled(minTravelers, maxTravelers));
	    for (let i = 1; i < 16; i++) {
		colorVals.push(segmentColorForAmountTraveled(minTravelers + i * (maxTravelers - minTravelers)/16, maxTravelers));
	    }
	    colorVals.push(segmentColorForAmountTraveled(maxTravelers, maxTravelers));
	    mapviewLegends.travelers +=
		'<i style="background-image: linear-gradient(to bottom,' +
		colorVals[0] + ',' + colorVals[1] + ',' + 
		colorVals[2] + ',' + colorVals[3] + ',' + 
		colorVals[4] + ');"></i>&nbsp; ' + minTravelers + '<br />';
	    mapviewLegends.travelers +=
		'<i style="background-image: linear-gradient(to bottom,' +
		colorVals[4] + ',' + colorVals[5] + ',' + 
		colorVals[6] + ',' + colorVals[7] + ',' + 
		colorVals[8] + ');"></i>&nbsp;<br />';
	    mapviewLegends.travelers +=
		'<i style="background-image: linear-gradient(to bottom,' +
		colorVals[8] + ',' + colorVals[9] + ',' + 
		colorVals[10] + ',' + colorVals[11] + ',' + 
		colorVals[12] + ');"></i>&nbsp;<br />';
	    mapviewLegends.travelers +=
		'<i style="background-image: linear-gradient(to bottom,' +
		colorVals[12] + ',' + colorVals[13] + ',' + 
		colorVals[14] + ',' + colorVals[15] + ',' + 
		colorVals[16] + ');"></i>&nbsp; ' + maxTravelers + '<br />';
	}
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

    endDataLoading();
}

// get a list of travelers who have traveled the given segment
// and put it into the popup for that segment
function getTravelers(segmentid) {

    let params = {
	segmentid: segmentid
    };
    let jsonParams = JSON.stringify(params);
    $.ajax({
	type: "POST",
	url: "/lib/getSegmentTravelers.php",
	datatype: "json",
	data: { "params" : jsonParams },
	success: function(data) {
	    let span = document.getElementById("popup" + segmentid);
	    let responses = $.parseJSON(data);
	    //span.innerHTML = ": " + responses.join(" ");
	    span.title = responses.join(" ");
	}
    });   
}

// event handler to call nominatimLookup on the placeinput text field
// if the key pressed was the Enter key
function nominatimLookupIfEnter(event) {

    if (event.keyCode == 13) nominatimLookup('placeinput');
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
		    startDataLoading();
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
