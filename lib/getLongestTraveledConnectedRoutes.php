<?php
// read information from the TM database about the top
// longest clinched connected routes for a given user, alternately
// restricted to a system
//
// Author: Jim Teresco, Travel Mapping Project, May 2020
//
$params = json_decode($_POST['params'], true);

// $params has 4 fields:
// traveler - user whose stats are being queried
// system - system to restrict, NONE otherwise
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
$sql = "select r.firstRoot, r.route, r.banner, r.groupName, round(r.mileage,4) as mileage, round(cr.mileage,4) as traveled from connectedRoutes as r left join clinchedConnectedRoutes as cr on r.firstRoot=cr.route and traveler='".$params['traveler']."' join systems as s on s.systemName=r.systemName where cr.mileage > 0";

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
$sql = $sql.$morewhere."  order by traveled desc limit ".$params['numentries'];

// make the specified DB query 
$result = tmdb_query($sql);

// parse results into the response array
while ($row = $result->fetch_assoc()) {

    $nextobj = new stdClass();
    $nextobj->root = $row['firstRoot'];
    $nextobj->routeonly = $row['route'];
    $nextobj->routeinfo = $row['route'].$row['banner'];
    $nextobj->shield = tm_shield_generate($row['firstRoot']);
    if ($row['groupName'] != "") {
        $nextobj->routeinfo .= " (".$row['groupName'].")";
    }
    $nextobj->traveled = $row['traveled'];
    $nextobj->mileage = $row['mileage'];
    array_push($response, $nextobj);
}

$result->free();

$tmdb->close();
echo json_encode($response);
?>
