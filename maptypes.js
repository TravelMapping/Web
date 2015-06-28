var mapTypes = [
	{
		alt: "Mapnik road tiles from OpenStreetMap.org",
		getTileUrl: function (point, zoom) {
			return 'http://tile.openstreetmap.org/' + zoom + '/' + point.x + '/' + point.y + '.png';
		},
		maxZoom: 18,
		minZoom: 0,
		name: "OSM Mapnik",
		opacity: 1,
		tileSize: new google.maps.Size(256, 256)
	},
	{
		alt: "MapQuest road map tiles based on OpenStreetMap.org data",
		getTileUrl: function (point, zoom) {
			return 'http://cmap.m-plex.com/hb/ymaptile.php?t=m&s=mq&x=' + point.x + '&y=' + point.y + '&z=' + zoom;
		},
		maxZoom: 18,
		minZoom: 0,
		name: "MapQuest Open",
		opacity: 1,
		tileSize: new google.maps.Size(256, 256)
	},
	{
		alt: "MapQuest satellite tiles based on OpenStreetMap.org data",
		getTileUrl: function (point, zoom) {
			return 'http://cmap.m-plex.com/hb/ymaptile.php?t=s&s=mq&x=' + point.x + '&y=' + point.y + '&z=' + zoom;
		},
		maxZoom: 17,
		minZoom: 0,
		name: "MapQuest Open Sat",
		opacity: 1,
		tileSize: new google.maps.Size(256, 256)
	}
];
