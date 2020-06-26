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

// for levels lookup
$levels = array('devel' => 0, 'preview' => 1, 'active' => 2);

// gather info about each chopped route
$response['listNames'] = array();
$response['tiers'] = array();
$response['systems'] = array();
$response['systemNames'] = array();
$response['levels'] = array();
$response['routeNames'] = array();
$response['regions'] = array();
$response['countries'] = array();
$response['roots'] = array();

$result = tmdb_query("select route, banner, abbrev, city, region, systems.tier, systems.systemName, systems.fullName, systems.level, regions.country, root from routes left join systems on systems.systemName=routes.systemName left join regions on routes.region=regions.code");
while ($row = $result->fetch_assoc()) {
    $routeName = $row['route'].$row['banner'].$row['abbrev'];
    if ($row['city'] != "") {
        $routeName .= " (".$row['city'].")";
    }
    $listName = $row['region']." ".$row['route'].$row['banner'].$row['abbrev'];
    array_push($response['listNames'], $listName);
    array_push($response['tiers'], $row['tier']);
    array_push($response['systems'], $row['systemName']);
    //array_push($response['systemNames'], $row['fullName']);
    array_push($response['levels'], $levels[$row['level']]);
    array_push($response['routeNames'], $routeName);
    array_push($response['regions'], $row['region']);
    array_push($response['countries'], $row['country']);
    array_push($response['roots'], $row['root']);
}
$result->free();

$tmdb->close();
echo json_encode($response);
?>
