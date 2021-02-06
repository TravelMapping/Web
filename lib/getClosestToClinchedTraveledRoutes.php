<?php
// read information from the TM database about the
// traveled routes closest yo clinched for a given user, alternately
// restricted to a region or system
//
// Author: Jim Teresco, Travel Mapping Project, June 2020
//
$params = json_decode($_POST['params'], true);

// $params has 4 fields:
// traveler - user whose stats are being queried
// system - system to restrict, NONE otherwise
// region - region to restrict, NONE otherwise
// numentries - number of top entries to return

// need to buffer and clean output since tmphpfuncs generates
// some output that breaks the JSON output
ob_start();
require "./tmphpfuncs.php";
require "../shields/shieldgen.php";
ob_end_clean();

// initialize the array of responses
$response = array();

// build the SQL query
$sql = "select r.root, r.region, r.route, r.city, r.abbrev, r.banner, round(cr.mileage,4) as traveled, round(r.mileage,4) as mileage, (r.mileage - cr.mileage) as missing from routes as r left join clinchedRoutes as cr on r.root=cr.route and traveler='".$params['traveler']."' join systems as s on s.systemName=r.systemName";

$morejoins = "";
if ($params['region'] != "null") {
    $morejoins .= " join regions as rg on rg.code=r.region";
}
$sql = $sql.$morejoins." where cr.mileage > 0 and (r.mileage - cr.mileage) > 0";
$morewhere = "";
if ($params['preview']) {
    $morewhere .= " and (s.level='active' or s.level='preview')";
}
else {
    $morewhere .= " and s.level='active'";
}
if ($params['system'] != "null") {
    $morewhere .= " and s.systemName='".$params['system']."'";
}
if ($params['region'] != "null") {
    $morewhere .= " and rg.code='".$params['region']."'";
}
$sql = $sql.$morewhere." order by missing limit ".$params['numentries'];

// make the specified DB query 
$result = tmdb_query($sql);

// parse results into the response array
while ($row = $result->fetch_assoc()) {

    if ($row['traveled'] > 0) {
        $nextobj = new stdClass();
        $nextobj->root = $row['root'];
	$nextobj->shield = tm_shield_generate($row['root']);
        $nextobj->routeinfo = $row['region']." ".$row['route'].$row['banner'];
        if ($row['city'] != "") {
            $nextobj->routeinfo .= " (".$row['city'].")";
        }
        $nextobj->missing = $row['missing'];
        $nextobj->mileage = $row['mileage'];
        array_push($response, $nextobj);
    }
}

$result->free();

$tmdb->close();
echo json_encode($response);
?>
