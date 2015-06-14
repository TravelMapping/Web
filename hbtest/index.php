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
  $con = mysql_connect("localhost","travmap","clinch") or die("Failed to connect to database");
  mysql_select_db("TravelMapping", $con);

  # functions from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
  function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
  }
  function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
  }
?>
<script src="chmviewerfunc3t.js" type="text/javascript"></script>
<script>
  function waypointsFromSQL() {
  <?php
    // select all waypoints matching the root given in the "r=" query string parameter
    $sql_command = "select pointName, latitude, longitude from waypoints where root = '".$_GET['r']."';";
    $res = mysql_query($sql_command);

    $pointnum = 0;
    while ($row = mysql_fetch_array($res)) {
      echo "waypoints[".$pointnum."] = new Waypoint(\"".$row[0]."\",".$row[1].",".$row[2].");\n";
      $pointnum = $pointnum + 1;
    }

    // check for query string parameter for traveler clinched mapping of route
    if (array_key_exists("u",$_GET)) {
       echo "traveler = '".$_GET['u']."';";
       // retrieve list of segments for this route
       $sql_command = "select segmentId from segments where root = '".$_GET['r']."';";
       $res = mysql_query($sql_command);
       $segmentIndex = 0;
       while ($row = mysql_fetch_array($res)) {
         echo "segments[".$segmentIndex."] = ".$row[0].";\n";
         $segmentIndex = $segmentIndex + 1;
       }
       $sql_command = "select segments.segmentId from segments right join clinched on segments.segmentId = clinched.segmentId where segments.root='".$_GET['r']."' and clinched.traveler='".$_GET['u']."';";
       $res = mysql_query($sql_command);
       $segmentIndex = 0;
       while ($row = mysql_fetch_array($res)) {
         echo "clinched[".$segmentIndex."] = ".$row[0].";\n";
         $segmentIndex = $segmentIndex + 1;
       }
       echo "mapClinched = true;\n";
    }
  ?>
    genEdges = true;
  }
</script>
<title>Travel Mapping Highway Browser</title>
</head>

<body onload="loadmap();">
<h1>Travel Mapping Highway Browser</h1>

<div id="pointbox">
  <table class="gratable"><thead><tr><th colspan="2">Waypoints</th></tr><tr><th>Coordinates</th><th>Waypoint Name</th></tr></thead><tbody>
  <?php
    $sql_command = "select pointName, latitude, longitude from waypoints where root = '".$_GET['r']."';";
    $res = mysql_query($sql_command);
    $waypointnum = 0;
    while ($row = mysql_fetch_array($res)) {
      # only visible points should be in this table
      if (! startsWith($row[0], "+")) {
        echo "<tr><td>(".$row[1].",".$row[2].")</td><td><a onclick='javascript:LabelClick(".$waypointnum.",\"".$row[0]."\",".$row[1].",".$row[2].",0);'>".$row[0]."</a></td></tr>";
      }
      $waypointnum = $waypointnum + 1;
    }
  ?>
</table>
</div>
<div id="controlbox">
  <span id="controlboxroute">
    <?php
       $sql_command = "select region, route, banner, city from routes where root = '".$_GET['r']."';";
       $res = mysql_query($sql_command);
       $row = mysql_fetch_array($res);
       echo $row[0]." ".$row[1];
       if (strlen($row[2]) > 0) {
          echo " ".$row[2];
       }
       if (strlen($row[3]) > 0) {
          echo " (".$row[3].")";
       }
       echo ": ";
    ?>
  </span>
<span id="controlboxinfo"></span>
</div>
<div id="map">
</div>
</body>
</html>
