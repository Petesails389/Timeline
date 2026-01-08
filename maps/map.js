function changeDate(val) {
    var date = new Date(day.value);
    date.setUTCDate(date.getUTCDate() + val);
    date = new Date(Math.min(date.valueOf(), Date.now()));
    document.getElementById("day").valueAsDate = date;

    getData()
}

function getData() {
    //Change url & call data get
    var url = new URL(document.URL);

    //attempt to get day and duration request from user and apply defaults if not
    var day = document.getElementById("day");
    if (day) {
        day = day.value;
    } else {
        day = new Date().valueOf() / 1000;
    }
    var duration = document.getElementById("duration");
    if (duration) {
        duration = duration.value;
    } else {
        duration = 86400;
    }

    url.searchParams.set('day', day);
    url.searchParams.set('duration', duration);
    history.pushState(null, "", url.href);

    url.pathname = url.pathname.replace("viewmap.php","points.js.php");

    fetch(url.href, { credentials: 'include' })
      .then(response => response.json())
      .then(json => {
        //if you have history access then render timeline
        if (json.history) {
            let day = new Date(json.day).valueOf() / 1000;
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
    if (json.routes && json.routes.length > 0) {
        drawRoutes(json.routes, json.duration > 604800 || !json.history);
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
    if (json.duration > 604800 || !json.history) { 
        map.removeLayer(markers);
    } else {
        map.addLayer(markers);
    }
}

function drawRoutes(routes, heatmap) {

    if (heatmap) {
        // display routes as heatmap
        for (let i in routes){
            L.corridor(routes[i][1], {color: '#00008B', opacity: 1, corridor: 10, minWeight: 1.5}).addTo(routesLayer);
        }
        for (let i in routes){
            L.corridor(routes[i][1], {color: '#7DF9FF', opacity: 0.2, corridor: 5, minWeight: 1}).addTo(routesLayer);
        }
        for (let i in routes){
            L.corridor(routes[i][1], {color: '#FFFFFF', opacity: 0.05, corridor: 2, minWeight: 0.5}).addTo(routesLayer);
        }
    } else {
        for (let i in routes){
            L.polyline(json.routes[i][1], {color: '#e62955'}).addTo(routesLayer);
        }
    }

    //fit bounds
    bounds = L.latLngBounds(routes[0][1]);
    for (let key = 1; key < routes.length; key ++) {
        bounds.extend(routes[key][1])
    }
    map.fitBounds(bounds);
}

//draws routes in time range
function highlight(start, end) {
    localBounds = L.latLngBounds();
    for (let i in json.routes){
        var routeStart = json.routes[i][0][0];
        var routeEnd = json.routes[i][0][1];
        if ((start <= routeStart && routeStart <= end)
        || (start <= routeEnd && routeEnd <= end)){
            //add to layer
            L.polyline(json.routes[i][1], {color: '#000000'}).addTo(highlightLayer);
            //extend bounds
            localBounds.extend(L.latLngBounds(json.routes[i][1]));
        }
    }
    //fit bounds
    map.flyToBounds(localBounds, {
        duration: 0.8
    });
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

    // more zoom and position controls
    L.Control.extraZoomControls = L.Control.extend({
        options: {
            position: 'topleft',
            boundsZoomText: '<span class="material-symbols-outlined" style="padding-top: 2px;padding-left: 1px;">zoom_out_map</span>',
            boundsZoomTitle: 'Go To Bounds',
            homeZoomText: '<span class="material-symbols-outlined" style="padding-top: 2px;padding-left: 1px;">home</span>',
            homeZoomTitle: 'Go To Home',
            locationZoomText: '<span class="material-symbols-outlined" style="padding-top: 2px;padding-left: 1px;">my_location</span>',
            locationZoomTitle: 'Current Location'
        },

        onAdd: function (map) {
            var controlName = 'extra-zoom-control',
                container = L.DomUtil.create('div', controlName + ' leaflet-bar'),
                options = this.options;

            this._boundsZoomButton = this._createButton(options.boundsZoomText, options.boundsZoomTitle, controlName + "-bounds", container, this._boundsZoom);
            this._homeZoomButton = this._createButton(options.homeZoomText, options.homeZoomTitle, controlName + "-home", container, this._homeZoom);
            this._locationZoomButton = this._createButton(options.locationZoomText, options.locationZoomTitle, controlName + "-location", container, this._locationZoom);

            this._map = map;

            return container;
        },

        _boundsZoom: function (e) {
            this._map.flyToBounds(bounds, {
                duration: 0.8
            });
        },

        _homeZoom: function (e) {
            if (json.home) {
                this._map.flyTo(json.home, 15, {
                    duration: 0.8
                });
            }
        },

        _locationZoom: function (e) {
            if (json.last) {
                this._map.flyTo(json.last, 17, {
                    duration: 0.8
                });
            }
        },

        _createButton: function (html, title, className, container, fn) {
            var link = L.DomUtil.create('a', className, container);
            link.innerHTML = html;
            link.href = '#';
            link.title = title;

            L.DomEvent.on(link, 'mousedown dblclick', L.DomEvent.stopPropagation)
                .on(link, 'click', L.DomEvent.stop)
                .on(link, 'click', fn, this)
                .on(link, 'click', this._refocusOnMap, this);

            return link;
        }
    });

    // add the controls to the map
    var zoomControls = new L.Control.extraZoomControls();
    zoomControls.addTo(map);


    getData()
}

function drawTimeline(routes, start, end) {
    timeline = document.getElementById('timeline');

    var x = [[],[],[],[],[]];
    var y = [[],[],[],[],[]];

    var timezoneOffset = new Date().getTimezoneOffset() * 60000;
    var start = new Date((start)*1000 - timezoneOffset).toISOString().replace("T", " ");
    var end = new Date((end)*1000 - timezoneOffset).toISOString().replace("T", " ");

    for (let i in routes) {
        if (routes[i][0][2] == 0) {
            continue;
        }
        let start = new Date((routes[i][0][0])*1000 - timezoneOffset).toISOString().replace("T", " ");
        let end = new Date((routes[i][0][1])*1000 - timezoneOffset).toISOString().replace("T", " ");

        x[routes[i][0][2]].push(start,start,end,end);
        y[routes[i][0][2]].push(-0.5,1.5,1.5,-0.5);
    }

    var colors = ['#000000','#ae1919ff','#be6c19ff','#e0d20aff','#48d013ff'];
    var data = [];

    for (let i = 0; i < 5; i++) {
        var trace = {
            x: x[i],
            y: y[i],
            fill: 'tozeroy',
            type: 'scatter',
            marker: {
                color: colors[i],
                size: 0,
            },
        };
        data.push(trace);
    }

    var layout = {
        margin: {
            b: 8,
            t: 8,
            l: 23,
            r: 8,
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
    });
}

//globals
var map;
var json;
var bounds;
var localBounds;
var globalDuration;

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