<?php
if (array_key_exists("u", $_GET)) {
    $user = $_GET['u'];
    setcookie("lastuser", $user, time() + (86400 * 30), "/");
} else if (isset($_COOKIE['lastuser'])) {
    $user = $_COOKIE['lastuser'];
}

$dbname = "TravelMapping";
if (isset($_COOKIE['currentdb'])) {
    $dbname = $_COOKIE['currentdb'];
}

if (array_key_exists("db", $_GET)) {
    $dbname = $_GET['db'];
    setcookie("currentdb", $dbname, time() + (86400 * 30), "/");
}

if (array_key_exists("rg", $_GET) && strlen($_GET["rg"]) > 0) {
    $region = $_GET['rg'];
}
if (array_key_exists("sys", $_GET)) {
    $system = $_GET['sys'];
}

if (is_null($user) || is_null($system)) {
    header('HTTP/ 400 Missing user (u=) or system(sys=) params');
    echo "<h1>ERROR: 400 Missing user (u=) or system (sys=) params</h1>";
    exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	Shows a user's stats for a particular system, whether overall or limited to a single region.  
	URL Params:
		u - the user.
        sys - The system being viewed on this page
        rg - The region to study this system
		db - the database being used. Use 'TravelMappingDev' for in-development systems.
		(u, sys, [rg], [db])
-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
    <style type="text/css">
        table.gratable {
            max-width: 50%;
            width: 700px;
            margin-bottom: 15px;
            margin-top: 15px;
        }

        #mapholder {
            position: relative;
            margin: auto;
            width: 800px;
        }

        #map {
            height: 500px;
            overflow: hidden;
        }

        @media screen and (max-width: 720px) {
            #mapholder {
                width: 100%;
            }
        }

        #map * {
            cursor: crosshair;
        }

        #body {
            left: 0px;
            top: 80px;
            bottom: 0px;
            width: 100%;
            overflow: auto;
            padding: 20px;
        }
    </style>
    <?php
    // establish connection to db: mysql_ interface is deprecated, should learn new options
    $db = mysql_connect("localhost", "travmap", "clinch") or die("Failed to connect to database");
    mysql_select_db($dbname, $db);

    if (!is_null($region)) {
        $sql_command = "SELECT * FROM regions where code = '".$region."'";
        echo "<!--".$sql_command."-->";
        $regionInfo = mysql_fetch_array(mysql_query($sql_command));
    }

    $sql_command = "SELECT * FROM systems where systemName = '".$system."'";
    echo "<!--".$sql_command."-->";
    $systemInfo = mysql_fetch_array(mysql_query($sql_command));


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

    function colorScale($percent)
    {

    }

    ?>
    <title><?php
        echo "Traveler Stats for " . $user . " on ";
        if (!is_null($region)) {
            echo $system;
            echo " in " . $region;
        } else {
            echo $systemInfo['fullName'];
        }
        ?></title>
    <script
        src="http://maps.googleapis.com/maps/api/js?sensor=false"
        type="text/javascript"></script>
    <script src="chmviewerfunc3.js" type="text/javascript"></script>
    <!-- jQuery -->
    <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <!-- TableSorter -->
    <script src="/lib/jquery.tablesorter.min.js"></script>
    <script>
        function waypointsFromSQL() {
            <?php
              $regions = array();
              if (array_key_exists("rg",$_GET) && strlen($region) > 0) {
                $regions[0] = $region;
              }

              // restrict to waypoints matching routes whose region is given in the "rg=" query string parameter
              // build strings needed for later queries
              $select_regions = "";
              $where_regions = "";
              $num_regions = 0;
              foreach ($regions as $aregion) {
                if ($num_regions == 0) {
                  $select_regions = " and (routes.region='".$aregion."'";
                  $where_regions = " where (region='".$aregion."'";
                }
                else {
                  $select_regions = $select_regions." or routes.region='".$aregion."'";
                  $where_regions = $where_regions." or region='".$aregion."'";
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
                 // for now, put in a default to usai, do something better later
                 $select_systems = " and (routes.systemName='usai')";
                 $where_systems = " where (routes.systemName='usai')";
              }

              $sql_command = "SELECT waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, systems.tier, systems.color, systems.systemname FROM waypoints JOIN routes ON routes.root = waypoints.root".$select_regions.$select_systems." JOIN systems ON routes.systemname = systems.systemname ORDER BY root, waypoints.pointId;";
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
                 echo "traveler = '".$_GET['u']."';\n";
                 // retrieve list of segments for this region or regions
                 $sql_command = "SELECT segments.segmentId, segments.root FROM segments JOIN routes ON routes.root = segments.root JOIN systems ON routes.systemname = systems.systemname".$where_regions.$select_systems." ORDER BY root, segments.segmentId;";
                 echo "// SQL: ".$sql_command."\n";
                 $res = mysql_query($sql_command);
                 $segmentIndex = 0;
                 while ($row = mysql_fetch_array($res)) {
                   echo "segments[".$segmentIndex."] = ".$row[0]."; // route=".$row[1]."\n";
                   $segmentIndex = $segmentIndex + 1;
                 }
                 $sql_command = "SELECT segments.segmentId, segments.root FROM segments RIGHT JOIN clinched ON segments.segmentId = clinched.segmentId JOIN routes ON routes.root = segments.root JOIN systems ON routes.systemname = systems.systemname".$where_regions.$select_systems." AND clinched.traveler='".$_GET['u']."' ORDER BY root, segments.segmentId;";
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
</head>
<body onload="<?php if (!$_GET['renderMap'] || !array_key_exists('renderMap', $_GET)) echo "loadmap();" ?>">
<script type="text/javascript">
    $(document).ready(function () {
            $("#routeTable").tablesorter({
                sortList: [[0, 0]],
                headers: {0: {sorter: false}, 1: {sorter: false}, 3: {sorter: false},}
            });
        }
    );
</script>
<div id="header">
    <a href="/user?u=<?php echo $user ?>"><?php echo $user ?></a>-
    <a href="/">Home</a>-
    <a href="/hbtest">Highway Browser</a>-
    <form id="userselect">
        <label>User: </label>
        <input type="text" name="u" form="userselect" value="<?php echo $user ?>" required>
        <label>System: </label>
        <input type="text" name="sys" form="userselect" value="<?php echo $system ?>" required>
        <label>Region: </label>
        <input type="text" name="rg" form="userselect" value="<?php echo $region ?>">
        <input type="submit">
    </form>
    <h1><?php
        echo "Traveler Stats for " . $user . " on " . $systemInfo['fullName'];
        if (!is_null($region)) {
            echo " in " . $regionInfo['name'];
        }
        ?>:</h1>
</div>
<div id="body">
    <div id="mapholder">
        <input id="showMarkers" type="checkbox" name="Show Markers" onclick="showMarkersClicked()">&nbsp;Show Markers
        <div id="controlboxinfo"></div>
        <div id="map"></div>
        <table class="gratable tablesorter" id="overallTable">
            <thead><tr><th colspan="5">System Stats</th></tr></thead>
            <tbody>
            <?php
            //First fetch overall mileage
            if(is_null($region)) {
                $sql_command = <<<SQL
                SELECT
                    round(sum(coalesce(cr.mileage, 0))) as overall,
                    round(sum(coalesce(ccr.mileage, 0))) as clinched,
                    round(sum(coalesce(ccr.mileage, 0))) / round(sum(coalesce(cr.mileage, 0)), 2) * 100 AS percentage
                FROM connectedRoutes as cr
                LEFT JOIN clinchedConnectedRoutes as ccr
                ON cr.firstRoot = ccr.route AND ccr.traveler = '$user'
                WHERE cr.systemName = '$system';
SQL;
            } else {
                $sql_command = <<<SQL
                SELECT
                    round(sum(coalesce(r.mileage, 0))) as overall,
                    round(sum(coalesce(cr.mileage, 0))) as clinched,
                    round(sum(coalesce(cr.mileage, 0))) / round(sum(coalesce(r.mileage, 0)), 2) * 100 AS percentage
                FROM routes as r
                LEFT JOIN clinchedRoutes as cr
                ON r.root = cr.route AND cr.traveler = '$user'
                WHERE r.region = '$region' AND r.systemName = '$system';
SQL;
            }
            echo "<!--".$sql_command."-->";
            $res = mysql_query($sql_command);
            $row = mysql_fetch_array($res);
            $link = "window.document.location='/hbtest/mapview.php?u=" . $user . "&rg=" . $region . "'";
            echo "<tr style=\"background-color:#EEEEFF\"><td>Overall</td><td colspan='2'>Miles Driven: ".$row['clinched']." mi (".$row['percentage']."%)</td><td>Total: ".$row['overall']." mi</td><td>Rank: TBD</td></tr>";

            //Second, fetch routes clinched/driven
            if (is_null($region)) {
                $sql_command = <<<SQL
                SELECT
                    count(cr.route) as total,
                    count(ccr.route) as driven,
                    sum(ccr.clinched) as clinched
                FROM connectedRoutes as cr
                LEFT JOIN clinchedConnectedRoutes as ccr
                ON cr.firstRoot = ccr.route AND ccr.traveler = '$user'
                WHERE cr.systemName = '$system';
SQL;
            } else {
                $sql_command = <<<SQL
                SELECT
                    count(cr.route) as total,
                    count(ccr.route) as driven,
                    sum(ccr.clinched) as clinched
                FROM routes as cr
                LEFT JOIN clinchedRoutes as ccr
                ON cr.root = ccr.route AND ccr.traveler = '$user'
                WHERE cr.region = '$region' AND cr.systemName = '$system'
SQL;
            }
            echo "<!--".$sql_command."-->";
            $res = mysql_query($sql_command);
            $row = mysql_fetch_array($res);
            echo "<tr onClick=\"" . $link . "\"><td>Routes</td><td>Driven: " . $row['driven'] . " (" . round($row['driven'] / $row['total'] * 100, 2) .
                "%)</td><td>Clinched: " . $row['clinched'] . " (" . round($row['clinched'] / $row['total'] * 100, 2) . "%)</td><td>Total: " . $row['total'] .
                "</td><td>Rank: TBD</td></tr>\n";
            ?>
            </tbody>
        </table>
        <table class="gratable tablesorter" id="routeTable">
            <caption>TIP: Click on a column head to sort. Hold SHIFT in order to sort by multiple columns.</caption>
            <thead>
            <tr>
                <th colspan="7">Statistics per Route</th>
            </tr>
            <tr>
                <th class="nonsortable">Route</th>
                <th class="sortable">#</th>
                <th class="nonsortable">Section</th>
                <th class="sortable">Clinched Mileage</th>
                <th class="sortable">Total Mileage</th>
                <th class="sortable">Percentage</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $sql_command = "";
            if (!is_null($region)) {
                $sql_command = "SELECT r.route, r.root, r.city, ROUND((COALESCE(r.mileage, 0)),2) AS totalMileage, ROUND((COALESCE(cr.mileage, 0)),2) AS clinchedMileage, ROUND((COALESCE(cr.mileage,0)) / (COALESCE(r.mileage, 0)) * 100,2) AS percentage, SUBSTRING(root, LOCATE('.', root)) AS routeNum FROM routes AS r LEFT JOIN clinchedRoutes AS cr ON r.root = cr.route AND traveler = 'xxxxxxxxxxxxxxxxx' WHERE systemName = 'yyyyyyyyyyyyyyyyy' AND region = '" . $region . "' ORDER BY routeNum;";
            } else {
                $sql_command = "SELECT r.route, r.groupName AS city, r.firstRoot AS root, ROUND((COALESCE(r.mileage, 0)),2) AS totalMileage, ROUND((COALESCE(cr.mileage, 0)),2) AS clinchedMileage, ROUND((COALESCE(cr.mileage,0)) / (COALESCE(r.mileage, 0)) * 100,2) AS percentage, SUBSTRING(firstRoot, LOCATE('.', firstRoot)) AS routeNum FROM connectedRoutes AS r LEFT JOIN clinchedConnectedRoutes AS cr ON r.firstRoot = cr.route AND traveler = 'xxxxxxxxxxxxxxxxx' WHERE systemName = 'yyyyyyyyyyyyyyyyy' " . $regionClause . " ORDER BY routeNum;";
            }

            $sql_command = str_replace("xxxxxxxxxxxxxxxxx", $user, $sql_command);
            $sql_command = str_replace("yyyyyyyyyyyyyyyyy", $system, $sql_command);
            echo "<!--" . $sql_command . "-->";

            $res = mysql_query($sql_command);

            while ($row = mysql_fetch_array($res)) {
                if (is_null($region)) {
                    $link = "window.document.location='/hbtest/mapview.php?u=" . $user . "&rte=" . $row['route'] . "'";
                } else {
                    $link = "window.document.location='/devel/hb.php?u=" . $user . "&r=" . $row['root'] . "'";
                }

                echo "<tr onClick=\"" . $link . "\">";
                echo "<td>" . $row['route'] . "</td>";
                echo "<td width='0'>" . $row['routeNum'] . "</td>";
                echo "<td>" . $row['city'] . "</td>";
                echo "<td>" . $row['clinchedMileage'] . "</td>";
                echo "<td>" . $row['totalMileage'] . "</td>";
                echo "<td>" . $row['percentage'] . "%</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>