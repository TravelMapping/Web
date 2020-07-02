<?
// read information from the TM database about the
// routes for the HB findroute page
//
// Author: Jim Teresco, Travel Mapping Project, June 2020
//
// need to buffer and clean output since tmphpfuncs generates
// some output that breaks the JSON output
ob_start('ob_gzhandler');
require "./tmphpfuncs.php";
ob_end_clean();

// initialize the array of responses
$response = array();

// gather info about each chopped route
$response['listNames'] = array();
$response['systems'] = array();
$response['routeNames'] = array();
$response['regions'] = array();
$response['countries'] = array();
$response['continents'] = array();
$response['roots'] = array();

$result = tmdb_query("select route, banner, abbrev, city, region, systemName, regions.country, regions.continent, root from routes left join regions on routes.region=regions.code order by csvOrder");
while ($row = $result->fetch_assoc()) {
    $routeName = $row['route'].$row['banner'].$row['abbrev'];
    if ($row['city'] != "") {
        $routeName .= " (".$row['city'].")";
    }
    $listName = $row['region']." ".$row['route'].$row['banner'].$row['abbrev'];
    array_push($response['listNames'], $listName);
    array_push($response['systems'], $row['systemName']);
    array_push($response['routeNames'], $routeName);
    array_push($response['regions'], $row['region']);
    array_push($response['countries'], $row['country']);
    array_push($response['continents'], $row['continent']);
    array_push($response['roots'], $row['root']);
}
$result->free();

$tmdb->close();
echo json_encode($response);
?>
