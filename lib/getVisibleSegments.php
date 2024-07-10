<?php
// read information from the TM database about all segments
// that have at least one waypoint endpoint within the
// latitude/longitude bounds given, and then information about
// each route that those segments are parts of, with a traveler name
// to check if that person has traveled each segment
//
$params = json_decode($_POST['params'], true);

// $params has 5 fields: minLat, maxLat, minLng, maxLng that specify
// those bounds, traveler to specify the traveler name

// need to buffer and clean output since tmphpfuncs generates
// some output that breaks the JSON output
ob_start();
require "./tmphpfuncs.php";
ob_end_clean();

// initialize the array of responses
$response = array('roots'=>array(),
		  'segmentids'=>array(),
		  'w1name'=>array(),
		  'w1lat'=>array(),
		  'w1lng'=>array(),
		  'w2name'=>array(),
		  'w2lat'=>array(),
		  'w2lng'=>array(),
		  'clinched'=>array(),
		  'travelers'=>array(),
		  'routeroots'=>array(),
		  'routelistnames'=>array(),
		  'routemileages'=>array(),
		  'routeclinchedmileages'=>array(),
		  'routeclinched'=>array(),
		  'routecolors'=>array(),
		  'routesystemnames'=>array(),
		  'routesystemcodes'=>array(),
		  'routelevels'=>array(),
		  'routetiers'=>array()
		  );

// first DB query to get all waypoints in the bounding area
$result = tmdb_query("select pointId from waypoints where latitude>".$params['minLat']." and latitude<".$params['maxLat']." and longitude<".$params['maxLng']." and longitude>".$params['minLng']);

$waypoints = array();
while ($row = $result->fetch_assoc()) {
    array_push($waypoints, $row['pointId']);
}

$result->free();

// No waypoints?  No results.
if (count($waypoints) == 0) {
    $tmdb->close();
    echo json_encode($response);
    return;
}

// make DB query for all segments with at least one waypoint in
// the bounding area
// if the number of waypoints is reasonably small, we will use it to restrict segments,
// otherwise we will search segments for endpoint coordinates
// NOTE: should do some experiments to see what this threshold should be
if (count($waypoints) < 500) {
    $result = tmdb_query("select segments.root, segments.segmentId, COUNT(CASE WHEN le.includeInRanks = 1 THEN acl.segmentId ELSE NULL END) AS travelers, if (cl.segmentId is null, false, true) as clinched, w1.pointName as w1name, w1.latitude as w1lat, w1.longitude as w1lng, w2.pointName as w2name, w2.latitude as w2lat, w2.longitude as w2lng from segments join waypoints as w1 on segments.waypoint1=w1.pointId join waypoints as w2 on segments.waypoint2=w2.pointId left join clinched as cl on (cl.segmentId=segments.segmentId and cl.traveler='".$params['traveler']."') left join clinched as acl on acl.segmentId=segments.segmentId LEFT JOIN listEntries AS le ON acl.traveler = le.traveler where ((w1.pointId in ('".implode("','",$waypoints)."')) or (w2.pointId in ('".implode("','",$waypoints)."'))) group by segments.segmentId order by segments.segmentId, segments.root;");
}
else {
    $result = tmdb_query("select segments.root, segments.segmentId, COUNT(CASE WHEN le.includeInRanks = 1 THEN acl.segmentId ELSE NULL END) AS travelers, if (cl.segmentId is null, false, true) as clinched, w1.pointName as w1name, w1.latitude as w1lat, w1.longitude as w1lng, w2.pointName as w2name, w2.latitude as w2lat, w2.longitude as w2lng from segments join waypoints as w1 on segments.waypoint1=w1.pointId join waypoints as w2 on segments.waypoint2=w2.pointId left join clinched as cl on (cl.segmentId=segments.segmentId and cl.traveler='".$params['traveler']."') left join clinched as acl on acl.segmentId=segments.segmentId LEFT JOIN listEntries AS le ON acl.traveler = le.traveler where ((w1.latitude>".$params['minLat']." and w1.latitude<".$params['maxLat']." and w1.longitude<".$params['maxLng']." and w1.longitude>".$params['minLng'].") or (w2.latitude>".$params['minLat']." and w2.latitude<".$params['maxLat']." and w2.longitude<".$params['maxLng']." and w2.longitude>".$params['minLng'].")) group by segments.segmentId order by segments.segmentId, segments.root;");
}

// parse results into the response array
while ($row = $result->fetch_assoc()) {

    array_push($response['roots'], $row['root']);
    array_push($response['segmentids'], $row['segmentId']);
    array_push($response['w1name'], $row['w1name']);
    array_push($response['w1lat'], $row['w1lat']);
    array_push($response['w1lng'], $row['w1lng']);
    array_push($response['w2name'], $row['w2name']);
    array_push($response['w2lat'], $row['w2lat']);
    array_push($response['w2lng'], $row['w2lng']);
    array_push($response['clinched'], $row['clinched']);
    array_push($response['travelers'], $row['travelers']);
}

$result->free();

// build a query to get information about all of the routes
$result = tmdb_query("select r.root, r.region, r.route, r.banner, r.abbrev, r.city, round(r.mileage,4) as mileage, COALESCE(round(cr.mileage,4), 0) as clinchedmileage, cr.clinched, s.color, s.tier, s.systemName, s.fullName, s.level from routes as r join systems as s on r.systemName=s.systemName LEFT JOIN clinchedRoutes AS cr ON r.root = cr.route AND traveler='".$params['traveler']."' where r.root in ('".implode("','",array_unique($response['roots']))."') order by s.tier, r.csvOrder;");

// parse results into the response array
while ($row = $result->fetch_assoc()) {
      array_push($response['routeroots'], $row['root']);
      array_push($response['routelistnames'], $row['region']." ".$row['route'].$row['banner'].$row['abbrev']);
      array_push($response['routemileages'], $row['mileage']);
      array_push($response['routeclinchedmileages'], $row['clinchedmileage']);
      array_push($response['routeclinched'], $row['clinched']);
      array_push($response['routecolors'], $row['color']);
      array_push($response['routesystemcodes'], $row['systemName']);
      array_push($response['routesystemnames'], $row['fullName']);
      array_push($response['routelevels'], $row['level']);
      array_push($response['routetiers'], $row['tier']);
}

$tmdb->close();
echo json_encode($response);
?>
