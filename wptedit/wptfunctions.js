// CHM Waypoint Editor code by Timothy Reichard adapted for
// Leaflet maps on the Travel Mapping project by Jim Teresco

var n_layouts = 2;
var layout = 0;
var wpts = new Array();
var oldwpts = new Array();
var tempwpts = new Array();
var hitrace = new Array();
var lotrace = new Array();
var trace = new Array();
var polypoints = new Array();
var markers = new Array();
var markerinfo = new Array();
var wptsave = new Array();
var inuselabels = new Array();
var inuselabelsroute = "";

var thawindex = -1;
var thickness = 1;

var visibleicon = {};
var intersectionimage = L.icon({
    iconUrl: '/lib/Intersection.png',
    // This marker is 16x16
    iconSize: [16, 16]});

var mi2km = 1.609344;

// Possible error bits
var DC_DUPLICATE_LABEL = 0x1;
var DC_DUPLICATE_COORDS = 0x2;
var DC_SHARP_ANGLE = 0x4;
var DC_VISIBLE_DISTANCE = 0x8;
var DC_LABEL_LOOKS_HIDDEN = 0x10;
var DC_LABEL_PARENS = 0x20;
var DC_LABEL_UNDERSCORES = 0x40;
var DC_BAD_ANGLE = 0x80;
var DC_HIDDEN_TERMINUS = 0x100;
var DC_BUS_WITH_I = 0x200;
var DC_LACKS_GENERIC = 0x400;
var DC_NONTERMINAL_UNDERSCORE = 0x800;
var DC_LONG_UNDERSCORE = 0x1000;
var DC_LABEL_INVALID_CHAR = 0x2000;
var DC_INVALID_FINAL_CHAR = 0x4000;
var DC_INVALID_FIRST_CHAR = 0x8000;
var DC_LABEL_SLASHES = 0x10000;
var DC_LABEL_TOO_LONG = 0x20000;
var DC_LONG_SEGMENT = 0x40000;
var DC_OUT_OF_BOUNDS = 0x80000;

var BC_hidden = "#CCCCCC";
var BC_visible = "#FFFFFF";
var BC_errors = "#FFDDCC";

var FC_inuse = "#CC0000";
var FC_notinuse = "#000099";

function CalcHighlightLines(wpts, pxthickness)
{
    // Get number of waypoints.

    n_points = wpts.length;

    // Make clean arrays for the two highlight line boundaries.

    lines = new Object({hi: new Array(), lo: new Array()});

    // Loop through the trace segments connecting points p and P=1.
    // Calculate a point pxthickness/2 away from the end of the segment,
    // as though a rectangle were centered on the segment.

    lopt1 = new Object({x: 0., y: 0.});
    lopt2 = new Object({x: 0., y: 0.});
    hipt1 = new Object({x: 0., y: 0.});
    hipt2 = new Object({x: 0., y: 0.});

    for(p = 0; p < n_points - 1; p++)
    {
	// Convert the segment endpoints to square Mercator (x,y) coords.

	// x = 0 means lon = -180 deg.
	// x = 1 means lon = +180 deg.
	// y = 0 means lat ~ +85.5 deg.
	// y = 1 means lat ~ -85.5 deg.
	// These square coordinates rely on the world mapped to a square 
	// 256 x 256 px image at zoom level 0.  Our $pxthickness is that at
	// zoom level 11.
	
	pt1 = ConvertLLToMercXY(wpts[p]);
	pt2 = ConvertLLToMercXY(wpts[p + 1]);

	// In the x,y-space, calculate the angle between the 
	// line segment and the +x (+lon) axis. Then calculate the 
	// angle's supplement to account for the flipped direction of 
	// the y and lat axes.
	
	theta = Math.PI - Math.atan2(pt2.y - pt1.y, pt2.x - pt1.x);

	// Calculate the new x,y points that are $pxthickness/2 offset from
	// the ends of the segment in the direction perpendicular to $theta.

	hipt1.x = pt1.x - pxthickness/(256*Math.pow(2,12)) * Math.sin(theta);
	hipt1.y = pt1.y - pxthickness/(256*Math.pow(2,12)) * Math.cos(theta);
	lopt1.x = pt1.x + pxthickness/(256*Math.pow(2,12)) * Math.sin(theta);
	lopt1.y = pt1.y + pxthickness/(256*Math.pow(2,12)) * Math.cos(theta);

	hipt2.x = pt2.x - pxthickness/(256*Math.pow(2,12)) * Math.sin(theta);
	hipt2.y = pt2.y - pxthickness/(256*Math.pow(2,12)) * Math.cos(theta);
	lopt2.x = pt2.x + pxthickness/(256*Math.pow(2,12)) * Math.sin(theta);
	lopt2.y = pt2.y + pxthickness/(256*Math.pow(2,12)) * Math.cos(theta);

	// Convert the new x,y points back to lat,lon.

	hiwpt1 = ConvertMercXYToLL(hipt1);
	hiwpt2 = ConvertMercXYToLL(hipt2);
	lowpt1 = ConvertMercXYToLL(lopt1);
	lowpt2 = ConvertMercXYToLL(lopt2);
	
	// Store the new lat,lon point in the output object.

	lines.lo[2*p] = lowpt1;
	lines.lo[2*p+1] = lowpt2;

	lines.hi[2*p] = hiwpt1;
	lines.hi[2*p+1] = hiwpt2;
    }
    return lines;
}

// Convert lat and lon to Mercator coordinates with x and y between 0 and 1.
function ConvertLLToMercXY(wpt)
{
    x = wpt.lon/360. + 0.5;
    y = (0.5*Math.log(Math.tan(Math.PI/4 - Math.PI/360.*wpt.lat))/Math.PI + 0.5);

    return new Object({x: x, y: y});
}

function ConvertMercXYToLL(pt)
{
    lon = 360. * (pt.x - 0.5);
    lat = 360./Math.PI * (Math.PI/4 - Math.atan(Math.exp(2*Math.PI*(pt.y - 0.5))));
    
    return [lat, lon];
}

function Waypoint(label, lat, lon, errors, altlabelsstring)
{
    this.label = label;
    this.altlabelsstring = altlabelsstring;
    this.lat = parseFloat(lat).toFixed(6);
    this.lon = parseFloat(lon).toFixed(6);
    this.visible = true;
    if(label.substr(0,1) == '+' || label.substr(0,2) == '*+')
	this.visible = false;
    this.errors = 0;
    return this;
}

function CopyWaypoint(wpt)
{
    return new Waypoint(wpt.label, wpt.lat, wpt.lon, wpt.errors, wpt.altlabelsstring);
}

function Line2Waypoint(line)
{
    line = line.replace(/\s+$/, '');
    line = line.replace(/^\s+/, '');
    line = line.replace('  ', ' ');
    line = line.replace('  ', ' ');
    line = line.replace('  ', ' ');
    line = line.replace('  ', ' ');
    
    
    var xline = line.split(' ');
    if(xline.length < 2)
    {
	return Waypoint('bad-line', 0, 0);
    }
    var label = xline[0];
    var url = xline[xline.length-1];
    var latlon = Url2LatLon(url);

    var altlabels = new Array();
    var altlabelsstring = "";
    if(xline.length > 2)
    {
	altlabels = xline.slice(1, xline.length - 1);
	for(l = 0; l < altlabels.length; l++)
	{
	    altlabels[l] = '+' + FrontTrimLabel(altlabels[l]);
	}
	altlabelsstring = altlabels.join(" ");
    }
//    alert(line + "\n" + ':' + altlabelsstring + ':');
    return new Waypoint(label, latlon[0], latlon[1], 0, altlabelsstring);
}

function Waypoints2Lines(wpts)
{
  var text = '';

  for(var i = 0; i < wpts.length; i++)
    {
	text = text + wpts[i].label + ' ' + wpts[i].altlabelsstring + (wpts[i].altlabelsstring != '' ? ' ' : '') + Waypoint2Url(wpts[i]) + "\n";
    }

  return text;
}

function Waypoint2Url(wpt)
{
  return 'http://www.openstreetmap.org/?lat=' + wpt.lat + '&lon=' + wpt.lon;
}

function Url2LatLon(url)
{
  var latlon = new Array(0., 0.);
  var floatpattern = '([-+]?[0-9]*\.?[0-9]+)';
  var latpattern = 'lat=' + floatpattern;
  var lonpattern = 'lon=' + floatpattern;

  //search for lat
  var matches = url.match(latpattern);
  if(matches != null)
    {
      latlon[0] = parseFloat(matches[1]).toFixed(6);
    }

  //search for lon
  matches = url.match(lonpattern);
  if(matches != null)
    {
      latlon[1] = parseFloat(matches[1]).toFixed(6);
    }


  return latlon;

}

function UpdateMessage(text)
{
    document.getElementById("errorbar").innerHTML = text;

}

function LoadText(pan)
{
  UpdateMessage("Loading lines...");

  //var outtext = '';
  var text = document.getElementById("inputtext").value;
  var lines = text.split('\n');
  wpts = new Array();
  var thiswpt;
  for(var i = 0; i < lines.length; i++)
    {
      thiswpt = Line2Waypoint(lines[i]);
//	AlertWaypoint(thiswpt);
      if(thiswpt.label == 'bad-line' || thiswpt.label == '')
	continue;

      wpts[wpts.length] = CopyWaypoint(thiswpt);
    }
  UpdateMap(pan);
  DataCheck();
  document.getElementById("output").innerHTML = WptTable(wpts);
 
  UpdateMessage(wpts.length + " waypoints are loaded.  Cannot be undone.");
    UpdateCoords();
}

function ClearText()
{
  var clear = confirm("Are you sure you want to delete all waypoints and all in-use labels and begin with a blank trace?");
  if(!clear) return;


  SaveWaypoints();
  document.getElementById("inputtext").value = "";
  LoadText(false);
    ClearInUseLabels();
  UpdateMessage("Removed all waypoints and in-use labels.  Click Undo to revert the waypoints.");
}

function WptTable(wpts)
{
    
    if(wpts == null || wpts.length == 0)
    {
	return "No waypoints loaded.";
    }
    
    var html = '';
    
    var n_visible = 0;
    var n_hidden = 0;
    var n_errors = 0;
    var length = 0;
    var isinuse = false;
    

    for(var i = 0; i < wpts.length; i++)
    {
	// Count visible, hidden, and error waypoints.

	if(IsVisible(wpts[i].label))
	    n_visible++;
	else
	    n_hidden++;
	
	
	
	if(wpts[i].errors > 0)
	    n_errors++;
	
	// Sum the length of the trace.

	if(i >= 1)
	{
	    length += SegMileage(wpts[i-1], wpts[i]);
	}
    }
    
    var labelroots = getLabelRoots(wpts);
    var labelrootcounts = getLabelRootCounts(wpts, labelroots);
    
    
    html += '<table class="wpttable" style="margin-left:auto; margin-right:auto; text-align:left;"><thead></thead><tbody><tr><td style="text-align:left;">'
    html += wpts.length + ' waypoints: ' + n_visible + ' visible, ' + n_hidden + ' hidden.<br>';
    html += (n_errors > 0 ? '<span style="background-color:' + BC_errors + ';">' : '') + n_errors + ' waypoints with possible errors.' + (n_errors > 0 ? '</span>' : '') + '<br>';
    html += 'Length: ' + length.toFixed(2) + ' mi, ' + (length*mi2km).toFixed(2) + ' km<br>';
    html += 'Average spacing: ' + (length/(wpts.length-1)).toFixed(2) + ' mi, ' + (length*mi2km/(wpts.length-1)).toFixed(2) + ' km<br>';
    html += 'Average visible spacing: ' + (length/(n_visible-1)).toFixed(2) + ' mi, ' + (length*mi2km/(n_visible-1)).toFixed(2) + ' km<br>';
    html += '</td></tr></tbody></table><br>';
    
    html += "<table class='wpttable'>\n";
    html += "<thead>\n<tr style='vertical-align:bottom;'><th>Wpt.</th><th>Label</th><th>Root<br>Ct.</th><th>Lat.</th><th>Lon.</th><th>Seg.<br>Dist.</th><th>Vis.<br>Dist.</th><th>Tot.<br>Dist.</th><th>Err.?</th></tr>\n</thead>\n<tbody>";
    var totaldist = 0.;
    var segdist = 0.;
    var visdist = 0.;
    
    for(var i = 0; i < wpts.length; i++)
    {
	totaldist += segdist;
	visdist += segdist;
	
	var c_bkgd = BC_visible;
	var c_text = FC_notinuse;
	var wptjustify = 'left';
	var isvis = IsVisible(wpts[i].label);
	if(!isvis)
	{
	    c_bkgd = BC_hidden;
	    wptjustify = 'right';
	}
	if(wpts[i].errors > 0)
	    c_bkgd = BC_errors;
	
	
	
	html += '<tr style="background-color:' + c_bkgd + ';vertical-align:top;">';
	html += '<td class="cellbutton" onmouseover="javascript: this.className = \'cellbuttonhover\';" onmouseout="javascript: this.className = \'cellbutton\';" onclick="javascript:LabelClick(' + i + ',' + wpts.length + ',\'' + wpts[i].label + '\','  + wpts[i].lat + ',' + wpts[i].lon + ',' + wpts[i].errors + ',\'' + wpts[i].altlabelsstring + '\');">' + (i+1) + '</td>';

	isinuse = IsLabelInUse(wpts[i].label);
	if(isinuse)
	    ctext = FC_inuse;
	else
	    ctext = FC_notinuse;

	html += '<td class="cellbutton"  onmouseover="javascript: this.className = \'cellbuttonhover\';" onmouseout="javascript: this.className = \'cellbutton\';" style="text-align:' + wptjustify + ';" onclick="RelabelWaypoint(' + i + ')">';
	if(isinuse)
	    html += '<span style="color:' + ctext + '";>';
	html += wpts[i].label;
	if(isinuse)
	    html += '</span>';

	if(wpts[i].altlabelsstring != "")
	{
	    //	    AlertWaypoint(wpts[i]);
	    altlabels = wpts[i].altlabelsstring.split(' ');
	    for(j = 0; j < altlabels.length; j++)
	    {
		isinuse = IsLabelInUse(altlabels[j]);
		if(isinuse)
		    ctext = FC_inuse;
		else
		    ctext = FC_notinuse;
		
		html += '<br>';
		if(isinuse)
		    html += '<span style="color:' + ctext + '";>';
		html += altlabels[j];
		if(isinuse)
		    html += '</span>';
	    }
	}
	html += '</td>';
	html += '<td>' + (wpts[i].visible && labelrootcounts[i] >= 2 ? labelrootcounts[i] : "&nbsp;") + '</td>';
	html += '<td>' + wpts[i].lat + '</td>';
	html += '<td>' + wpts[i].lon  + '</td>';
	html += '<td>' + segdist.toFixed(2) + '</td>';
	html += '<td>' + visdist.toFixed(2) + '</td>';
	html += '<td>' + totaldist.toFixed(2) + '</td>';
	html += '<td>' + Error2Abbrev(wpts[i].errors) + '</td>';
	html += '</tr>\n';
	if(i < wpts.length-1)
	    segdist = SegMileage(wpts[i], wpts[i+1]);
	
	if(isvis)
	    visdist = 0.;
	
    }
    html = html.concat('</tbody></table>');
    
    return html;
}

function LabelClick(i, n, label, lat, lon, errors, altlabelsstring)
{
    var info = MarkerInfo(i, n, new Waypoint(label, lat, lon, errors, altlabelsstring));
    L.popup().setLatLng([lat, lon]).setContent(info).openOn(map);
    map.panTo([lat, lon]);
}

function UpdateMap(pan)
{
    trace.remove();
    hitrace.remove();
    lotrace.remove();
    polypoints = new Array();

    var minlat = 999;
    var maxlat = -999;
    var minlon = 999;
    var maxlon = -999;
    
    for(var i = 0; i < markers.length; i++)
    {
	markers[i].off();
	markers[i].remove();
    }

    markers = new Array();
    markerinfo = new Array();
    for(var i = 0; i < wpts.length; i++)
    {
	minlat = Math.min(minlat, wpts[i].lat);
	maxlat = Math.max(maxlat, wpts[i].lat);
	minlon = Math.min(minlon, wpts[i].lon);
	maxlon = Math.max(maxlon, wpts[i].lon);
	
	polypoints[i] = [wpts[i].lat, wpts[i].lon];
	
	markerinfo[i] = MarkerInfo(i, wpts.length, wpts[i]);

	markers[i] = L.marker(polypoints[i], {
	    draggable: (i == thawindex)
	});

	if(!IsVisible(wpts[i].label))
	    markers[i].options.icon = intersectionimage;
	
	markers[i].addTo(map);

	if(i == thawindex)
	{
	    BindReposition(markers[i], i);
	}

	BindInfoWindow(markers[i], markerinfo[i]);
    }

    var midlat = (minlat + maxlat)/2;
    var midlon = (minlon + maxlon)/2;
    
    var nsdist = Mileage(minlat, midlon, maxlat, midlon);
    var ewdist = Mileage(midlat, minlon, midlat, maxlon);
    var maxdist = Math.max(nsdist, ewdist);
    
    var zoom = 17 - (12 + Math.floor(Math.log(maxdist/800)/Math.log(2.0)));
    zoom = Math.max(zoom, 0);
    zoom = Math.min(zoom, 17);
    if(pan)
    {
	map.panTo([midlat, midlon]);
    }
    
    var traceopt = new Object({
	color: '#0000FF',
	weight: 10,
	opacity: 0.2
    });
    trace = L.polyline(polypoints, traceopt).addTo(map);
    
    var boundtracepoints = CalcHighlightLines(wpts, 20);

    var hitraceopt = new Object({
	color: '#FF0000',
	weight: 2,
	opacity: 0.7
    });
    var lotraceopt = new Object({
	color: '#FF0000',
	weight: 2,
	opacity: 0.7
    });
    hitrace = L.polyline(boundtracepoints.hi, hitraceopt).addTo(map);
    lotrace = L.polyline(boundtracepoints.lo, lotraceopt).addTo(map);
  
}

function AddWaypoint()
{  
  SaveWaypoints();
  var center = map.getCenter();
  var centerlabel = prompt('Enter primary label for new waypoint:', '+X' + Math.floor(100000+Math.random()*900000));
  var centerwpt = new Waypoint(centerlabel, center.lat, center.lng, 0, "");
  var bestlength = 1e10;
  var bestpos = -1;

  for(var pos = 0; pos <= wpts.length; pos++)
    {
      var tracelength = TraceLength(wpts, centerwpt, pos);

      if(tracelength <= bestlength)
	{
	  bestlength = tracelength;
	  bestpos = pos;
	}
    }

  var newwpts = wpts.slice(0, bestpos);
  newwpts.push(centerwpt);
  newwpts = newwpts.concat(wpts.slice(bestpos));
  wpts = newwpts;

  thawindex = bestpos;

  UpdateText();
  LoadText(false);

  UpdateMessage("Added waypoint #" + (bestpos+1) + " " + centerwpt.label + " at (" + centerwpt.lat + ", " + centerwpt.lon + "). Click Undo to remove it.");
}

function ReverseWaypoints()
{
    SaveWaypoints();
    thawindex = -1;
    var newwpts = new Array();
    for(var i = 0; i < wpts.length; i++)
    {
	newwpts[i] = CopyWaypoint(wpts[wpts.length-i-1]);
    }
    
    wpts = newwpts;
    map.closePopup();
    UpdateText();
    LoadText(false);
    
    UpdateMessage("Reversed the waypoint order.  Click Undo to restore the order.");

}

function DuplicateHiddenWaypoint(i)
{
  SaveWaypoints();
  var pos = i+1;
  if(i == wpts.length-1)
    pos = i;
  var newwpts = wpts.slice(0, pos);
  var dupwpt = CopyWaypoint(wpts[i]);
  if(dupwpt.label.substr(0,1) != '+' && dupwpt.label.substr(0,2) != '*+')
    {
      dupwpt.label = '+' + dupwpt.label;
    }

  var newlabel = prompt('Enter label for this hidden-duplicate waypoint:', dupwpt.label);
  if(newlabel == '' || newlabel == null)
    return;

  thawindex = -1;
  dupwpt.label = newlabel;

  newwpts.push(dupwpt);
  newwpts = newwpts.concat(wpts.slice(pos));
  wpts = newwpts;
  map.closePopup();

  UpdateText();
  LoadText(false);

  UpdateMessage("Added hidden duplicate waypoint #" + (i+1) + " " + dupwpt.label + " at (" + dupwpt.lat + ", " + dupwpt.lon + "). Click Undo to remove it.");

}

function RemoveWaypoint(i)
{

    var anyinuse = false;
    var isinuse = IsLabelInUse(wpts[i].label);
    anyinuse = anyinuse || isinuse;

    if(!anyinuse && wpts[i].altlabelsstring != "")
    {
	var altlabels = wpts[i].altlabelsstring.split(' ');

	for(j = 0; j < altlabels.length; j++)
	{
	    isinuse = IsLabelInUse(altlabels[j]);
	    anyinuse = anyinuse || isinuse;
	}
    }

    if(anyinuse)
    {
	warning = 'WARNING!!! This waypoint has a label that is in use!!!\n\nYou probably should not remove this waypoint!\n\nAre you sure you want to remove this waypoint?';
	var answer = window.confirm(warning);
	if(answer == false)
	    return;
    }

  SaveWaypoints();

  var lostwpt = wpts[i];
  wpts.splice(i,1);
  map.closePopup();
  thawindex = -1;

  UpdateText();
  LoadText(false);
  
  UpdateMessage("Removed waypoint #" + (i+1) + " " + lostwpt.label + " at (" + lostwpt.lat + ", " + lostwpt.lon + "). Click Undo to re-add it.");
}

function RelabelWaypoint(i)
{
    var warning = '';
    var isinuse = IsLabelInUse(wpts[i].label);
    if(isinuse)
    {
	warning = 'WARNING!!! This label is in use!!!\n\nYou should DEMOTE this label instead of relabeling it!\n\n';
	var answer = window.alert(warning);
	return;
    }

  SaveWaypoints();

  var oldlabel = wpts[i].label;
  var newlabel = prompt('Enter new primary label for this waypoint:', wpts[i].label);
  if(newlabel == null || newlabel == '')
    return;
  
  wpts[i].label = newlabel;

  UpdateText();
  LoadText(false);
  map.closePopup();
  
  UpdateMessage("Changed primary label of waypoint #" + (i+1) + " from " + oldlabel + ' to ' + newlabel + " at (" + wpts[i].lat + ", " + wpts[i].lon + "). Click Undo to revert the label.");
}



function DemotePrimaryLabel(i)
{
  var oldlabel = wpts[i].label;
  var newlabel = prompt('Enter new primary label for this waypoint:', wpts[i].label);
  if(newlabel == null || newlabel == '')
    return;
  
  SaveWaypoints();

  var altlabels = wpts[i].altlabelsstring.split(' ');
  altlabels.push('+' + FrontTrimLabel(oldlabel));
  wpts[i].altlabelsstring = altlabels.join(' ');


  wpts[i].label = newlabel;

  UpdateText();
  LoadText(false);
  map.closePopup();
  
  UpdateMessage("Demoted primary label " + oldlabel + " and added new primary label " + newlabel + " of waypoint #" + (i+1) + " at (" + wpts[i].lat + ", " + wpts[i].lon + "). Click Undo to revert the labels.");
}

function ChangeAltlabelWaypoint(i, j)
{
  var warning = '';
    
  var altlabels = wpts[i].altlabelsstring.split(' ');

  var isinuse = IsLabelInUse(altlabels[j]);
  if(isinuse)
      warning = 'WARNING!!! This label is in use!!!\n\nYou should not change this label!\n\n';

  var oldlabel = altlabels[j];
  var newlabel = prompt(warning + 'Enter new alt. label for this waypoint:', altlabels[j]);
  if(newlabel == null || newlabel == '')
    return;
  
    SaveWaypoints();

    altlabels[j] = newlabel;
    
    wpts[i].altlabelsstring = altlabels.join(' ');

  UpdateText();
  LoadText(false);
  map.closePopup();
  
  UpdateMessage("Changed alt. label #" + (j + 1) + " of waypoint #" + (i+1) + " from " + oldlabel + ' to ' + newlabel + " at (" + wpts[i].lat + ", " + wpts[i].lon + "). Click Undo to revert the label.");
}

function AddAltlabelWaypoint(i, j)
{
    
  var altlabels = wpts[i].altlabelsstring.split(' ');
  var newlabel = prompt('Enter new alt. label for this waypoint:', 'NewAltLabel');
  if(newlabel == null || newlabel == '')
    return;
  
  SaveWaypoints();

  altlabels.push(newlabel);
    
  wpts[i].altlabelsstring = altlabels.join(' ');

  UpdateText();
  LoadText(false);
  map.closePopup();
  
  UpdateMessage("Added alt. label " + newlabel + " to waypoint #" + (i+1) + " at (" + wpts[i].lat + ", " + wpts[i].lon + "). Click Undo to revert the label.");
}


function RemoveAltlabelWaypoint(i, j)
{
    var altlabels = wpts[i].altlabelsstring.split(' ');

    var isinuse = IsLabelInUse(altlabels[j]);
    if(isinuse)
    {
	warning = 'WARNING!!! This label ' + altlabels[j] + ' is in use!!!\n\nYou probably should not remove this label!\n\nAre you sure you want to remove this label?';
	var answer = window.confirm(warning);
	if(answer == false)
	    return;
    }

    SaveWaypoints();
    
    var delaltlabel = altlabels[j];

    altlabels.splice(j, 1);
    
    wpts[i].altlabelsstring = altlabels.join(' ');

    UpdateText();
    LoadText(false);
    map.closePopup();
  
    UpdateMessage("Removed alt. label #" + (j + 1) + " " + delaltlabel + " of waypoint #" + (i+1) + " at (" + wpts[i].lat + ", " + wpts[i].lon + "). Click Undo to revert the label.");
}


function ShiftWaypoint(i,di)
{
  SaveWaypoints();
  thawindex = -1;
  j = i + di;
  if(j < 0 || j > wpts.length-1)
    return;
  if(i < 0 || i > wpts.length-1)
    return;

  map.closePopup();

  wpt1 = CopyWaypoint(wpts[i]);
  wpt2 = CopyWaypoint(wpts[j]);

  wpts[i] = CopyWaypoint(wpt2);
  wpts[j] = CopyWaypoint(wpt1);
  
  UpdateText();
  LoadText(false);
  
  UpdateMessage("Switched waypoints #" + (i+1) + " " + wpt1.label + ' and #' + (j+1) + " " + wpt2.label + ".  Click Undo to switch them back.");
  

}

function ThawWaypoint(i)
{
  thawindex = i;
  map.closePopup();

  UpdateText();
  LoadText(false);
  
  UpdateMessage("Thawed position of waypoint #" + (i+1) + " " + wpts[i].label + ".");

}
 
function RepositionWaypoint(i)
{
  SaveWaypoints();
  map.closePopup();
    
  oldwpt = CopyWaypoint(wpts[i]);
    
  var coords = markers[i].getLatLng();
  wpts[i].lat = coords.lat.toFixed(6);
  wpts[i].lon = coords.lng.toFixed(6);
    
  UpdateText();
  LoadText(false);
    
  UpdateMessage("Repositioned waypoint #" + (i+1) + " " + wpts[i].label + " to (" + wpts[i].lat + ", " + wpts[i].lon + ").  Click Undo to revert the position.");
}



function SaveWaypoints()
{
  oldwpts = new Array();
  for(var i = 0; i < wpts.length; i++)
    {
      oldwpts[i] = CopyWaypoint(wpts[i]);
    }
}


function RestoreWaypoints()
{
  tempwpts = new Array();
  for(var i = 0; i < wpts.length; i++)
    {
      tempwpts[i] = CopyWaypoint(wpts[i]);
    }


  wpts = new Array();
  for(var i = 0; i < oldwpts.length; i++)
    {
      wpts[i] = CopyWaypoint(oldwpts[i]);
    }


  oldwpts = new Array();
  for(var i = 0; i < tempwpts.length; i++)
    {
      oldwpts[i] = CopyWaypoint(tempwpts[i]);
    }

  tempwpts.length = 0;
  UpdateText();
  LoadText(false);
  UpdateMessage("Last change undone.");

}

function UpdateText()
{
  document.getElementById('inputtext').value = Waypoints2Lines(wpts);
}

function WriteTabText()
{
  var wptsave = window.open('new.wpt', 'wptsave' + (new Date()).getTime());
  var text = Waypoints2Lines(wpts);
  text = text.replace(/\n/g, "<br>\n");
  wptsave.document.write(text);
  wptsave.document.close();
}

function TraceLength(wpts, newwpt, pos)
{
  var newwpts = wpts.slice(0, pos);
  newwpts.push(newwpt);
  newwpts = newwpts.concat(wpts.slice(pos));
  
  totaldist = 0;
  for(var i = 0; i < newwpts.length-1; i++)
    {
      totaldist += SegMileage(newwpts[i], newwpts[i+1]);
    }

  return totaldist;
  
}

function Mileage(lat1, lon1, lat2, lon2)
{
  if(lat1 == lat2 && lon1 == lon2)
    return 0.;

  var rad = 3963.;
  var deg2rad = Math.PI/180.;
  var ang = Math.cos(lat1 * deg2rad) * Math.cos(lat2 * deg2rad) * Math.cos((lon1 - lon2)*deg2rad) + Math.sin(lat1 * deg2rad) * Math.sin(lat2 * deg2rad);
  return Math.acos(ang) * 1.02112 * rad;

}

function SegMileage(wpt1, wpt2)
{
  return Mileage(wpt1.lat, wpt1.lon, wpt2.lat, wpt2.lon);
}

function MarkerInfo(i, n, wpt)
{
  var isinuse = IsLabelInUse(wpt.label);
  var prespan = '<span style="color:' + FC_inuse + ';">';
  var postspan = '</span>';

  var info =  '<p style="line-height:160%;"><span style="font-size:20pt;">' + (isinuse ? prespan : '') + wpt.label + (isinuse ? postspan : '') + '</span>&nbsp;<a onclick="RelabelWaypoint(' + i + ')" class="button">Relabel</\a>&nbsp;<a onclick="DemotePrimaryLabel(' + i + ')" class="button">Demote</\a><br>';
    
  var altlabels = wpt.altlabelsstring.split(' ');
  for(j = 0; j < altlabels.length; j++)
  {
	if(altlabels[j] == "")
	    continue;
	
	isinuse = IsLabelInUse(altlabels[j]);
	info += (isinuse ? prespan : '') + altlabels[j] + (isinuse ? postspan : '') + '&nbsp;<a onclick="ChangeAltlabelWaypoint(' + i + ',' + j + ')" class="button">Change</\a>&nbsp;<a onclick="RemoveAltlabelWaypoint(' + i + ',' + j + ')" class="button">Remove</\a><br>';

  }
  info += '<a onclick="AddAltlabelWaypoint(' + i + ')" class="button">Add Alt. Label</\a><br>';
  info += '<br><b>Waypoint ' + (i+1) + ' of ' + n + '<\/b><br><b>Coords.:<\/b> ' + wpt.lat + '&deg;, ' + wpt.lon + '&deg;<\/p>';

  if(i == thawindex)
    info += '<p style="color:#990000;font-weight:bold;">This waypoint can be repositioned.</p>';

    info += '<p style="line-height:160%;"><b>Actions:</b><br><a onclick="ThawWaypoint(' + i + ')" class="button">Thaw location</\a><br><a onclick="ShiftWaypoint(' + i + ',-1)" class="button">Shift toward the beginning</\a><br><a onclick="ShiftWaypoint(' + i + ',1)" class="button">Shift toward the end</\a><br><a onclick="RemoveWaypoint(' + i + ')" class="button">Remove<\/a><\/p>';
    info += '</p>';
    return info;
}

function ReduceLabel(label)
{
    label = label.toLowerCase();
    label = label.replace('+', '');
    label = label.replace('*', '');
    return label;
}

function FrontTrimLabel(label)
{
    label = label.replace('+', '');
    label = label.replace('*', '');
    return label;
}

function DataCheck()
{
    BUS_WITH_I();
    DUPLICATE_COORDS();
    DUPLICATE_LABEL();
    HIDDEN_TERMINUS();
    LABEL_INVALID_CHAR();
    LABEL_INVALID_ENDS();
    LABEL_LOOKS_HIDDEN();
    LABEL_PARENS();
    LABEL_SLASHES();
    LABEL_TOO_LONG();
    LABEL_UNDERSCORES();
    LACKS_GENERIC();
    LONG_UNDERSCORE();
    NONTERMINAL_UNDERSCORE();
    OUT_OF_BOUNDS();
    SHARP_ANGLE();
    VISIBLE_DISTANCE();
}

function Error2Abbrev(e)
{
  text = '';
  if(e & DC_BUS_WITH_I)
    text += '<span title="Label uses Bus with I- (Interstate)">[BUS_WITH_I]</span><br>';
  if(e & DC_BAD_ANGLE)
    text += '<span title="Consecutive points at same coordinates">[BAD_ANGLE]</span><br>';
  if(e & DC_DUPLICATE_COORDS)
    text += '<span title="Duplicated coordinates">[DUPLICATE_COORDS]</span><br>';
  if(e & DC_DUPLICATE_LABEL)
    text += '<span title="Duplicated labels">[DUPLICATE_LABEL]</span><br>';
  if(e & DC_HIDDEN_TERMINUS)
    text += '<span title="Terminus is not visible">[HIDDEN_TERMINUS]</span><br>';
  if(e & DC_INVALID_FINAL_CHAR)
    text += '<span title="Invalid char at end of label">[INVALID_FINAL_CHAR]</span><br>';
  if(e & DC_INVALID_FIRST_CHAR)
    text += '<span title="Invalid char at beginning of label">[INVALID_FIRST_CHAR]</span><br>';
  if(e & DC_LABEL_INVALID_CHAR)
    text += '<span title="Invalid character(s)">[LABEL_INVALID_CHAR]</span><br>';
  if(e & DC_LABEL_LOOKS_HIDDEN)
    text += '<span title="Looks like a default hidden waypont label without the leading +">[LABEL_LOOKS_HIDDEN]</span><br>';
  if(e & DC_LABEL_PARENS)
    text += '<span title="Reversed or wrong number of parentheses in primary label">[LABEL_PARENS]</span><br>';
  if(e & DC_LABEL_SLASHES)
    text += '<span title="Too many slashes (> 1)">[LABEL_SLASHES]</span><br>';
  if(e & DC_LABEL_TOO_LONG)
    text += '<span title="Label too long to fit in database">[LABEL_TOO_LONG]</span><br>';
  if(e & DC_LABEL_UNDERSCORES)
    text += '<span title="Too many underscored suffixes (> 1)">[LABEL_UNDERSCORES]</span><br>';
  if(e & DC_LACKS_GENERIC)
    text += '<span title="Label lacks the generic highway type">[LACKS_GENERIC]</span><br>';
  if(e & DC_LONG_SEGMENT)
    text += '<span title="single segment > 20 miles">[LONG_SEGMENT]</span><br>';
  if(e & DC_LONG_UNDERSCORE)
    text += '<span title="Label has long underscore suffix">[LONG_UNDERSCORE]</span><br>';
  if(e & DC_NONTERMINAL_UNDERSCORE)
    text += '<span title="Label has underscore suffix before slash">[NONTERMINAL_UNDERSCORE]</span><br>';
  if(e & DC_OUT_OF_BOUNDS)
    text += '<span title="Latitude beyond +/-90 deg or longitude beyiond +/- 180 deg">[OUT_OF_BOUNDS]</span><br>';
  if(e & DC_SHARP_ANGLE)
    text += '<span title="Sharp Angle (> 135 deg)">[SHARP_ANGLE]</span><br>';
  if(e & DC_VISIBLE_DISTANCE)
    text += '<span title="Long distance (Vis. Dist. > 10 mi, 16 km) between this and the previous visible point">[VISIBLE_DISTANCE]</span><br>';
  
  return text;
  
}

function BUS_WITH_I()
{
    for(var i = 0; i < wpts.length; i++)
    {
	if(wpts[i].label.search(/^\*?I\-[0-9]+[EWCNS]?Bus/i) >= 0)
	{
	    wpts[i].errors |= DC_BUS_WITH_I;
	}
    }
}

function DUPLICATE_COORDS()
{
  for(var i1 = 0; i1 < wpts.length - 1; i1++)
    {
      for(var i2 = i1 + 1; i2 < wpts.length; i2++)
	{
	  var lat1 = parseFloat(wpts[i1].lat).toFixed(6);
	  var lon1 = parseFloat(wpts[i1].lon).toFixed(6);
	  var lat2 = parseFloat(wpts[i2].lat).toFixed(6);
	  var lon2 = parseFloat(wpts[i2].lon).toFixed(6);

	  if(lat1 == lat2 && lon1 == lon2)
	    {
	      wpts[i1].errors |= DC_DUPLICATE_COORDS;
	      wpts[i2].errors |= DC_DUPLICATE_COORDS;
	    }
	}
    }
}

function DUPLICATE_LABEL()
{

    // Make array of labels.

    var labels = Array();
    
    for(var w = 0; w < wpts.length; w++)
    {
	labels[w] = Array();
	labels[w].push(ReduceLabel(wpts[w].label));
	var altlabels = wpts[w].altlabelsstring.split(' ');
	if(altlabels != "")
	{
	    for(var a = 0; a < altlabels.length; a++)
	    {
		labels[w].push(ReduceLabel(altlabels[a]));
	    }	
	    
	}
    }
    
    // Check for duplicate labels within a waypoint.

    for(var w = 0; w < wpts.length; w++)
    {
	if(labels[w].length <= 1)
	    continue;

	for(var a1 = 0; a1 < labels[w].length - 1; a1++)
	{
	    for(var a2 = a1 + 1; a2 < labels[w].length; a2++)
	    {
		if(labels[w][a1] == labels[w][a2])
		{
		    wpts[w].errors |= DC_DUPLICATE_LABEL;
		}
	    }
	}
    }

    // Check for duplicate labels between waypoints.

    for(var w1 = 0; w1 < wpts.length - 1; w1++)
    {
	for(var w2 = w1 + 1; w2 < wpts.length; w2++)
	{
	    for(var a1 = 0; a1 < labels[w1].length; a1++)
	    {
		for(var a2 = 0; a2 < labels[w2].length; a2++)
		{
		    if(labels[w1][a1] == labels[w2][a2])
		    {
			wpts[w1].errors |= DC_DUPLICATE_LABEL;
			wpts[w2].errors |= DC_DUPLICATE_LABEL;
		    }
		}
	    }
	}
    }

}

function OldDUPLICATE_LABEL()
{
  for(var i1 = 0; i1 < wpts.length; i1++)
    {
	var label1 = ReduceLabel(wpts[i1].label);
	var altlabelsstring1 = wpts[i1].altlabelsstring;
	var altlabels1 = altlabelsstring1.split(' ');
	for(var j1 = 0; j1 < altlabels1.length; j1++)
	{
	    altlabels1[j1] = ReduceLabel(altlabels1[j1]);
	}
	altlabels1.push(label1);
	
	//Compare all labels within the waypoint
	for(var j1 = 0; j1 < altlabels1.length - 1; j1++)
	{

	    for(var j2 = j1 + 1; j2 < altlabels1.length; j2++)
	    {
		if(altlabels1[j2] == "")
		    continue;

		if(altlabels1[j1] == altlabels1[j2])
		{
		    alert("j1=" + j1 + "\nj2=" + j2 + "\nlabel1=" + altlabels1[j1] + "\nlabel2=" + altlabels1[j2]);
		    wpts[i1].errors |= DC_DUPLICATE_LABEL;
		}
	    }
	}
	
	for(var i2 = i1 + 1; i2 < wpts.length; i2++)
	{
	    var label2 = ReduceLabel(wpts[i2].label)
	    var altlabelsstring2 = wpts[i2].altlabelsstring;
	    var altlabels2 = altlabelsstring2.split(' ');
	    for(var j2 = 0; j2 < altlabels2.length; j2++)
	    {
		altlabels2[j2] = ReduceLabel(altlabels2[j2]);
	    }
	    altlabels2.push(label2);
	    

	    //Compare all labels between waypoints
	    for(var j1 = 0; j1 < altlabels1.length; j1++)
	    {
		if(altlabels1[j1] == "")
		    continue;

		for(var j2 = 0; j2 < altlabels2.length; j2++)
		{
		    if(altlabels1[j2] == "")
			continue;

		    if(altlabels1[j1] == altlabels2[j2])
		    {
			alert("j1=" + j1 + "\nj2=" + j2 + "\nlabel1=" + altlabels1[j1] + "\nlabel2=" + altlabels2[j2]);
			wpts[i1].errors |= DC_DUPLICATE_LABEL;
			wpts[i2].errors |= DC_DUPLICATE_LABEL;
		    }
		}
	    }


	    
	}
    }
}

function HIDDEN_TERMINUS()
{
    if (wpts.length > 1)
    {
	if(!IsVisible(wpts[0].label))
	{
	    wpts[0].errors |= DC_HIDDEN_TERMINUS;
	}
	if(!IsVisible(wpts[wpts.length-1].label))
	{
	    wpts[wpts.length-1].errors |= DC_HIDDEN_TERMINUS;
	}
    }
}

function LABEL_INVALID_CHAR()
{
    for(var i = 0; i < wpts.length; i++)
    {
	if(wpts[i].label.search(/^\+?\*?[a-zA-Z0-9()\/_\-\.]+$/) < 0)
	{
	    wpts[i].errors |= DC_LABEL_INVALID_CHAR;
	    continue;
	}
	if (wpts[i].altlabelsstring.length > 0)
	{
	    var alts = wpts[i].altlabelsstring.split(' ');
	    for (var a = 0; a < alts.length; a++)
	    {
		if(alts[a].search(/^\+?\*?[a-zA-Z0-9()\/_\-\.]+$/) < 0)
		{
		    wpts[i].errors |= DC_LABEL_INVALID_CHAR;
		    break;
		}
	    }
	}
    }
}

function LABEL_INVALID_ENDS()
{
    for(var i = 0; i < wpts.length; i++)
    {
	if(IsVisible(wpts[i].label) && wpts[i].label.search(/[_\/]$/) >= 0)
	{
		wpts[i].errors |= DC_INVALID_FINAL_CHAR;
	}
	if(wpts[i].label.search(/^\**[_\/(]/) >= 0)
	{
		wpts[i].errors |= DC_INVALID_FIRST_CHAR;
	}
    }
}

function LABEL_LOOKS_HIDDEN()
{
    for(var i = 0; i < wpts.length; i++)
    {
	if (wpts[i].label.search(/^X[0-9]{6}$/) >= 0)
	{
		wpts[i].errors |= DC_LABEL_LOOKS_HIDDEN;
	}
    }
}

function LABEL_PARENS()
{
    for(var i = 0; i < wpts.length; i++)
    {
	if (!IsVisible(wpts[i].label))
	{
	    continue;
	}

	var n_l = wpts[i].label.split('(').length - 1;
	var n_r = wpts[i].label.split(')').length - 1;
	
	if( n_l != n_r || n_l > 1 || (n_l == 1 && wpts[i].label.indexOf('(') > wpts[i].label.indexOf(')')) )
	{
	    wpts[i].errors |= DC_LABEL_PARENS;
	}
    }
}

function LABEL_SLASHES()
{
    for(var i = 0; i < wpts.length; i++)
    {
	if (!IsVisible(wpts[i].label))
	{
	    continue;
	}

	var n_slash = wpts[i].label.split('/').length - 1;
	
	if(n_slash > 1)
	{
	    wpts[i].errors |= DC_LABEL_SLASHES;
	}
    }
}

function LABEL_TOO_LONG()
{
    for(var i = 0; i < wpts.length; i++)
    {
	if (wpts[i].label.length > 26)
	{
	    wpts[i].errors |= DC_LABEL_TOO_LONG;
	}
    }
}

function LABEL_UNDERSCORES()
{
    for(var i = 0; i < wpts.length; i++)
    {
	if (!IsVisible(wpts[i].label))
	{
	    continue;
	}

	var n_underscore = wpts[i].label.split('_').length - 1;
	
	if(n_underscore > 1)
	{
	    wpts[i].errors |= DC_LABEL_UNDERSCORES;
	}
    }
}

function LACKS_GENERIC()
{
    for(var i = 0; i < wpts.length; i++)
    {
	if(wpts[i].label.search(/^\*?old\d/i) >= 0)
	{
	    wpts[i].errors |= DC_LACKS_GENERIC;
	}
    }
}

function LONG_UNDERSCORE()
{
    for(var i = 0; i < wpts.length; i++)
    {
	var uindex = wpts[i].label.indexOf('_')
	if(IsVisible(wpts[i].label) && uindex >= 0 && uindex < wpts[i].label.length - 4)
	{
	    if(wpts[i].label.charAt(wpts[i].label.length-1) < 'A'
	    || wpts[i].label.charAt(wpts[i].label.length-1) > 'Z'
	    || uindex < wpts[i].label.length - 5)
	    {
		wpts[i].errors |= DC_LONG_UNDERSCORE;
	    }
	}
    }
}

function NONTERMINAL_UNDERSCORE()
{
    for(var i = 0; i < wpts.length; i++)
    {
	if(IsVisible(wpts[i].label) && 
	    wpts[i].label.search(/_.+\//) >= 0
	  )
	{
	    wpts[i].errors |= DC_NONTERMINAL_UNDERSCORE;
	}
    }
}

function OUT_OF_BOUNDS()
{
    for(var i = 0; i < wpts.length; i++)
    {
	if(wpts[i].lat > 90 || wpts[i].lat < -90 || wpts[i].lon > 180 || wpts[i].lon < -180)
	{
	    wpts[i].errors |= DC_OUT_OF_BOUNDS;
	}
    }
}

function SHARP_ANGLE()
{
  var deg2rad = Math.PI/180.;
  
  for(var i = 1; i < wpts.length-1; i++)
    {
      if((wpts[i].lat == wpts[i-1].lat && wpts[i].lon == wpts[i-1].lon)
      || (wpts[i].lat == wpts[i+1].lat && wpts[i].lon == wpts[i+1].lon))
      {
	  wpts[i].errors |= DC_BAD_ANGLE;
	  return;
      }

      var x0 = Math.cos(wpts[i-1].lon*deg2rad) * Math.cos(wpts[i-1].lat*deg2rad);
      var x1 = Math.cos(wpts[i].lon*deg2rad) * Math.cos(wpts[i].lat*deg2rad);
      var x2 = Math.cos(wpts[i+1].lon*deg2rad) * Math.cos(wpts[i+1].lat*deg2rad);

      var y0 = Math.sin(wpts[i-1].lon*deg2rad) * Math.cos(wpts[i-1].lat*deg2rad);
      var y1 = Math.sin(wpts[i].lon*deg2rad) * Math.cos(wpts[i].lat*deg2rad);
      var y2 = Math.sin(wpts[i+1].lon*deg2rad) * Math.cos(wpts[i+1].lat*deg2rad);

      var z0 = Math.sin(wpts[i-1].lat*deg2rad);
      var z1 = Math.sin(wpts[i].lat*deg2rad);
      var z2 = Math.sin(wpts[i+1].lat*deg2rad);

      var angle = 180./Math.PI * 
	Math.acos(
		  ((x2 - x1)*(x1 - x0) + (y2 - y1)*(y1 - y0) + (z2 - z1)*(z1 - z0)) 
		  / Math.sqrt(
			      ((x2 - x1)*(x2 - x1) + (y2 - y1)*(y2 - y1) + (z2 - z1)*(z2 - z1))
			      * ((x1 - x0)*(x1 - x0) + (y1 - y0)*(y1 - y0) + (z1 - z0)*(z1 - z0))
			      )
		  );
      if(angle > 135)
	{
	  wpts[i].errors |= DC_SHARP_ANGLE;
	}
    }
}

function VISIBLE_DISTANCE()
{
  var visdist = 0.;
  var segdist = 0.;
  for(var i = 1; i < wpts.length; i++)
    {
      segdist = SegMileage(wpts[i-1], wpts[i]);
      if(segdist > 20)
	{
	  wpts[i].errors |= DC_LONG_SEGMENT;
	}
      visdist += segdist;
      if(visdist > 10 && IsVisible(wpts[i].label))
	{
	  wpts[i].errors |= DC_VISIBLE_DISTANCE;
	}

      if(IsVisible(wpts[i].label))
	visdist = 0;
    }
}


function IsVisible(label)
{
  var isvis = true;
  if(label.substr(0,1) == '+' || label.substr(0,2) == '*+')
    isvis = false;

  return isvis;
}


function OSMUrl(lat, lon) {return "http://www.openstreetmap.org/?lat=" + lat.toFixed(6) + "&amp;lon=" + lon.toFixed(6);};
function YahooUrl(lat, lon) {return "http://maps.yahoo.com/#mvt=m&amp;lat=" + lat.toFixed(6) + "&amp;lon=" + lon.toFixed(6) + "&amp;zoom=16";};
function GoogleUrl(lat, lon) {return "http://maps.google.com/maps?ll=" + lat.toFixed(6) + "," + lon.toFixed(6) + "&amp;z=15";};
function BingUrl(lat, lon) {return "http://www.bing.com/maps/?v=2&amp;cp=" + lat.toFixed(6) + "~" + lon.toFixed(6) + "&amp;lvl=15";};
function GMSVUrl(lat, lon) {return "http://maps.google.com/?ll=" + lat.toFixed(6) + "," + lon.toFixed(6) + "&amp;cbp=12,0,,0,5&amp;cbll=" + lat.toFixed(6) + "," + lon.toFixed(6) + "&amp;layer=c";};

function UpdateCoords() 
{     
  var center = map.getCenter();    
    document.getElementById("coordbar").innerHTML = '<a onclick="javascript:AddWaypoint();" class="button">Add waypoint</a> ' + OSMUrl(center.lat, center.lng) + "<br>Open location in <a href='" + GMSVUrl(center.lat, center.lng) + "' target='sv' class='button'>Street View</a> <a href='" + OSMUrl(center.lat, center.lng) + "&amp;zoom=15' target='o' class='button'>OSM</a> <a href='" + GoogleUrl(center.lat, center.lng) + "' target='g' class='button'>Google</a> <a href='" + YahooUrl(center.lat, center.lng) + "' target='y' class='button'>Yahoo</a> <a href='" + BingUrl(center.lat, center.lng) + "' target='b' class='button'>Bing</a>";    
}

function ChangeLineThickness(t)
{
  thickness = t;
  UpdateMap(false);
}

function ChangeLayout()
{
  layout++;
  if(layout >= n_layouts)
    layout = 0;
  switch(layout)
    {
    case 1: //wide screen
      document.getElementById('map').style.cssText = "position: absolute; top:0px; bottom:0px; left:550px; right:0px; overflow:hidden; ";
      document.getElementById('waypoints').style.cssText = "position: absolute; left: 0px; top: 340px; right:550px; width: 550px; bottom:0px; height:auto; overflow:auto;";
      document.getElementById('inputbox').style.cssText = "position: fixed; top:0px; bottom:340px; height:340px; left:0px; right:550px; width:550px; overflow:auto; padding:5px;";
      break;

    default: // narrow-screen 
      document.getElementById('map').style.cssText = "position: absolute; top:300px; bottom:0px; left:400px; right:0px; overflow:hidden; ";
      document.getElementById('waypoints').style.cssText = "position: fixed; left: 0px; top: 0px; right:400px; height: 100%; width: 400px; overflow:auto;";
      document.getElementById('inputbox').style.cssText = "position: absolute; top:0px; bottom:300px; height:100%; left:400px; right:0px; overflow:auto; padding:5px;";
      break;

    }
}

function WptClose()
{
  return "Really close the Waypoint Editor? All data will be lost.";
}

function AlertWaypoint(wpt)
{
    text = 'label = ' + wpt.label + "\n";
    text += 'altlabelsstring = ' + wpt.altlabelsstring + "\n";
    text += 'lat = ' + wpt.lat + "\n";
    text += 'lon = ' + wpt.lon + "\n";

    alert(text);
}

function BindInfoWindow(marker, markerinfo)
{
    marker.bindPopup(markerinfo);
}

function BindReposition(marker, i)
{
    marker.on('dragend', function() {RepositionWaypoint(i);});

}


function LoadInUseLabels()
{
    UpdateMessage("Loading in-use labels...");
    
    var labelstext = "";
    inuselabelstext = "";
    inuselabels = new Array();

    var text = document.getElementById("inuselabelstext").value;
    text = text.replace(/\s+/g, " ");
    text = text.replace(/^\s+|\s+$/g, "");

    var n_colons = 0;
    mtext = text.match(":");
    if(mtext == null)
	n_colons = 0;
    else
	n_colons = mtext.length;

    if(n_colons > 1)
    {
	UpdateMessage("Cannot parse in-use labels text. Too many colons!");
	return null;
    }
    else if(n_colons == 1)
    {
	var xtext = text.split(":");
	inuselabelsroute = xtext[0];
	labelstext = xtext[1];
    }
    else
    {
	labelstext = text;
	inuselabelsroute = "";
    }

    labelstext = labelstext.replace(/^\s+|\s+$/g, "");
    labelstext = labelstext.replace(/\s+/g, " ");
    inuselabels = labelstext.split(" ");

    var n_inuselabels = inuselabels.length;
    if(labelstext == "")
	inuselabels.length = 0;

    UpdateInUseLabels();

    LoadText(false);

    var msg = 'Loaded ' + inuselabels.length + ' in-use labels';
    if(inuselabelsroute != "")
    {
	msg += ' for route ' + inuselabelsroute;
    }
    else
    {
	msg += ' for unidentified route';
    }
    msg += '. Cannot be undone.';
    UpdateMessage(msg);
}


function UpdateInUseLabels()
{
    var newtext = "";

    if(inuselabels != null)
	newtext = inuselabelsroute + ': ' + inuselabels.join(" ");
    document.getElementById("inuselabelstext").value = newtext;
}


function ClearInUseLabels()
{
    UpdateMessage("Clearing in-use labels...");
    
    inuselabels = new Array();
    inuselabelsroute = "";

    UpdateInUseLabels();
    
    LoadText(false);

    UpdateMessage("Cleared in-use labels.");
}

function IsLabelInUse(label)
{
    var isinuse = false;
    
    if(inuselabels == null)
	return false;

    var reducedlabel = ReduceLabel(label);

    if(reducedlabel == "")
	return false;

    var n_inuselabels = inuselabels.length;
    var i = 0;
    while(isinuse == false && i < n_inuselabels)
    {
	
	if(reducedlabel == ReduceLabel(inuselabels[i]))
	    isinuse = true;

	i++;
    }

    return isinuse;
}


function getLabelRoots(wpts)
{
    var n_wpts = wpts.length;
    var labelroots = new Array();

    for(var i = 0; i < n_wpts; i++)
    {
	labelroots[i] = ReduceLabel(wpts[i].label);
	labelroots[i] = labelroots[i].replace(/_.+/g, "");
	labelroots[i] = labelroots[i].replace(/\(.+\)/g, "");
    }
    
    return labelroots;
}

function getLabelRootCounts(wpts, labelroots)
{
    var n_wpts = wpts.length;
    var labelrootcounts = new Array();

    for(var i = 0; i < n_wpts; i++)
    {
	if(!wpts[i].visible)
	{
	    labelrootcounts[i] = 1;
	    continue;
	}
	
	labelrootcounts[i] = 0;
	
	for(var j = 0; j < n_wpts; j++)
	{
	    if(labelroots[i] == labelroots[j] && wpts[j].visible)
		labelrootcounts[i]++;
	}
    }

    return labelrootcounts;
}
