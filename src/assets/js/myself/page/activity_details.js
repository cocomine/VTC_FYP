define(['jquery', 'mapbox-gl', 'toastr'], function (jq, mapboxgl, toastr) {
    "use strict";
    mapboxgl.accessToken = 'pk.eyJ1IjoiY29jb21pbmUiLCJhIjoiY2xhanp1Ymh1MGlhejNvczJpbHhpdjV5dSJ9.oGNqsDB7ybqV5q6T961bqA';
    /**
     * @type {{lat: number, lng: number}} 經緯度
     */
    const map_location = JSON.parse($('#map-location').text());

    console.log(map_location);
    /* Load map */
    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v12',
        zoom: 14,
        center: [ map_location.lng, map_location.lat ]
    });

    /* Enable stars with reduced atmosphere */
    map.on('style.load', () => {
        map.setFog({ 'horizon-blend': 0.05 });
    });

    /* Add Map Control */
    new mapboxgl.Marker({
        color: 'red',
    }).setLngLat([ map_location.lng, map_location.lat ]).addTo(map);
    map.addControl(new mapboxgl.ScaleControl());
    map.addControl(new mapboxgl.NavigationControl());
})