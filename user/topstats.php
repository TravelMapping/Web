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
    let params = {
	traveler: selects[0].value,
	system: selects[3].value,
        region: selects[2].value,
	numentries: document.getElementById("numentries").value
    };
    
    let jsonParams = JSON.stringify(params);
    $.ajax({
	type: "POST",
	url: "/lib/getLongestClinchedRoutes.php",
	datatype: "json",
	data: { "params" : jsonParams },
	success: parseLongestClinchedData
    });
}

function parseLongestClinchedData(data) {

    let responses = $.parseJSON(data);
    // we have 2 arrays in responses: routes and mileages
    // build the table of longest clinched from that
    let tbody = document.getElementById("longestClinchedRoutes");
    let rows = "";
    for (let i = 0; i < responses['routes'].length; i++) {
        rows += "<tr><td>" + responses['routes'][i] + "</td><td>" +
	    responses['mileages'][i] + "</td></tr>";
    }
    tbody.innerHTML = rows;
}

</script>

<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<div id="controlbox">
    <table id="optionsTable" class="gratable">
    <thead>
    <tr><th>Select Map Options</th></tr>
    </thead>
    <tbody>
    <tr><td>User: 
<?php tm_user_select(); ?>
    </td></tr>
    
    <tr><td>Units: 
<?php tm_units_select(); ?>
    </td></tr>
    
    <tr><td>Region: <br />
<?php tm_region_select(FALSE); ?>
    </td></tr>

    <tr><td>System: <br />
<?php tm_system_select(FALSE); ?>
    </td></tr>

<tr><td>Entries per table: <br />
<input id="numentries" type="number" min="1" max="500" value="25" />
    </td></tr>
    
    <tr><td>
    <input type="submit" onclick="updateStats();" />	
    </td></tr>
    </tbody>
    </table>
</div>
<div id="stats">
    <table class="gratable">
        <thead>
            <tr><th colspan="2" class="routeName">Longest Clinched Routes</th></tr>
            <tr><th class="routeName">Route</th>
                <th class="clinched">Length (<?php tm_echo_units(); ?>)</th>
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
