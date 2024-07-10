<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 ***
 * Map viewer page. Displays the routes selected using the url params on a map, as well as on a table to the side.
 * URL Params:
 *  u - user to display highlighting for on map (required)
 *  units - units to display distances (optional)
 *  lat - initial latitude at center of map (optional)
 *  lon - initial longitude at center of map (optional)
 *  zoom - initial zoom level of map (optional)
 *  rg - region to show routes for on the map (optional)
 *  country - country to show routes for on the map (optional)
 *  sys - system to show routes for on the map (optional)
 *  v - show routes/points on the visible portion of the map (optional)
 *  rte - route name to show on the map, all routes with the same "route" will appear (optional)
 *  cr - connected route to show, provide the root of any in-region route as parameter (optional)
 *  colors - custom color string(s) (optional)
 * (u, [units][lat lon zoom][rg|sys|country|rte|cr][v])
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
            left: 75px;
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

    // from Leaflet https://leafletjs.com/examples/choropleth/
    .mapviewLegend {
      padding: 6px 8px;
      font: 14px/16px Arial, Helvetica, sans-serif;
      background: rgba(255,255,255,0.8);
      box-shadow: 0 0 15px rgba(0,0,0,0.2);
      border-radius: 5px;
      line-height: 18px;
    }
    .mapviewLegend i {
      width: 18px;
      height: 18px;
      float: left;
      margin-left: 8px;
      opacity: 0.7;
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
<?php tm_generate_custom_colors_array(); ?>
let scrollableMapviewDialog = `
<p><b>Set Initial Location and Zoom Level</b></br >
Find place: <input id="placeinput" name="placeinput" type="text" size="20" maxlength="100" onkeypress="nominatimLookupIfEnter(event);"/>&nbsp;<input id="findbutton" name="findbutton" type="button" value="Press to Search and Set Coords" onclick="nominatimLookup('placeinput');" /><br />
<span style="font-size: smaller">(Geocoding by <a target="_blank" href="https://nominatim.openstreetmap.org/">OSM Nominatim</a>)</span><br />
or <input id="currentbutton" name="currentbutton" type="button" value="Press to Query Current Location and Set Coords" onclick="getCurrentLocationMapview();" /><br />
<hr />
Coords: (<input id="latvalinput" name="latvalinput" type="number" min="-90" max="90" size="15" maxlength="12" step="any" value="` + setlat + `" />,
<input id="lonvalinput" name="lonvalinput" type="number" min="-180" max="180" size="15" maxlength="12" step="any" value="` + setlon + `" />)<br />
Zoom level: [Far]<input id="zoomvalinput" name="zoomvalinput" type="range" min="8" max="15" value="` + setzoom + `" step="1" size="2" />[Close]<br />
<hr />
<b>OR Select by Region and/or System</b></br >
<table border="0"><tr><td>Region(s)</td><td>System(s)</td></tr>
<tr><td><?php tm_region_select(TRUE); ?></td><td><?php tm_system_select(TRUE); ?></td></tr></table>
<span style="font-size: smaller">Note that if regions and/or systems are selected, the coords and zoom above will be ignored.</span><br />
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
  <p id="updatingrow" style="display: none" class="errorbar">
  Travel Mapping database update in progress.  Some functionality might
  not work.  Please try again in a few minutes if you notice problems.
<?php tm_dismiss_button("updatingrow"); ?>
  </p>
  <table id="topControlPanelTable">
    <tbody>
      <tr>
      <td>
          <input id="jumpButton" type="button" value="Jump" onclick="showScrollableMapviewPopup();" />
      </td>
      <td>
	  <input id="showRoutesCheckbox" type="checkbox" name="showRoutes" checked onclick="showHideRouteTable();" />&nbsp;Route Table<br>
	</td>
      <td>
	  <input id="updateCheckbox" type="checkbox" name="updateRoutes" checked onclick="updateCheckboxChanged();" />&nbsp;Always Update Visible Routes<br>
	</td>
	<td>
    <select id="coloring" name="coloring" onchange="updateConnectionColors();" >
    <option value="system">System Colors</option>
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
      <td>
	  <input id="legendCheckbox" type="checkbox" name="legendCheckbox" checked onclick="legendCheckboxChanged();" />&nbsp;Legend<br>
      </td>
      <td>
        <?php tm_position_checkbox(); ?><br>
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

    // all in connected route given any root, QS param 'cr'
    echo "mapviewParams.roots = [];\n";
    if (array_key_exists('cr', $_GET)) {
        $result = tmdb_query("select firstRoot from connectedRouteRoots where root='".$_GET['cr']."';");
        $row = $result->fetch_assoc();
	$firstRoot = "";
	if ($row == NULL) {
	    $firstRoot = $_GET['cr'];
	}
	else {
	    $firstRoot = $row['firstRoot'];
	}
	$result->free();
        echo "mapviewParams.roots.push('".$firstRoot."');\n";
        $result2 = tmdb_query("select root from connectedRouteRoots where firstRoot='".$firstRoot."';");
	while ($row2 = $result2->fetch_assoc()) {
            echo "mapviewParams.roots.push('".$row2['root']."');\n";
        }
	$result2->free();
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

    // country, QS param 'country'
    if (array_key_exists('country', $_GET)) {
        $result = tmdb_query("select regions.code from regions where country='".$_GET['country']."';");
	while ($row = $result->fetch_assoc()) {
	    echo "mapviewParams.regions.push('".$row['code']."');\n";
        }
	$result->free();
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
    //if (array_key_exists('country', $_GET)) {
    //    echo "mapviewParams.country = '".$_GET['country']."';\n";
   // }
   // else {
    //    echo "mapviewParams.country = '';\n";
    //}
    $tmdb->close();
?>
</script>
</html>
