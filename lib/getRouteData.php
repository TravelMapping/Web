<?
// read information from the TM database about the
// routes for the HB showroutes Route Stats table:
// waypoints and traveled info for connections
//
// Author: Jim Teresco, Travel Mapping Project, June 2020
//
$params = json_decode($_POST['params'], true);

// $params has 2 fields:
// roots - array of TM chopped route roots (e.g., ny.i090)
// traveler - user whose stats are to be included

// note that roots are in connected-route order for a connected route with
// multiple chopped routes, will be a single route for a chopped route
// or a connected route with just one chopped route

// need to buffer and clean output since tmphpfuncs generates
// some output that breaks the JSON output
ob_start();
require "./tmphpfuncs.php";
ob_end_clean();

// initialize the array of responses
$response = array();

$roots = $params['roots'];

// total number of users
$result = tmdb_query("SELECT COUNT(DISTINCT traveler) as numUsers FROM clinchedOverallMileageByRegion");
$response['numUsers'] = $result->fetch_assoc()['numUsers'];
$result->free();

// gather info about each chopped route
$response['traveler'] = $params['traveler'];
$response['listNames'] = array();
$response['pointNames'] = array();
$response['latitudes'] = array();
$response['longitudes'] = array();
$response['driverCounts'] = array();
$response['segmentIds'] = array();
$response['clinched'] = array();
$response['intersects'] = array();
foreach ($roots as $root) {

    $result = tmdb_query("SELECT region, route, abbrev, banner FROM routes WHERE root='".$root."'");
    $row = $result->fetch_assoc();
    $listName = $row['region']." ".$row['route'].$row['banner'].$row['abbrev'];
    array_push($response['listNames'], $listName);
    $result->free();

    $rootPointNames = array();
    $rootLatitudes = array();
    $rootLongitudes = array();
    $rootDriverCounts = array();
    $rootSegmentIds = array();
    $rootClinched = array();
    $rootIntersects = array();
    $sql_command = <<<SQL
        SELECT pointName, latitude, longitude, driverCount, segmentId
        FROM waypoints
        LEFT JOIN (
            SELECT
              waypoints.pointId,
              sum(!ISNULL(clinched.traveler)) as driverCount,
              segments.segmentId
            FROM segments
            LEFT JOIN clinched ON segments.segmentId = clinched.segmentId
            LEFT JOIN waypoints ON segments.waypoint1 = waypoints.pointId
            WHERE segments.root = '$root'
            GROUP BY segments.segmentId
        ) as pointStats on pointStats.pointId = waypoints.pointId
        WHERE root = '$root';
SQL;
    $result = tmdb_query($sql_command);
    while ($row = $result->fetch_assoc()) {
        array_push($rootPointNames, $row['pointName']);
        array_push($rootLatitudes, $row['latitude']);
        array_push($rootLongitudes, $row['longitude']);
        array_push($rootDriverCounts, $row['driverCount']);
        array_push($rootSegmentIds, $row['segmentId']);
	// an additional query to see if the traveler has clinched this segment
	array_push($rootClinched,
	    tm_count_rows("clinched", "WHERE traveler='".$params['traveler']."' AND segmentId='".$row['segmentId']."'"));
	// check for intersecting routes, but skip for hidden points as they
	// will not be given Markers on the map anyway
	if ($row['pointName'][0] != '+') {
	    $iresult = tmdb_query("SELECT DISTINCT w.root, r.route, r.region, r.banner, r.abbrev FROM waypoints AS w LEFT JOIN routes AS r ON w.root = r.root WHERE w.latitude='".$row['latitude']."' AND w.longitude='".$row['longitude']."';");
	    if ($iresult->num_rows > 1) {
	        $myIntersects = array();
	        while ($match_row = $iresult->fetch_assoc()) {
		    if ($match_row['root'] != $root) {
		        $matchListName = $match_row['region']." ".$match_row['route'].$match_row['banner'].$match_row['abbrev'];
		        array_push($myIntersects, array($match_row['root'],
						        $matchListName));
                    }
                }
	        array_push($rootIntersects, $myIntersects);
	    }
	    else {
	        array_push($rootIntersects, null);
	    }
	    $iresult->free();
	}
	else {
	    array_push($rootIntersects, null);
	}
    }
    $result->free();

    array_push($response['pointNames'], $rootPointNames);
    array_push($response['latitudes'], $rootLatitudes);
    array_push($response['longitudes'], $rootLongitudes);
    array_push($response['driverCounts'], $rootDriverCounts);
    array_push($response['segmentIds'], $rootSegmentIds);
    array_push($response['clinched'], $rootClinched);
    array_push($response['intersects'], $rootIntersects);
}
$tmdb->close();
echo json_encode($response);
?>
