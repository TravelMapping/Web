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
    console.log("parseRouteInfo start: " + (Date.now() - startTime));
    document.getElementById("chopmessage").innerHTML = "Processing Data";
    
    let responses = $.parseJSON(data);

    // setting up filters
    let levels = [];
    levels['devel'] = 0;
    levels['preview'] = 1;
    levels['active'] = 2;
    let systems = new Set();
    let countries = new Set();
    let continents = new Set();
    let regions = new Set();

    // use "unclinched" colors for systems
    Object.keys(findrouteSystems).forEach(name => {
	let colors = lookupColors(findrouteSystems[name].color,
				  findrouteSystems[name].tier, name);
	findrouteSystems[name].color = colors[0];
    });
    
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
	    routeName: responses['routeNames'][i].toUpperCase(),
	    listName: responses['listNames'][i],
	    level: levels[findrouteSystems[responses['systems'][i]].level],
	    root: responses['roots'][i]
	};
	systems.add(r.system);
	countries.add(r.country);
	continents.add(r.continent);
	regions.add(r.region);
	r.tr = "<tr id=\"fr" + i + "\"><td>" + r.tier + "</td><td>" +
	    r.continent + "</td><td>" + r.country + "</td><td>" +
	    r.region + "</td><td style=\"background-color: " +
	    findrouteSystems[responses['systems'][i]].color + "\">" + r.system +
	    "</td><td style=\"max-width: 150px;  background-color: " +
	    findrouteSystems[responses['systems'][i]].color + "\">" +
	    responses['routeNames'][i] +
	    "</td><td><a href=\"/hb/showroute.php?r=" + r.root + "\">" +
	    r.listName + "</a></td><td class=\"status-" +
	    findrouteSystems[responses['systems'][i]].level + "\">" +
	    findrouteSystems[responses['systems'][i]].level +
	    "</td></tr>";
	frData.push(r);
    }

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
    
    filterChopped();
    console.log("parseRouteInfo end: " + (Date.now() - startTime));
}

// clear all filters and reapply to show all routes
function clearChoppedFilters() {

    let levelSelector = document.getElementById("choplevel");
    levelSelector.selectedIndex = 0;
    
    let tierSelector = document.getElementById("choptier");
    tierSelector.selectedIndex = 0;

    let sysSelector = document.getElementById("chopsys");
    sysSelector.selectedIndex = 0;

    let continentSelector = document.getElementById("chopcontinent");
    continentSelector.selectedIndex = 0;

    let countrySelector = document.getElementById("chopcountry");
    countrySelector.selectedIndex = 0;

    let regionSelector = document.getElementById("chopregion");
    regionSelector.selectedIndex = 0;
    
    let routeInput = document.getElementById("choppattern");
    routeInput.value = "";

    filterChopped();
 }

// call filterChopped if the given keyboard event was Enter
function filterChoppedIfEnter(event) {

    if (event.keyCode == 13) filterChopped();
}

// set a timeout function to filter after setting the message
function filterChopped() {

    startTime = Date.now();
    document.getElementById("chopmessage").style.display = "";
    document.getElementById("chopmessage").innerHTML = "Applying Filters";
    setTimeout(filterChoppedReal, 0.1);
}

// filter chopped routes table by selections in the header selectors
function filterChoppedReal() {

    console.log("filterChoppedReal start: " + (Date.now() - startTime));
    let tbody = document.getElementById("chopboxtbody");
    tbody.innerHTML = "";

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
    let routePattern = routeInput.value.toUpperCase();

    let trs = "";
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
	
	if (!hide) trs += frData[i].tr;
    }

    tbody.innerHTML = trs;
    document.getElementById("chopselectrow").style.display = "";
    document.getElementById("chopmessage").style.display = "none";
    console.log("filterChoppedReal end: " + (Date.now() - startTime));
}

// turn on column sorting with the sortable.js library
function enableColumnSorting() {

    // replace the button with a message
    document.getElementById("sortmsg").innerHTML = "Click on a Column Header to Sort";

    // add the sortable to the class of the table
    document.getElementById("chopped").classList.add("sortable");
}
