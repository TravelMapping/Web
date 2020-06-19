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
	connected: showrouteParams.connected
    };
    let jsonParams = JSON.stringify(routeParams);
    $.ajax({
	type: "POST",
	url: "/lib/getRouteData.php",
	datatype: "json",
	data: { "params" : jsonParams },
	success: parseRouteData
    });
 
    // waypoint tables(s)

}

// process returned information for the Route Stats table
function parseRouteData(data) {

    console.log(data);
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
    }
    /*
    $style = 'style="background-color: '.tm_color_for_amount_traveled($row['avgMileage'],$totalMileage).';"';
    echo "    <tr><td>Average Traveled</td><td ".$style.">".tm_convert_distance(round($row['avgMileage'],2))." ".$tmunits." (".round(100 * $row['avgMileage'] / $totalMileage, 2)."%)</td></tr>\n";
    if ($tmuser != "null") {
      $sql_command = "SELECT round(mileage,4) as mileage FROM clinchedRoutes where traveler='" . $tmuser . "' AND route='" . $routeparam . "'";
      $row = tmdb_query($sql_command) -> fetch_assoc();
      $style = 'style="background-color: '.tm_color_for_amount_traveled($row['mileage'],$numUsers).';"';
      echo "    <tr><td>{$tmuser} Traveled</td><td ".$style.">".tm_convert_distance($row['mileage'])." ".$tmunits." (".round(100 * $row['mileage'] / $totalMileage, 2)."%)</td></tr>\n";
    }
    */
    document.getElementById("routeInfoTBody").innerHTML = trs;
}

function parseWaypointRouteData(data) {
    
    console.log(data);
    let responses = $.parseJSON(data);
    
    // set up to find bounding box of points we plot
    let minlat = 999;
    let maxlat = -999;
    let minlon = 999;
    let maxlon = -999;

    // fit to data, if we're supposed to
    if (nextMapPosUpdate == mapPosUpdates.FIT_TO_DATA) {
        map.fitBounds([[minlat, minlon],[maxlat, maxlon]]);
    }
    else if (nextMapPosUpdate == mapPosUpdates.USE_LAT_LON_ZOOM) {
	map.setView([setlat, setlon], setzoom);
    }

    endDataLoading();
}
