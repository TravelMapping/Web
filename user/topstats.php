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
function updateStats() {

    let controls = document.getElementById("controlbox");
    let selects = controls.getElementsByTagName("select");
    distanceUnits = selects[1].value;
    document.getElementById("unitsText").innerHTML = distanceUnits;
    let params = {
	traveler: selects[0].value,
	system: selects[3].value,
        region: selects[2].value,
	numentries: document.getElementById("numentries").value
    };
    
    let jsonParams = JSON.stringify(params);
    // regular clinched routes (in a single region)
    $.ajax({
	type: "POST",
	url: "/lib/getLongestClinchedRoutes.php",
	datatype: "json",
	data: { "params" : jsonParams },
	success: parseLongestClinchedData
    });
    // connected clinched routes
    $.ajax({
	type: "POST",
	url: "/lib/getLongestClinchedConnectedRoutes.php",
	datatype: "json",
	data: { "params" : jsonParams },
	success: parseLongestClinchedConnectedData
    });
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
    let tbody = document.getElementById("longestClinchedConnectedRoutes");
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

</script>

<body>
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
<?php tm_region_select(FALSE); ?> (does not apply to connected routes)
    </td></tr>

    <tr><td>System:
<?php tm_system_select(FALSE); ?>
    </td></tr>

<tr><td>Max entries per table:
<input id="numentries" type="number" min="1" max="500" value="25" />
    </td></tr>
    
    <tr><td style="text-align: center">
    <input type="submit" onclick="updateStats();" />	
    </td></tr>
    </tbody>
    </table>
</div>
<div id="stats">
    <table class="gratable">
        <thead>
            <tr><th colspan="2" class="routeName">Longest Clinched Connected Routes</th></tr>
            <tr><th class="routeName">Route</th>
                <th class="clinched">Length (<span id="unitsText"><?php tm_echo_units(); ?></span>)</th>
        </thead>
        <tbody id="longestClinchedConnectedRoutes">
        </tbody>
    </table>
    <table class="gratable">
        <thead>
            <tr><th colspan="2" class="routeName">Longest Clinched Routes</th></tr>
            <tr><th class="routeName">Route</th>
                <th class="clinched">Length (<span id="unitsText"><?php tm_echo_units(); ?></span>)</th>
        </thead>
        <tbody id="longestClinchedRoutes">
        </tbody>
    </table>
</div>
</body>
<?php
    $tmdb->close();
?>

</html>
