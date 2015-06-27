/* This function will make it easier for us to move waypoints
 * around, both in the data and in the DOM. */
Array.prototype.move = function (from, to) {
	this.splice(to, 0, this.splice(from, 1)[0]);
};
NodeList.prototype.splice = Array.prototype.splice;
NodeList.prototype.move = NodeList.prototype.move;

var visibleWptMarker = {
	url: "wpt-visible.png",
	size: new google.maps.Size(8, 8),
	origin: new google.maps.Point(0, 0),
	anchor: new google.maps.Point(4, 4)
};
var hiddenWptMarker = {
	url: "wpt-hidden.png",
	size: new google.maps.Size(8, 8),
	origin: new google.maps.Point(0, 0),
	anchor: new google.maps.Point(4, 4)
};

function isHidden(wpt) {
	return wpt.label[0] == "+";
}

function dragEnd(i) {
	var marker = markers[i];
	var wpt = waypoints[i];
	var pos = marker.getPosition();
	wpt.lat = Math.round(1e6 * pos.lat()) / 1e6;
	wpt.lng = Math.round(1e6 * pos.lng()) / 1e6;

	points = [];
	waypoints.forEach(function (w) {
		points.push(new google.maps.LatLng(w.lat, w.lng));
	});
	path.setPath(points);
	update();

	expandWaypoint(i);
	if (getExpanded() == -1) {
		expandWaypoint(i);
	}
};

function readData(data) {
	var waypoints = [];
	var lines = data.split(/\n/);
	lines.forEach(function (line) {
		line = line.trim();
		var fields = line.split(/ +/);
		if (fields.length < 3) {
			return;
		}

		var lat = fields[0], lng = fields[1], main = fields[2], alts = fields.slice(3);
		var wpt = {label: main, lat: lat, lng: lng};
		if (alts) {
			wpt.altLabels = alts;
		}

		waypoints.push(wpt);
	});

	return waypoints;
}

function readCHMFormatData(data) {
	var waypoints = [];
	var lines = data.split(/\n/);
	lines.forEach(function (line) {
		line = line.trim();
		var fields = line.split(/ +/);
		if (fields.length < 2) {
			return;
		}

		var main = fields[0], alts = fields.slice(1, -1), url = fields[fields.length - 1];
		var q = parseUri(url).queryKey;
		var wpt = {label: main, lat: q.lat, lng: q.lon};
		if (alts) {
			wpt.altLabels = alts;
		}

		waypoints.push(wpt);
	});

	return waypoints;
}

function loadWaypoints(waypoints, map) {
	var bounds = null;
	var path = null;
	var points = [];
	var markers = [];

	for (var i = 0; i < waypoints.length; i++) {
		var wpt = waypoints[i];
		var idx = i;
		var coords = new google.maps.LatLng(wpt.lat, wpt.lng);
		var marker = new google.maps.Marker({
			map: map,
			draggable: true,
			position: coords,
			title: wpt.label,
			icon: (isHidden(wpt) ? hiddenWptMarker : visibleWptMarker)
		});
		if (bounds === null) {
			bounds = new google.maps.LatLngBounds(coords, coords);
		} else {
			bounds.extend(coords);
		}
		points.push(coords);
		markers.push(marker);

		google.maps.event.addListener(marker, "click", createExpandWaypoint(i));
		google.maps.event.addListener(marker, "dragend", createDragEnd(i));
	};

	path = new google.maps.Polyline({
		map: map,
		path: points,
		strokeColor: "#214478",
		strokeOpacity: 0.7
	});

	return {markers: markers, path: path, bounds: bounds};
}

function getSegmentDistance(p1, p2) {
	return google.maps.geometry.spherical.computeDistanceBetween(p1, p2) / 1000.;
}

function getTotalDistance(points) {
	return google.maps.geometry.spherical.computeLength(points) / 1000.;
}

function getAngle(p1, p2, p3) {
	var heading1 = google.maps.geometry.spherical.computeHeading(p2, p1);
	var heading2 = google.maps.geometry.spherical.computeHeading(p2, p3);
	var angle = heading2 - heading1;
	if (angle < 0) {
		angle = 360 + angle;
	}
	if (angle > 180) {
		angle = 360 - angle;
	}
	return Math.round(angle * 100) / 100;
}

function latLngFromWaypoint(pt) {
	return new google.maps.LatLng(pt.lat, pt.lng);
}

function countParens(label) {
	var parens = 0;
	for (var i = 0; i < label.length; i++) {
		chr = label.charAt(i);
		if (chr == "(") {
			parens++;
		} else if (chr == ")") {
			parens--;
		}
	}
	return parens;
}

function countSlashes(label) {
	var slashes = 0;
	for (var i = 0; i < label.length; i++) {
		chr = label.charAt(i);
		if (chr == "/") {
			slashes++;
		}
	}
	return slashes;
}

var sharpAngleThreshold = 60;
var longSegmentThreshold = 30;
var validLabel = /^[A-Za-z0-9_\-()/+*]+$/;

function checkErrors(wpts) {
	var errors = [];

	/* Segments with an angle of less than 'sharpAngleThreshold' */
	for (var i = 0; i < wpts.length - 2; i++) {
		var angle = getAngle(latLngFromWaypoint(wpts[i]),
			latLngFromWaypoint(wpts[i + 1]),
			latLngFromWaypoint(wpts[i + 2]));
		if (angle < sharpAngleThreshold) {
			errors.push({waypoint: i + 2, error: "Sharp angle (" + angle + "°)"});
		}
	}

	/* Duplicate labels */
	var labels = {};
	for (var i = 0; i < wpts.length; i++) {
		var wpt = wpts[i];
		if (labels[wpt.label]) {
			labels[wpt.label].push(i);
		} else {
			labels[wpt.label] = [i];
		}
	}
	Object.getOwnPropertyNames(labels).forEach(function (label) {
		if (labels[label].length > 1) {
			labels[label].forEach(function (wpt) {
				errors.push({waypoint: wpt, error: "Duplicate label", trueError: true});
			});
		}
	});

	/* Duplicate coordinates */
	var coords = {};
	for (var i = 0; i < wpts.length; i++) {
		var wpt = wpts[i];
		var latlng = wpt.lat + "," + wpt.lng;
		if (coords[latlng]) {
			coords[latlng].push(i);
		} else {
			coords[latlng] = [i];
		}
	}
	Object.getOwnPropertyNames(coords).forEach(function (coord) {
		if (coords[coord].length > 1) {
			coords[coord].forEach(function (wpt) {
				errors.push({waypoint: wpt, error: "Duplicate coordinates"});
			});
		}
	});

	/* Invalid characters */
	for (var i = 0; i < wpts.length; i++) {
		var wpt = wpts[i];
		if (!wpt.label.match(validLabel)) {
			errors.push({waypoint: i, error: "Invalid main label", trueError: true});
		}
		if (wpt.altLabels) {
			var invalidAlt = false;
			wpt.altLabels.forEach(function (alt) {
				if (!alt.match(validLabel)) {
					invalidAlt = true;
				}
			});
			if (invalidAlt) {
				errors.push({waypoint: i, error: "Invalid alt label(s)", trueError: true});
			}
		}
	}

	/* Long segments */
	for (var i = 0; i < wpts.length - 1; i++) {
		var pt1 = wpts[i], pt2 = wpts[i + 1];
		var p1 = new google.maps.LatLng(pt1.lat, pt1.lng),
			p2 = new google.maps.LatLng(pt2.lat, pt2.lng);
		if (getSegmentDistance(p1, p2) > longSegmentThreshold) {
			errors.push({waypoint: i + 1, error: "Long segment"});
		}
	}

	/* Unbalanced parentheses */
	for (var i = 0; i < wpts.length; i++) {
		var wpt = wpts[i];
		if (countParens(wpt.label) != 0) {
			errors.push({waypoint: i, error: "Unmatched parentheses in main label", trueError: true});
		}
		if (wpt.altLabels) {
			var unmatchedInAlt = false;
			wpt.altLabels.forEach(function (alt) {
				if (countParens(alt) != 0) {
					unmatchedInAlt = true;
				}
			});
			if (unmatchedInAlt) {
				errors.push({waypoint: i, error: "Unmatched parentheses in alt label(s)", trueError: true});
			}
		}
	}

	/* Too many slashes */
	for (var i = 0; i < wpts.length; i++) {
		var wpt = wpts[i];
		if (countSlashes(wpt.label) > 1) {
			errors.push({waypoint: i, error: "Too many slashes in main label", trueError: true});
		}
		if (wpt.altLabels) {
			var slashesInAlt = false;
			wpt.altLabels.forEach(function (alt) {
				if (countSlashes(alt) > 1) {
					slashesInAlt = true;
				}
			});
			if (slashesInAlt) {
				errors.push({waypoint: i, error: "Too many slashes in alt label(s)", trueError: true});
			}
		}
	}

	return errors;
}
