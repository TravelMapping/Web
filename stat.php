<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	A basic user stats page. 
	URL Params:
		u - the user.
		rg_order - the way to order records in the regions table.
		sys_order - the way to order the records in the systems table.
		db - the database being used. Use 'TravelMappingDev' for in-development systems. 
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

#body h2{
	margin: auto;
	text-align: center;
	padding: 10px;
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
width: 50%;
background-color:white;
}

table.gratable  td, th {
border: solid black;
border-width: 1px;
}

table.gratable tr td {
text-align:left;
}

table.gratable tr:hover td {
	background-color: #CCCCCC;
}
</style>
<title>Traveler Statistics</title>
</head>
<body>
	<h1>Traveler Statistics</h1>
	<h2>Overall Traveler Rankings</h2>
	<table class="gratable">
		<thead>
			<tr><td colspan="3">Overall Traveler Mileage</td></tr>
			<tr><td>Rank</td><td>Username</td><td>Miles Traveled</td></tr>
		</thead>
		<tbody>
			<?php
				$dbname = "TravelMapping";
				if (array_key_exists("db",$_GET)) {
				  $dbname = $_GET['db'];
				}

				// Fetch mileage and # of highways clinched / driven. Using two queries (one for mileage, the other for highways clinched) because the join required to do it in one takes ~16s.
				$db = new mysqli("localhost","travmap","clinch",$dbname) or die("Failed to connect to database"); 
				$sql_command = "SELECT com.traveler, SUM(com.mileage) AS clinchedMileage, FROM clinchedOverallMileageByRegion AS com GROUP BY com.traveler ORDER BY clinchedMileage DESC;";
				$res_mileage = $db->query($sql_command);
				$sql_command = "SELECT traveler, COUNT(*) AS highwaysDriven, SUM(clinched) AS highwaysClinched FROM clinchedRoutes GROUP BY traveler ORDER BY highwaysClinched DESC;";
				$res_highways = $db->query($sql_command);
				$num = 1;

				while ($row = array_merge($res_mileage->fetch_assoc(), $res_highways->fetch_assoc())) {
					echo "<tr><td>".$num."</td>";
					echo "<td>".$row['traveler']."</td>";
					echo "<td>".$row['clinchedMileage']."</td>";
					echo "<td>".$row['highwaysDriven']."</td>";
					echo "<td>".$row['highwaysDriven']."</td></tr>";
				}
			?>
		</tbody>
	</table>
</body>
</html>