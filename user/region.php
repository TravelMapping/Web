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

    if (array_key_exists("rg", $_GET)) {
        $region = $_GET['rg'];
    }

    if (is_null($user) || is_null($region)) {
        header('HTTP/ 400 Missing user (u=) or region (rg=) params');
        echo "</head><body><h1>ERROR: 400 Missing user (u=) or region (rg=) params</h1></body></html>";
        exit();
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	Shows a users' stats for a particular region. 
	URL Params:
		u - the user.
        rg - the region viewing stats for.
		db - the database being used. Use 'TravelMappingDev' for in-development systems.
		(u, rg, [db])
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
            width: 1000px;
        }

        #map {
            height: 500px;
            overflow: hidden;
            clear: left;
        }

        #colorBox {
            position: relative;
            float: right;
            margin: auto;
            padding: 10px;
        }

        #systemsTable {
            clear: both;
        }

        @media screen and (max-width: 720px) {

            #mapholder {
                width: 100%;
            }
        }

        #map * {
            cursor: crosshair;
        }
        
        #overallTable, #routesTable {
            margin: 5px auto 5px auto;
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
    // TODO: Update to MySQLi or PDO
    $db = mysql_connect("localhost", "travmap", "clinch") or die("Failed to connect to database");
    mysql_select_db($dbname, $db);
    $sql_command = "SELECT * FROM regions where code = '".$region."'";
    echo "<!--".$sql_command."-->";
    $regionInfo = mysql_fetch_array(mysql_query($sql_command));

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
    <title><?php echo $regionInfo['name']." - ".$user; ?></title>
    <script
        src="http://maps.googleapis.com/maps/api/js?sensor=false"
        type="text/javascript"></script>
    <script src="chmviewerfunc3.js" type="text/javascript"></script>
    <script src="/lib/tmjsfuncs.js" type="text/javascript"></script>
    <!-- jQuery -->
    <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <!-- TableSorter -->
    <script src="/lib/jquery.tablesorter.min.js"></script>
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
                 // for now, put in a default to usai, do something better later
                 $select_systems = " and (routes.systemName='usai')";
                 $where_systems = " where (routes.systemName='usai')";
              }

              $sql_command = "select waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, systems.tier, systems.color, systems.systemname from waypoints join routes on routes.root = waypoints.root".$select_regions.$select_systems." join systems on routes.systemname = systems.systemname and systems.active='1' order by root, waypoints.pointId;";
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
                 $sql_command = "select segments.segmentId, segments.root from segments join routes on routes.root = segments.root join systems on routes.systemname = systems.systemname and systems.active='1'".$where_regions.$where_systems." order by root, segments.segmentId;";
                 echo "// SQL: ".$sql_command."\n";
                 $res = mysql_query($sql_command);
                 $segmentIndex = 0;
                 while ($row = mysql_fetch_array($res)) {
                   echo "segments[".$segmentIndex."] = ".$row[0]."; // route=".$row[1]."\n";
                   $segmentIndex = $segmentIndex + 1;
                 }
                 $sql_command = "select segments.segmentId, segments.root from segments right join clinched on segments.segmentId = clinched.segmentId join routes on routes.root = segments.root join systems on routes.systemname = systems.systemname and systems.active='1'".$where_regions.$where_systems." and clinched.traveler='".$_GET['u']."' order by root, segments.segmentId;";
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
<body onload="loadmap();">
<script type="text/javascript">
    $(document).ready(function () {
            $("table.tablesorter").tablesorter({
                sortList: [[0, 0]],
                headers: {0: {sorter: false},}
            });
        }
    );

    function redirect($link) {
        window.document.location=$link;
    }
</script>
<div id="header">
    <a href="/user?u=<?php echo $user ?>"><?php echo $user ?></a> -
    <a href="/">Home</a> -
    <a href="/hbtest">Highway Browser</a>

    <form id="userselect" action="region.php">
        <label>User: </label>
        <input type="text" name="u" form="userselect" value="<?php echo $user ?>">
        <label>Region: </label>
        <input type="text" name="rg" form="userselect" value="<?php echo $region ?>">
        <input type="submit">
    </form>
    <h1>Traveler Stats for <?php echo $user . " in " . $regionInfo['name'] ?>:</h1>

</div>
<div id="body">
    <div id="mapholder">
        <input id="showMarkers" type="checkbox" name="Show Markers" onclick="showMarkersClicked()">&nbsp;Show Markers
        <div id="colorBox">

        </div>
        <div id="map"></div>
    </div>
    <h6>TIP: Click on a column head to sort. Hold SHIFT in order to sort by multiple columns.</h6>
    <table class="gratable" id="overallTable">
        <thead><tr><th colspan="5">Overall Region Stats</th></tr></thead>
        <tbody>
            <?php
            //First fetch overall mileage
            $sql_command = <<<SQL
            SELECT o.mileage AS overall, c.mileage as clinched, round(c.mileage / o.mileage * 100) AS percentage
            FROM overallMileageByRegion AS o
            LEFT JOIN clinchedOverallMileageByRegion AS c ON c.region = o.region AND  c.traveler = '
SQL
            .$user."' WHERE o.region = '".$region."'";
            echo "<!--".$sql_command."-->";
            $res = mysql_query($sql_command);
            $row = mysql_fetch_array($res);
            $link = "redirect('/hbtest/mapview.php?u=" . $user . "&rg=" . $region . "')";
            echo "<tr style=\"background-color:#EEEEFF\"><td>Overall</td><td colspan='2'>Miles Driven: ".$row['clinched']." mi (".$row['percentage']."%)</td><td>Total: ".$row['overall']." mi</td><td>Rank: TBD</td></tr>";

            //Second, fetch routes clinched/driven
            $sql_command = "SELECT COUNT(r.route) AS total, COUNT(cr.route) AS driven, SUM(cr.clinched) AS clinched, ROUND(COUNT(cr.route) / count(r.route) * 100, 2) as drivenPct, ROUND(sum(cr.clinched) / count(r.route) * 100, 2) as clinchedPct FROM routes AS r LEFT JOIN clinchedRoutes AS cr ON cr.route = r.root AND traveler='" .$user. "' WHERE region = '" .$region. "';";
            echo "<!--".$sql_command."-->";
            $res = mysql_query($sql_command);
            $row = mysql_fetch_array($res);
            echo "<tr onClick=\"" . $link . "\"><td>Routes</td><td>Driven: " . $row['driven'] . " (" . $row['drivenPct'] . "%)</td><td>Clinched: " . $row['clinched'] . " (" . $row['clinchedPct'] . "%)</td><td>Total: " . $row['total'] . "</td><td>Rank: TBD</td></tr>\n";
            ?>
        </tbody>
    </table>
    <table class="gratable tablesorter" id="systemsTable">
        <caption>TIP: Click on a column head to sort. Hold SHIFT in order to sort by multiple columns.</caption>
        <thead>
        <tr>
            <th colspan="5">Clinched Mileage by System</th>
        </tr>
        <tr>
            <th class="sortable">System Code</th>
            <th class="sortable">System Name</th>
            <th class="sortable">Clinched Mileage</th>
            <th class="sortable">Total Mileage</th>
            <th class="sortable">Percent</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql_command = <<<SQL
          SELECT
            sys.systemName,
            sys.tier,
            sys.level AS status,
            sys.fullName,
            r.root,
            COALESCE(ROUND(SUM(cr.mileage), 2), 0) AS clinchedMileage,
            COALESCE(ROUND(SUM(r.mileage), 2), 0) AS totalMileage,
            COALESCE(ROUND(SUM(cr.mileage) / SUM(r.mileage) * 100, 3), 0) AS percentage
          FROM systems as sys
          INNER JOIN routes AS r
            ON r.systemName = sys.systemName
          LEFT JOIN clinchedRoutes AS cr
            ON cr.route = r.root AND cr.traveler = 'xxxxxxxxxxxxxxxxx'
          WHERE r.region = 'yyyyyyyyyyyyyyyyy'
          GROUP BY r.systemName
          ORDER BY sys.tier, sys.systemName;
SQL;

        $sql_command = str_replace("xxxxxxxxxxxxxxxxx", $user, $sql_command);
        $sql_command = str_replace("yyyyyyyyyyyyyyyyy", $region, $sql_command);
        echo "<!--" . $sql_command . "-->";

        $res = mysql_query($sql_command);
        while ($row = mysql_fetch_array($res)) {
            echo "<tr onClick=\"window.open('/user/system.php?u=" . $user . "&sys=" . $row['systemName'] . "&rg=" . $region . "')\" class=\"status-" . $row['status'] . "\">";
            echo "<td>" . $row['systemName'] . "</td>";
            echo "<td>" . $row['fullName'] . "</td>";
            echo "<td>" . $row['clinchedMileage'] . "</td>";
            echo "<td>" . $row['totalMileage'] . "</td>";
            echo "<td>" . $row['percentage'] . "%</td></tr>";
        }
        ?>
        </tbody>
    </table>
    <table class="gratable tablesorter" id="routesTable">
        <thead>
            <tr><th colspan="4">Stats by Route: (<?php echo "<a href=\"/hbtest/mapview.php?u=".$user."&rg=".$region."\">" ?>Full Map)</a></th></tr>
            <tr><th class="sortable">Route</th><th class="sortable">Clinched Mileage</th><th class="sortable">Total Mileage</th><th class="sortable">%</th></tr>
        </thead>
        <tbody>
            <?php
                $sql_command = "SELECT r.route, r.root, r.banner, r.city, ROUND((COALESCE(r.mileage, 0)),2) AS totalMileage, ROUND((COALESCE(cr.mileage, 0)),2) AS clinchedMileage, ROUND((COALESCE(cr.mileage,0)) / (COALESCE(r.mileage, 0)) * 100,2) AS percentage FROM routes AS r LEFT JOIN clinchedRoutes AS cr ON r.root = cr.route AND traveler = '".$user."' WHERE region = '" . $region . "'";
                echo "<!--".$sql_command."-->";
                $res = mysql_query($sql_command);
                while ($row = mysql_fetch_array($res)) {
                    echo "<tr onClick=\"window.open('/devel/hb.php?u=".$user."&r=".$row['root']."')\">";
                    echo "<td>".$row['route'];
                    if (strlen($row['banner']) > 0) {
                        echo " ".$row['banner']." ";
                    }
                    if (strlen($row['city']) > 0) {
                        echo " (".$row['city'].")";
                    }
                    echo "</td>";
                    echo "<td>".$row['clinchedMileage']."</td>";
                    echo "<td>".$row['totalMileage']."</td>";
                    echo "<td>".$row['percentage']."</td></tr>";
                }
            ?>
        </tbody>
    </table>
</div>
</div>
</body>
</html>