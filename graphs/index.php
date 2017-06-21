<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<title>Travel Mapping Graph Data</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css">
<style>
table.dataTable tbody td{
padding:0px;
}
</style>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="http://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js" type="text/javascript"></script>
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<p class="heading">Travel Mapping Graph Data</p>

<div class="text"> 

This is the repository of graphs generated from the current <a
href="/">Travel Mapping Project</a> data.  These were created for use
in the <a href="http://courses.teresco.org/metal/">Map-based
Educational Tools for Algorithm Learning (METAL)</a> academic project.
You may contact <a href="http://j.teresco.org/">the author</a> if you
would like an archive of some or all of the graphs, or if you have
special requests for a graph of specific subset of the project's data
to be generated.  If you do decide to make use of these graphs and/or
the tools described here, please drop a note to <a
href="http://j.teresco.org/">the author</a>.

</div>

<div class="text">

For each graph, the tables below gives the graph name, a brief
description, number of vertices and edges in both the collapsed and
simple <a
href="http://courses.teresco.org/metal/graph-formats.shtml">formats</a>,
and links to download the graph files.  <b>Note:</b> larger graphs
greatly tax the Highway Data Examiner and the Google Maps API.  They
should all work, but large graphs require a lot of memory for your
browser and some patience.

</div>

<div class="text">

If you would like a local copy of all of the latest graphs, you can
download <a href="../graphs.zip">a zip archive of all of them</a>.
Beware that this is a large file!

</div>

<div class="text"> 

Graphs are copyright &copy; <a href="http://j.teresco.org/">James
D. Teresco</a>, generated from highway data gathered and maintained by
<a href="http://tm.teresco.org/credits.php#contributors">Travel
Mapping Project</a> contributors.  Graphs may be downloaded freely for
academic use.  Other use prohibited.

</div>
<p class="subheading">All Graphs</p>
<p />
<div style="text-align:center;">
			<p>Filter by number of vertices (collapsed)</p>
			from
			<input type="number" min="1" value="1" id="regMin" style="width:6rem;" onchange="tableFilter(event)">
			to
			<input type="number" min="1" value="30000" id="regMax" style="width:6rem;" onchange="tableFilter(event)">
			vertices
			<p>Filter by graph type</p>
			<select id="regFil" onchange="selFilter(event)">
				<option value="all">All</option>
			</select>
			</div>
			<table class="gratable" id="regTable" border="1">
			<thead>
			<tr><th rowspan="2">Graph Description</th><th colspan="3">Collapsed Format Graph</th><th colspan="3">Simple Format Graph</th></tr>
			<tr><th>Download Link</th><th>Vertices</th><th>Edges</th><th>Download Link</th><th>Vertices</th><th>Edges</th></tr></thead>
			<tbody id="regBody">
<?
$tmconffile = fopen($_SERVER['DOCUMENT_ROOT']."/lib/tm.conf", "r");
$tmdbname = chop(fgets($tmconffile));
$tmdbuser = chop(fgets($tmconffile));
$tmdbpasswd = chop(fgets($tmconffile));
$tmdbhost = chop(fgets($tmconffile));


// make the connection
//echo "<!-- mysqli connecting to database ".$tmdbname." on ".$tmdbhost." -->\n";
mysqli_report(MYSQLI_REPORT_STRICT);
try {
    $tmdb = new mysqli($tmdbhost, $tmdbuser, $tmdbpasswd, $tmdbname);
}
catch ( Exception $e ) {
   //echoecho "<h1 style='color: red'>Failed to connect to database ".$tmdbname." on ".$tmdbhost." Please try again later.</h1>";
   exit;
}
$result = $tmdb->query("SELECT * FROM graphs");
$counter = 0;
$prevRow;

while ($row = $result->fetch_array()) {	
	//keep track of simple info so we can put it after collapsed
	if ($counter%2 == 0){		
		$prevRow = $row;
	}
	//produce table row in correct order
	else {
		echo "<tr class = ".$row[5].">
		<td>".$row[1]."</td>
		<td><a href=".$row[0].">".$row[0]."</a></td>
		<td class = c".$row[2].">".$row[2]."</td>
		<td>".$row[3]."</td>";
		
		echo "
		<td><a href=".$prevRow[0].">".$prevRow[0]."</a></td>
		<td>".$prevRow[2]."</td>
		<td>".$prevRow[3]."</td>
		</tr>";
	}
	$counter++;
}
$values = array();
$descr = array();
$result = $tmdb->query("SELECT * FROM graphTypes");
while ($row = $result->fetch_array()) {	
	array_push($values, $row[0]);
	array_push($descr, $row[1]);
}
?>
</tbody>
</table>

<script>
$("#regTable").DataTable(
	{paging: false,
	info: false
});

var vals = <?php echo '["' . implode('", "', $values) . '"]' ?>;
var inner = <?php echo '["' . implode('", "', $descr) . '"]' ?>;
for (var i=0; i<vals.length; i++){
	var op = document.createElement("option");
	op.value = vals[i];
	op.innerHTML = vals[i] + ": " + inner[i];
	document.getElementById("regFil").appendChild(op);
}

function tableFilter(event){
	if (event.target.value > 0){
		var str = event.target.id.substring(0,3)+"Body";
		var tbody = document.getElementById(str);
		for (var i=1; i<tbody.childNodes.length; i++){
			if(event.target.id.substring(3) == "Max"){
				if(parseInt(tbody.childNodes[i].childNodes[5].className.substring(1)) > event.target.value)
					tbody.childNodes[i].classList.add("hideNumL");
				else
					tbody.childNodes[i].classList.remove("hideNumL");
			}
			else{
				if(parseInt(tbody.childNodes[i].childNodes[5].className.substring(1)) < event.target.value)
					tbody.childNodes[i].classList.add("hideNumS");
				else
					tbody.childNodes[i].classList.remove("hideNumS");
			}
			hideRow(tbody.childNodes[i]);			
		}
	}
}
function selFilter(event){	
	var str = event.target.id.substring(0,3)+"Body";
		var tbody = document.getElementById(str);
		for (var i=1; i<tbody.childNodes.length; i++){	
			if(document.getElementById("regFil").value != "all" && tbody.childNodes[i].className.indexOf(document.getElementById("regFil").value) == -1)
				tbody.childNodes[i].classList.add("hideType");
			else
				tbody.childNodes[i].classList.remove("hideType");
			hideRow(tbody.childNodes[i]);	
		}
}
function hideRow(elem){
	if(elem.classList.contains("hideType") || elem.classList.contains("hideNumL") || elem.classList.contains("hideNumS"))
		elem.style.display = "none";
	else
		elem.style.display = "";
}
</script>
<p class="text">
The most recent site update including graph generation completed at <?php echo tm_update_time(); ?> US/Eastern.
</p>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $tmdb->close();
?>
</html>
