<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<?php tm_common_js(); ?>
<script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
<script src="../lib/graphindexfuncs.js" type="text/javascript"></script>
<?php
$archiveSet = "";
if (array_key_exists("gv", $_GET)) {
    $archiveSet = strtolower($_GET['gv']);
}

// build info about graph types
$result = $tmdb->query("SELECT * FROM graphTypes");
$graphTypeValues = array();
$graphTypeDescrs = array();
while ($row = $result->fetch_array()) {	
	array_push($graphTypeValues, $row['category']);
	array_push($graphTypeDescrs, $row['descr']);
}
$result->free();

// build info about graph archive sets
$result = tmdb_query("SELECT * from graphArchiveSets");
$currIndex = 0;
$matchIndex = -1;
$graphPath = "../graphdata/";
$archiveSetNames = array();
$archiveSetDescrs = array();
while ($row = $result->fetch_array()) {
    array_push($archiveSetNames, $row['setName']);
    array_push($archiveSetDescrs, $row['descr']);
    if ($row['setName'] == $archiveSet) {
        $matchIndex = $currIndex;
	$graphPath = "../grapharchives/".$archiveSet."/";
    }
    $currIndex = $currIndex + 1;
}
$result->free();
?>
<title>Travel Mapping/METAL Graph Data</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css">
<style>
table.dataTable tbody td{
padding:0px;
}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js" type="text/javascript"></script>
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<p class="heading">Travel Mapping/METAL Graph Data</p>

<div class="text"> 

  These graphs represent subsets of highway networks around the world.
  They are generated daily for
  the <a href="https://courses.teresco.org/metal/">Map-based
  Educational Tools for Algorithm Learning (METAL)</a> academic
  project using highway data from the <a href="/">Travel Mapping
    Project</a>.  Details about graph data and the file formats are
  <a href="https://courses.teresco.org/metal/graph-formats.shtml">here</a>.  
  
</div>
  
<div class="text">

Contact <a href="https://j.teresco.org/">the METAL project leader</a> with
questions or concerns, and please drop a note if you find these graphs and the related tools to be useful.

</div>

<div class="text">

For each graph, the table below gives a brief description, then
download links and graph information for the default (collapsed),
traveled, and simple formats.
<b>Note:</b> larger graphs
greatly tax the Highway Data Examiner and the Leaflet maps.  They
should all work, but large graphs require a lot of memory for your
browser and some patience.
</div>

<div class="text">

You can download <a href="../graphdata/graphs.zip">a zip archive of
all of the latest graphs</a>.  Beware that this is a large file!

</div>

<div class="text"> 

Graphs are copyright &copy; <a href="https://j.teresco.org/">James
D. Teresco</a>, generated from highway data gathered and maintained by
<a href="https://travelmapping.net/credits.php#contributors">Travel
Mapping Project</a> contributors.  Graphs may be downloaded freely for
academic use.  Other use is by written permission only.

</div>
<p class="subheading">
  <?php
  if (($archiveSet != "") && ($matchIndex == -1)) {
      echo "Most Recent Graphs (Archive set ".$archiveSet." not found)";
      $archiveSet = "";
  }
  else if ($archiveSet == "") {
      echo "Most Recent Graphs";
  }
  else {
      echo $archiveSetDescrs[$matchIndex]." Graphs";
  }
  ?>
</p>
<p />
<div class="text">
  <p>Filter by number of vertices (in default/collapsed) 
  from
  <input type="number" min="1" value="1" id="minSize" style="width:6rem;" onchange="graphTableFilterSizeChanged(event)">
    to
    <input type="number" min="1" value="2000000" id="maxSize" style="width:6rem;" onchange="graphTableFilterSizeChanged(event)">
      vertices
      <p>Filter by graph type 
      <select id="graphTypes" onchange="graphTypeFilterChanged(event)">
	<option value="all">All</option>
      </select>
      </p>
      <p>Switch to graph set
	<select id="graphSet" onchange="graphSetChanged(event)">
	  <option value="current">Most Recent Graphs</option>
	</select>
      </p>
    </div>
    <table class="gratable" id="graphTable" border="1">
      <thead>
	<tr>
	  <th rowspan="2">Graph Description</th>
	  <th colspan="3">Default (Collapsed) Format Graph</th>
	  <?php
	  if ($archiveSet != "") {
	     echo "<th colspan=\"3\">Intersection (Collapsed) Format Graph</th>";
	  }
	  ?>
	  <th colspan="4">Traveled Format Graph</th>
	  <th colspan="3">Simple Format Graph</th></tr>
	<tr>
	  <?php
	  if ($archiveSet != "") {
	     echo "<th>Download Link</th><th>Vertices</th><th>Edges</th>";
	  }
	  ?>
	  <th>Download Link</th><th>Vertices</th><th>Edges</th>
	  <th>Download Link</th><th>Vertices</th><th>Edges</th><th>Travelers</th>
	  <th>Download Link</th><th>Vertices</th><th>Edges</th>
	</tr>
      </thead>
      <tbody id="graphTableBody">
	<?php
	if ($archiveSet == "") {
	   $result = $tmdb->query("SELECT * FROM graphs ORDER BY descr, format");
        }
	else {
	   $result = $tmdb->query("SELECT * FROM graphArchives WHERE setName='".$archiveSet."' ORDER BY descr, format");
        }
	$counter = 0;
	$prevRow;  // what is this?

	while ($counter < $result->num_rows) {

	    // get three or four entries: collapsed, (intonly), simple,
      	    // then traveled for each
      	    $crow = $result->fetch_assoc();
	    if ($archiveSet != "") {
	       $irow = $result->fetch_assoc();
	       $counter += 1;
            }
      	    $srow = $result->fetch_assoc();
      	    $trow = $result->fetch_assoc();

      	    $counter += 3;

      	    if ($crow == NULL || $srow == NULL || $trow == NULL) {
               // should produce some kind of error message
               continue;
      	    }

      	    // build table row (was: class=collapsed)
      	    echo "<tr class='".$crow['category']."'>
	    <td>".$crow['descr']."</td>
	    <td><a href=\"".$graphPath.$crow['filename']."\">".$crow['filename']."</a></td>
	   <td class='c".$crow['vertices']."'>".$crow['vertices']."</td>
	   <td>".$crow['edges']."</td>\n";

	   if ($archiveSet != "") {
	       echo "<td><a href=\"".$graphPath.$irow['filename']."\">".$irow['filename']."</a></td>
	       <td class='c".$irow['vertices']."'>".$irow['vertices']."</td>
	       <td>".$irow['edges']."</td>\n";
	    }
		
	   echo "<td><a href=\"".$graphPath.$trow['filename']."\">".$trow['filename']."</a></td>
	   <td>".$trow['vertices']."</td>
	   <td>".$trow['edges']."</td>
	   <td>".$trow['travelers']."</td>\n";

	   echo "<td><a href=\"".$graphPath.$srow['filename']."\">".$srow['filename']."</a></td>
	   <td>".$srow['vertices']."</td>
	   <td>".$srow['edges']."</td>
           </tr>\n";
	  }
?>
      </tbody>
    </table>
    
<p class="text">
The most recent site update including graph generation completed at <?php echo tm_update_time(); ?> US/Eastern.
</p>

  <script type="text/javascript">

    $("#graphTable").DataTable(
    {paging: false,
    info: false
    });
    
    let graphTypeVals = <?php echo '["'.implode('", "', $graphTypeValues).'"]' ?>;
    let graphTypeDescrs = <?php echo '["'.implode('", "', $graphTypeDescrs).'"]' ?>;
    let graphArchiveNames = <?php echo '["'.implode('", "', $archiveSetNames).'"]' ?>;
    let graphArchiveDescrs = <?php echo '["'.implode('", "', $archiveSetDescrs).'"]' ?>;
    let currentArchiveSet = <?php echo '"'.$archiveSet.'"' ?>;
    populateGraphIndexMenus(graphTypeVals, graphTypeDescrs,
        graphArchiveNames, graphArchiveDescrs, currentArchiveSet);
  </script>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
  </body>
<?php
    $tmdb->close();
?>
</html>
