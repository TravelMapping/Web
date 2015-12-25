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

#statsbox {
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
left:0px;
right:0px;
overflow:auto;
padding:5px;
font-size:20px;
}

#map {
position: absolute;
top:100px;
bottom:0px;
width:100%;
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
  if (array_key_exists("u",$_GET)) {
    setcookie("lastuser", $user, time() + (86400 * 30), "/")
  } else if (isset($_COOKIE['lastuser'])) {
    $_GET['u'] = $_COOKIE['lastuser'];
  }

  $dbname = "TravelMapping";
  if(isset($_COOKIE['currentdb')) {
    $dbname = $_COOKIE['currentdb');
  }

  if (array_key_exists("db",$_GET)) {
    $dbname = $_GET['db'];
    setcookie("currentdb", $dbname, time() + (86400 * 30), "/");
  }

  // establish connection to db: mysql_ interface is deprecated, should learn new options
  $con = mysql_connect("localhost","travmap","clinch") or die("Failed to connect to database");
  mysql_select_db($dbname, $con);

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
<script src="chmviewerfunc3.js" type="text/javascript"></script>
<script>
  function waypointsFromSQL() {
  <?php
    $regions = array();
    if (array_key_exists("rg",$_GET)) {
      $regions = explode(',',$_GET['rg']);
    }

    // restrict to waypoints matching routes whose region is given in the "rg=" query string parameter
    // build strings needed for later queries
    $select_regions = "";
    $where_regions = "";
    $num_regions = 0;
    foreach ($regions as $region) {
      if ($num_regions == 0) {
        $select_regions = " and (routes.region='".$region."'";
        $where_regions = " where (region='".$region."'";
      }
      else {
        $select_regions = $select_regions." or routes.region='".$region."'";
        $where_regions = $where_regions." or region='".$region."'";
      }
      $num_regions = $num_regions + 1;
    }
    if ($num_regions > 0) {
      $select_regions = $select_regions.")";
      $where_regions = $where_regions.")";
    }

    // select based on system?
    $systems = array();
    if (array_key_exists("sys",$_GET)) {
      $systems = explode(',',$_GET['sys']);
    }

    // restrict to waypoints matching routes whose system is given in the "sys=" query string parameter
    $select_systems = "";
    $where_systems = "";
    $num_systems = 0;
    foreach ($systems as $system) {
      if ($num_systems == 0) {
        $select_systems = " and (routes.systemName='".$system."'";
        $where_systems = " where (routes.systemName='".$system."'";
      }
      else {
        $select_systems = $select_systems." or routes.systemName='".$system."'";
        $where_systems = $where_systems." or routes.systemName='".$system."'";
      }
      $num_systems = $num_systems + 1;
    }
    if ($num_systems > 0) {
      $select_systems = $select_systems.")";
      $where_systems = $where_systems.")";
    }

    // make sure we have selected some region or system
    if (($num_systems == 0) && ($num_regions == 0)) {
      if (array_key_exists("rte", $_GET)) {
        $rteClause = " where (routes.route = '".$_GET['rte']."')";
        $sql_command = "select waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, systems.tier, systems.color, systems.systemname from waypoints join routes on routes.root = waypoints.root join systems on routes.systemname = systems.systemname and systems.active='1' ".$rteClause." order by root, waypoints.pointId;";
      } else {
       // for now, put in a default to usai, do something better later
       $select_systems = " and (routes.systemName='usai')";
       $where_systems = " where (routes.systemName='usai')";
       $sql_command = "select waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, systems.tier, systems.color, systems.systemname from waypoints join routes on routes.root = waypoints.root".$select_regions.$select_systems." join systems on routes.systemname = systems.systemname and systems.active='1' order by root, waypoints.pointId;";
      }
    } else {
      $sql_command = "select waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, systems.tier, systems.color, systems.systemname from waypoints join routes on routes.root = waypoints.root".$select_regions.$select_systems." join systems on routes.systemname = systems.systemname and systems.active='1' order by root, waypoints.pointId;";
    }

    echo "// SQL: ".$sql_command."\n";
    $res = mysql_query($sql_command);

    $routenum = 0;
    $pointnum = 0;
    $lastRoute = "";
    while ($row = mysql_fetch_array($res)) {
      if (!($row[3] == $lastRoute)) {
         echo "newRouteIndices[".$routenum."] = ".$pointnum.";\n";
         echo "routeTier[".$routenum."] = ".$row[4].";\n";
         echo "routeColor[".$routenum."] = '".$row[5]."';\n";
         echo "routeSystem[".$routenum."] = '".$row[6]."';\n";
         $lastRoute = $row[3];
         $routenum = $routenum + 1;
      }
      echo "waypoints[".$pointnum."] = new Waypoint(\"".$row[0]."\",".$row[1].",".$row[2]."); // Route = ".$row[3]." (".$row[5].")\n";
      $pointnum = $pointnum + 1;
    }

    // check for query string parameter for traveler clinched mapping of route
    if (array_key_exists("u",$_GET)) {
       echo "// select_systems: ".$select_systems."\n";
       echo "// where_systems: ".$where_systems."\n";
       echo "traveler = '".$_GET['u']."';\n";
       // retrieve list of segments for this region or regions
       if(isset($rteClause)) {
        $sql_command = "select segments.segmentId, segments.root from segments join routes on routes.root = segments.root join systems on routes.systemname = systems.systemname and systems.active='1'".$rteClause." order by root, segments.segmentId;"; 
       } else {
        $sql_command = "select segments.segmentId, segments.root from segments join routes on routes.root = segments.root join systems on routes.systemname = systems.systemname and systems.active='1'".$where_regions.$select_systems." order by root, segments.segmentId;";
       }
       echo "// SQL: ".$sql_command."\n";
       $res = mysql_query($sql_command);
       $segmentIndex = 0;
       while ($row = mysql_fetch_array($res)) {
         echo "segments[".$segmentIndex."] = ".$row[0]."; // route=".$row[1]."\n";
         $segmentIndex = $segmentIndex + 1;
       }
       if(isset($rteClause)) {
        $sql_command = "select segments.segmentId, segments.root from segments right join clinched on segments.segmentId = clinched.segmentId join routes on routes.root = segments.root join systems on routes.systemname = systems.systemname and systems.active='1'".$rteClause." and clinched.traveler='".$_GET['u']."' order by root, segments.segmentId;";
       } else {
        $sql_command = "select segments.segmentId, segments.root from segments right join clinched on segments.segmentId = clinched.segmentId join routes on routes.root = segments.root join systems on routes.systemname = systems.systemname and systems.active='1'".$where_regions.$select_systems." and clinched.traveler='".$_GET['u']."' order by root, segments.segmentId;";
       }
       echo "// SQL: " .$sql_command."\n";
       $res = mysql_query($sql_command);
       $segmentIndex = 0;
       while ($row = mysql_fetch_array($res)) {
         echo "clinched[".$segmentIndex."] = ".$row[0]."; // route=".$row[1]."\n";
         $segmentIndex = $segmentIndex + 1;
       }
     echo "mapClinched = true;\n";
    }

    // check for custom colors query string parameters
    $customColors = array();
    if (array_key_exists("colors",$_GET)) {
       $customColors = explode(';',$_GET['colors']);
       $colorNum = 0;
       foreach ($customColors as $customColor) {
          $colorEntry = array();
          $colorEntry = explode(':',$customColor);
          echo "customColorCodes[".$colorNum."] = { name: \"".$colorEntry[0]."\", unclinched: \"".$colorEntry[1]."\", clinched: \"".$colorEntry[2]."\" };\n";
          $colorNum = $colorNum + 1;
       }
    }

  ?>
    genEdges = true;
  }
</script>
<title>Travel Mapping: Draft Map Overlay Viewer</title>
</head>

<body onload="loadmap();">
<h1 style="text-align: center">Travel Mapping: Draft Map Overlay Viewer</h1>

  <div id="controlbox">
    <input id="showMarkers" type="checkbox" name="Show Markers" onclick="showMarkersClicked()">&nbsp;Show Markers
      
  <span id="controlboxroute">
    <?php
       if (array_key_exists("r",$_GET)) {
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
       }
       else if (array_key_exists("rg",$_GET)) {
         echo "Displaying region: ".$_GET['rg'].".";
       }
    ?>
  </span>
<span id="controlboxinfo"></span>
</div>
<div id="map">
</div>
</body>
</html>
