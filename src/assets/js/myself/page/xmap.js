/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

define(['jquery', 'toastr', 'mapbox-gl', '@mapbox/mapbox-gl-geocoder'], function (jq, toastr, mapboxgl, MapboxGeocoder) {
    "use strict";
    mapboxgl.accessToken = 'pk.eyJ1IjoiY29jb21pbmUiLCJhIjoiY2xhanp1Ymh1MGlhejNvczJpbHhpdjV5dSJ9.oGNqsDB7ybqV5q6T961bqA';

    /* Load map */
    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v12',
        zoom: 10,
        center: [ 114.1558142, 22.3446534 ]
    });

    /* Enable stars with reduced atmosphere */
    map.on('style.load', () => {
        map.setFog({ 'horizon-blend': 0.05 });
    });

    /* Given a query in the form "lng, lat" or "lat, lng"
        * returns the matching geographic coordinate(s)
        * as search results in carmen geojson format,
        * https://github.com/mapbox/carmen/blob/master/carmen-geojson.md */
    const coordinatesGeocoder = function (query){
        // Match anything which looks like
        // decimal degrees coordinate pair.
        const matches = query.match(
            /^[ ]*(?:Lat: )?(-?\d+\.?\d*)[, ]+(?:Lng: )?(-?\d+\.?\d*)[ ]*$/i
        );
        if (!matches){
            return null;
        }

        function coordinateFeature(lng, lat){
            return {
                center: [ lng, lat ],
                geometry: {
                    type: 'Point',
                    coordinates: [ lng, lat ]
                },
                place_name: 'Lat: ' + lat + ' Lng: ' + lng,
                place_type: [ 'coordinate' ],
                properties: {},
                type: 'Feature'
            };
        }

        const coord1 = Number(matches[1]);
        const coord2 = Number(matches[2]);
        const geocodes = [];

        if (coord1 < -90 || coord1 > 90){
            // must be lng, lat
            geocodes.push(coordinateFeature(coord1, coord2));
        }

        if (coord2 < -90 || coord2 > 90){
            // must be lat, lng
            geocodes.push(coordinateFeature(coord2, coord1));
        }

        if (geocodes.length === 0){
            // else could be either lng, lat or lat, lng
            geocodes.push(coordinateFeature(coord1, coord2));
            geocodes.push(coordinateFeature(coord2, coord1));
        }

        return geocodes;
    };

    /* Add Map Control */
    const map_geo = new MapboxGeocoder({
        accessToken: mapboxgl.accessToken,
        marker: false,
        mapboxgl: mapboxgl,
        limit: 8,
        localGeocoder: coordinatesGeocoder,
        reverseGeocode: true
    });
    map.addControl(map_geo);
    map.addControl(new mapboxgl.ScaleControl());
    map.addControl(new mapboxgl.NavigationControl());
    const map_track = new mapboxgl.GeolocateControl({ fitBoundsOptions: { zoom: 15 } });
    map.addControl(map_track);


    map_track.on('geolocate', ({ coords }) => {
        console.log(coords);
    });
});