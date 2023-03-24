/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

define([ 'jquery', 'toastr', 'mapbox-gl', '@mapbox/mapbox-gl-geocoder', 'owl.carousel'], function (jq, toastr, mapboxgl, MapboxGeocoder){
    "use strict";
    mapboxgl.accessToken = 'pk.eyJ1IjoiY29jb21pbmUiLCJhIjoiY2xhanp1Ymh1MGlhejNvczJpbHhpdjV5dSJ9.oGNqsDB7ybqV5q6T961bqA';
    /**
     * @type {mapboxgl.Marker[]} 標記列表
     * @private
     */
    let _marks = [];
    /**
     * @type {OwlCarousel.Options} 標記彈出視窗
     * @private
     */
    const _owlOpt = {
        nav: true,
        dots: false,
        margin: 10,
        mergeFit:false,
        autoplay:true,
        autoplayHoverPause:true,
        navText: [
            "<i class=\"fa-solid fa-chevron-left\"></i>",
            "<i class=\"fa-solid fa-chevron-right\"></i>"
        ],
        stagePadding: 40,
        lazyLoad:true,
        responsive: {
            0: {
                items: 1,
            },
            576: {
                items: 2,
            },
            992: {
                items: 3,
            },
            1300: {
                items: 4,
            }
        }
    }
    const jq_carousel = $('#carousel-list')

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

    /* 搜尋區域 */
    $('#search-map').click(function (){
        /* get map view area log lnt */
        const bounds = map.getBounds();
        const NorthWest = bounds.getNorthWest();
        const SouthEast = bounds.getSouthEast();

        /* 封鎖按鈕 */
        const bt = $(this);
        const html = bt.html();
        bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

        /* clear old list */
        _marks.forEach((mark) => mark.remove()); // remove old marker
        _marks = []; // clear old marker
        jq_carousel.children().remove(); // remove old carousel item
        jq_carousel.append('<div class="owl-carousel owl-theme"></div>'); // create new carousel

        /* send */
        fetch('/xmap/', {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({NorthWest, SouthEast})
        }).then(async (response) => {
            const json = await response.json();
            console.log(json); //debug
            if (response.ok && json.code === 200){

                /* analyze new list */
                let html = [];
                json.data.forEach((item) => {
                    // add new carousel item
                    const tmp = $(`<div class="item">
                        <div class="card" style="height: 10rem; width: 20rem">
                            <img data-src="/panel/api/media/${item.thumbnail}" alt="${item.thumbnail}" class="card-img-top owl-lazy" style="object-fit: cover; height: 50%">
                            <div class="card-body p-3">
                                <p class="text-truncate"><a href="/activity_details/${item.ID}" class="stretched-link">${item.name}</a></p>
                                <p class="text-secondary text-truncate card-text">${item.summary}</p>
                            </div>
                        </div>
                    </div>`)
                    html.push(tmp);

                    // add new marker
                    const mark = new mapboxgl.Marker({ color: '#12a0ff' })
                    mark.setLngLat([ item.longitude, item.latitude ]);
                    mark.setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML('<b>'+item.name+'</b>')) // add popups
                    mark.addTo(map);
                    _marks.push(mark);

                    // 懸浮效果
                    tmp.on('mouseenter', () => {
                        mark.togglePopup();
                    }).on('mouseleave', () => {
                        mark.togglePopup();
                    });
                });
                jq_carousel.children().html(html).owlCarousel(_owlOpt); // init carousel

            }else{
                toastr.error(json.Message, json.Title);
            }
        }).finally(() => {
            bt.html(html).removeAttr('disabled');
        }).catch((error) => {
            console.log(error);
        });
    });
});