<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<?php
$archiveSet = "";
if (array_key_exists("gv", $_GET)) {
    $archiveSet = strtolower($_GET['gv']);
}

// build info about graph archive sets
$result = tmdb_query("SELECT * from graphArchiveSets");
$currIndex = 0;
$matchIndex = -1;
$graphPath = "../graphdata/";
$archiveSets = array('setName'=>array(), 'descr'=>array());
while ($row = $result->fetch_array()) {
    array_push($archiveSets['setName'], $row['setName']);
    array_push($archiveSets['descr'], $row['descr']);
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
      echo $archiveSets['descr'][$matchIndex]." Graphs";
  }
  ?>
</p>
<p />
<div style="text-align:center;">
  <p>Filter by number of vertices (in default/collapsed)</p>
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
      <tbody id="regBody">
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

// populate graph types menu
var vals = <?php echo '["' . implode('", "', $values) . '"]' ?>;
var inner = <?php echo '["' . implode('", "', $descr) . '"]' ?>;
for (var i=0; i<vals.length; i++) {
  var op = document.createElement("option");
  op.value = vals[i];
  op.innerHTML = vals[i] + ": " + inner[i];
  document.getElementById("regFil").appendChild(op);
}

// filter for min/max number of vertices
function tableFilter(event){
  if (event.target.value > 0) {
    let tbody = document.getElementById("regBody");
    // loop over each table row, where the cell at index 2
    // has the size as its className prepended with a 'c'
    // mark each row with a class indicating if should be hidden
    // for exceeding the max or falling below the min
    for (var i=0; i<tbody.rows.length; i++) {
      let tRow = tbody.rows[i];
      let numV = parseInt(tRow.cells[2].className.substring(1));
      if (event.target.id == "regMax") {
        if (numV > event.target.value) {
          tRow.classList.add("hideNumL");
        }
        else {
          tRow.classList.remove("hideNumL");
        }
      }
      else {
        if (numV < event.target.value) {
          tRow.classList.add("hideNumS");
        }
        else {
          tRow.classList.remove("hideNumS");
        }
      }
      hideRow(tRow);      
    }
  }
}

// filter based on graph categories
function selFilter(event) {  
  let tbody = document.getElementById("regBody");

  // loop over each row, if selection is anything but "all" we filter
  // based on the category, which is stored as the class of each row
  for (var i=0; i<tbody.rows.length; i++) {
    if (document.getElementById("regFil").value != "all" &&
        tbody.rows[i].className.indexOf(document.getElementById("regFil").value) == -1) {
      tbody.rows[i].classList.add("hideType");
    }
    else {
      tbody.rows[i].classList.remove("hideType");
    }
    hideRow(tbody.rows[i]);  
  }
}

// do the actual hide/show based on the existence of any of the
// classes that would hide it based on one of those categories
function hideRow(elem) {
  if (elem.classList.contains("hideType") ||
      elem.classList.contains("hideNumL") ||
      elem.classList.contains("hideNumS")) {
    elem.style.display = "none";
  }
  else {
    elem.style.display = "";
  }
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
