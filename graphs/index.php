<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<title>Travel Mapping Graph Data</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
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
<p class="subheading">Graphs of All TM Data</p>

<p />
<table class="gratable" border="1">
<thead>
<tr><th rowspan="2">Graph Description</th><th colspan="2">Collapsed Format Graph</th><th colspan="2">Simple Format Graph</th></tr>
<tr><th>Download Link</th><th>(|V|,|E|)</th><th>Download Link</th><th>(|V|,|E|)</th></tr></thead>
<?
$result = $tmdb->query("SELECT * FROM graphs");
$counter = 0;
$prevRow;
$firstSys = true;
while ($row = $result->fetch_array()) {
	//check for first additional interesting graph
	if ($row[0] == "usa-national.tmg"){
		echo '</table>
			<p class="subheading">Additional Interesting Graphs</p>

			<p />
			<table class="gratable" border="1">
			<thead>
			<tr><th rowspan="2">Graph Description</th><th colspan="2">Collapsed Format Graph</th><th colspan="2">Simple Format Graph</th></tr>
			<tr><th>Download Link</th><th>(|V|,|E|)</th><th>Download Link</th><th>(|V|,|E|)</th></tr></thead>';
				}
	//keep track of simple info so we can put it after collapsed
	if ($counter%2 == 0){		
		$prevRow = $row;
	}
	//produce table row in correct order
	else {
		echo "<tr>
		<td>".$row[1]."</td>
		<td><a href=".$row[0].">".$row[0]."</a></td>
		<td>(".$row[2].", ".$row[3].")</td>";
		
		echo "
		<td><a href=".$prevRow[0].">".$prevRow[0]."</a></td>
		<td>(".$prevRow[2].", ".$prevRow[3].")</td>
		</tr>";
	}
	//check for first graph by region
	if ($counter == 1){
		echo '</table>
			<p class="subheading">Graphs Restricted by Region</p>
			<p />
			<table class="gratable" border="1">
			<thead>
			<tr><th rowspan="2">Graph Description</th><th colspan="2">Collapsed Format Graph</th><th colspan="2">Simple Format Graph</th></tr>
			<tr><th>Download Link</th><th>(|V|,|E|)</th><th>Download Link</th><th>(|V|,|E|)</th></tr></thead>';
	}
	//check for first graph by system
	if ($counter > 1 && !ctype_upper(substr($row[0], 0, 1)) && $firstSys){
		echo '</table>
			<p class="subheading">Graphs Restricted by Highway System</p>
			<p />
			<table class="gratable" border="1">
			<thead>
			<tr><th rowspan="2">Graph Description</th><th colspan="2">Collapsed Format Graph</th><th colspan="2">Simple Format Graph</th></tr>
			<tr><th>Download Link</th><th>(|V|,|E|)</th><th>Download Link</th><th>(|V|,|E|)</th></tr></thead>';
		$firstSys = false;
	}
	$counter++;
}

?>
</table>
<p class="text">
The most recent site update including graph generation completed at <?php echo tm_update_time(); ?> US/Eastern.
</p>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $tmdb->close();
?>
</html>
