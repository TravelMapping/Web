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
$result = tmdb_query("SELECT pointId
FROM waypoints
WHERE latitude BETWEEN {$params['minLat']} AND {$params['maxLat']}
  AND longitude BETWEEN {$params['minLng']} AND {$params['maxLng']};");

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
// the bounding area, with efficiency improved with ChatGPT input
$sql_command = <<<SQL
WITH
filtered_w1 AS (
  SELECT pointId, pointName, latitude, longitude
  FROM waypoints
  WHERE latitude BETWEEN {$params['minLat']} AND {$params['maxLat']}
    AND longitude BETWEEN {$params['minLng']} AND {$params['maxLng']}
),
filtered_w2 AS (
  SELECT pointId, pointName, latitude, longitude
  FROM waypoints
  WHERE latitude BETWEEN {$params['minLat']} AND {$params['maxLat']}
    AND longitude BETWEEN {$params['minLng']} AND {$params['maxLng']}
)

-- First half: w1 in bounding box
SELECT
  segments.root,
  segments.segmentId,
  COUNT(CASE WHEN le.includeInRanks = 1 THEN acl.segmentId ELSE NULL END) AS travelers,
  IF(cl.segmentId IS NULL, false, true) AS clinched,
  w1.pointName AS w1name, w1.latitude AS w1lat, w1.longitude AS w1lng,
  w2.pointName AS w2name, w2.latitude AS w2lat, w2.longitude AS w2lng
FROM segments
JOIN filtered_w1 w1 ON segments.waypoint1 = w1.pointId
JOIN waypoints w2 ON segments.waypoint2 = w2.pointId
LEFT JOIN clinched cl ON cl.segmentId = segments.segmentId AND cl.traveler = '{$params['traveler']}'
LEFT JOIN clinched acl ON acl.segmentId = segments.segmentId
LEFT JOIN listEntries le ON acl.traveler = le.traveler
GROUP BY segments.segmentId

UNION

-- Second half: w2 in bounding box
SELECT
  segments.root,
  segments.segmentId,
  COUNT(CASE WHEN le.includeInRanks = 1 THEN acl.segmentId ELSE NULL END) AS travelers,
  IF(cl.segmentId IS NULL, false, true) AS clinched,
  w1.pointName AS w1name, w1.latitude AS w1lat, w1.longitude AS w1lng,
  w2.pointName AS w2name, w2.latitude AS w2lat, w2.longitude AS w2lng
FROM segments
JOIN waypoints w1 ON segments.waypoint1 = w1.pointId
JOIN filtered_w2 w2 ON segments.waypoint2 = w2.pointId
LEFT JOIN clinched cl ON cl.segmentId = segments.segmentId AND cl.traveler = '{$params['traveler']}'
LEFT JOIN clinched acl ON acl.segmentId = segments.segmentId
LEFT JOIN listEntries le ON acl.traveler = le.traveler
GROUP BY segments.segmentId

ORDER BY segmentId, root;
SQL;

$result = tmdb_query($sql_command);

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
