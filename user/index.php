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

#body {
position: fixed;
left: 0px;
top: 80px;
bottom: 0px;
width: 100%;
overflow:auto;
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
	?>
</title>
</head>
<body>
	<h1>Traveler Stats for <?php echo $user ?>:</h1>
	<div id="body">
		<table class="gratable">
			<thead>
				<tr>
					<th colspan="4">Clinched Mileage by Region:</th>
				</tr>
				<tr>
					<th>Region</th>
					<th>Clinched Mileage</th>
					<th>Overall Mileage</th>
					<th>Percent Clinched</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$sql_command = "SELECT o.region, co.mileage as clinchedMileage, o.mileage as totalMileage FROM overallMileageByRegion AS o INNER JOIN clinchedOverallMileageByRegion AS co ON co.region = o.region WHERE co.traveler = '".$user."' ORDER BY o.region;";
					echo "<!-- SQL: ".$sql_command."-->";
					$res = $db->query($sql_command);
					while ($row = $res->fetch_assoc()) {
						$percent = round($row['clinchedMileage'] / $row['totalMileage'] * 100.0, 3);
				        echo "<tr><td>".$row['region']."</td><td>".$row['clinchedMileage']."</td><td>".$row['totalMileage']."</td><td>".$percent."%</td></tr>";
				    }
			        $res->free();
				?>
			</tbody>
		</table>
	</div>
</body>