<?php
/**
 * Returns the WaypointsFromSQL() JS function used to define waypoints for map drawing.
 * URL params:
 *  u - Traveler Name
 *
 *  For a singular route:
 *  r - root of desired route, if singular
 *
 *  For many routes:
 *  rg - code of region to search in
 *  sys - code of system to search in
 *
 */
    require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php";

    if(!array_key_exists('r', $_GET) && !array_key_exists('sys', $_GET) && !array_key_exists('rg', $_GET) && !array_key_exists('rte', $_GET))
    {
        echo "Missing parameter: one of r, rg, sys, or rte MUST be set.";
        http_response_code(400);
        exit();
    }
    
    function select_route_set () {
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
                $select_regions = " and (routes.region='$region'";
                $where_regions = " where (region='$region'";
            }
            else {
                $select_regions = $select_regions." or routes.region='$region'";
                $where_regions = $where_regions." or region='$region'";
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
                $select_systems = " and (routes.systemName='$system'";
                $where_systems = " where (routes.systemName='$system'";
            }
            else {
                $select_systems = $select_systems." or routes.systemName='$system'";
                $where_systems = $where_systems." or routes.systemName='$system'";
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
            $sql_command = <<<SQL
SELECT 
  waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, 
  systems.tier, systems.color, systems.systemname, routes.route, routes.banner, routes.abbrev, routes.region
FROM waypoints 
  JOIN routes ON routes.root = waypoints.root 
  JOIN systems ON routes.systemname = systems.systemname AND $activeClause $rteClause
  ORDER BY systems.tier DESC, root, waypoints.pointId;
SQL;
        } elseif (($num_systems == 0) && ($num_regions == 0)) {
            // for now, put in a default to usai, do something better later
            $select_systems = " and (routes.systemName='usai')";
            $where_systems = " where (routes.systemName='usai')";
            $sql_command = <<<SQL
SELECT 
  waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, 
  systems.tier, systems.color, systems.systemname, routes.route, routes.banner, routes.abbrev, routes.region
FROM waypoints 
  JOIN routes ON routes.root = waypoints.root
  {$select_regions}{$select_systems}
  JOIN systems ON routes.systemname = systems.systemname AND $activeClause 
ORDER BY systems.tier DESC, root, waypoints.pointId;
SQL;
        } else {
            $sql_command = <<<SQL
SELECT 
  waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, 
  systems.tier, systems.color, systems.systemname, routes.route, routes.banner, routes.abbrev, routes.region
FROM waypoints 
  JOIN routes ON routes.root = waypoints.root $select_regions $select_systems
  JOIN systems ON routes.systemname = systems.systemname AND $activeClause
ORDER BY systems.tier DESC, root, waypoints.pointId;
SQL;
        }

        $res = tmdb_query($sql_command);

        $routenum = 0;
        $pointnum = 0;
        $lastRoute = "";
	$waypoints_array = "waypoints = [";
	$comma_after_first = "";
        while ($row = $res->fetch_assoc()) {
            if (!($row['root'] == $lastRoute)) {
                echo <<<JS
newRouteIndices[$routenum] = $pointnum;
routeInfo[$routenum] = { tier: {$row['tier']}, color: "{$row['color']}", system: "{$row['systemname']}", label: "{$row['region']} {$row['route']}{$row['banner']}{$row['abbrev']}" };\n
JS;
                $lastRoute = $row['root'];
                $routenum = $routenum + 1;
            }
	    $waypoints_array .= $comma_after_first."\nnew Waypoint(\"{$row['pointName']}\",{$row['latitude']},{$row['longitude']})";
	    $comma_after_first = ",";
            $pointnum = $pointnum + 1;
        }

	echo $waypoints_array."\n];\n";
	
        // check for query string parameter for traveler clinched mapping of route
        if (array_key_exists("u",$_GET)) {
            //echo "// select_systems: ".$select_systems."\n";
            //echo "// where_systems: ".$where_systems."\n";
            echo "traveler = '".$_GET['u']."';\n";
            // retrieve list of segments for this region or regions
            if(isset($rteClause)) {
                $sql_command = <<<SQL
SELECT 
  segments.segmentId, segments.root 
FROM segments 
  JOIN routes ON routes.root = segments.root 
  JOIN systems ON routes.systemname = systems.systemname AND $activeClause $rteClause
ORDER BY systems.tier DESC, root, segments.segmentId;
SQL;
            } else {
                $sql_command = <<<SQL
SELECT 
  segments.segmentId, segments.root 
FROM segments 
  JOIN routes ON routes.root = segments.root 
  JOIN systems ON routes.systemname = systems.systemname AND $activeClause $where_regions $select_systems
ORDER BY systems.tier DESC, root, segments.segmentId;
SQL;
            }
            $res = tmdb_query($sql_command);
            $segmentIndex = 0;
	    $comma_after_first = "";
	    echo "segments = [";
            while ($row = $res->fetch_assoc()) {
                echo $comma_after_first."\n".$row['segmentId'];
		$comma_after_first = ",";
                $segmentIndex = $segmentIndex + 1;
            }
	    echo "\n];\n";
            if(isset($rteClause)) {
                $sql_command = <<<SQL
SELECT 
  segments.segmentId, segments.root 
FROM segments 
  RIGHT JOIN clinched ON segments.segmentId = clinched.segmentId 
  JOIN routes ON routes.root = segments.root 
  JOIN systems ON routes.systemname = systems.systemname AND $activeClause $rteClause AND clinched.traveler='{$_GET['u']}'
  ORDER BY systems.tier DESC, root, segments.segmentId;
SQL;
            } else {
                $sql_command = <<<SQL
SELECT 
  segments.segmentId, segments.root 
FROM segments 
  RIGHT JOIN clinched ON segments.segmentId = clinched.segmentId 
  JOIN routes ON routes.root = segments.root 
  JOIN systems ON routes.systemname = systems.systemname AND $activeClause $where_regions $select_systems AND clinched.traveler='{$_GET['u']}'
  ORDER BY systems.tier DESC, root, segments.segmentId;
SQL;
            }
            $res = tmdb_query($sql_command);
            $segmentIndex = 0;
	    $comma_after_first = "";
	    echo "clinched = [";
            while ($row = $res->fetch_assoc()) {
                echo $comma_after_first."\n".$row['segmentId'];
		$comma_after_first = ",";
                $segmentIndex = $segmentIndex + 1;
            }
	    echo "\n];\n";
            echo "mapClinched = true;\n";
        }

        // insert custom color code if needed
        tm_generate_custom_colors_array();
	echo "mapStatus = mapStates.MAPVIEW;\n";
    }
    
    function select_single_route ()
    {
        $routeparam = strtolower($_GET['r']);

        if (array_key_exists('u', $_GET)) {
            $tmuser = $_GET['u'];
        } else {
            $tmuser = "";
        }
        
        if ($routeparam != "") {
            // select all waypoints matching the root given in the "r=" query string parameter
            $sql_command = "SELECT pointName, latitude, longitude FROM waypoints WHERE root = '" . $routeparam . "';";
            $res = tmdb_query($sql_command);
            $pointnum = 0;
            while ($row = $res->fetch_assoc()) {
                echo "waypoints[$pointnum] = new Waypoint(\"{$row['pointName']}\",{$row['latitude']},{$row['longitude']}); // line 216\n";
		if ($row['pointName'][0] === '+') {
                    $pointnum = $pointnum + 1;
		    continue;
		}
		$sql_command = "SELECT DISTINCT w.root, r.route, r.region, r.banner, r.abbrev, r.city FROM waypoints AS w LEFT JOIN routes AS r ON w.root = r.root WHERE w.latitude='".$row['latitude']."' AND w.longitude='".$row['longitude']."';";
		$res2 = tmdb_query($sql_command);
		if ($res2->num_rows > 1) {
		   echo "waypoints[$pointnum].intersecting = new Array();\n";
	           while ($match_row = $res2->fetch_assoc()) {
		       if ($match_row['root'] != $routeparam) {	 
	   	           echo "waypoints[$pointnum].intersecting.push(new Route(\"".$match_row['root']."\",\"".$match_row['route']."\",\"".$match_row['region']."\",\"".$match_row['banner']."\",\"".$match_row['abbrev']."\",\"".$match_row['city']."\"));\n";
		       }
		   }
                }
		$res2->free();
                $pointnum = $pointnum + 1;
            }
            $res->free();
        } else {
            // nothing to select waypoints, we're done
            echo "return;\n";
        }
        // check for query string parameter for traveler clinched mapping of route
        if ($tmuser != "") {
            echo "traveler = '$tmuser';\n";
            if ($routeparam != "") {
                // retrieve list of segments for this route
                echo "// SQL: select segmentId from segments where root = '" . $routeparam . "';\n";
                $sql_command = "SELECT segmentId FROM segments WHERE root = '$routeparam';";
                $res = tmdb_query($sql_command);
                $segmentIndex = 0;
		echo "segments = [";
		$comma_after_first = "";
                while ($row = $res->fetch_assoc()) {
                    echo $comma_after_first."\n".$row['segmentId'];
		    $comma_after_first = ",";
                    $segmentIndex = $segmentIndex + 1;
                }
                $res->free();
		echo "\n];\n";
                $sql_command = <<<SQL
SELECT 
  segments.segmentId 
FROM segments 
  RIGHT JOIN clinched ON segments.segmentId = clinched.segmentId 
WHERE segments.root='$routeparam' AND clinched.traveler = '$tmuser';
SQL;
                $res = tmdb_query($sql_command);
                $segmentIndex = 0;
		$comma_after_first = "";
		echo "clinched = [";
                while ($row = $res->fetch_assoc()) {
                    echo $comma_after_first."\n".$row['segmentId'];
		    $comma_after_first = ",";
                    $segmentIndex = $segmentIndex + 1;
                }
                $res->free();
		echo "\n];\n";
            }
            echo "mapClinched = true;\n";
	    echo "mapStatus = mapStates.HB_ROUTE;\n";
        }
    }
?>

function waypointsFromSQL() {
    <?php
        if (array_key_exists('r', $_GET)) {
	   select_single_route();
	}
        else {
	    select_route_set();
        }
    ?>
    genEdges = true;
}
