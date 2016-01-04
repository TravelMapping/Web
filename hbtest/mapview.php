<?php
    if (array_key_exists("u", $_GET)) {
        setcookie("lastuser", $_GET['u'], time() + (86400 * 30), "/");
    } else if (isset($_COOKIE['lastuser'])) {
        $_GET['u'] = $_COOKIE['lastuser'];
    }

    $dbname = "TravelMapping";
    if (isset($_COOKIE['currentdb'])) {
        $dbname = $_COOKIE['currentdb'];
    }

    if (array_key_exists("db", $_GET)) {
        $dbname = $_GET['db'];
        setcookie("currentdb", $dbname, time() + (86400 * 30), "/");
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 ***
 * Map viewer page. Displays the routes selected using the url params on a map, as well as on a table to the side.
 * URL Params:
 *  u - user to display highlighting for on map (required)
 *  rg - region to show routes for on the map (optional)
 *  sys - system to show routes for on the map (optional)
 *  rte - route name to show on the map. Supports pattern matching, with _ matching a single character, and % matching 0 or multiple characters.
 *  db - database to use (optional, defaults to TravelMapping
 * (u, [rg|sys][rte], [db])
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
            top: 50px;
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
            top: 110px;
            bottom: 10px;
            overflow-y: scroll;
            max-width: 25%;
        }
    </style>
    <script
        src="http://maps.googleapis.com/maps/api/js?sensor=false"
        type="text/javascript"></script>

    <!-- jQuery -->
    <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <!-- TableSorter -->
    <script src="/lib/jquery.tablesorter.min.js"></script>

    <?php
    // establish connection to db: mysql_ interface is deprecated, should learn new options
    $con = mysql_connect("localhost", "travmap", "clinch") or die("Failed to connect to database");
    mysql_select_db($dbname, $con);

    # functions from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
    function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    ?>
    <script src="chmviewerfunc3.js" type="text/javascript"></script>
    <script>
        function waypointsFromSQL() {
            <?php
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

              // make sure we have selected some region or system
              if (($num_systems == 0) && ($num_regions == 0)) {
                if (array_key_exists("rte", $_GET)) {
                  $rteClause = " where (routes.route like '".$_GET['rte']."' or route regexp '".$_GET['rte']."[a-z]')";
                  $rteClause = str_replace("*", "%", $rteClause);
                  $sql_command = "SELECT waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, systems.tier, systems.color, systems.systemname FROM waypoints JOIN routes ON routes.root = waypoints.root JOIN systems ON routes.systemname = systems.systemname AND systems.active='1' ".$rteClause." ORDER BY root, waypoints.pointId;";
                } else {
                 // for now, put in a default to usai, do something better later
                 $select_systems = " and (routes.systemName='usai')";
                 $where_systems = " where (routes.systemName='usai')";
                 $sql_command = "SELECT waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, systems.tier, systems.color, systems.systemname FROM waypoints JOIN routes ON routes.root = waypoints.root".$select_regions.$select_systems." JOIN systems ON routes.systemname = systems.systemname AND systems.active='1' ORDER BY root, waypoints.pointId;";
                }
              } else {
                $sql_command = "SELECT waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, systems.tier, systems.color, systems.systemname FROM waypoints JOIN routes ON routes.root = waypoints.root".$select_regions.$select_systems." JOIN systems ON routes.systemname = systems.systemname AND systems.active='1' ORDER BY root, waypoints.pointId;";
              }

              echo "// SQL: ".$sql_command."\n";
              $res = mysql_query($sql_command);

              $routenum = 0;
              $pointnum = 0;
              $lastRoute = "";
              while ($row = mysql_fetch_array($res)) {
                if (!($row[3] == $lastRoute)) {
                   echo "newRouteIndices[".$routenum."] = ".$pointnum.";\n";
                   echo "routeTier[".$routenum."] = ".$row[4].";\n";
                   echo "routeColor[".$routenum."] = '".$row[5]."';\n";
                   echo "routeSystem[".$routenum."] = '".$row[6]."';\n";
                   $lastRoute = $row[3];
                   $routenum = $routenum + 1;
                }
                echo "waypoints[".$pointnum."] = new Waypoint(\"".$row[0]."\",".$row[1].",".$row[2]."); // Route = ".$row[3]." (".$row[5].")\n";
                $pointnum = $pointnum + 1;
              }

              // check for query string parameter for traveler clinched mapping of route
              if (array_key_exists("u",$_GET)) {
                 echo "// select_systems: ".$select_systems."\n";
                 echo "// where_systems: ".$where_systems."\n";
                 echo "traveler = '".$_GET['u']."';\n";
                 // retrieve list of segments for this region or regions
                 if(isset($rteClause)) {
                  $sql_command = "SELECT segments.segmentId, segments.root FROM segments JOIN routes ON routes.root = segments.root JOIN systems ON routes.systemname = systems.systemname AND systems.active='1'".$rteClause." ORDER BY root, segments.segmentId;";
                 } else {
                  $sql_command = "SELECT segments.segmentId, segments.root FROM segments JOIN routes ON routes.root = segments.root JOIN systems ON routes.systemname = systems.systemname AND systems.active='1'".$where_regions.$select_systems." ORDER BY root, segments.segmentId;";
                 }
                 echo "// SQL: ".$sql_command."\n";
                 $res = mysql_query($sql_command);
                 $segmentIndex = 0;
                 while ($row = mysql_fetch_array($res)) {
                   echo "segments[".$segmentIndex."] = ".$row[0]."; // route=".$row[1]."\n";
                   $segmentIndex = $segmentIndex + 1;
                 }
                 if(isset($rteClause)) {
                  $sql_command = "SELECT segments.segmentId, segments.root FROM segments RIGHT JOIN clinched ON segments.segmentId = clinched.segmentId JOIN routes ON routes.root = segments.root JOIN systems ON routes.systemname = systems.systemname AND systems.active='1'".$rteClause." AND clinched.traveler='".$_GET['u']."' ORDER BY root, segments.segmentId;";
                 } else {
                  $sql_command = "SELECT segments.segmentId, segments.root FROM segments RIGHT JOIN clinched ON segments.segmentId = clinched.segmentId JOIN routes ON routes.root = segments.root JOIN systems ON routes.systemname = systems.systemname AND systems.active='1'".$where_regions.$select_systems." AND clinched.traveler='".$_GET['u']."' ORDER BY root, segments.segmentId;";
                 }
                 echo "// SQL: " .$sql_command."\n";
                 $res = mysql_query($sql_command);
                 $segmentIndex = 0;
                 while ($row = mysql_fetch_array($res)) {
                   echo "clinched[".$segmentIndex."] = ".$row[0]."; // route=".$row[1]."\n";
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

    $(document).ready(function () {
            $("#routesTable").tablesorter({
                sortList: [[0, 0]],
                headers: {}
            });
        }
    );
</script>
<a href="/user?u=<?php echo $_GET['u'] ?>"><?php echo $_GET['u'] ?></a>-
<a href="/">Home</a>-
<a href="/hbtest">Highway Browser</a>
<h1 style="text-align: center">Travel Mapping: Draft Map Overlay Viewer</h1>

<div id="controlbox">
    <input id="showMarkers" type="checkbox" name="Show Markers" onclick="showMarkersClicked()">&nbsp;Show Markers
      
  <span id="controlboxroute">
    <?php
    if (array_key_exists("r", $_GET)) {
        $sql_command = "SELECT region, route, banner, city FROM routes WHERE root = '" . $_GET['r'] . "';";
        $res = mysql_query($sql_command);
        $row = mysql_fetch_array($res);
        echo $row[0] . " " . $row[1];
        if (strlen($row[2]) > 0) {
            echo " " . $row[2];
        }
        if (strlen($row[3]) > 0) {
            echo " (" . $row[3] . ")";
        }
        echo ": ";
    } else if (array_key_exists("rg", $_GET)) {
        echo "Displaying region: " . $_GET['rg'] . ".";
    }
    ?>
  </span>
    <span id="controlboxinfo"></span>
    <button onclick="toggleTable()">Show/Hide Table</button>
</div>
<div id="map">
</div>
<div id="routes">
    <table id="routesTable" class="gratable tablesorter">
        <thead><tr><th class="sortable">Route</th><th class="sortable">System</th><th class="sortable">Clinched</th><th class="sortable">Overall</th><th class="sortable">%</th></tr></thead>
        <tbody>
        <?php
        $sql_command = "SELECT r.region, r.root, r.route, r.systemName, banner, city, round(r.mileage, 2) AS total, round(COALESCE(cr.mileage, 0), 2) as clinched, round(COALESCE(cr.mileage, 0) / COALESCE(r.mileage, 0) * 100,2) as percentage FROM routes AS r LEFT JOIN clinchedRoutes AS cr ON r.root = cr.route AND traveler = '" .$_GET['u']. "' WHERE ";
        if (array_key_exists('rte', $_GET)) {
            $sql_command .= "(r.route like '".$_GET['rte']."' or r.route regexp '".$_GET['rte']."[a-z]')";
            $sql_command = str_replace("*", "%", $sql_command);
        } elseif (array_key_exists('rg', $_GET) && array_key_exists('sys', $_GET)) {
            $sql_command .= "r.region = '".$_GET['rg']."' AND r.systemName = '".$_GET['sys']."';";
        } elseif (array_key_exists('rg', $_GET)) {
            $sql_command .= "r.region = '".$_GET['rg']."';";
        } elseif (array_key_exists('sys', $_GET)) {
            $sql_command .= "r.systemName = '".$_GET['sys']."';";
        } else {
            //Don't show. Too many routes
            $sql_command .= "r.root IS NULL;";
        }
        echo "<!--".$sql_command."-->";
        $res = mysql_query($sql_command);
        while($row = mysql_fetch_array($res)) {
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
            echo "</td><td>".$row['systemName']."</td><td>".$row['clinched']."</td><td>".$row['total']."</td><td>".$row['percentage']."%</td></tr>\n";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>
