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
// to display/hide the loading message and do other cleanup (like
// removing any hovering overlay segments)
function startDataLoading() {

    document.getElementById("loadingMsg").style.display = "";
}

function endDataLoading() {

    document.getElementById("loadingMsg").style.display = "none";
}

// function to perform the sequence of actions when showroute pages are
// first loaded
function showrouteStartup(lat, lon, zoom) {

    mapStatus = mapStates.HB_ROUTE;
    
    loadmap();
    startDataLoading();

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
	for (let i = 0; i < showrouteParams.roots.length; i++) {
	    let choppedDrivers = responses['drivers'][i].split(',');
	    choppedDrivers.forEach(d => drivers.add(d));
	    let choppedClinchers = responses['clinchers'][i].split(',');
	    choppedClinchers.forEach(c => clinchers.add(c));
	    trs += "<tr><td class=\"important\">" +
		'<a href="/hb/showroute.php?r=' +
		showrouteParams.roots[i] + '">' +
		responses['listNames'][i] + "</a></td><td>" +
		length_in_current_units(parseFloat(responses['mileage'][i])) +
		"</td></tr>";
	}	
    }
    else {
	let choppedDrivers = responses['drivers'][0].split(',');
	choppedDrivers.forEach(d => drivers.add(d));
	let choppedClinchers = responses['clinchers'][0].split(',');
	choppedClinchers.forEach(c => clinchers.add(c));
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
}

function parseRouteData(data) {
    
    console.log(data);
    let responses = $.parseJSON(data);
    
    // set up to find bounding box of points we plot
    let minlat = 999;
    let maxlat = -999;
    let minlon = 999;
    let maxlon = -999;

    // build waypoints array for the map and waypoints table
    let trs = "";
    for (let i = 0; i < showrouteParams.roots.length; i++) {
	// route header for connected routes
	if (showrouteParams.connected) {
	    trs += '<tr><td colspan="2" style="text-align: center">' +
		'.list Name: <a href="/hb/showroute.php?r=' +
		showrouteParams.roots[i] + '">' +
		responses['listNames'][i] + '</a></td></tr>';
	}

	// loop over waypoints for this chopped route
	let pointNames = responses['pointNames'][i];
	let clinched = responses['clinched'][i];
	let driverCounts = responses['driverCounts'][i];

	for (let j = 0 ; j < pointNames.length; j++) {

	    // table entries only for visible points
	    if (pointNames[j][0] != '+') {
		let style1 = 'style="text-align: center; background-color: ' +
		    (clinched[j] == "1" ? 'rgb(255,167,167)' :
		     'rgb(255,255,255)') + '";';
		let style2 = 'style="text-align: center; background-color: ' +
		    colorForAmountTraveled(driverCounts[j],
					   responses['numUsers']) + '";';
		
		// tr's labelClick functionality here to be replaced with
		// updated implementation
		trs += '<tr><td ' + style1 + '>' + pointNames[j] + '</td>';
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
    
    // fit to data, if we're supposed to
    if (nextMapPosUpdate == mapPosUpdates.FIT_TO_DATA) {
        map.fitBounds([[minlat, minlon],[maxlat, maxlon]]);
    }
    else if (nextMapPosUpdate == mapPosUpdates.USE_LAT_LON_ZOOM) {
	map.setView([setlat, setlon], setzoom);
    }

    document.getElementById("waypointsTBody").innerHTML = trs;
    endDataLoading();
}
