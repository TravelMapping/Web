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
	return google.maps.geometry.spherical.computeDistanceBetween(p1, p2);
}

function getTotalDistance(points) {
	return google.maps.geometry.spherical.computeLength(points) / 1000.;
}

function isValidWaypointName(name) {
	return name != "" /* and some other checks */;
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

var sharpAngleThreshold = 60;

function checkErrors(wpts) {
	var errors = [];

	/* Segments with an angle of less than 'sharpAngleThreshold' */
	for (var i = 0; i < wpts.length - 2; i++) {
		var angle = getAngle(latLngFromWaypoint(wpts[i]),
			latLngFromWaypoint(wpts[i + 1]),
			latLngFromWaypoint(wpts[i + 2]));
		if (angle < sharpAngleThreshold) {
			errors.push({waypoint: wpts[i], error: "Sharp angle (" + angle + "°)"});
			errors.push({waypoint: wpts[i + 1], error: "Sharp angle (" + angle + "°)"});
			errors.push({waypoint: wpts[i + 2], error: "Sharp angle (" + angle + "°)"});
		}
	}

	/* Duplicate labels */
	var labels = {};
	waypoints.forEach(function (wpt) {
		if (labels[wpt.label]) {
			labels[wpt.label].push(wpt);
		} else {
			labels[wpt.label] = [wpt];
		}
	});
	Object.getOwnPropertyNames(labels).forEach(function (label) {
		if (labels[label].length > 1) {
			labels[label].forEach(function (wpt) {
				errors.push({waypoint: wpt, error: "Duplicate label"});
			});
		}
	});

	return errors;
}
