<?php
// read information from the TM database about the
// routes traveled by a user
//
// Author: Jim Teresco, Travel Mapping Project, January 2022
//
$params = json_decode($_POST['params'], true);

// $params has 1 field:
// traveler - user whose stats are to be included

// need to buffer and clean output since tmphpfuncs generates
// some output that breaks the JSON output
ob_start();
require "./tmphpfuncs.php";
ob_end_clean();

// initialize the array of responses
$response = array();

// array in the response for the list of routes
$response['routes'] = array();

$result = tmdb_query("select distinct route from clinched left join segments as s on clinched.segmentId = s.segmentId left join routes as r on s.root = r.root where traveler='".$params['traveler']."'");

while ($row = $result->fetch_assoc()) {
    array_push($response['routes'], $row['route']);
}
$result->free();

$tmdb->close();
echo json_encode($response);
?>
