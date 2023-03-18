define(['jquery', 'mapbox-gl', 'toastr'], function (jq, mapboxgl, toastr) {
    "use strict";
    mapboxgl.accessToken = 'pk.eyJ1IjoiY29jb21pbmUiLCJhIjoiY2xhanp1Ymh1MGlhejNvczJpbHhpdjV5dSJ9.oGNqsDB7ybqV5q6T961bqA';
    /**
     * @type {{lat: number, lng: number}} 經緯度
     */
    const map_location = JSON.parse($('#map-location').text());

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

    /* 放大圖片 */
    $('#review').on('click', '.review-image', function (e) {
        const elm = $(this);
        const url = elm.data('src');
        const alt = elm.attr('alt');
        elm.parents('.card-body').children('.zoom-image').show().html(
            `<img src="${url}" class="head-image d-block w-100" alt="${alt}">`
        )
    });

    /* 尋找當月可用日期 */
    const jq_bookDate = $('#book-date')
    jq_bookDate.on('datepicker.prev_month', function (e, data) {
        fetch(location.pathname+'/?type=available_date', {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({date: data.newDate.format('YYYY-MM-DD')})
        }).then(async (response) => {
            const json = await response.json();
            console.log(json); //debug
            if (response.ok && json.code === 200){
                const data = json.data;

            }else{
                toastr.error(json.Message, json.Title ?? globalLang.Error);
            }
        }).catch((error) => {
            console.log(error);
        });
    })
})