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

function loadWaypoints(waypoints, map) {
	var bounds = null;
	var path = null;
	var points = [];
	var markers = {};

	waypoints.forEach(function (wpt) {
		var coords = new google.maps.LatLng(wpt.lat, wpt.lng);
		var marker = new google.maps.Marker({
			map: map,
			draggable: true,
			position: coords,
			title: wpt.label,
			icon: (wpt.hidden ? hiddenWptMarker : visibleWptMarker)
		});
		if (bounds === null) {
			bounds = new google.maps.LatLngBounds(coords, coords);
		} else {
			bounds.extend(coords);
		}
		points.push(coords);

		markers[wpt.label] = marker;

		google.maps.event.addListener(marker, "click", function () {
			expandWaypoint(wpt.label);
		});
		google.maps.event.addListener(marker, "dragend", function () {
			var pos = marker.getPosition();
			wpt.lat = Math.round(1e6 * pos.lat()) / 1e6;
			wpt.lng = Math.round(1e6 * pos.lng()) / 1e6;
			update();

			points = [];
			waypoints.forEach(function (w) {
				points.push(new google.maps.LatLng(w.lat, w.lng));
			});
			path.setPath(points);

			if (document.getElementsByClassName("expanded").dataset.label == wpt.label) {
				expandWaypoint(wpt.label);
			}
			expandWaypoint(wpt.label);
		});
	});

	path = new google.maps.Polyline({
		map: map,
		path: points,
		strokeColor: "#214478",
		strokeOpacity: 0.7
	});

	map.fitBounds(bounds);

	return markers;
}
