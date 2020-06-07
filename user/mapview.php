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
    	#loadingMsg {
            background-color: white;
            position: absolute;
            top: 100px;
            left: 150px;
            z-index: 11000;
        }

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
    <script src="../lib/mapviewfuncs.js" type="text/javascript"></script>
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
Find place: <input id="placeinput" name="placeinput" type="text" size="20" maxlength="100" onkeypress="nominatimLookupIfEnter(event);"/>&nbsp;<input id="findbutton" name="findbutton" type="button" value="Press to Search and Set Coords" onclick="nominatimLookup('placeinput');" /><br />
(Geocoding by <a target="_blank" href="https://nominatim.openstreetmap.org/">OSM Nominatim</a>)<br />
<hr />
Latitude: <input id="latvalinput" name="latvalinput" type="number" min="-90" max="90" size="15" maxlength="12" step="any" value="` + setlat + `" /><br />
Longitude: <input id="lonvalinput" name="lonvalinput" type="number" min="-180" max="180" size="15" maxlength="12" step="any" value="` + setlon + `" /><br />
Zoom level: [Far]<input id="zoomvalinput" name="zoomvalinput" type="range" min="8" max="15" value="` + setzoom + `" step="1" size="2" />[Close]<br />
<hr />
User: <?php tm_user_select(); ?>&nbsp;Units: <?php tm_units_select(); ?><br />
</p>
`;
</script>
<?php $nobigheader = 1; ?>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>
<div id="map">
</div>
<div id="loadingMsg" style="display: none";>
<table class="gratable">
<tr><td style="font-size: 500%;">Loading Data...</td></tr>
</table>
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
	<td>
    <select id="coloring" name="coloring" onchange="updateConnectionColors();" >
    <option value="system">Highway System Colors</option>
    <option value="travelers">Color by Traveler Count</option>
    <option value="concurrent">Color by Concurrencies</option>
    <option value="plain">Plain</option>
    </select>
    </td>
    <td>
    <select id="highlighting" name="highlighting" onchange="updateConnectionColors();" >
    <option value="traveled">Highlight Traveled</option>
    <option value="untraveled">Highlight Untraveled</option>
    <option value="all">Highlight All</option>
    <option value="none">Highlight None</option>
    </select>
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

    // check for user
    if (array_key_exists('u', $_GET)) {
        $tmuser = $_GET['u'];
        echo "traveler = '$tmuser';\n";
    }

    // check for scrollable mapview
    if (array_key_exists('v', $_GET)) {
        echo "showAllInView = true;\n";
    }
    else {
        echo "showAllInView = false;\n";
    }
    // check for other QS parameters, put them into this object

    // route patterns, QS param 'rte'
    echo "var mapviewParams = new Object();\n";
    if (array_key_exists('rte', $_GET)) {
        echo "mapviewParams.routePattern = '".$_GET['rte']."';\n";
    }
    else {
        echo "mapviewParams.routePattern = '';\n";
    }

    // regions, QS param 'rg'
    echo "mapviewParams.regions = [];\n";
    if (array_key_exists('rg', $_GET)) {
        $array = array();
        if (is_array($_GET['rg'])) {
            foreach ($GET['rg'] as $r) {
	        $array = array_merge($array, explode(',', $r));
            }
        }
	else {
	    $array = explode(",", $_GET['rg']);
	}
	$array = array_diff($array, array("null"));
	foreach ($array as $r) {
	    echo "mapviewParams.regions.push('".$r."');\n";
	}
    }

    // systems, QS param 'sys'
    echo "mapviewParams.systems = [];\n";
    if (array_key_exists('sys', $_GET)) {
        $array = array();
        if (is_array($_GET['sys'])) {
            foreach ($GET['sys'] as $s) {
	        $array = array_merge($array, explode(',', $s));
            }
        }
	else {
	    $array = explode(",", $_GET['sys']);
	}
	$array = array_diff($array, array("null"));
	foreach ($array as $s) {
	    echo "mapviewParams.systems.push('".$s."');\n";
	}
    }

    // country, QS param 'country'
    if (array_key_exists('country', $_GET)) {
        echo "mapviewParams.country = '".$_GET['country']."';\n";
    }
    else {
        echo "mapviewParams.country = '';\n";
    }
?>
</script>
</html>
