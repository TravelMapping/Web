//
// Travel Mapping (TM) JavaScript functions related to findroute functionality
//
// Primary author: Jim Teresco
//
// Note: this file should include only functionality specific to
// hb/findroute.php.  Generally-applicable code should continue to
// be placed in tmjsfuncs.js.  This file assumes tmjsfuncs.js
// will be loaded before itself.

var frSystemQS;
var frRegionQS;
var startTime;

var frData = [];

// function to perform the sequence of actions when findroute is
// first loaded
function findrouteStartup(sys, rg) {

    frSystemQS = sys;
    frRegionQS = rg;
    startTime = Date.now();
    $.ajax({
	type: "POST",
	url: "/lib/getAllRoutesInfo.php",
	datatype: "json",
	success: parseRouteInfo
    });
}

// process returned information for the Route info
function parseRouteInfo(data) {

    //console.log(data);
    console.log(Date.now() - startTime);
    document.getElementById("chopmessage").innerHTML = "Processing Data";
    let tbody = document.getElementById("chopboxtbody");
    tbody.innerHTML = "";
    
    let responses = $.parseJSON(data);
    let trs = "";

    let levels = ['devel', 'preview', 'active'];
    
    // build array of info about routes from response, and create
    // corresponding table rows, finding unique values for select
    // objects along the way
    for (let i = 0; i < responses['roots'].length; i++) {
	let r = {
	    tier: responses['tiers'][i],
	    system: responses['systems'][i],
	    country: responses['countries'][i],
	    region: responses['regions'][i],
	    routeName: responses['routeNames'][i],
	    listName: responses['listNames'][i],
	    level: responses['levels'][i],
	    root: responses['roots'][i]
	};
	let tr = "<tr id=\"fr" + i + "\"><td>" + r.tier + "</td><td>" +
	    r.system + "</td><td>" + r.country + "</td><td>" + r.region +
	    "</td><td style=\"max-width: 150px;\">" + r.routeName +
	    "</td><td>" + r.listName + "</td><td>" + levels[r.level] +
	    "</td><td><a href=\"/hb/showroute.php?r=" + r.root + "\">" +
	    r.root + "</a></td></tr>";
	trs += tr;
	frData.push(r);
    }
    tbody.innerHTML = trs;
    
    document.getElementById("chopmessage").style.display = "none";
    document.getElementById("chopselectrow").style.display = "";

    // remember row ids for filtering
    for (let i = 0; i < frData.length; i++) {
	frData[i].tr = document.getElementById("fr" + i);
    }
    filterChopped();
    console.log(Date.now() - startTime);
}

// filter chopped routes table by selections in the header selectors
function filterChopped() {

    let levelSelector = document.getElementById("choplevel");
    let selectedLevel = levelSelector.options[levelSelector.selectedIndex].value;
    let levelFilter = [ selectedLevel.includes("d"),
			selectedLevel.includes("p"),
			selectedLevel.includes("a") ];
    
    for (let i = 0; i < frData.length; i++) {
	let show = levelFilter[frData[i].level];
	
	frData[i].tr.style.display = (show ? "" : "none");
    }
}
