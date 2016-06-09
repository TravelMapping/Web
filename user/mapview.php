<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
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
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
    <style type="text/css">
        #headerbox {
            position: absolute;
            top: 0px;
            bottom: 50px;
            width: 100%;
            overflow: hidden;
            text-align: center;
            font-size: 30px;
            font-family: "Times New Roman", serif;
            font-style: bold;
        }

        #statsbox {
            position: fixed;
            left: 0px;
            top: 50px;
            right: 400px;
            bottom: 0px;
            width: 400px;
            overflow: auto;
        }

        #controlbox {
            position: fixed;
            top: 60px;
            bottom: 100px;
            height: 100%;
            left: 0px;
            right: 0px;
            overflow: auto;
            padding: 5px;
            font-size: 20px;
        }

        #map {
            position: absolute;
            top: 100px;
            bottom: 0px;
            width: 100%;
            overflow: hidden;
        }

        #map * {
            cursor: crosshair;
        }

        #routes {
            position: absolute;
            right: 10px;
            top: 100px;
            bottom: 20px;
            overflow-y: scroll;
            max-width: 25%;
        }

        #showHideBtn {
            position: absolute;
            right: 10px;
        }
    </style>
    <script
        src="http://maps.googleapis.com/maps/api/js?sensor=false"
        type="text/javascript"></script>

    <!-- jQuery -->
    <script type="application/javascript" src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <!-- TableSorter -->
    <script type="application/javascript" src="/lib/jquery.tablesorter.min.js"></script>

    <?php

    function orClauseBuilder($param, $name, $tablename = 'r') {
        $array = explode(",", $_GET[$param]);
        $clause = "(";
        $i = 0;
        foreach($array as $item) {
            $clause.="{$tablename}.{$name} = '{$item}'";
            $i++;
            if($i < sizeof($array)) $clause .= " or ";
        }
        $clause .= ")";
        return $clause;
    }

    ?>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
    <script>
        function waypointsFromSQL() {
            <?php
	      // later get this from a QS parameter probably
	      // idea: option to include devel routes as a debugging aid
	      $activeClause = "(systems.level='preview' OR systems.level='active')";
              $regions = array();
              if (array_key_exists("rg",$_GET)) {
                $regions = explode(',',$_GET['rg']);
              }

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
              $systems = array();
              if (array_key_exists("sys",$_GET)) {
                $systems = explode(',',$_GET['sys']);
              }

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

              // check for custom colors query string parameters
              $customColors = array();
              if (array_key_exists("colors",$_GET)) {
                 $customColors = explode(';',$_GET['colors']);
                 $colorNum = 0;
                 foreach ($customColors as $customColor) {
                    $colorEntry = array();
                    $colorEntry = explode(':',$customColor);
                    echo "customColorCodes[".$colorNum."] = { name: \"".$colorEntry[0]."\", unclinched: \"".$colorEntry[1]."\", clinched: \"".$colorEntry[2]."\" };\n";
                    $colorNum = $colorNum + 1;
                 }
              }

            ?>
            genEdges = true;
        }
    </script>
    <title>Travel Mapping: Draft Map Overlay Viewer</title>
</head>

<body onload="loadmap();">
<script type="application/javascript">
    function toggleTable()
    {
        var visibility = document.getElementById("routes").style.visibility;
        if (visibility == 'hidden') {
            visibility = 'visible'
        } else {
            visibility = 'hidden';
        }
        document.getElementById("routes").style.visibility = visibility;
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
            console.log($table.offset().left);
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
<h1 style="text-align: center">Travel Mapping: Draft Map Overlay Viewer</h1>

<div id="controlbox">
    <input id="showMarkers" type="checkbox" name="Show Markers" onclick="showMarkersClicked()">&nbsp;Show Markers
      
  <span id="controlboxroute">
    <?php
    if (array_key_exists("r", $_GET)) {
        $sql_command = "SELECT region, route, banner, city FROM routes WHERE root = '" . $_GET['r'] . "';";
        $res = tmdb_query($sql_command);
        $row = $res->fetch_assoc();
        echo $row['region'] . " " . $row['route'];
        if (strlen($row['banner']) > 0) {
            echo " " . $row['banner'];
        }
        if (strlen($row['city']) > 0) {
            echo " (" . $row['city'] . ")";
        }
        echo ": ";
    } else if (array_key_exists("rg", $_GET)) {
        echo "Displaying region: " . $_GET['rg'] . ".";
    }
    ?>
  </span>
    <span id="controlboxinfo"></span>
    <button id="showHideBtn" onclick="toggleTable()">Show/Hide Table</button>
</div>
<div id="map">
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
            $link = "/devel/hb.php?u=".$_GET['u']."&r=".$row['root'];
            echo "<tr onClick=\"window.open('".$link."')\"><td>";
            //REGION ROUTE BANNER (CITY)
            echo $row['region'] . " " . $row['route'];
            if (strlen($row['banner']) > 0) {
                echo " " . $row['banner'];
            }
            if (strlen($row['city']) > 0) {
                echo " (" . $row['city'] . ")";
            }
	    $pct = sprintf("%0.2f",( $row['clinched'] / $row['total'] * 100) );
            echo "</td><td class='link'><a href='/user/system.php?u={$_GET['u']}&sys={$row['systemName']}'>{$row['systemName']}</a></td><td>".$row['clinched']."</td><td>".$row['total']."</td><td>".$pct."%</td></tr>\n";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
<?php
    $tmdb->close();
?>
</html>
