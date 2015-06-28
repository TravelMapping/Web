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
			return 'http://otile1.mqcdn.com/tiles/1.0.0/map/' + zoom + '/' + point.x + '/' + point.y + '.png';
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
			return 'http://otile1.mqcdn.com/tiles/1.0.0/sat/' + zoom + '/' + point.x + '/' + point.y + '.png';
		},
		maxZoom: 17,
		minZoom: 0,
		name: "MapQuest Open Sat",
		opacity: 1,
		tileSize: new google.maps.Size(256, 256)
	}
];

var copyrightNotices = {
	"OSM Mapnik": "Map data © OpenStreetMap contributors",
	"MapQuest Open": "Data, imagery and map information provided by MapQuest, OpenStreetMap and contributors, ODbL",
	"MapQuest Open Sat": "Data, imagery and map information provided by MapQuest, OpenStreetMap and contributors, ODbL"
};
