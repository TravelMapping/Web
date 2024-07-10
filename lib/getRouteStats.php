<?php
// read information from the TM database about the
// routes for the HB showroutes Route Stats table:
// mileage and traveler stat info
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
$response['mileage'] = array();
$response['listNames'] = array();
$response['numDrivers'] = array();
$response['numClinched'] = array();
$response['drivers'] = array();
$response['clinchers'] = array();
$response['avgMileage'] = array();
$response['clinchedMileage'] = array();
foreach ($roots as $root) {
    $result = tmdb_query("SELECT ROUND(mileage,4) as mileage, region, route, abbrev, banner FROM routes WHERE root='".$root."'");
    $row = $result->fetch_assoc();
    array_push($response['mileage'], $row['mileage']);
    $listName = $row['region']." ".$row['route'].$row['banner'].$row['abbrev'];
    array_push($response['listNames'], $listName);
    $result->free();

    $sql_command = <<<SQL
SELECT
    COUNT(*) as numDrivers,
    IFNULL(SUM(cr.clinched), 0) as numClinched,
    GROUP_CONCAT(cr.traveler SEPARATOR ',') as drivers,
    GROUP_CONCAT(IF(cr.clinched = 1, cr.traveler, null) SEPARATOR ',') as clinchers,
    ROUND(AVG(cr.mileage), 4) as avgMileage
FROM 
    clinchedRoutes cr
LEFT JOIN 
    listEntries le ON cr.traveler = le.traveler
WHERE 
    cr.route = '$root'
    AND le.includeInRanks = 1;
SQL;
    $result = tmdb_query($sql_command);
    $row = $result->fetch_assoc();
    array_push($response['numDrivers'], $row['numDrivers']);
    array_push($response['numClinched'], $row['numClinched']);
    array_push($response['drivers'], $row['drivers']);
    array_push($response['clinchers'], $row['clinchers']);
    array_push($response['avgMileage'], $row['avgMileage']);
    $result->free();
    
    if ($params['traveler'] != null) {
        $sql_command = "SELECT round(mileage,4) as mileage FROM clinchedRoutes where traveler='".$params['traveler']."' AND route='".$root."'";
        $result = tmdb_query($sql_command);
        $row = $result->fetch_assoc();
	if ($row != null) {
            array_push($response['clinchedMileage'], $row['mileage']);
	}
	else {
	    array_push($response['clinchedMileage'], "0.0");
	}
	$result->free();
    }
}

$tmdb->close();
echo json_encode($response);
?>
