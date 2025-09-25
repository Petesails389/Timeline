function getData() {
    //Change url & call data get
    var url = new URL(document.URL);

    var day = document.getElementById("day").value;
    var duration = document.getElementById("duration").value;

    url.searchParams.set('day', day);
    url.searchParams.set('duration', duration);
    history.pushState(null, "", url.href);

    url.pathname = url.pathname.replace("viewmap.php","points.js.php");

    fetch(url.href, { credentials: 'include' })
      .then(response => response.json())
      .then(json => {
        //if you have history access then render timeline
        if (json.history) {
            //add one day to date due to difference with PHP date handeling
            let day = new Date(json.day).valueOf() / 1000 + 86400;
            drawTimeline(json.routes, day - json.duration, day);
        }
        processData(json)
      });
  }

function processData(jsonIn) {
    json = jsonIn;

    globalDuration = json.duration

    //clear layers
    routesLayer.clearLayers();
    highlightLayer.clearLayers();
    markers.clearLayers();

    //draw markers
    if (json.markers) {
        for (let i in json.markers){
            newIcon = L.divIcon({
                html: `<span class='material-symbols-outlined mapIcon'>${json.markers[i][3]}</span>`,
                className: "mapIcon",
                iconSize: [30,30]
            });
            var newMarker = L.marker([json.markers[i][0], json.markers[i][1]],{title: json.markers[i][2], icon: newIcon});
            newMarker.addTo(markers);
        }
    }

    if (json.last) {
        var lastloc = L.marker([json.last[0],json.last[1]],{title: "Last known location", icon: myLocation});
        var time = json.last[2];
        lastloc.bindPopup(`<b>Last known location at ${time}.</b>`).openPopup();
        lastloc.addTo(markers);
    }

    //draw points
    if (json.displayPoints && json.routes && (json.displayPoints.length > 0 || json.routes.length > 0)) {
        drawRoutes(json.displayPoints, json.routes, json.duration > 604800);
    } else {
        if (json.last) {
            map.flyTo(json.last, 15, {
                animate: true,
                duration: 1
            });
        } else if (json.home) {
            map.flyTo(json.home, 15, {
                animate: true,
                duration: 1
            });
        } else {
            //weird edge case if you've got acess to veiw nothing on this map....
            //not sure why it would ever come up
            map.flyTo([0,0], 10, {
                animate: true,
                duration: 1
            });
        }
    }

    //display the layers in the right order
    map.addLayer(routesLayer);
    map.addLayer(highlightLayer);
    map.addLayer(markers);
}

function drawRoutes(displayPoints, routes, heatmap) {

    if (heatmap) {
        // display routes as heatmap
        for (let i in routes){
            L.corridor(routes[i], {color: '#00008B', opacity: 1, corridor: 10, minWeight: 1.5}).addTo(routesLayer);
        }
        for (let i in routes){
            L.corridor(routes[i], {color: '#7DF9FF', opacity: 0.2, corridor: 5, minWeight: 1}).addTo(routesLayer);
        }
        for (let i in routes){
            L.corridor(routes[i], {color: '#FFFFFF', opacity: 0.05, corridor: 2, minWeight: 0.5}).addTo(routesLayer);
        }
    } else {
        for (let i in routes){
            L.polyline(json.routes[i], {color: '#e62955'}).addTo(routesLayer);
        }
    }

    //add points to heatmap
    for (let i in displayPoints){
        //L.circle([displayPoints[i][0],displayPoints[i][1]], {radius: 0.5, fillColor: '#f34723', fillOpacity: 1, color: '#f34723', weight: 1}).addTo(highlightLayer);
        //L.circleMarker([displayPoints[i][0],displayPoints[i][1]], {radius: 1, fillColor: '#7DF9FF', fillOpacity: 0.1, stroke: false}).addTo(routesLayer);
    }

    //fit bounds
    bounds = L.latLngBounds(routes,displayPoints);
    map.fitBounds(bounds, {
        animate: true,
        duration: 1
    });
}

//draws routes in time range
function highlight(start, end) {
    var localBounds = L.latLngBounds();
    for (let i in json.routes){
        var routeStart = json.routes[i][0][2];
        var routeEnd = json.routes[i][json.routes[i].length-1][2];
        if ((start <= routeStart && routeStart <= end)
        || (start <= routeEnd && routeEnd <= end)){
            //add to layer
            L.polyline(json.routes[i], {color: '#000000'}).addTo(highlightLayer);
            //extend bounds
            localBounds.extend(L.latLngBounds(json.routes[i]));
        }
    }
    //fit bounds
    // map.fitBounds(localBounds, {
    //     animate: true,
    //     duration: 1
    // });
}

function drawMap() {
    //map layers
    var osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    });
    var Esri_WorldImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
    });
    var attThunder = "Map &copy; <a href='https://www.thunderforest.com'>Thunderforest</a>, Data &copy; <a href='http://www.openstreetmap.org/////copyright'>OpenStreetMap contributors</a>";
    var layNeighbour = L.tileLayer("https://tile.thunderforest.com/neighbourhood/{z}/{x}/{y}.png?apikey=361c7476e1734806ad3aa7a453469dfa", {
    maxZoom: 19,
    attribution: attThunder
    });
    var layHot = L.tileLayer("https://a.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png", {maxZoom: 19, attribution: ''});
    var layOutdoors = L.tileLayer("https://tile.thunderforest.com/outdoors/{z}/{x}/{y}.png?apikey=361c7476e1734806ad3aa7a453469dfa", {
    maxZoom: 19,
    attribution: attThunder
    });
    var layOCM = L.tileLayer("https://tile.thunderforest.com/cycle/{z}/{x}/{y}.png?apikey=361c7476e1734806ad3aa7a453469dfa", {
    maxZoom: 19,
    attribution: attThunder
    });
    var layLand = L.tileLayer("https://tile.thunderforest.com/landscape/{z}/{x}/{y}.png?apikey=361c7476e1734806ad3aa7a453469dfa", {
    maxZoom: 19,
    attribution: attThunder
    });
    var layPio = L.tileLayer("https://tile.thunderforest.com/pioneer/{z}/{x}/{y}.png?apikey=361c7476e1734806ad3aa7a453469dfa", {
    maxZoom: 19,
    attribution: attThunder
    });

    //actual map
    map = L.map('map', {
        center: [0,0],
        zoom: 10,
        layers: [osm, highlightLayer, markers]
    });

    //layer controls
    var baseMaps = {
        "OpenStreetMap": osm,
        "Satelite (Esri)": Esri_WorldImagery,
        "Neighbourhood": layNeighbour,
        "Humanitarian": layHot,
        "Outdoors": layOutdoors,
        "Open CycleMap":layOCM,
        "Landscape": layLand,
        "Pioneer": layPio
    };

    var overlayLayers = {
        "Markers": markers
    };

    var layerControl = L.control.layers(baseMaps, overlayLayers, {position: 'bottomleft'}).addTo(map);

    getData()
}

function drawTimeline(routes, start, end) {
    timeline = document.getElementById('timeline');

    var x = [];
    var y = [];

    var timezoneOffset = new Date().getTimezoneOffset() * 60000;
    var start = new Date((start)*1000 - timezoneOffset).toISOString().replace("T", " ");
    var end = new Date((end)*1000 - timezoneOffset).toISOString().replace("T", " ");

	for (let i in routes) {
        let start = new Date((routes[i][0][2])*1000 - timezoneOffset).toISOString().replace("T", " ");
        let end = new Date((routes[i][routes[i].length-1][2])*1000 - timezoneOffset).toISOString().replace("T", " ");

        x.push(start,start,end,end);
        y.push(-0.5,1.5,1.5,-0.5)
    }

    var trace = {
        x: x,
        y: y,
        fill: 'tozeroy',
        type: 'scatter'
    };

    data = [trace];

    var layout = {
        title: {
            text: 'Timeline'
        },
        margin: {
            b: 20,
            t: 45,
            l: 25,
            r: 10,
        },
        paper_bgcolor: "#fceaee",
        plot_bgcolor: "#fceaee",
        font: {
            color: "#000000",
            family: "monospace",
        },
        showlegend: false,
        xaxis: {
            autorange: false,
            range: [start, end],
            rangeslider: {range: [start, end]},
            type: 'date'
        },
        yaxis: {
            autorange: false,
            range: [0, 1],
            type: 'linear'
        }
    };

    var config = {
        responsive: true,
        scrollZoom: true,
        displayModeBar: false,
    }

    Plotly.newPlot(timeline, data, layout, config);

    timeline.on('plotly_relayout', function(eventData) {
        highlightLayer.clearLayers();

        if (eventData['xaxis.range[0]']){
            var start = new Date(eventData['xaxis.range[0]']).valueOf()/1000;
            var end = new Date(eventData['xaxis.range[1]']).valueOf()/1000;
            var duration = end - start;

            highlightLayer.clearLayers();
            if (duration < globalDuration) {
                highlight(start, end);
                return;
            }
        }

        //zoom to heatmap
        map.fitBounds(bounds, {
            animate: true,
            duration: 1
        });
    });
}

//globals
var map;
var json;
var bounds;
var globalDuration

//overlay layers
var highlightLayer =  L.layerGroup("");
var routesLayer =  L.layerGroup("");
var markers = L.layerGroup("");

//icons
var myLocation = L.divIcon({
    html: "<span class='material-symbols-outlined mapIcon'>my_location</span>",
    className: "mapIcon",
    iconSize: [30,30]
});