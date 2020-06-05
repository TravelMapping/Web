<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 ***
 * Map viewer page. Displays the routes selected using the url params on a map, as well as on a table to the side.
 * URL Params:
 *  u - user to display highlighting for on map (required)
 *  lat - initial latitude at center of map
 *  lon - initial longitude at center of map
 *  zoom - initial zoom level of map
 *  rg - region to show routes for on the map (optional)
 *  country - country to show routes for on the map (optional)
 *  sys - system to show routes for on the map (optional)
 *  v - show routes/points on the visible portion of the map (optional)
 *  rte - route name to show on the map. Supports pattern matching, with _ matching a single character, and % matching 0 or multiple characters.
 * (u, [lat lng zoom][rg|sys|country|v][rte])
 *
 ***
 -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
    <link rel="stylesheet" type="text/css" href="/css/L.Control.Window.css" />
    <link rel="shortcut icon" type="image/png" href="/favicon.png">
    <style type="text/css">
        #topControlPanel {
            background-color: white;
            position: absolute;
            top: 25px;
            left: 150px;
            z-index: 11000;
        }

        #routesTable .routeName {
            width: 149px;
        }

        #routesTable .systemName {
            min-width: 54px;
        }

        #routesTable .clinched {
            min-width: 66px;
        }

        #routesTable .overall {
            min-width: 57px;
        }

        #routesTable .percent {
            min-width: 57px;
        }

        #map {
            position: absolute;
            top: 25px;
            bottom: 0px;
            width: 100%;
            overflow: hidden;
        }

        #map * {
            cursor: crosshair;
        }

	#routes {
            position: absolute;
	    z-index: 850; /* above all Leaflet layers except controls */
            right: 10px;
            top: 90px;
	    max-height: 80%;
            overflow-y: auto;
            max-width: 40%;
	    opacity: .75;
        }
    </style>
    <?php tm_common_js(); ?>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
    <script src="../lib/L.Control.Window.js" type="text/javascript"></script>
    <title>Travel Mapping: Mapview</title>
</head>

<?php
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

<body onload="mapviewStartup(<?php echo $lat.",".$lon.",".$zoom; ?>);">
<script type="application/javascript">
let scrollableMapviewDialog = `
<p><b>Set Initial Location and Zoom Level</b></br >
Find place: <input id="placeinput" name="placeinput" type="text" size="20" maxlength="100" />&nbsp;<input id="findbutton" name="findbutton" type="button" value="Press to Search and Set Coords" onclick="nominatimLookup('placeinput');" /><br />
(Geocoding by <a target="_blank" href="https://nominatim.openstreetmap.org/">OSM Nominatim</a>)<br />
<hr />
Latitude: <input id="latvalinput" name="latvalinput" type="number" min="-90" max="90" size="15" maxlength="12" step="any" value="` + setlat + `" /><br />
Longitude: <input id="lonvalinput" name="lonvalinput" type="number" min="-180" max="180" size="15" maxlength="12" step="any" value="` + setlon + `" /><br />
Zoom level: [Far]<input id="zoomvalinput" name="zoomvalinput" type="range" min="8" max="15" value="` + setzoom + `" step="1" size="2" />[Close]<br />
<hr />
User: <?php tm_user_select(); ?>&nbsp;Units: <?php tm_units_select(); ?><br />
</p>
`;

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
        updateVisibleData();
    }
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

    loadmap();
    updateMap(lat, lon, zoom);
}

</script>
<?php $nobigheader = 1; ?>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>
<div id="map">
</div>
<div id="topControlPanel">
  <table id="topControlPanelTable">
    <tbody>
      <tr>
      <td>
          <input id="jumpButton" type="button" value="Jump" onclick="showScrollableMapviewPopup();" />
      </td>
      <td>
	  <input id="showRoutesCheckbox" type="checkbox" name="showRoutes" checked onclick="showHideRouteTable();" />&nbsp;Show Route Table<br>
	</td>
      <td>
	  <input id="updateCheckbox" type="checkbox" name="updateRoutes" checked onclick="updateCheckboxChanged();" />&nbsp;Always Update Visible Routes<br>
	</td>
      </tr>
    </tbody>
  </table>
</div>

<div id="routes">
    <table id="routesTable" class="gratable">
        <thead>
            <tr><th>No Routes Loaded</th></tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
</body>
<?php
    $tmdb->close();
    // set up for initial map load
    echo "<script type=\"application/javascript\">\n";
    if (array_key_exists('u', $_GET)) {
        $tmuser = $_GET['u'];
        echo "traveler = '$tmuser';\n";
    }
    if (array_key_exists('v', $_GET)) {
        echo "showAllInView = true;\n";
    }
    else {
        echo "showAllInView = false;\n";
    }
?>
</script>
</html>
