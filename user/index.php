<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	A basic user stats page. 
	URL Params:
		u - the user
		order - the way to order fields in the regions table
		rg - region to view detailed stats for
-->
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

#body {
position: fixed;
left: 0px;
top: 80px;
bottom: 0px;
width: 100%;
overflow:auto;
padding: 20px;
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
<title>
	<?php
		if (array_key_exists("u",$_GET)) {
			$user = $_GET['u'];
			echo "Traveler Stats for ".$user;
		}

		if (array_key_exists("rg", $_GET)) {
			$rg = $_GET['rg'];
		}

		$dbname = "TravelMapping";
		if (array_key_exists("db",$_GET)) {
		  $dbname = $_GET['db'];
		}

		// establish connection to db: mysql_ interface is deprecated, should learn new options
		$db = new mysqli("localhost","travmap","clinch",$dbname) or die("Failed to connect to database");

		# functions from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
		function startsWith($haystack, $needle) {
		    // search backwards starting from haystack length characters from the end
			return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
		}
		function endsWith($haystack, $needle) {
		    // search forward starting from end minus needle length characters
		    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
		}

		function colorScale($percent) {
			
		}
	?>
</title>
</head>
<body>
	<div id="header">
	<form id="userselect">
		<label>User: </label>
		<input type="text" name="u" form="userselect" value="<?php echo $user ?>">
		<input type="submit">
	</form>
	<h1>Traveler Stats for 
	<?php 
		echo $user;
		if (!is_null($rg)) {
			echo " in ".$rg;
		} 
	?>
	:</h1>
	</div>
	<div id="body">
		<h2>Stats by Region
		<table class="gratable">
			<thead>
				<tr>
					<th colspan="5">Clinched Mileage by Region:</th>
				</tr>
				<tr>
					<th><a href="?<?php echo "u=".$user."&rorder=region" ?>">Region</a></th>
					<th><a href="?<?php echo "u=".$user."&rorder=clinchedMileage desc" ?>">Clinched Mileage</a></th>
					<th><a href="?<?php echo "u=".$user."&rorder=totalMileage desc" ?>">Overall Mileage</a></th>
					<th><a href="?<?php echo "u=".$user."&rorder=percentage" ?>">Percent Clinched</a></th>
					<th>Map</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$sql_command = "SELECT o.region, co.mileage as clinchedMileage, o.mileage as totalMileage FROM overallMileageByRegion AS o INNER JOIN clinchedOverallMileageByRegion AS co ON co.region = o.region WHERE co.traveler = '".$user."' ORDER BY ";
					if (array_key_exists("rorder", $_GET) && strlen($_GET["rorder"]) > 0) {
						if (!strcmp($_GET['rorder'], "percentage")) {
							$sql_command .= "1 - clinchedMileage / totalMileage";
						} else {
							$sql_command .= $_GET["rorder"];
						}
				    } else {
				    	$sql_command .= "o.region";
				    }
					echo "<!-- SQL: ".$sql_command."-->";
					$res = $db->query($sql_command);
					while ($row = $res->fetch_assoc()) {
						$percent = round($row['clinchedMileage'] / $row['totalMileage'] * 100.0, 3);
				        echo "<tr><td>".$row['region']."</td><td>".$row['clinchedMileage']."</td><td>".$row['totalMileage']."</td><td>".$percent."%</td><td><a href=\"/hbtest/mapview.php?u=".$user."&rg=".$row['region']."\">Map</a></td></tr>";
				    }
			        $res->free();
				?>
				<tr><td colspan="5">*Regions with no mileage not shown</td>
			</tbody>
		</table>
		<h2>Stats by System</h2>
		<table class="gratable">
			<thead>
				<tr>
					<th colspan="7">Clinched Mileage by System</th>
				</tr>
				<tr>
					<th>Country</th>
					<th>System Code</th>
					<th>System Name</th>
					<th>Clinched Mileage</th>
					<th>Overall Mileage</th>
					<th>Percent Clinched</th>
					<th>Map</th>
				</tr>
			</thead>
			<tbody>
				<?php 
					$sql_command = "SELECT sys.countryCode, sys.systemName, sys.fullName, r.root, COALESCE(ROUND(SUM(cr.mileage), 2),0) AS clinchedMileage, COALESCE(ROUND(SUM(r.mileage), 2), 0) AS totalMileage, COALESCE(ROUND(SUM(cr.mileage) / SUM(r.mileage) * 100, 3), 0) AS percentage FROM systems as sys INNER JOIN routes AS r ON r.systemName = sys.systemName LEFT JOIN clinchedRoutes AS cr ON cr.route = r.root AND cr.traveler = '".$user."' GROUP BY r.systemName ORDER BY sys.countryCode DESC, sys.tier, sys.systemName;";
					echo "<!-- SQL: ".$sql_command."-->";
					$res = $db->query($sql_command);
					while ($row = $res->fetch_assoc()) {
						echo "<tr><td>".$row['countryCode']."</td><td>".$row['systemName']."</td><td>".$row['fullName']."</td><td>".$row['clinchedMileage']."</td><td>".$row['totalMileage']."</td><td>".$row['percentage']."</td><td><a href=\"/hbtest/mapview.php?u=".$user."&sys=".$row['systemName']."\">Map</a></td></tr>";
					}
					$res->free();
				?>
			</tbody>
		</table>
	</div>
</body>