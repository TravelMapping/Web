<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 ***
 * Find routes traveled by numbers for a user
 *
 * URL Params:
 *  u - user whose stats to display (required)
 ***
 -->
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
	<link rel="stylesheet" type="text/css" href="/fonts/roadgeek.css" />
	<link rel="shortcut icon" type="image/png" href="/favicon.png">
    <?php tm_common_js(); ?>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
    <title>Travel Mapping: Routes Traveled by Number</title>
</head>

<script type="text/javascript">
 var selects = new Object();
 function initUI() {

     let controls = document.getElementById("controlbox");
     let allselects = controls.getElementsByTagName("select");
     for (let i = 0; i < allselects.length; i++) {
         selects[allselects[i].name] = allselects[i];
     }
 }

 function updateList() {

     traveler = selects['u'].value;
     setTMCookie("traveler", traveler);
     let params = {
	 traveler: traveler,
     };
     
     let jsonParams = JSON.stringify(params);
     $.ajax({
         type: "POST",
	 url: "/lib/getTravelerRoutes.php",
	 datatype: "json",
	 data: { "params" : jsonParams },
	     success: parseTravelerRouteData
     });
 }

 function parseTravelerRouteData(data) {

     let response = $.parseJSON(data);
     // we have an array in response, each element has a field
     // route with the route name traveled
     let tbody = document.getElementById("traveledRoutes");
     let missing = document.getElementById("missing");
     let rows = "";
     if (response['routes'].length == 0) {
	 rows = '<tr><td colspan="2" style="text-align:center">No Routes Traveled</td></tr>';
	 missing.innerHTML = "N/A";
     }
     else {
         let theRoutes = new Array();
	 let unnumbered = "";
         for (let i = 0; i < response['routes'].length; i++) {
	     let route = response['routes'][i];
	     // parse out the number from the string
	     let numberString = "";
	     for (let j = 0; j < route.length; j++) {
	         if (route.charAt(j) >= '0' && route.charAt(j) <= '9') {
		     numberString += route.charAt(j);
		 }
		 else if (numberString != "") {
		     break; // if we hit a non-digit after a digit, we stop
		 }
             }
	     if (numberString.length > 0) {
                 let number = parseInt(numberString, 10);

		 // add to the list at this index
	         if (typeof theRoutes[number] === 'undefined') {
	             theRoutes[number] = "" + route;
                 }
	         else {
	             theRoutes[number] += ", " + route;
                 }
	    }
	    else {
	        // unnumbered
	        if (unnumbered.length == 0) {
	            unnumbered = route;
                }
	        else {
	            unnumbered += ", " + route;
                }
	    }
	 }
	 for (let i = 0; i < theRoutes.length; i++) {
	     if (!(typeof theRoutes[i] === 'undefined')) {
	         rows += '<tr><td>' + i + '</td><td>' + theRoutes[i] + '</td></tr>';
	     }
         }
	 // unnumbered routes last
	 if (unnumbered.length > 0) {
	     rows += "<tr><td>Unnumbered</td><td>" + unnumbered + "</td></tr>";
	 }
	 // fill in missing number list
	 let missingMax = document.getElementById("maxlist").value;
	 let missingStr = "";
	 for (let i = 0; i <= missingMax; i++) {
	     if (typeof theRoutes[i] === 'undefined') {
	         missingStr += " " + i;
	     }
	 }
	 missing.innerHTML = missingStr;
     }
     tbody.innerHTML = rows;
 }
</script>

<body onload="initUI(); updateList(); ">
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<div id="controlbox" style="padding: 20px;">
    <table id="optionsTable" class="gratable">
    <thead>
    <tr><th>Select Options</th></tr>
    </thead>
    <tbody>
    <tr><td>User: 
<?php tm_user_select(); ?>
    </td></tr>
    
<tr><td>List missing numbers up to:
<input id="maxlist" type="number" min="1" max="10000" value="999" />
    </td></tr>
    
    <tr><td style="text-align: center">
    <input type="button" value="Update List" onclick="updateList();" />
    </td></tr>
    </tbody>
    </table>
</div>
<p class="text">
Route numbers here are determined by the first sequence of characters
    in the "route" string that are all numerals.  For example, "NY5S"
    would count as a "5", and "LA92-1" would count as a "92".  This
    could lead to some numbers that aren't really route numbers being
    detected, such as "MKAD2", which is the "2nd Minsk Motorway Ring",
    would count as a "2".  Multiple routes that have the same
    designation (such as the two I-84 instances) would be listed just
    once even if you have traveled both.  Also note that things like
    banners are 
    ignored: if you have traveled a Business US 41 somewhere, it would
    be listed here simply as a "US41".
</p>
<div>
<center>
<span style="display: inline-block; vertical-align: top;">
    <table class="gratable" style="padding: 10px;">
        <thead>
            <tr><th class="routeNum">Missing Numbers</th></tr>
        </thead>
        <tbody>
	<tr><td colspan="2" id="missing">Loading Data...</td></tr>
        </tbody>
    </table>
</span>
</center>
</div>
<div>
<center>
<span style="display: inline-block; vertical-align: top;">
    <table class="gratable" style="padding: 10px;">
        <thead>
            <tr><th class="routeNum">Number</th><th class="routes">Routes Traveled (see note above)</th></tr>
        </thead>
        <tbody id="traveledRoutes">
	<tr><td colspan="2">Loading Data...</td></tr>
        </tbody>
    </table>
</span>
</center>
</div>
</body>
</html>
