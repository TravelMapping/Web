<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 ***
 * Highway Browser Route Lookup Page
 * URL Params:
 *  u - user to display highlighting for on map (optional)
 *  rg - region to filter for on the highway browser list (optional)
 *  sys - system to filter for on the highway browser list (optional)
 *  ([r [u] [lat lon zoom]] [rg] [sys])
 ***
 -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
    <link rel="shortcut icon" type="image/png" href="/favicon.png">
    <style type="text/css">
        #routebox {
            position: fixed;
            left: 0px;
            top: 110px;
            bottom: 0px;
            width: 100%;
            overflow: auto;
        }

        #routeInfo td {
            text-align: right;
        }

	.status-active {
	    background-color: #CCFFCC;
	}

	.status-preview {
	    background-color: #FFFFCC;
	}
	
	.status-devel {
	    background-color: #FFCCCC;
	}

    </style>
    <?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
    <?php
    // check for region and/or system parameters
    $regions = tm_qs_multi_or_comma_to_array("rg");
    if (count($regions) > 0) {
        $region = $regions[0];
        $regionName = tm_region_code_to_name($region);
    }
    else {
        $region = "";
        $regionName = "No Region Specified";
    }

    $systems = tm_qs_multi_or_comma_to_array("sys");
    if (count($systems) > 0) {
        $system = $systems[0];
        $systemName = tm_system_code_to_name($system);
    }
    else {
        $system = "";
        $systemName = "No System Specified";
    }
    ?>
    <?php tm_common_js(); ?>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
    <script src="../lib/findroutefuncs.js" type="text/javascript"></script>
    <script type="text/javascript">
var findrouteSystems = [];
    <?php
    // read info about systems to reduce later transfer
    $result = tmdb_query("SELECT systemName, fullName, tier, level, color from systems;");
    while ($row = $result->fetch_assoc()) {
       echo "findrouteSystems['".$row['systemName']."'] = { name: \"".$row['fullName']."\", tier: ".$row['tier'].", level: '".$row['level']."', color: '".$row['color']."' };\n";
    }
    $result->free();
    ?>
    </script>
    <title>Travel Mapping Route Finder</title>
</head>
<?php
echo "<body onload=\"findrouteStartup('".$system."','".$region."');\">";
$nobigheader = 1;
require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php";
?>
<h1>Travel Mapping Route Finder</h1>
<p class="text" id="frIntro">
Use the table below to find routes from among the
<?php $r = number_format(tm_count_rows("routes", "")); echo $r; ?> routes in TM's database.
You can filter by most categories.  To filter by route name, type a
substring of its name in the box below "Route Name" then hit Enter.
Initial loading may take several seconds to a few minutes depending on
the server and network conditions and your browser's capabilities.
Table updates after changing the filters can also take a few seconds.
Links under ".list Name" will take you to the map and other details of that
route.
<?php tm_dismiss_button("frIntro"); ?>
</p>
<div id="choppedbox">
<table class="sortable gratable" id="chopped">
<thead>
<tr><th colspan="8">Select Route to Display, Select Filters,
<input type="button" onclick="clearChoppedFilters();" value="Clear Filters" />
, or Click on a Column Header to Sort</th></tr>
<tr><th colspan="8" style="color:red; text-align:center;" id="chopmessage">Loading Route Data...</th></tr>
<tr><th>Tier</th><th>Continent</th><th>Country</th><th>Region</th><th>System</th><th>Route&nbsp;Name</th><th>.list Name</th><th>Level</th></tr>
<tr id="chopselectrow" style="display: none"><th>
<select id="choptier" name="choptier" onchange="filterChopped();">
<option value="any" selected>Any</option>
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
</select>
</th><th>
<select id="chopcontinent" name="chopcontinent" onchange="filterChopped();">
<option selected>Any</option>
</select>
</th><th>
<select id="chopcountry" name="chopcountry" onchange="filterChopped();">
<option selected>Any</option>
</select>
</th><th>
<select id="chopregion" name="chopregion" onchange="filterChopped();">
<option selected>Any</option>
</select>
</th><th>
<select id="chopsys" name="chopsys" onchange="filterChopped();">
<option selected>Any</option>
</select>
</th><th>
<input id="choppattern" name="choppattern" type="text" size="20" maxlength="20" onkeypress="filterChoppedIfEnter(event);" />
</th><th></th><th>
<select id="choplevel" name="choplevel" onchange="filterChopped();">
<option value="apd">All</option>
<option value="ap" selected>Active+Preview</option>
<option value="a">Active Only</option>
<option value="p">Preview Only</option>
<option value="d">Devel Only</option>
</select>
</th></tr>
</thead>
<tbody id="chopboxtbody">
</tbody>
</table>
</div>
<?php $tmdb->close(); ?>
</body>
</html>
