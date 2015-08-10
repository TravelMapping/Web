<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
body, html {
  margin:0;
  border:0;
  padding:0;
  height:100%;
  max-height:100%;
  overflow: hidden;
  font-size:9pt;
  background-color:#EEEEFF;
}

#headerbox {
	position: absolute;
	top:0px;
	bottom:50px;
	width:100%;
	overflow:hidden;
	text-align:center;
	font-size:30px;
	font-family: "Times New Roman", serif;
	font-style:bold;
}

#routebox {
position: fixed;
left: 0px;
top: 50px;
bottom: 0px;
width: 100%;
overflow:auto;
}

#pointbox {
position: fixed;
left: 0px;
top: 50px;
right:400px;
bottom: 0px;
width: 400px;
overflow:auto;
}

#controlbox {
position: fixed;
top:50px;
bottom:100px;
height:100%;
left:400px;
right:0px;
overflow:auto;
padding:5px;
font-size:20px;
}

#map {
position: absolute;
top:100px;
bottom:0px;
left:400px;
right:0px;
overflow:hidden;
}

#map * {
cursor:crosshair;
}

table.nmptable {
font-size:8pt;
border: 1px solid black;
border-spacing: 0px;
margin-left: auto;
margin-right: auto;
background-color:white;
}

table.nmptable  td, th {
border: solid black;
border-width: 1px;
}

table.nmptable2 td, th {
border-width: 0px;
}

table.nmptable tr td {
text-align:right;
}

table.pthtable {
font-size:10pt;
border: 1px solid black;
border-spacing: 0px;
margin-left: auto;
margin-right: auto;
background-color:white;
}

table.pthtable  td, th {
border: solid black;
border-width: 1px;
}

table.pthtable tr td {
text-align:left;
}

table.gratable {
font-size:10pt;
border: 1px solid black;
border-spacing: 0px;
margin-left: auto;
margin-right: auto;
background-color:white;
}

table.gratable  td, th {
border: solid black;
border-width: 1px;
}

table.gratable tr td {
text-align:left;
}
</style>
<script
 src="http://maps.googleapis.com/maps/api/js?sensor=false"
  type="text/javascript"></script>

<?php
  // establish connection to db: mysql_ interface is deprecated, should learn new options
  $db = new mysqli("localhost","travmap","clinch","TravelMapping") or die("Failed to connect to database");

  # functions from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
  function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
  }
  function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
  }

  if (array_key_exists("r",$_GET)) {
    $showingmap = 1;
  }
  else {
    $showingmap = 0;
  }
?>
<script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
<script>
  function waypointsFromSQL() {
  <?php
    if (array_key_exists("r",$_GET)) {
      // select all waypoints matching the root given in the "r=" query string parameter
      $sql_command = "select pointName, latitude, longitude from waypoints where root = '".$_GET['r']."';";
      $res = $db->query($sql_command);

      $pointnum = 0;
      while ($row = $res->fetch_assoc()) {
        echo "waypoints[".$pointnum."] = new Waypoint(\"".$row['pointname']."\",".$row['latitude'].",".$row['longitude'].");\n";
        $pointnum = $pointnum + 1;
      }
      $res->free();
    }
    else {
      // nothing to select waypoints, we're done
      echo "return;\n";
    }

    // check for query string parameter for traveler clinched mapping of route
    if (array_key_exists("u",$_GET)) {
       echo "traveler = '".$_GET['u']."';\n";
       if (array_key_exists("r",$_GET)) {
         // retrieve list of segments for this route
         echo "// SQL: select segmentId from segments where root = '".$_GET['r']."';\n";
         $sql_command = "select segmentId from segments where root = '".$_GET['r']."';";
         $res = $db->query($sql_command);
         $segmentIndex = 0;
         while ($row = $res->fetch_assoc()) {
           echo "segments[".$segmentIndex."] = ".$row['segmentId'].";\n";
           $segmentIndex = $segmentIndex + 1;
         }
         $res->free();
         $sql_command = "select segments.segmentId from segments right join clinched on segments.segmentId = clinched.segmentId where segments.root='".$_GET['r']."' and clinched.traveler='".$_GET['u']."';";
         $res = $db->query($sql_command);
         $segmentIndex = 0;
         while ($row = $res->fetch_assoc()) {
           echo "clinched[".$segmentIndex."] = ".$row['segmentId'].";\n";
           $segmentIndex = $segmentIndex + 1;
         }
         $res->free();
       }
       echo "mapClinched = true;\n";
    }
  ?>
    genEdges = true;
  }
</script>
<title>Travel Mapping Highway Browser (Draft)</title>
</head>

<?php
  if ($showingmap == 0) {
    echo "<body>\n";
  }
  else {
    echo "<body onload=\"loadmap();\">\n";
  }
?>
<h1>Travel Mapping Highway Browser (Draft)</h1>

<?php
  if ($showingmap == 1) {
    echo "<div id=\"pointbox\">\n";
    echo "<table class=\"gratable\"><thead><tr><th colspan=\"2\">Waypoints</th></tr><tr><th>Coordinates</th><th>Waypoint Name</th></tr></thead><tbody>\n";

    $sql_command = "select pointName, latitude, longitude from waypoints where root = '".$_GET['r']."';";
    $res = $db->query($sql_command);
    $waypointnum = 0;
    while ($row = $res->fetch_assoc()) {
      # only visible points should be in this table
      if (! startsWith($row['pointName'], "+")) {
        echo "<tr><td>(".$row['latitude'].",".$row['longitude'].")</td><td><a onclick='javascript:LabelClick(".$waypointnum.",\"".$row['pointName']."\",".$row['latitude'].",".$row['longitude'].",0);'>".$row['pointName']."</a></td></tr>";
      }
      $waypointnum = $waypointnum + 1;
    }
    $res->free();
echo <<<ENDA
</table>
</div>
  <div id="controlbox">
    <input id="showMarkers" type="checkbox" name="Show Markers" onclick="showMarkersClicked()" checked="false">&nbsp;Show Markers
      
      <span id="controlboxroute">
ENDA;
       if (array_key_exists("r",$_GET)) {
         $sql_command = "select region, route, banner, city from routes where root = '".$_GET['r']."';";
         $res = $db->query($sql_command);
         $row = $res->fetch_assoc();
         echo $row['region']." ".$row['route'];
         if (strlen($row['banner']) > 0) {
            echo " ".$row['banner'];
         }
         if (strlen($row['city']) > 0) {
            echo " (".$row['city'].")";
         }
         echo ": ";
         $res->free();
       }
echo <<<ENDB
  </span>
<span id="controlboxinfo"></span>
</div>
<div id="map">
</div>
ENDB;
  }
  else {  // we have no r=, so we will show a list of all
    echo "<div id=\"routebox\">\n";
    echo "<table class=\"gratable\"><thead><tr><th colspan=\"4\">Select Route to Display</th></tr><tr><th>System</th><th>Region</th><th>Route Name</th><th>Root</th></tr></thead><tbody>\n";
    $sql_command = "select * from routes;";
    $res = $db->query($sql_command);
    while ($row = $res->fetch_assoc()) {
      echo "<tr><td>".$row['systemName']."</td><td>".$row['region']."</td><td>".$row['route'].$row['banner'];
      if (strcmp($row['city'],"") != 0) {
        echo " (".$row['city'].")";
      }
      echo "</td><td><a href=\"hb.php?r=".$row['root']."\">".$row['root']."</a></td></tr>\n";
    }
    $res->free();
    echo "</table></div>\n";
}
  $db->close();
?>
</body>
</html>
