/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'mapbox', 'mapboxSdk', 'turf', 'myself/map-auto-fit'], function (jq, mapboxgl, mapboxSdk, turf, MapAutoFit) {
    "use strict";
    mapboxgl.accessToken = 'pk.eyJ1IjoiY29jb21pbmUiLCJhIjoiY2xhanp1Ymh1MGlhejNvczJpbHhpdjV5dSJ9.oGNqsDB7ybqV5q6T961bqA';
    let Economy = 0, Business = 0;

    /* js資料 */
    //const Lang = JSON.parse($('#langJson').text());
    const Data = JSON.parse($('#DataJson').text());

    /* 前往預留座位 Card */
    $('.rout').click(() => {
        $('html').animate({scrollTop: $('#Reserve')[0].offsetTop}, 200, 'swing', () => {
            $('#Reserve').addClass('card-highlight')
            setTimeout(() => {
                $('#Reserve').removeClass('card-highlight')
            }, 1000)
        })
    })

    /* go-top 自動適應 */
    if(window.innerWidth < 768) $('.go-top').css('bottom', $('#fixed-price')[0].offsetHeight)
    $(window).resize(function () {
        if(window.innerWidth < 768) $('.go-top').css('bottom', $('#fixed-price')[0].offsetHeight)
    })

    /* 調整預定數量 */
    $("[data-reserve]").click(function () {
        const type = $(this).data('reserve');
        if (type === "Business-add" && Business + 1 <= Data.Business) Business++;
        if (type === "Business-sub" && Business - 1 >= 0) Business--;
        if (type === "Economy-add" && Economy + 1 <= Data.Economy) Economy++;
        if (type === "Economy-sub" && Economy - 1 >= 0) Economy--;

        // show count
        $('#Economy-count').text(Economy);
        $('#Business-count').text(Business);
        $('#total').text('$ ' + formatPrice(Data.PriceBusiness * Business + Data.PriceEconomy * Economy))
    })

    /* 前往預約確認介面 */
    $('#checkout').click(function () {

    })

    /* Load Map */
    const map = new mapboxgl.Map({
        container: 'map',
        // Choose from Mapbox's core styles, or make your own style with Mapbox Studio
        style: 'mapbox://styles/mapbox/streets-v11',
        zoom: 0.5,
    });

    /* Add Map Control */
    map.addControl(new mapboxgl.GeolocateControl());
    map.addControl(new mapboxgl.ScaleControl());
    map.addControl(new mapboxgl.NavigationControl());

    /* Map Load Image */
    map.loadImage('/panel/assets/images/plane-solid.png', (error, image) => {
        if (error) throw error;
        map.addImage('plane', image);
    });

    map.on('style.load', () => {
        map.setFog({ 'horizon-blend': 0.05 }); // Enable stars with reduced atmosphere
    });

    /* 起點終點位置 Marker */
    map.on('load', () => {
        let origin, destination;

        /* 起點 */
        const mapboxClient = mapboxSdk({accessToken: mapboxgl.accessToken});
        mapboxClient.geocoding.forwardGeocode({
            query: Data.FromStr,
            autocomplete: false,
            limit: 1
        }).send().then((response) => {
            if (!response || !response.body || !response.body.features || !response.body.features.length) {
                console.error('Invalid response:');
                console.error(response);
                return;
            }
            const feature = response.body.features[0];
            new mapboxgl.Marker({color: 'green'}).setLngLat(feature.center).addTo(map);
            origin = feature.center;
            drawLine(origin, destination);
        });

        /* 終點 */
        mapboxClient.geocoding.forwardGeocode({
            query: Data.ToStr,
            autocomplete: false,
            limit: 1
        }).send().then((response) => {
            if (!response || !response.body || !response.body.features || !response.body.features.length) {
                console.error('Invalid response:');
                console.error(response);
                return;
            }
            const feature = response.body.features[0];
            new mapboxgl.Marker({color: 'red'}).setLngLat(feature.center).addTo(map);
            destination = feature.center;
            drawLine(origin, destination);
        });
    })

    /* 劃線&動畫 */
    function drawLine(origin, destination) {
        if (origin && destination) {
            map.addControl(new MapAutoFit(origin, destination)); //auto fit

            //A simple line from origin to destination.
            const route = {
                'type': 'FeatureCollection',
                'features': [
                    {
                        'type': 'Feature',
                        'geometry': {
                            'type': 'LineString',
                            'coordinates': [origin, destination]
                        }
                    }
                ]
            };

            // A single point that animates along the route.
            // Coordinates are initially set to origin.
            const point = {
                'type': 'FeatureCollection',
                'features': [
                    {
                        'type': 'Feature',
                        'properties': {},
                        'geometry': {
                            'type': 'Point',
                            'coordinates': origin
                        }
                    }
                ]
            };

            // Calculate the distance in kilometers between route start/end point.
            const lineDistance = turf.length(route.features[0]);

            // Number of steps to use in the arc and animation, more steps means
            // a smoother arc and animation, but too many steps will result in a
            // low frame rate
            const steps = 500;

            // Draw an arc between the `origin` & `destination` of the two points
            const arc = [];
            for (let i = 0; i < lineDistance; i += lineDistance / steps) {
                const segment = turf.along(route.features[0], i);
                arc.push(segment.geometry.coordinates);
            }

            // Update the route with calculated arc coordinates
            route.features[0].geometry.coordinates = arc;

            // Used to increment the value of the point measurement against the route.
            let counter = 0;

            // Add a source and layer displaying a point which will be animated in a circle.
            map.addSource('route', {
                'type': 'geojson',
                'data': route
            });

            map.addSource('point', {
                'type': 'geojson',
                'data': point
            });

            map.addLayer({
                'id': 'route',
                'source': 'route',
                'type': 'line',
                'paint': {
                    'line-width': 2,
                    'line-color': '#007cbf'
                }
            });

            map.addLayer({
                'id': 'point',
                'source': 'point',
                'type': 'symbol',
                'layout': {
                    // This icon is a part of the Mapbox Streets style.
                    // To view all images available in a Mapbox style, open
                    // the style in Mapbox Studio and click the "Images" tab.
                    // To add a new image to the style at runtime see
                    // https://docs.mapbox.com/mapbox-gl-js/example/add-image/
                    'icon-image': 'plane',
                    'icon-size': 0.7,
                    'icon-rotate': ['get', 'bearing'],
                    'icon-rotation-alignment': 'map',
                    'icon-allow-overlap': true,
                    'icon-ignore-placement': true
                }
            });

            map.fitBounds([origin, destination], {padding: 40});

            animate()

            function animate() {
                if (counter < steps) {
                    const start = route.features[0].geometry.coordinates[counter >= steps ? counter - 1 : counter];
                    const end = route.features[0].geometry.coordinates[counter >= steps ? counter : counter + 1];
                    if (!start || !end) return;

                    // Update point geometry to a new position based on counter denoting
                    // the index to access the arc
                    point.features[0].geometry.coordinates = route.features[0].geometry.coordinates[counter];

                    // Calculate the bearing to ensure the icon is rotated to match the route arc
                    // The bearing is calculated between the current point and the next point, except
                    // at the end of the arc, which uses the previous point and the current point
                    point.features[0].properties.bearing = turf.bearing(
                        turf.point(start),
                        turf.point(end)
                    );

                    // Update the source with this new data
                    map.getSource('point').setData(point);
                }

                // Request the next frame of animation as long as the end has not been reached
                requestAnimationFrame(animate);

                counter = (counter + 1) % (steps - 1);

                if (counter <= 0) {
                    // Set the coordinates of the original point back to origin
                    point.features[0].geometry.coordinates = origin;

                    // Update the source layer
                    map.getSource('point').setData(point);
                }
            }
        }
    }
})