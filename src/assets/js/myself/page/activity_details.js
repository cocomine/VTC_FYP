define(['jquery', 'mapbox-gl', 'toastr', 'moment'], function (jq, mapboxgl, toastr, moment) {
    "use strict";
    mapboxgl.accessToken = 'pk.eyJ1IjoiY29jb21pbmUiLCJhIjoiY2xhanp1Ymh1MGlhejNvczJpbHhpdjV5dSJ9.oGNqsDB7ybqV5q6T961bqA';
    /**
     * 地圖位置
     * @type {{lat: number, lng: number}} 經緯度
     */
    const map_location = JSON.parse($('#map-location').text());
    const jq_bookDate = $('#book-date');
    jq_bookDate.children('input').attr('min', moment().format('YYYY-MM-DD')); // 設定最小日期
    let _plan; // 計劃

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

    /* 尋找選擇月份可用日期 */
    jq_bookDate.on('datepicker.prev_month datepicker.next_month', function (e, data) {
        available_date(data.newDate);
    })

    /**
     * 尋找當月可用日期
     * @param {moment.Moment} newDate 日期
     */
    function available_date(newDate){
        fetch(location.pathname+'/?type=available_date', {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({date: newDate.format('YYYY-MM-DD')})
        }).then(async (response) => {
            const json = await response.json();
            if (response.ok && json.code === 200){
                const data = json.data;

                const start = newDate.clone().startOf('month'); // 當月第一天
                const correct = start.clone(); // 計算用
                const end = start.clone().endOf('month'); // 當月最後一天
                let disableDate = []; // 不可用日期

                // 計算當月不可用日期
                for(let i = 0; i < end.date(); i++){
                    let isDisable = true;
                    // 重複日期
                    data.repeat.forEach((item) => {
                        const repeat_week = JSON.parse(item.repeat_week);
                        //console.log(correct.isSameOrAfter(item.start_date), correct.isSameOrBefore(item.end_date), repeat_week.includes(correct.day().toString()), correct.format('YYYY-MM-DD'), repeat_week); //debug
                        if(correct.isSameOrAfter(item.start_date) // 日期在範圍內
                            && correct.isSameOrBefore(item.end_date) // 日期在範圍內
                            && repeat_week.includes(correct.day().toString()) // 重複日期
                            && !disableDate.includes(correct.format('YYYY-MM-DD')) // 不在不可用日期內
                        )isDisable = false;
                    })

                    // 單次日期
                    data.single.forEach((item) => {
                        if(correct.isSame(item.start_date) && !disableDate.includes(correct.format('YYYY-MM-DD'))) // 日期相同 & 不在不可用日期內
                            isDisable = false;
                    });

                    // 加入不可用日期
                    if(isDisable) disableDate.push(correct.format('YYYY-MM-DD'));
                    correct.add(1, 'day'); // 加一天
                }
                jq_bookDate[0].datepicker.disableDate = disableDate; // 設定不可用日期
                jq_bookDate[0].datepicker.draw(); // 重繪
            }else{
                toastr.error(json.Message, json.Title ?? globalLang.Error);
            }
        }).catch((error) => {
            console.log(error);
        });
    }
    available_date(moment()); // 預設當月

    /* 選擇日期 */
    jq_bookDate.on('datepicker.select_date datepicker.select_today', function (e, data) {
        const input_elm = jq_bookDate.children('input');

        // 檢查日期是否可用
        if(input_elm[0].checkValidity() && !jq_bookDate[0].datepicker.disableDate.includes(data.newSelect.format('YYYY-MM-DD'))){
            input_elm.removeClass('is-invalid');
            show_plan(data.newSelect);
        }else{
            input_elm.addClass('is-invalid');
        }
    })

    /* input直接輸入, 日期改變 */
    jq_bookDate.children('input').blur(function (e) {
        console.log($(this).val()); //debug
        const val = $(this).val();

        // 檢查日期是否可用
        if(this.checkValidity() && !jq_bookDate[0].datepicker.disableDate.includes(val)){
            $(this).removeClass('is-invalid');
            show_plan(moment(val));
        }else{
            $(this).addClass('is-invalid');
        }
    })

    /* input直接輸入, 尋找選擇月份可用日期 */
    .change(function (e) {
        available_date(moment($(this).val()));
    });

    /**
     * 顯示計劃
     * @param date {moment.Moment} 日期
     */
    function show_plan(date){
        fetch(location.pathname+'/?type=available_plan', {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({date: date.format('YYYY-MM-DD')})
        }).then(async (response) => {
            const json = await response.json();
            console.log(json); //debug
            if (response.ok && json.code === 200){
                _plan = json.data;

                //todo: 顯示計劃
            }else{
                toastr.error(json.Message, json.Title ?? globalLang.Error);
            }
        }).catch((error) => {
            console.log(error);
        });
    }
    show_plan(moment()); // 預設當日
})