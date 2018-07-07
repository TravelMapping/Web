<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 ***
 * Map viewer page. Displays the routes selected using the url params on a map, as well as on a table to the side.
 * URL Params:
 *  u - user to display highlighting for on map (required)
 *  rg - region to show routes for on the map (optional)
 *  country - country to show routes for on the map (optional)
 *  sys - system to show routes for on the map (optional)
 *  rte - route name to show on the map. Supports pattern matching, with _ matching a single character, and % matching 0 or multiple characters.
 * (u, [rg|sys|country][rte])
 ***
 -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
    <link rel="shortcut icon" type="image/png" href="/favicon.png">
    <style type="text/css">
        #controlbox {
            position: absolute;
            top: 60px;
            height: 30px;
            right: 50px;
            overflow: auto;
            padding: 5px;
            font-size: 20px;
	    width: 25%;
	    z-index: 850; /* above all Leaflet layers except controls */
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

        #routesTable tr.float th {
            position: relative;!important;
            top: -1px;!important;
            left: -1px;!important;
            border-left-width: 1px;
            border-right-width: 1px;
        }

        #routesTable tr.float th:first-child {
            border-left-width: 2px;
        }

        #routesTable tr.float th:last-child {
            border-right-width: 2px;
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

	#selected {
            position: absolute;
	    z-index: 850; /* above all Leaflet layers except controls */
            right: 10px;
            top: 90px;
            bottom: 20px;
            overflow-y: auto;
            max-width: 40%;
	    opacity: .75;
        }

        #routes {
	    display: none;
	    left: 0px;
            width: 1px;
	    height: 1px;
	    position: absolute;
        }

        #options {
	    position: absolute;
	    display: none;
	    left: 0px;
            width: 1px;
	    height: 1px;
        }

        #showHideMenu {
            position: absolute;
            right: 10px;
	    opacity: .75;  /* also forces stacking order */
        }
    </style>
    <?php tm_common_js(); ?>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
    <title>Travel Mapping: Draft Map Overlay Viewer</title>
</head>

<body onload="loadmap(); waypointsFromSQL(); updateMap(); toggleTable();">
<script type="application/javascript">

    function toggleTable() {
        var menu = document.getElementById("showHideMenu");
        var index = menu.selectedIndex;
        var value = menu.options[index].value;
        routes = document.getElementById("routes");
        options = document.getElementById("options");
        selected = document.getElementById("selected");
        // show only table (or no table) based on value
        if (value == "routetable") {
            selected.innerHTML = routes.innerHTML;
        }
        else if (value == "options") {
            selected.innerHTML = options.innerHTML;
        }
        else {
            selected.innerHTML = "";
        }
    }

    function initFloatingHeaders($table) {
        var $col = $table.find('tr.float');
        var $th = $col.find('th');
        var tag = "<tr style='height: 22px'></tr>";
        $(tag).insertAfter($col);
        $th.each(function (index) {
            var $row = $table.find('tr td:nth-child(' + (index + 1) + ')');
            if ($row.outerWidth() > $(this).width()) {
                $(this).width($row.width());
            } else {
                $row.width($(this).width());
            }
        });
    }

    $(document).ready(function () {
            $routesTable = $('#routesTable');
            $routesTable.tablesorter({
                sortList: [[1, 0]]
            });
            $('td').filter(function() {
                return this.innerHTML.match(/^[0-9\s\.,%]+$/);
            }).css('text-align','right');
            initFloatingHeaders($routesTable);
        }
    );
</script>
<?php $nobigheader = 1; ?>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>
<div id="map">
</div>

<div id="selected"></div>
<div id="options">
    <form id="optionsForm" action="mapview.php">
    <table id="optionsTable" class="gratable">
    <thead>
    <tr><th>Select Map Options</th></tr>
    </thead>
    <tbody>
    <tr><td>
    <input id="showMarkers" type="checkbox" name="Show Markers" onclick="showMarkersClicked()" />&nbsp;Show Markers
    </td></tr>

    <tr><td>User: 
<?php tm_user_select(); ?>
    </td></tr>
    
    <tr><td>Region(s): <br />
<?php tm_region_select(TRUE); ?>
    </td></tr>
    
    <tr><td>System(s): <br />
<?php tm_system_select(TRUE); ?>
    </td></tr>

    <tr><td>
    <input type="submit" value="Apply Changes" />	
    </td></tr>
    </tbody>
    </table>
    </form>
</div>
<div id="routes">
    <table id="routesTable" class="gratable tablesorter">
        <thead>
            <tr class="float" ><th class="sortable routeName">Route</th><th class="sortable systemName">System</th>
                <th class="sortable clinched">Clinched (<?php tm_echo_units(); ?>)</th><th class="sortable overall">Overall (<?php tm_echo_units(); ?>)</th><th class="sortable percent">%</th></tr>
        </thead>
        <tbody>
        <!-- TEMP FIX: 1 dummy table line to account for the fact that the
	styling places the table header row above on top of the first
	row of data in the table -->
        <tr><td class='routeName'>&nbsp;</td>
            <td class='link systemName'>&nbsp;</td>
            <td class="clinched">&nbsp;</td><td class='overall'>&nbsp;</td><td class='percent'>&nbsp;</td>
	</tr>
        <?php
	$add_regions = "";
	// TODO: a toggle to include/exclude devel routes?
        $sql_command = <<<SQL
SELECT r.region, r.root, r.route, r.systemName, r.banner, r.city, sys.tier, 
  round(r.mileage, 2) AS total, 
  round(COALESCE(cr.mileage, 0), 2) as clinched 
FROM routes AS r 
  LEFT JOIN clinchedRoutes AS cr ON r.root = cr.route AND traveler = '{$_GET['u']}' 
  LEFT JOIN systems as sys on r.systemName = sys.systemName
  LEFT JOIN connectedRouteRoots AS crr ON r.root = crr.root
  LEFT JOIN connectedRoutes as conr on crr.firstRoot = conr.firstRoot OR conr.firstRoot = r.root
WHERE  
SQL;
        if (array_key_exists('rte', $_GET)) {
            $sql_command .= "(r.route like '".$_GET['rte']."' or r.route regexp '".$_GET['rte']."[a-z]')";
            $sql_command = str_replace("*", "%", $sql_command);
            if (array_key_exists('rg', $_GET) or array_key_exists('sys', $_GET)) $sql_command .= ' AND ';
        }
        if (array_key_exists('rg', $_GET) && array_key_exists('sys', $_GET)) {
            $sql_command .= orClauseBuilder('rg', 'region')." AND ".orClauseBuilder('sys', 'systemName');
        } elseif (array_key_exists('rg', $_GET)) {
            $sql_command .= orClauseBuilder('rg', 'region');
        } elseif (array_key_exists('sys', $_GET)) {
            $sql_command .= orClauseBuilder('sys', 'systemName');
	} elseif (array_key_exists('country', $_GET)) {
	    $sql_command2 = "SELECT code FROM regions WHERE country='".$_GET['country']."';";
	    $res2 = tmdb_query($sql_command2);
	    $add_regions = "&rg=";
            $sql_command .= "(";
	    while ($row = $res2->fetch_assoc()) {
	    	  $add_regions .= $row['code'].",";
		  $sql_command .= "r.region = '".$row['code']."' OR ";
	    }
	    $add_regions .= "null";
	    $sql_command .= "r.region = 'null')";
        } elseif (!array_key_exists('rte', $_GET)) {
            //Don't show. Too many routes
            $sql_command .= "r.root IS NULL";
        }
        $sql_command .= "ORDER BY sys.tier, conr.csvOrder, r.rootOrder;";
        $res = tmdb_query($sql_command);
        while($row = $res->fetch_assoc()) {
            $link = "/hb?u=".$_GET['u']."&amp;r=".$row['root'];
            echo "<tr onclick=\"window.open('".$link."')\"><td class='routeName'>";
            //REGION ROUTE BANNER (CITY)
            echo $row['region'] . " " . $row['route'];
            if (strlen($row['banner']) > 0) {
                echo " " . $row['banner'];
            }
            if (strlen($row['city']) > 0) {
                echo " (" . $row['city'] . ")";
            }
	    $pct = sprintf("%0.2f",( $row['clinched'] / $row['total'] * 100) );
            echo <<<HTML
                </td>
                <td class='link systemName'>{$row['tier']}. <a href='/user/system.php?u={$_GET['u']}&amp;sys={$row['systemName']}'>{$row['systemName']}</a></td>
                <td class="clinched">
HTML
.tm_convert_distance($row['clinched'])."</td><td class='overall'>".tm_convert_distance($row['total'])."</td><td class='percent'>".$pct."%</td></tr>\n";
        }
        ?>
        </tbody>
    </table>
</div>
<div id="controlbox">
    <select id="showHideMenu" onchange="toggleTable();">
    <option value="maponly">Map Only</option>
    <option value="options">Show Map Options</option>
    <option value="routetable" selected="selected">Show Route Table</option>
    </select>
</div>
</body>
<?php
    $tmdb->close();
?>

<script type="application/javascript" src="../lib/waypoints.js.php?<?php echo $_SERVER['QUERY_STRING'].$add_regions?>"></script>
</html>
