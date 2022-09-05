var MapnikOptions = { alt: "Show Mapnik road map tiles from OpenStreetMap.org",
		      getTileUrl: getMapnikTileURL,
		      maxZoom: 18,
		      minZoom: 0,
		      name: "Mapnik",
		      opacity: 1,
		      tileSize: new google.maps.Size(256, 256)
		    };

function getMapnikTileURL(point, zoom)
{
    return 'http://tile.openstreetmap.org/' + zoom + '/' + point.x + '/' + point.y + '.png';
}



var MQOpenMapOptions = { alt: "Show Mapquest Open Map road map tiles based on OpenStreetMap.org data",
		      getTileUrl: getMQOpenMapTileURL,
		      maxZoom: 18,
		      minZoom: 0,
		      name: "MQOpenMap",
		      opacity: 1,
		      tileSize: new google.maps.Size(256, 256)
		    };

function getMQOpenMapTileURL(point, zoom)
{
    return 'http://cmap.m-plex.com/hb/ymaptile.php?t=m&s=mq&x=' + point.x + '&y=' + point.y + '&z=' + zoom;
}



var MQOpenSatOptions = { alt: "Show Mapquest Open Map satellite imagery tiles based on OpenStreetMap.org data",
		      getTileUrl: getMQOpenSatTileURL,
		      maxZoom: 18,
		      minZoom: 0,
		      name: "MQOpenSat",
		      opacity: 1,
		      tileSize: new google.maps.Size(256, 256)
		    };

function getMQOpenSatTileURL(point, zoom)
{
    return 'http://cmap.m-plex.com/hb/ymaptile.php?t=s&s=mq&x=' + point.x + '&y=' + point.y + '&z=' + zoom;
}




var MQMapOptions = { alt: "Show Mapquest road map tiles",
		      getTileUrl: getMQMapTileURL,
		      maxZoom: 18,
		      minZoom: 0,
		      name: "MQMap",
		      opacity: 1,
		      tileSize: new google.maps.Size(256, 256)
		    };

function getMQMapTileURL(point, zoom)
{
    return 'http://vtiles03.mqcdn.com/tiles/1.0.0/vy/map/' + zoom + '/' + point.x + '/' + point.y + '.png';
}



var MQSatOptions = { alt: "Show Mapquest satellite imagery tiles",
		      getTileUrl: getMQSatTileURL,
		      maxZoom: 17,
		      minZoom: 0,
		      name: "MQSat",
		      opacity: 1,
		      tileSize: new google.maps.Size(256, 256)
		    };

function getMQSatTileURL(point, zoom)
{
    return 'http://vtiles03.mqcdn.com/tiles/1.0.0/vy/sat/' + zoom + '/' + point.x + '/' + point.y + '.png';
}



var CHMSysOptions = { alt: "Show Clinched Highway Mapping highways colored by highway system",
		      getTileUrl: getCHMSysTileURL,
		      maxZoom: 9,
		      minZoom: 1,
		      name: "CHMSys",
		      opacity: 1,
		      tileSize: new google.maps.Size(256, 256)
		    };

function getCHMSysTileURL(point, zoom)
{
    return 'http://cmap.m-plex.com/maps/tiles/sys/' + zoom + '/' + point.x + '/chmsystile_' + zoom + '_' + point.x + '_' + point.y + '.png';
}







var CHMPctOptions = { alt: "Show Clinched Highway Mapping highways colored by percentage of CHM users who have traveled on them",
		      getTileUrl: getCHMPctTileURL,
		      maxZoom: 9,
		      minZoom: 1,
		      name: "CHMPct",
		      opacity: 1,
		      tileSize: new google.maps.Size(256, 256)
		    };

function getCHMPctTileURL(point, zoom)
{
    return 'http://cmap.m-plex.com/maps/tiles/pct/' + zoom + '/' + point.x + '/chmpcttile_' + zoom + '_' + point.x + '_' + point.y + '.png';
}



var YahooMapOptions = { alt: "Show Yahoo road map tiles",
		      getTileUrl: getYahooMapTileURL,
		      maxZoom: 17,
		      minZoom: 1,
		      name: "YahooMap",
		      opacity: 1,
		      tileSize: new google.maps.Size(256, 256)
		    };

function getYahooMapTileURL(point, zoom)
{
    return 'http://cmap.m-plex.com/hb/ymaptile.php?t=m&s=y&x=' + point.x + '&y=' + point.y + '&z=' + zoom;
}



var YahooSatOptions = { alt: "Show Yahoo satellite imagery tiles",
		      getTileUrl: getYahooSatTileURL,
		      maxZoom: 17,
		      minZoom: 1,
		      name: "YahooSat",
		      opacity: 1,
		      tileSize: new google.maps.Size(256, 256)
		    };

function getYahooSatTileURL(point, zoom)
{
    return 'http://cmap.m-plex.com/hb/ymaptile.php?t=s&s=y&x=' + point.x + '&y=' + point.y + '&z=' + zoom;
}





var BingMapOptions = { alt: "Show Bing road map tiles",
		      getTileUrl: getBingMapTileURL,
		      maxZoom: 19,
		      minZoom: 1,
		      name: "BingMap",
		      opacity: 1,
		      tileSize: new google.maps.Size(256, 256)
		    };

function getBingMapTileURL(point, zoom)
{
    return 'http://cmap.m-plex.com/hb/ymaptile.php?t=m&s=b&x=' + point.x + '&y=' + point.y + '&z=' + zoom;
}



var BingSatOptions = { alt: "Show Bing satellite imagery tiles",
		      getTileUrl: getBingSatTileURL,
		      maxZoom: 19,
		      minZoom: 1,
		      name: "BingSat",
		      opacity: 1,
		      tileSize: new google.maps.Size(256, 256)
		    };

function getBingSatTileURL(point, zoom)
{
    return 'http://cmap.m-plex.com/hb/ymaptile.php?t=s&s=b&x=' + point.x + '&y=' + point.y + '&z=' + zoom;
}

