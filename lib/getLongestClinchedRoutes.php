<?
// read information from the TM database about the top
// longest clinched routes for a given user, alternately
// restricted to a region or system
//
// Author: Jim Teresco, Travel Mapping Project, May 2020
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
ob_end_clean();

// initialize the array of responses
$response = array('routes'=>array(),
		  'mileages'=>array()
		  );

// build the SQL query
$sql = "select r.root, r.mileage from routes as r left join clinchedRoutes as cr on r.root=cr.route and traveler='".$params['traveler']."'";

$morejoins = "";
if ($params['system'] != "null") {
    $morejoins = " join systems as s on s.systemName=r.systemName";
}
if ($params['region'] != "null") {
    $morejoins .= " join regions as rg on rg.code=r.region";
}
$sql = $sql.$morejoins." where cr.clinched='1'";
$morewhere = "";
if ($params['system'] != "null") {
    $morewhere = " and s.systemName='".$params['system']."'";
}
if ($params['region'] != "null") {
    $morewhere .= " and rg.code='".$params['region']."'";
}
$sql = $sql.$morewhere."  order by r.mileage desc limit ".$params['numentries'];

// make the specified DB query 
$result = tmdb_query($sql);

// parse results into the response array
while ($row = $result->fetch_assoc()) {

    array_push($response['routes'], $row['root']);
    array_push($response['mileages'], $row['mileage']);
}

$result->free();

$tmdb->close();
echo json_encode($response);
?>
