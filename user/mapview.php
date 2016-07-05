<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 ***
 * Map viewer page. Displays the routes selected using the url params on a map, as well as on a table to the side.
 * URL Params:
 *  u - user to display highlighting for on map (required)
 *  rg - region to show routes for on the map (optional)
 *  sys - system to show routes for on the map (optional)
 *  rte - route name to show on the map. Supports pattern matching, with _ matching a single character, and % matching 0 or multiple characters.
 * (u, [rg|sys][rte])
 ***
 -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
    <style type="text/css">
        #controlbox {
            position: absolute;
            top: 30px;
            height: 30px;
            right: 20px;
            overflow: auto;
            padding: 5px;
            font-size: 20px;
	    width: 25%;
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
            right: 10px;
            top: 60px;
            bottom: 20px;
            overflow-y: auto;
            max-width: 320px;
	    opacity: .95;  /* also forces stacking order */
        }

        #routes {
	    display: none;
	    left: 0px;
            width: 1px;
	    height: 1px;
        }

        #options {
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
    <script
        src="http://maps.googleapis.com/maps/api/js?key=<?php echo $gmaps_api_key ?>&sensor=false"
        type="text/javascript"></script>

    <!-- jQuery -->
    <script type="application/javascript" src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <!-- TableSorter -->
    <script type="application/javascript" src="/lib/jquery.tablesorter.min.js"></script>

    <?php

    function orClauseBuilder($param, $name, $tablename = 'r') {
        $array = array();
        if (is_array($_GET[$param])) {
          foreach ($_GET[$param] as $p) {
            $array = array_merge($array, explode(',',$p));
          }
        }
        else {
          $array = explode(",", $_GET[$param]);
        }
        $array = array_diff($array, array("null"));
        $clause = "(";
        $i = 0;
        foreach($array as $item) {
            $clause.="{$tablename}.{$name} = '{$item}'";
            $i++;
            if($i < sizeof($array)) $clause .= " or ";
        }
        $clause .= ")";
        if ($i == 0) {
            return "TRUE";
        }
        return $clause;
    }

    ?>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
    
    <script type="application/javascript">
        function waypointsFromSQL() {
            <?php
	      // later get this from a QS parameter probably
	      // idea: option to include devel routes as a debugging aid
	      $activeClause = "(systems.level='preview' OR systems.level='active')";
              $regions = tm_qs_multi_or_comma_to_array("rg");

              // restrict to waypoints matching routes whose region is given in the "rg=" query string parameter
              // build strings needed for later queries
              $select_regions = "";
              $where_regions = "";
              $num_regions = 0;
              foreach ($regions as $region) {
                if ($num_regions == 0) {
                  $select_regions = " and (routes.region='".$region."'";
                  $where_regions = " where (region='".$region."'";
                }
                else {
                  $select_regions = $select_regions." or routes.region='".$region."'";
                  $where_regions = $where_regions." or region='".$region."'";
                }
                $num_regions = $num_regions + 1;
              }
              if ($num_regions > 0) {
                $select_regions = $select_regions.")";
                $where_regions = $where_regions.")";
              }

              // select based on system?
              $systems = tm_qs_multi_or_comma_to_array("sys");

              // restrict to waypoints matching routes whose system is given in the "sys=" query string parameter
              $select_systems = "";
              $where_systems = "";
              $num_systems = 0;
              foreach ($systems as $system) {
                if ($num_systems == 0) {
                  $select_systems = " and (routes.systemName='".$system."'";
                  $where_systems = " where (routes.systemName='".$system."'";
                }
                else {
                  $select_systems = $select_systems." or routes.systemName='".$system."'";
                  $where_systems = $where_systems." or routes.systemName='".$system."'";
                }
                $num_systems = $num_systems + 1;
              }
              if ($num_systems > 0) {
                $select_systems = $select_systems.")";
                $where_systems = $where_systems.")";
              }

            if (array_key_exists("rte", $_GET)) {
              $rteClause = " where (routes.route like '".$_GET['rte']."' or route regexp '".$_GET['rte']."[a-z]')";
              $rteClause = str_replace("*", "%", $rteClause);
              if (array_key_exists('rg', $_GET)) $rteClause .= " AND ".orClauseBuilder('rg', 'region','routes');
              if (array_key_exists('sys', $_GET)) $rteClause .= " AND ".orClauseBuilder('sys', 'systemName','routes');
              $sql_command = "SELECT waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, systems.tier, systems.color, systems.systemname FROM waypoints JOIN routes ON routes.root = waypoints.root JOIN systems ON routes.systemname = systems.systemname AND ".$activeClause."  ".$rteClause." ORDER BY root, waypoints.pointId;";
            } elseif (($num_systems == 0) && ($num_regions == 0)) {
                 // for now, put in a default to usai, do something better later
                 $select_systems = " and (routes.systemName='usai')";
                 $where_systems = " where (routes.systemName='usai')";
                 $sql_command = "SELECT waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, systems.tier, systems.color, systems.systemname FROM waypoints JOIN routes ON routes.root = waypoints.root".$select_regions.$select_systems." JOIN systems ON routes.systemname = systems.systemname AND ".$activeClause."  ORDER BY root, waypoints.pointId;";
              } else {
                $sql_command = "SELECT waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, systems.tier, systems.color, systems.systemname FROM waypoints JOIN routes ON routes.root = waypoints.root".$select_regions.$select_systems." JOIN systems ON routes.systemname = systems.systemname AND ".$activeClause."  ORDER BY root, waypoints.pointId;";
              }

              $res = tmdb_query($sql_command);

              $routenum = 0;
              $pointnum = 0;
              $lastRoute = "";
              while ($row = $res->fetch_assoc()) {
                if (!($row['root'] == $lastRoute)) {
                   echo "newRouteIndices[".$routenum."] = ".$pointnum.";\n";
                   echo "routeTier[".$routenum."] = ".$row['tier'].";\n";
                   echo "routeColor[".$routenum."] = '".$row['color']."';\n";
                   echo "routeSystem[".$routenum."] = '".$row['systemname']."';\n";
                   $lastRoute = $row['root'];
                   $routenum = $routenum + 1;
                }
                echo "waypoints[".$pointnum."] = new Waypoint(\"".$row['pointName']."\",".$row['latitude'].",".$row['longitude']."); // Route = ".$row['root']." (".$row['color'].")\n";
                $pointnum = $pointnum + 1;
              }

              // check for query string parameter for traveler clinched mapping of route
              if (array_key_exists("u",$_GET)) {
                 echo "// select_systems: ".$select_systems."\n";
                 echo "// where_systems: ".$where_systems."\n";
                 echo "traveler = '".$_GET['u']."';\n";
                 // retrieve list of segments for this region or regions
                 if(isset($rteClause)) {
                  $sql_command = "SELECT segments.segmentId, segments.root FROM segments JOIN routes ON routes.root = segments.root JOIN systems ON routes.systemname = systems.systemname AND ".$activeClause." ".$rteClause." ORDER BY root, segments.segmentId;";
                 } else {
                  $sql_command = "SELECT segments.segmentId, segments.root FROM segments JOIN routes ON routes.root = segments.root JOIN systems ON routes.systemname = systems.systemname AND ".$activeClause." ".$where_regions.$select_systems." ORDER BY root, segments.segmentId;";
                 }
                 $res = tmdb_query($sql_command);
                 $segmentIndex = 0;
                 while ($row = $res->fetch_assoc()) {
                   echo "segments[".$segmentIndex."] = ".$row['segmentId']."; // route=".$row['root']."\n";
                   $segmentIndex = $segmentIndex + 1;
                 }
                 if(isset($rteClause)) {
                  $sql_command = "SELECT segments.segmentId, segments.root FROM segments RIGHT JOIN clinched ON segments.segmentId = clinched.segmentId JOIN routes ON routes.root = segments.root JOIN systems ON routes.systemname = systems.systemname AND ".$activeClause." ".$rteClause." AND clinched.traveler='".$_GET['u']."' ORDER BY root, segments.segmentId;";
                 } else {
                  $sql_command = "SELECT segments.segmentId, segments.root FROM segments RIGHT JOIN clinched ON segments.segmentId = clinched.segmentId JOIN routes ON routes.root = segments.root JOIN systems ON routes.systemname = systems.systemname AND ".$activeClause." ".$where_regions.$select_systems." AND clinched.traveler='".$_GET['u']."' ORDER BY root, segments.segmentId;";
                 }
                 $res = tmdb_query($sql_command);
                 $segmentIndex = 0;
                 while ($row = $res->fetch_assoc()) {
                   echo "clinched[".$segmentIndex."] = ".$row['segmentId']."; // route=".$row['root']."\n";
                   $segmentIndex = $segmentIndex + 1;
                 }
               echo "mapClinched = true;\n";
              }

              // insert custom color code if needed
              tm_generate_custom_colors_array();

            ?>
            genEdges = true;
        }
    </script>
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
            var pos =  $row.position().left - 2;
            //console.log($table.offset().left);
            $(this).css({left: pos})
        });
    }

    $(document).ready(function () {
            $routesTable = $('#routesTable');
            $routesTable.tablesorter({
                sortList: [[0, 0]],
                headers: {}
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
            <tr class="float"><th class="sortable">Route</th><th class="sortable">System</th><th class="sortable">Clinched</th><th class="sortable">Overall</th><th class="sortable">%</th></tr>
        </thead>
        <tbody>
        <?php
	// TODO: a toggle to include/exclude devel routes?
        $sql_command = "SELECT r.region, r.root, r.route, r.systemName, banner, city, round(r.mileage, 2) AS total, round(COALESCE(cr.mileage, 0), 2) as clinched FROM routes AS r LEFT JOIN clinchedRoutes AS cr ON r.root = cr.route AND traveler = '" .$_GET['u']. "' WHERE ";
        if (array_key_exists('rte', $_GET)) {
            $sql_command .= "(r.route like '".$_GET['rte']."' or r.route regexp '".$_GET['rte']."[a-z]')";
            $sql_command = str_replace("*", "%", $sql_command);
            if (array_key_exists('rg', $_GET) or array_key_exists('sys', $_GET)) $sql_command .= ' AND ';
        }
        if (array_key_exists('rg', $_GET) && array_key_exists('sys', $_GET)) {
            $sql_command .= orClauseBuilder('rg', 'region')." AND ".orClauseBuilder('sys', 'systemName').";";
        } elseif (array_key_exists('rg', $_GET)) {
            $sql_command .= orClauseBuilder('rg', 'region').";";
        } elseif (array_key_exists('sys', $_GET)) {
            $sql_command .= orClauseBuilder('sys', 'systemName').";";
        } elseif (!array_key_exists('rte', $_GET)) {
            //Don't show. Too many routes
            $sql_command .= "r.root IS NULL;";
        }
        $res = tmdb_query($sql_command);
        while($row = $res->fetch_assoc()) {
            $link = "/hb?u=".$_GET['u']."&amp;r=".$row['root'];
            echo "<tr onclick=\"window.open('".$link."')\"><td>";
            //REGION ROUTE BANNER (CITY)
            echo $row['region'] . " " . $row['route'];
            if (strlen($row['banner']) > 0) {
                echo " " . $row['banner'];
            }
            if (strlen($row['city']) > 0) {
                echo " (" . $row['city'] . ")";
            }
	    $pct = sprintf("%0.2f",( $row['clinched'] / $row['total'] * 100) );
            echo "</td><td class='link'><a href='/user/system.php?u={$_GET['u']}&amp;sys={$row['systemName']}'>{$row['systemName']}</a></td><td>".$row['clinched']."</td><td>".$row['total']."</td><td>".$pct."%</td></tr>\n";
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
</html>
