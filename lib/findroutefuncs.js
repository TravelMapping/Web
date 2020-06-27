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

    let levels = [];
    levels['devel'] = 0;
    levels['preview'] = 1;
    levels['active'] = 2;
    let systems = new Set();
    let countries = new Set();
    let continents = new Set();
    let regions = new Set();
    
    // build array of info about routes from response, and create
    // corresponding table rows, finding unique values for select
    // objects along the way
    for (let i = 0; i < responses['roots'].length; i++) {
	let r = {
	    tier: findrouteSystems[responses['systems'][i]].tier,
	    system: responses['systems'][i],
	    continent: responses['continents'][i],
	    country: responses['countries'][i],
	    region: responses['regions'][i],
	    routeName: responses['routeNames'][i],
	    listName: responses['listNames'][i],
	    level: levels[findrouteSystems[responses['systems'][i]].level],
	    root: responses['roots'][i]
	};
	systems.add(r.system);
	countries.add(r.country);
	continents.add(r.continent);
	regions.add(r.region);
	let tr = "<tr id=\"fr" + i + "\"><td>" + r.tier + "</td><td>" +
	    r.continent + "</td><td>" + r.country + "</td><td>" +
	    r.region + "</td><td>" + r.system +
	    "</td><td style=\"max-width: 150px;\">" + r.routeName +
	    "</td><td><a href=\"/hb/showroute.php?r=" + r.root + "\">" +
	    r.listName + "</a></td><td>" +
	    findrouteSystems[responses['systems'][i]].level +
	    "</td></tr>";
	trs += tr;
	frData.push(r);
    }
    tbody.innerHTML = trs;

    // populate unique systems, countries, regions
    let systemSelector = document.getElementById("chopsys");
    [...systems].forEach(s => {
	let entry = document.createElement("option");
	entry.text = s;
	if (s == frSystemQS) entry.selected = "selected";
	systemSelector.add(entry);
    });
    
    let continentSelector = document.getElementById("chopcontinent");
    [...continents].forEach(c => {
	let entry = document.createElement("option");
	entry.text = c;
	continentSelector.add(entry);
    });
    
    let countrySelector = document.getElementById("chopcountry");
    [...countries].forEach(c => {
	let entry = document.createElement("option");
	entry.text = c;
	countrySelector.add(entry);
    });
    
    let regionSelector = document.getElementById("chopregion");
    [...regions].forEach(r => {
	let entry = document.createElement("option");
	entry.text = r;
	if (r == frRegionQS) entry.selected = "selected";
	regionSelector.add(entry);
    });
    
    document.getElementById("chopmessage").style.display = "none";
    document.getElementById("chopselectrow").style.display = "";

    // remember row ids for filtering
    for (let i = 0; i < frData.length; i++) {
	frData[i].tr = document.getElementById("fr" + i);
    }
    filterChopped();
    console.log(Date.now() - startTime);
}

// call filterChopped if the given keyboard event was Enter
function filterChoppedIfEnter(event) {

    if (event.keyCode == 13) filterChopped();
}

// filter chopped routes table by selections in the header selectors
function filterChopped() {

    let levelSelector = document.getElementById("choplevel");
    let selectedLevel = levelSelector.options[levelSelector.selectedIndex].value;
    let levelFilter = [ selectedLevel.includes("d"),
			selectedLevel.includes("p"),
			selectedLevel.includes("a") ];
    let tierSelector = document.getElementById("choptier");
    let selectedTier = tierSelector.options[tierSelector.selectedIndex].value;

    let sysSelector = document.getElementById("chopsys");
    let selectedSys = sysSelector.options[sysSelector.selectedIndex].text;

    let continentSelector = document.getElementById("chopcontinent");
    let selectedContinent = continentSelector.options[continentSelector.selectedIndex].text;

    let countrySelector = document.getElementById("chopcountry");
    let selectedCountry = countrySelector.options[countrySelector.selectedIndex].text;

    let regionSelector = document.getElementById("chopregion");
    let selectedRegion = regionSelector.options[regionSelector.selectedIndex].text;
    let routeInput = document.getElementById("choppattern");
    let routePattern = routeInput.value;
    
    for (let i = 0; i < frData.length; i++) {
	// level filter first
	let hide = !levelFilter[frData[i].level];

	// tier filter
	hide |= ((selectedTier != "any") && (selectedTier != frData[i].tier));

	// system filter
	hide |= ((selectedSys != "Any") && (selectedSys != frData[i].system));

	// continent filter
	hide |= ((selectedContinent != "Any") && (selectedContinent != frData[i].continent));

	// country filter
	hide |= ((selectedCountry != "Any") && (selectedCountry != frData[i].country));

	// region filter
	hide |= ((selectedRegion != "Any") && (selectedRegion != frData[i].region));

	// route pattern filter
	hide |= ((routePattern.length > 0) && !frData[i].routeName.includes(routePattern));
	
	frData[i].tr.style.display = (hide ? "none" : "");
    }
    console.log("filter complete");
}
