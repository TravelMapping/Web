<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<style type="text/css">
<link rel="shortcut icon" type="image/png" href="/favicon.png">

body, html {
  margin:0;
  border:0;
  padding:0;
  height:100%; 
  max-height:100%; 
  overflow: hidden;
  font-size:9pt;
  font-family:"Trebuchet MS", Helvetica, Tahoma, sans-serif;
  background-color:#BBDDFF;
}

a.button:hover,  a.button:active { 
  color:white;
border: 1px solid black;
background-color: #3333FF;
  text-decoration: none;
cursor:pointer;
}


.button a:link, .button a:visited { 
  background-color: #000099;
color:white;
text-decoration: none;
cursor:auto;
}


#map {
position: absolute;
top:300px;
bottom:0px;
left:400px;
right:0px;
overflow:hidden; 
}

#map * {
cursor:crosshair;
}

#inputbox {
position: fixed;
top:0px;
bottom:300px;
height:100%;
left:400px;
right:0px;
overflow:auto;
	padding:5px;
}

#waypoints {
position: fixed;
left: 0px;
top: 0px;
right:400px;
height: 100%;
width: 400px;
overflow:auto;
}




* html body {
  padding: 0 0 0 0; 
  }

* html #map {
  height:100%; 
  width:75%; 
  }

img {
  line-height: 0;
}


table.wpttable {
font-size:8pt;
border: 1px solid black;
border-spacing: 0px;
margin-left: auto;
margin-right: auto;
color: black;
background-color: #FFFFFF;
}

table.wpttable  td, th {
border: solid black;
border-width: 1px;
padding: 0px 2px;
}

table.wpttable tr td {
text-align:right;
}

.button {
   background-color: #000099;
	color:white;	
   border: 1px solid #0000FF;
   padding: 0px 3px;
	text-decoration:none;
}


.cellbutton {
   color: black; /*#000099;*/
/*background-color: inherit;	*/
   border: 1px solid #000099;
cursor:pointer;

}


.cellbuttonhover {
   background-color: #3333FF;
color:white;	
   border: 1px solid white;
	cursor:pointer;
}

</style>

<?php
  require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php";
  tm_common_js();  
  ?>
<script src="../lib/tmjsfuncs.js" type="text/javascript"></script>

<script type="text/javascript">
var map;
//var infowindow;
var trace;

function recenter(ev) {

  console.log("dblclick event");
  console.log(ev);
  map.panTo(ev.latlng);
}

function loadwptmap() {

  // common TM map creation to get all map tiles, etc 
  loadmap();

// wptedit-specific

  trace = L.polyline([]);
  lotrace = L.polyline([]);
  hitrace = L.polyline([]);
  
  map.on("moveend", UpdateCoords);
  map.off("dblclick");
  map.on("dblclick", recenter);
  
  window.onbeforeunload = WptClose;
}
</script>
<title>CHM Waypoints Editor Updated for Leaflet</title>
<script src="wptfunctions.js" type="text/javascript"></script>
</head>

<body onload="javascript: ChangeLayout(); loadwptmap();">

<div id="waypoints">
  <p id="output"></p>
  
</div>
 
<div id="inputbox">
  <p><a onclick="javascript:LoadText(false);" class="button">Load</a> <a onclick="javascript:LoadText(true);" class="button">Load and Pan</a> <a onclick="javascript:ReverseWaypoints();" class="button">Reverse order</a> <a onclick="javascript:RestoreWaypoints();" class="button">Undo</a> <a onclick="javascript:WriteTabText();" class="button">Save to Tab</a> <a onclick="javascript:ClearText();" class="button">Clear</a> <a onclick="javascript:ChangeLayout();" class="button">Change Layout</a>
<select name="thickness" onchange="ChangeLineThickness(this.value);">
<option value="0.25" selected >Th. -2</option>
<option value="0.5" selected >Th. -1</option>
<option value="1" selected >Th. Hwy.</option>
<option value="2">Th. +1</option>
<option value="4">Th. +2</option>
<option value="8">Th. +3</option>
<option value="16">Th. +4</option>
<option value="32">Th. +5</option>
</select>
</p>
  <textarea id="inputtext" style="font-size:x-small;" rows="7" cols="80"></textarea>
  <p id="inuselabelsbar"><a onclick="javascript:LoadInUseLabels()" class="button">Load In-use Labels</a> <a onclick="javascript:ClearInUseLabels()" class="button">Clear</a> <textarea id="inuselabelstext" style="font-size:x-small;" rows="1" cols="55"></textarea></p>
  <p id="errorbar">Paste .wpt file lines in the text area above.  Click Load and Pan above.</p>
  <p id="coordbar">&nbsp;</p>
</div>

<div id="map">
</div>
 

</body></html>
