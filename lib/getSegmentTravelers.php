<?
// read information from the TM database about the travelers
// on a given segment by id
//
// Author: Jim Teresco, Travel Mapping Project, June 2020
//
$params = json_decode($_POST['params'], true);

// $params has 1 field:
// segmentid - the segment for which to retrieve traveler list

// need to buffer and clean output since tmphpfuncs generates
// some output that breaks the JSON output
ob_start();
require "./tmphpfuncs.php";
ob_end_clean();

// initialize the array of responses
$response = array();

// make the specified DB query 
$result = tmdb_query("select segments.segmentId, clinched.traveler from segments left join clinched on clinched.segmentId=segments.segmentId where segments.segmentId='".$params['segmentid']."';");

// parse results into the response array
while ($row = $result->fetch_assoc()) {

    array_push($response, $row['traveler']);
}

$result->free();

$tmdb->close();
echo json_encode($response);
?>
