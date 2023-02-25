//
// Travel Mapping (TM) JavaScript functions related to graph index functionality
//
// Primary author: Jim Teresco, based on original code by METAL
// Summer Scholars students
//
// Note: this file should include only functionality specific to
// graphs/index.php.  Generally-applicable code should continue to
// be placed in tmjsfuncs.js.  This file assumes tmjsfuncs.js
// will be loaded before itself.


function populateGraphIndexMenus(gtVals, gtDescrs, asNames, asDescrs, currentAS) {

    // populate graph types menu
    let graphTypes = document.getElementById("graphTypes");
    for (let i = 0; i < gtVals.length; i++) {
	let op = document.createElement("option");
	op.value = gtVals[i];
	op.innerHTML = gtDescrs[i];
	graphTypes.appendChild(op);
    }
    
    // populate archive sets
    let graphSet = document.getElementById("graphSet");
    for (let i = 0; i < asNames.length; i++) {
	let op = document.createElement("option");
	op.value = asNames[i];
	op.innerHTML = asDescrs[i];
	graphSet.appendChild(op);
	if (asNames[i] == currentAS) {
	    graphSet.value = currentAS;
	}
    }
}

// filter for min/max number of vertices
function graphTableFilterSizeChanged(event){
  if (event.target.value > 0) {
    let tbody = document.getElementById("graphTableBody");
    // loop over each table row, where the cell at index 2
    // has the size as its className prepended with a 'c'
    // mark each row with a class indicating if should be hidden
    // for exceeding the max or falling below the min
    for (var i=0; i<tbody.rows.length; i++) {
      let tRow = tbody.rows[i];
      let numV = parseInt(tRow.cells[2].className.substring(1));
      if (event.target.id == "maxSize") {
        if (numV > event.target.value) {
          tRow.classList.add("hideNumL");
        }
        else { // minsize
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
function graphTypeFilterChanged(event) {  
    let tbody = document.getElementById("graphTableBody");
    let gtSelected = document.getElementById("graphTypes").value;

    // loop over each row, if selection is anything but "all" we filter
    // based on the category, which is stored as the class of each row
    for (let i = 0; i < tbody.rows.length; i++) {
	if (gtSelected != "all" &&
            tbody.rows[i].className.indexOf(gtSelected) == -1) {
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

// handle graph archive set update
function graphSetChanged(event) {

    let selectedSet = document.getElementById("graphSet").value;
    let baseURL = window.location.href.split('?')[0];
    
    if (selectedSet == "current") {
	window.location.href = baseURL;
    }
    else {
	window.location.href = baseURL + "?gv=" + selectedSet;
    }
}
