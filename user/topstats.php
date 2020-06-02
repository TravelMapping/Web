<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 ***
 * Top stats page.
 * URL Params:
 *  u - user whose stats to display (required)
 *  rg - region to consider (optional)
 *  country - country to consider (optional, not yet implemented)
 *  sys - system to consider (optional)
 * (u, [rg|sys|country])
 ***
 -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
    <link rel="shortcut icon" type="image/png" href="/favicon.png">
    <?php tm_common_js(); ?>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
    <title>Travel Mapping: Top User Stats</title>
</head>

<script type="text/javascript">
var selects = new Object();
function initUI() {

    let controls = document.getElementById("controlbox");
    let allselects = controls.getElementsByTagName("select");
    for (let i = 0; i < allselects.length; i++) {
        selects[allselects[i].name] = allselects[i];
    }
    selects['rg'].disabled = true;
}

function connectedChanged() {

    selects['rg'].disabled = selects['connected'].value == "connected";
}

function updateStats() {

    distanceUnits = selects['units'].value;
    document.getElementById("unitsText").innerHTML = distanceUnits;
    let params = {
	traveler: selects['u'].value,
	system: selects['sys'].value,
        region: selects['rg'].value,
	preview: selects['preview'].value == "yes",
	numentries: document.getElementById("numentries").value
    };
    
    let jsonParams = JSON.stringify(params);
    if (selects['connected'].value == "inregion") {
        // regular clinched routes (in a single region)
        $.ajax({
    	    type: "POST",
	    url: "/lib/getLongestClinchedRoutes.php",
	    datatype: "json",
	    data: { "params" : jsonParams },
	    success: parseLongestClinchedData
        });
        // longest travels on routes (in a single region)
        $.ajax({
    	    type: "POST",
 	    url: "/lib/getLongestTraveledRoutes.php",
	    datatype: "json",
	    data: { "params" : jsonParams },
	    success: parseLongestTraveledData
        });
        // shortest unclinched travels on traveled routes (in a single region)
        $.ajax({
    	    type: "POST",
 	    url: "/lib/getClosestToClinchedTraveledRoutes.php",
	    datatype: "json",
	    data: { "params" : jsonParams },
	    success: parseClosestToClinchedTraveledData
        });
    }
    else {
        // connected clinched routes
        $.ajax({
	    type: "POST",
	    url: "/lib/getLongestClinchedConnectedRoutes.php",
	    datatype: "json",
	    data: { "params" : jsonParams },
	    success: parseLongestClinchedConnectedData
        });
        // longest travels on connected routes
        $.ajax({
	    type: "POST",
	    url: "/lib/getLongestTraveledConnectedRoutes.php",
	    datatype: "json",
	    data: { "params" : jsonParams },
	    success: parseLongestTraveledConnectedData
        });
        // shortest unclinched travels on traveled connected routes
        $.ajax({
    	    type: "POST",
 	    url: "/lib/getClosestToClinchedConnectedTraveledRoutes.php",
	    datatype: "json",
	    data: { "params" : jsonParams },
	    success: parseClosestToClinchedConnectedTraveledData
        });
    }
}

function parseLongestClinchedData(data) {

    let response = $.parseJSON(data);
    // we have an array in response, each element has fields
    // root (for link), routeinfo (human-readable), and mileage
    // build the table of longest clinched from that
    let tbody = document.getElementById("longestClinchedRoutes");
    let rows = "";
    if (response.length == 0) {
       rows = '<tr><td colspan="2" style="text-align:center">No Routes Clinched</td></tr>';
    }
    else {
        for (let i = 0; i < response.length; i++) {
	    let link = "/hb/?r=" + response[i].root;
            rows += '<tr onclick="window.open(\'' + link + '\')"><td>' +
	        response[i].routeinfo +
	        '</td><td style="text-align: right">' +
	        convertToCurrentUnits(response[i].mileage).toFixed(2) +
		"</td></tr>";
        }
    }
    tbody.innerHTML = rows;
}

function parseLongestClinchedConnectedData(data) {

    let response = $.parseJSON(data);
    // we have an array in response, each element has fields
    // root (for link), routeinfo (human-readable), and mileage
    // build the table of longest clinched from that
    let tbody = document.getElementById("longestClinchedRoutes");
    let rows = "";
    if (response.length == 0) {
       rows = '<tr><td colspan="2" style="text-align:center">No Routes Clinched</td></tr>';
    }
    else {
        for (let i = 0; i < response.length; i++) {
	    let link = "/user/mapview.php?rte=" + response[i].routeonly;
            rows += '<tr onclick="window.open(\'' + link + '\')"><td>' +
	        response[i].routeinfo +
	        '</td><td style="text-align: right">' +
	        convertToCurrentUnits(response[i].mileage).toFixed(2) +
		"</td></tr>";
        }
    }
    tbody.innerHTML = rows;
}

function parseLongestTraveledData(data) {

    let response = $.parseJSON(data);
    // we have an array in response, each element has fields
    // root (for link), routeinfo (human-readable), traveled (in miles)
    // and mileage (of route)
    // build the table of longest traveled from that
    let tbody = document.getElementById("longestTraveledRoutes");
    let rows = "";
    if (response.length == 0) {
       rows = '<tr><td colspan="3" style="text-align:center">No Routes Traveled</td></tr>';
    }
    else {
        for (let i = 0; i < response.length; i++) {
	    let link = "/hb/?r=" + response[i].root;
            rows += '<tr onclick="window.open(\'' + link + '\')"><td>' +
	        response[i].routeinfo +
	        '</td><td style="text-align: right">' +
	        convertToCurrentUnits(response[i].traveled).toFixed(2) +
		'</td><td style="text-align: right">' +
	        convertToCurrentUnits(response[i].mileage).toFixed(2) +
		"</td></tr>";
        }
    }
    tbody.innerHTML = rows;
}

function parseLongestTraveledConnectedData(data) {

    let response = $.parseJSON(data);
    // we have an array in response, each element has fields
    // root (for link), routeinfo (human-readable), and mileage
    // build the table of longest traveled from that
    let tbody = document.getElementById("longestTraveledRoutes");
    let rows = "";
    if (response.length == 0) {
       rows = '<tr><td colspan="3" style="text-align:center">No Routes Traveled</td></tr>';
    }
    else {
        for (let i = 0; i < response.length; i++) {
	    let link = "/user/mapview.php?rte=" + response[i].routeonly;
            rows += '<tr onclick="window.open(\'' + link + '\')"><td>' +
	        response[i].routeinfo +
	        '</td><td style="text-align: right">' +
	        convertToCurrentUnits(response[i].traveled).toFixed(2) +
	        '</td><td style="text-align: right">' +
	        convertToCurrentUnits(response[i].mileage).toFixed(2) +
		"</td></tr>";
        }
    }
    tbody.innerHTML = rows;
}

function parseClosestToClinchedTraveledData(data) {

    let response = $.parseJSON(data);
    // we have an array in response, each element has fields
    // root (for link), routeinfo (human-readable), missing (in miles)
    // and mileage (of route)
    // build the table of longest traveled from that
    let tbody = document.getElementById("closestToClinchedTraveledRoutes");
    let rows = "";
    if (response.length == 0) {
       rows = '<tr><td colspan="3" style="text-align:center">No Unclinched Routes Traveled</td></tr>';
    }
    else {
        for (let i = 0; i < response.length; i++) {
	    let link = "/hb/?r=" + response[i].root;
            rows += '<tr onclick="window.open(\'' + link + '\')"><td>' +
	        response[i].routeinfo +
	        '</td><td style="text-align: right">' +
	        convertToCurrentUnits(response[i].missing).toFixed(2) +
		'</td><td style="text-align: right">' +
	        convertToCurrentUnits(response[i].mileage).toFixed(2) +
		"</td></tr>";
        }
    }
    tbody.innerHTML = rows;
}

function parseClosestToClinchedConnectedTraveledData(data) {

    let response = $.parseJSON(data);
    // we have an array in response, each element has fields
    // root (for link), routeinfo (human-readable), missing (in miles)
    // and mileage (of route)
    // build the table of longest traveled from that
    let tbody = document.getElementById("closestToClinchedTraveledRoutes");
    let rows = "";
    if (response.length == 0) {
       rows = '<tr><td colspan="3" style="text-align:center">No Unclinched Routes Traveled</td></tr>';
    }
    else {
        for (let i = 0; i < response.length; i++) {
	    let link = "/user/mapview.php?rte=" + response[i].routeonly;
            rows += '<tr onclick="window.open(\'' + link + '\')"><td>' +
	        response[i].routeinfo +
	        '</td><td style="text-align: right">' +
	        convertToCurrentUnits(response[i].missing).toFixed(2) +
		'</td><td style="text-align: right">' +
	        convertToCurrentUnits(response[i].mileage).toFixed(2) +
		"</td></tr>";
        }
    }
    tbody.innerHTML = rows;
}
</script>

<body onload="initUI(); updateStats(); ">
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<div id="controlbox">
    <table id="optionsTable" class="gratable">
    <thead>
    <tr><th>Select Options To Display Stats</th></tr>
    </thead>
    <tbody>
    <tr><td>User: 
<?php tm_user_select(); ?>
    &nbsp;Units: 
<?php tm_units_select(); ?>
    </td></tr>
    
    <tr><td>Region:
<?php tm_region_select(FALSE); ?><span id="regionlabel"> (does not apply to connected routes)</span>
    </td></tr>

    <tr><td>System:
<?php tm_system_select(FALSE); ?>
    </td></tr>

<tr><td>Max entries per table:
<input id="numentries" type="number" min="1" max="500" value="25" />
    <select name="connected" onchange="connectedChanged();" >
    <option value="connected">Connected Routes</option>
    <option value="inregion">In-Region Routes</option>
    </select>
    <select name="preview">
    <option value="no">Active Systems Only</option>
    <option value="yes">Active+Preview Systems</option>
    </select>
    </td></tr>
    
    <tr><td style="text-align: center">
    <input type="button" value="Update Stats" onclick="updateStats();" />
    </td></tr>
    </tbody>
    </table>
</div>
<center>
<span style="display: inline-block">
    <table class="gratable">
        <thead>
            <tr><th colspan="2" class="routeName">Longest Clinched Routes</th></tr>
            <tr><th class="routeName">Route</th>
                <th class="clinched">Length (<span id="unitsText"><?php tm_echo_units(); ?></span>)</th>
        </thead>
        <tbody id="longestClinchedRoutes">
        </tbody>
    </table>
</span><span style="display: inline-block">
    <table class="gratable">
        <thead>
            <tr><th colspan="3" class="routeName">Most Traveled Routes</th></tr>
            <tr><th class="routeName">Route</th>
                <th class="clinched">Traveled (<span id="unitsText"><?php tm_echo_units(); ?></span>)</th>
                <th class="clinched">Length (<span id="unitsText"><?php tm_echo_units(); ?></span>)</th>
        </thead>
        <tbody id="longestTraveledRoutes">
        </tbody>
    </table>
</span><span style="display: inline-block">
    <table class="gratable">
        <thead>
            <tr><th colspan="3" class="routeName">Unclinched Traveled Routes Closest to Clinched</th></tr>
            <tr><th class="routeName">Route</th>
                <th class="clinched">Untraveled (<span id="unitsText"><?php tm_echo_units(); ?></span>)</th>
                <th class="clinched">Length (<span id="unitsText"><?php tm_echo_units(); ?></span>)</th>
        </thead>
        <tbody id="closestToClinchedTraveledRoutes">
        </tbody>
    </table>
</span>
</center>
</body>
<?php
    $tmdb->close();
?>

</html>
