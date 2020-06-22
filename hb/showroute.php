<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 ***
 * Highway Browser Show Route page
 *
 * Two modes of operation, depending on whether cr parameter is provided
 *
 * r= the "chopped" route for the given TM root (e.g., ny.ny005) to display
 *
 * cr, if provided, will display the the entire "connected" route that contains
 *   the given TM root
 *
 * Other QS parameters:
 *
 *  u - user to display highlighting for on map (optional)
 *  lat - initial latitude at center of map
 *  lon - initial longitude at center of map
 *  zoom - initial zoom level of map
 *
 * If all three of lat, lon, and zoom are provided, the map will initialize
 * to those coordinates and zoom level.
 *
 * Otherwise, the map will pan and zoom to fit the entire route at the center.
 *
 *  r [cr] [u] [lat lon zoom]
 ***
 -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
    <link rel="stylesheet" type="text/css" href="/fonts/roadgeek.css" />
    <link rel="shortcut icon" type="image/png" href="/favicon.png">
    <style type="text/css">
    	#loadingMsg {
            background-color: white;
            position: absolute;
            top: 100px;
            left: 150px;
            z-index: 11000;
        }

        #headerbox {
            position: absolute;
            top: 0px;
            bottom: 50px;
            width: 100%;
            overflow: hidden;
            text-align: center;
            font-size: 20px;
            font-family: "Times New Roman", serif;
            font-style: bold;
        }

        #routebox {
            position: fixed;
            left: 0px;
            top: 110px;
            bottom: 0px;
            width: 100%;
            overflow: auto;
        }

        #pointbox {
            position: fixed;
            left: 0px;
            top: 30px;
            right: 275px;
            bottom: 0px;
            width: 275px;
            overflow: auto;
            font-size: 18px;
        }

        #controlbox {
            position: fixed;
            top: 22px;
            bottom: 52px;
            height: 100%;
            left: 300px;
            right: 0px;
            overflow: auto;
            padding: 5px;
            font-size: 18px;
        }

        #map {
            position: absolute;
            top: 60px;
            bottom: 0px;
            left: 275px;
            right: 0px;
            overflow: hidden;
        }

        #map * {
            cursor: crosshair;
        }

        #waypoints:hover {
            cursor: pointer;
        }

        #pointbox span {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 5px;
        }

        #pointbox table {
            width: 95%;
            margin-bottom: 15px;
        }

        #routeInfo td {
            text-align: right;
        }

	.status-active {
	    background-color: #CCFFCC;
            font-size: 14px;
	}

	.status-preview {
	    background-color: #FFFFCC;
            font-size: 14px;
	}
	
	.status-devel {
	    background-color: #FFCCCC;
            font-size: 14px;
	}

	#listEntryToolbox {
	    position: absolute;
	    top: 75px;
	    left: 75%;
	    z-index: 1000;
	    background-color: #f1f1f1;
	    border: 1px solid #000000;
	    text-align: center;
            font-size: 14px;
	    width: 20%;
         }

	 #lEToolboxHeader {
	     padding: 2px;
	     cursor: move;
	     z-index: 1001;
	     background-color: #101010;
	     color: #fff;
	 }
    </style>
    <?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
    <?php
    function showroute_error($msg) {

        global $tmdb;
        echo '<h1 style="color: red">'.$msg.'</h1>';
	$tmdb->close();
	echo '</body></html>';
	exit;
    }

    function check_root_error($qsparam, $rootparam, $origparam) {

        if ($rootparam == "toolong.root") {
            showroute_error("Parameter ".$qsparam."=".$origparam." is too long");
        }
        if ($rootparam == "noregion.root" ||
	    $rootparam == "badregion.root" ||
	    $rootparam == "badroute.root") {
            showroute_error("Parameter ".$qsparam."=".$origparam." is invalid.");
        }
    }
    
    // check for required parameters
    if (array_key_exists("r", $_GET)) {
        $rootparam = tm_validate_root($_GET['r']);
	check_root_error('r', $rootparam, $_GET['r']);
	$routetype = "chopped";
        $sql_command = "SELECT * FROM routes LEFT JOIN systems ON systems.systemName=routes.systemName WHERE root='". $rootparam."'";
        $res = tmdb_query($sql_command);
        $routeInfo = $res->fetch_assoc();
        $res->free();
	if ($routeInfo == NULL) {
            showroute_error("Route for r=".$rootparam." not found.");
        }
	$titleRoute = $routeInfo['region']." ".$routeInfo['route'].$routeInfo['banner'].$routeInfo['abbrev'];
    }
    else {
    	showroute_error("Query string parameter r= required.");
    }

    // build JS info about the roots to be displayed
    echo "<script type=\"application/javascript\">\n";
    echo "traveler = '".$tmuser."';\n";
    echo "var showrouteParams = new Object();\n";
    echo "showrouteParams.roots = [];\n";
    
    // is the entire connected route requested?
    $connected = array_key_exists("cr", $_GET);
    if ($connected) {
        // TODO: factor out code from here and mapview into tmphpfuncs.php
        $result = tmdb_query("select firstRoot from connectedrouteroots where root='".$rootparam."';");
        $row = $result->fetch_assoc();
	$firstRoot = "";
	if ($row == NULL) {
	    $firstRoot = $rootparam;
	}
	else {
	    $firstRoot = $row['firstRoot'];
	}
	$result->free();
        echo "showrouteParams.roots.push('".$firstRoot."');\n";
        $result2 = tmdb_query("select root from connectedRouteRoots where firstRoot='".$firstRoot."';");
	while ($row2 = $result2->fetch_assoc()) {
            echo "showrouteParams.roots.push('".$row2['root']."');\n";
        }
	$result2->free();
	$result = tmdb_query("select * from connectedroutes where firstRoot='".$firstRoot."';");
	$connInfo = $result->fetch_assoc();
	$result->free();
	$titleRoute = $connInfo['route'].$connInfo['banner'];
	if ($connInfo['groupName'] != "") {
	   $titleRoute .= ' ('.$connInfo['groupName'].')';
	}
        echo "showrouteParams.connected = true;\n";
    }
    else {
        echo "showrouteParams.roots.push('".$rootparam."');\n";
        echo "showrouteParams.connected = false;\n";
    }
    echo "</script>\n";
    
    // parse lat, lon, zoom parameters if present
    $lat = "null";
    $lon = "null";
    $zoom = "null";
    if (array_key_exists("lat", $_GET)) {
        $lat = floatval($_GET["lat"]);
    }
    if (array_key_exists("lon", $_GET)) {
        $lon = floatval($_GET["lon"]);
    }
    if (array_key_exists("zoom", $_GET)) {
        $zoom = intval($_GET["zoom"]);
    }

    ?>
    <?php tm_common_js(); ?>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
    <script src="../lib/showroutefuncs.js" type="text/javascript"></script>
    <title><?php echo $titleRoute; ?> - TM Highway Browser</title>
</head>
<?php 
$nobigheader = 1;
echo "<body onload=\"showrouteStartup(".$lat.",".$lon.",".$zoom.");\">\n";
require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php";
require $_SERVER['DOCUMENT_ROOT'] . "/shields/shieldgen.php";
echo "<div id=\"pointbox\">\n";
echo "<span class='status-".$routeInfo['level']."' style='text-align:center'><a href='/hb/?sys=".$routeInfo['systemName']."'>".$routeInfo['fullName']." (".$routeInfo['systemName'].")</a></span>\n";
if ($routeInfo['level'] == 'preview') {
    $msg = 'Preview systems are substantially complete, but are undergoing final review and revisions. These may still undergo significant changes without notification in the updates log. Users can include these systems in list files for mapping and stats, but should expect to revise as the system progresses toward activation.  Users plotting travels in preview systems may wish to follow the forum discussions to see the progress and find out about revisions being made.';
    echo "<span class='status-preview' style='text-align:center' title='".$msg."'>Warning: ".$routeInfo['systemName']." is a preview system. <span onclick='alert(\"".$msg."\");'>(?)</span></span>\n";
}
else if ($routeInfo['level'] == 'devel') {
    $msg = 'Devel systems are a work in progress. Routes in these systems are not yet available for mapping and inclusion in user stats, and are shown in the Highway Browser primarily for the benefit of the highway data managers who are developing the system. Once the system is substantially complete it will be upgraded to preview status, at which time users can begin to plot their travels in the system.';
    echo "<span class='status-devel' style='text-align:center' title='".$msg."'>Warning: ".$routeInfo['systemName']." is an in-development system. <span onclick='alert(\"".$msg."\");'>(?)</span></span>\n";
}

// shield and other info, depending on whether we are showing
// connected or chopped route
// always have the shield based on the root
echo "<span class='bigshield'>".generate($rootparam, true)."</span>\n";
if ($connected) {
    echo "<span style=\"text-align: center;\">" . $connInfo['banner'];
    if (strlen($connInfo['groupName']) > 0) {
        echo " (" . $connInfo['groupName'] . ")";
    }
    echo "</span>\n";
}
else {
    echo "<span>" . $routeInfo['banner'];
    if (strlen($routeInfo['city']) > 0) {
        echo " (" . $routeInfo['city'] . ")";
    }
    echo "</span>\n";
    echo "<span>.list name: <span style='font-family:courier'>" . $routeInfo['region'] . " " . $routeInfo['route'] . $routeInfo['banner'] . $routeInfo['abbrev'] . "</span></span>\n";
    echo '<span style="font-size: smaller;"><a href="/hb/showroute.php?r='.$rootparam.'&cr">View Connected Route</a></span>'."\n";
}
?>

<table id='routeInfo' class="gratable">
<thead><tr><th colspan='2'>Route Stats</th></tr></thead>
<tbody id='routeInfoTBody'>
</tbody></table>

<table id='waypoints' class="gratable">
<thead>
<tr><th colspan="2">Waypoints</th></tr>
<tr><th>Name</th><th title='Percent of people who have driven this route who have driven the segment starting at this point.'>%</th></tr>
</thead><tbody id='waypointsTBody'>
</tbody></table>
</div>

<div id="controlbox">
<span id="controlboxroute">
<table><tbody><tr style="font-size: smaller;">
<td>
<input id="showMarkers" type="checkbox" name="Show Markers" onclick="hideShowMarkers()" checked />&nbsp;Show Markers&nbsp;
</td><td>
<input id="showToolbox" type="checkbox" name="Show Toolbox" onclick="hideShowToolbox()" />&nbsp;Show Toolbox&nbsp;
</td><td>
<form id="userForm" action="/hb/showroute.php">
User: 
<?php tm_user_select(); ?>
<label>Units: </label>
<?php tm_units_select(); ?>
</td><td>
<input type="hidden" name="r" value="<?php echo $rootparam ?>" />
<?php if ($connected) echo '<input type="hidden" name="cr" value="true" />' ?>
<input type="submit" value="Apply" />
</td><td>
<input type="button" onclick="map.fitBounds(new L.featureGroup(markers).getBounds());" value="Zoom to Fit" />
</td><td>
<?php tm_position_checkbox(); ?>
</td></tr></tbody></table>
</span>
</div>
<div id="map">
</div>
<div id="loadingMsg" style="display: none">
<table class="gratable">
<tr><td id="loadingMsgText" style="font-size: 500%;">Loading Data...</td></tr>
</table>
</div>
<div id="listEntryToolbox" style="display: none">
<div id="lEToolboxHeader">
.list Entry Toolbox
</div>
<input type="button" onclick="connections.forEach(c => {c.selected = false;}); updateLEToolboxSelection();" value="Clear Selection">
<input type="button" onclick="connections.forEach(c => {c.selected = true;}); updateLEToolboxSelection();" value="Select All Segments">
<input type="button" onclick="connections.forEach(c => {c.selected = c.clinched;}); updateLEToolboxSelection();" value="Select Clinched Segments">
<input type="button" class="let" data-clipboard-target="#lEToolboxLines" value="Copy to Clipboard">
<hr />
<textarea name="lEToolboxLines" id="lEToolboxLines" readonly="readonly" rows="4" cols="50">Press "Select Clinched Segments" or click on a segment on
the map to begin.  .list file entries for selected segments
will appear here.  Hold shift to select/unselect all segments
to previous click.
</textarea>
</div>
<?php $tmdb->close(); ?>
</body>
</html>
