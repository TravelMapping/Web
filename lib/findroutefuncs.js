//
// Travel Mapping (TM) JavaScript functions related to findroute functionality
//
// Primary author: Jim Teresco
//
// Note: this file should include only functionality specific to
// hb/findroute.php.  Generally-applicable code should continue to
// be placed in tmjsfuncs.js.  This file assumes tmjsfuncs.js
// will be loaded before itself.

var showrouteSystemQS;
var showrouteRegionQS;
// function to perform the sequence of actions when findroute is
// first loaded
function showrouteStartup(sys, rg) {

    showrouteSystemQS = sys;
    showrouteRegionQS = rg;
    
    $.ajax({
	type: "POST",
	url: "/lib/getAllRoutesInfo.php",
	datatype: "json",
	success: parseRouteInfo
    });
}

// process returned information for the Route info
function parseRouteInfo(data) {

    let responses = $.parseJSON(data);
}
