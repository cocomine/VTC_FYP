define([ 'jquery', 'mapbox-gl', 'toastr', 'moment', 'datepicker' ], function (jq, mapboxgl, toastr, moment){
    "use strict";
    mapboxgl.accessToken = 'pk.eyJ1IjoiY29jb21pbmUiLCJhIjoiY2xhanp1Ymh1MGlhejNvczJpbHhpdjV5dSJ9.oGNqsDB7ybqV5q6T961bqA';
    /**
     * 地圖位置
     * @type {{lat: number, lng: number}} 經緯度
     */
    const map_location = JSON.parse($('#map-location').text());
    const jq_bookDate = $('#book-date');
    const jq_plan = $('#plan');
    jq_bookDate.children('input').attr('min', moment().format('YYYY-MM-DD')); // 設定最小日期
    let _plan; // 計劃
    let _select_plan = []; // 選擇的計劃

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
    $('#review').on('click', '.review-image', function (e){
        const elm = $(this);
        const url = elm.data('src');
        const alt = elm.attr('alt');
        elm.parents('.card-body').children('.zoom-image').show().html(
            `<img src="${url}" class="head-image d-block w-100" alt="${alt}">`
        );
    });

    /* 尋找選擇月份可用日期 */
    jq_bookDate.on('datepicker.prev_month datepicker.next_month', function (e, data){
        available_date(data.newDate);
    });

    /**
     * 尋找當月可用日期
     * @param {moment.Moment} newDate 日期
     */
    function available_date(newDate){
        fetch(location.pathname + '/?type=available_date', {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ date: newDate.format('YYYY-MM-DD') })
        }).then(async (response) => {
            const json = await response.json();
            if (response.ok && json.code === 200){
                const data = json.data;

                const start = newDate.clone().startOf('month'); // 當月第一天
                const correct = start.clone(); // 計算用
                const end = start.clone().endOf('month'); // 當月最後一天
                let disableDate = []; // 不可用日期

                // 計算當月不可用日期
                for (let i = 0; i < end.date(); i++){
                    let isDisable = true;
                    // 重複日期
                    data.repeat.forEach((item) => {
                        const repeat_week = JSON.parse(item.repeat_week);
                        //console.log(correct.isSameOrAfter(item.start_date), correct.isSameOrBefore(item.end_date), repeat_week.includes(correct.day().toString()), correct.format('YYYY-MM-DD'), repeat_week); //debug
                        if (correct.isSameOrAfter(item.start_date) // 日期在範圍內
                            && correct.isSameOrBefore(item.end_date) // 日期在範圍內
                            && repeat_week.includes(correct.day().toString()) // 重複日期
                            && !disableDate.includes(correct.format('YYYY-MM-DD')) // 不在不可用日期內
                        ) isDisable = false;
                    });

                    // 單次日期
                    data.single.forEach((item) => {
                        if (correct.isSame(item.start_date) && !disableDate.includes(correct.format('YYYY-MM-DD'))) // 日期相同 & 不在不可用日期內
                            isDisable = false;
                    });

                    // 加入不可用日期
                    if (isDisable) disableDate.push(correct.format('YYYY-MM-DD'));
                    correct.add(1, 'day'); // 加一天
                }
                jq_bookDate[0].datepicker.disableDate = disableDate; // 設定不可用日期
                jq_bookDate[0].datepicker.draw(); // 重繪
            }else{
                toastr.error(json.Message, json.Title ?? globalLang.Error);
            }
        }).catch((error) => {
            console.error(error);
        });
    }

    available_date(moment()); // 預設當月

    /* 選擇日期 */
    jq_bookDate.on('datepicker.select_date datepicker.select_today', function (e, data){
        const input_elm = jq_bookDate.children('input');

        // 檢查日期是否可用
        if (input_elm[0].checkValidity() && !jq_bookDate[0].datepicker.disableDate.includes(data.newSelect.format('YYYY-MM-DD'))){
            input_elm.removeClass('is-invalid');
            show_plan(data.newSelect);
        }else{
            input_elm.addClass('is-invalid');
        }
    });

    /* input直接輸入, 日期改變 */
    jq_bookDate.children('input').blur(function (e){
        console.log($(this).val()); //debug
        const val = $(this).val();

        // 檢查日期是否可用
        if (this.checkValidity() && !jq_bookDate[0].datepicker.disableDate.includes(val)){
            $(this).removeClass('is-invalid');
            show_plan(moment(val));
        }else{
            $(this).addClass('is-invalid');
        }
    }).change(function (e){ // input直接輸入, 尋找選擇月份可用日期
        available_date(moment($(this).val()));
    });

    /**
     * 顯示計劃
     * @param date {moment.Moment} 日期
     */
    function show_plan(date){
        fetch(location.pathname + '/?type=available_plan', {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ date: date.format('YYYY-MM-DD') })
        }).then(async (response) => {
            const json = await response.json();
            if (response.ok && json.code === 200){
                _plan = json.data;
                _select_plan = [];
                $('#total').text("$ 0");

                if(_plan.length <= 0){
                    jq_plan.html(`<div class="rounded px-3 py-2 bg-light col-12 text-center">
                        <p class="text-muted">無可預訂計劃</p>
                    </div>`);
                    return;
                }

                //顯示計劃
                const plan_html = _plan.map((item) => {
                    const tmp = $(`<div class="rounded px-3 py-2 bg-light col-12">
                            <div class="row justify-content-sm-between align-items-center justify-content-center">
                                <div class="col-auto">
                                    <h5>${item.plan_name}</h5>
                                    <p class="text-muted">${item.start_time.slice(0, -3)}<i class="fa-solid fa-angles-right mx-2"></i>${item.end_time.slice(0, -3)}</p>
                                </div>
                                <div class="col">
                                    <p class="text-muted"><i class="fa-solid fa-users"></i> 空位: ${item.max_people}</p>
                                    <p class="text-muted">最多可預訂空位: ${item.max_each_user}</p>
                                </div>
                                <div class="col-auto">
                                    <div class="row align-items-center">
                                        <h6 class="col-auto">$ ${formatPrice(item.price)}</h6>
                                        <div class="col-auto"><button type="button" class="btn btn-primary btn-rounded" data-plan="add" style="--bs-btn-disabled-opacity: 0.3"><i class="fa-solid fa-plus"></i></button></div>
                                        <h6 class="col-auto" data-plan="count">0</h6>
                                        <div class="col-auto"><button type="button" class="btn btn-primary btn-rounded" data-plan="sub" disabled style="--bs-btn-disabled-opacity: 0.3"><i class="fa-solid fa-minus"></i></button></div>
                                    </div>
                                </div>
                            </div>
                        </div>`);

                    // 增加人數
                    tmp.find('[data-plan="add"]').click(function (){
                        const count_elm = tmp.find('[data-plan="count"]');
                        const select_plan = _select_plan.find(({ plan }) => plan === item.Schedule_ID);
                        if (select_plan){
                            select_plan.count++;
                            count_elm.text(select_plan.count);

                            // 人數達上限, 禁用按鈕
                            if (select_plan.count >= item.max_people || select_plan.count >= item.max_each_user)
                                $(this).prop('disabled', true);
                        }else{
                            _select_plan.push({ plan: item.Schedule_ID, count: 1 });
                            count_elm.text(1);
                            tmp.find('[data-plan="sub"]').prop('disabled', false); // 啟用減少按鈕
                        }
                    });

                    // 減少人數
                    tmp.find('[data-plan="sub"]').click(function (){
                        const count_elm = tmp.find('[data-plan="count"]');
                        const select_plan = _select_plan.find(({ plan }) => plan === item.Schedule_ID);
                        if (select_plan){
                            select_plan.count--;
                            count_elm.text(select_plan.count);

                            // 人數未達上限, 啟用按鈕
                            if (select_plan.count < item.max_people && select_plan.count < item.max_each_user)
                                tmp.find('[data-plan="add"]').prop('disabled', false);

                            // 人數為0, 移除
                            if (select_plan.count === 0){
                                _select_plan.splice(_select_plan.indexOf(select_plan), 1);
                                $(this).prop('disabled', true); // 人數為0, 禁用減少按鈕
                            }
                        }
                    });

                    // 人數改變時, 更新總價錢
                    tmp.find('[data-plan="sub"], [data-plan="add"]').click(function (){
                        const total_elm = $('#total');
                        let total = 0;
                        _select_plan.forEach(({ plan, count }) => {
                            total += _plan.find(({ Schedule_ID }) => Schedule_ID === plan).price * count;
                        });
                        total_elm.text("$ " + formatPrice(total));
                    });
                    return tmp;
                });

                jq_plan.html(plan_html);
            }else{
                toastr.error(json.Message, json.Title ?? globalLang.Error);
            }
        }).catch((error) => {
            console.error(error);
        });
    }

    show_plan(moment()); // 預設當日

    /* 送出訂單 */
    $('#checkout').click(function (){
        const id = location.pathname.split('/').pop();

        /* 封鎖按鈕 */
        const bt = $(this);
        const html = bt.html();
        bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

        fetch('/api/checkout/', {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ plan: _select_plan, eventId: parseInt(id), date: jq_bookDate.children('input').val() })
        }).then(async (response) => {
            const json = await response.json();
            console.log(json); //debug
            if (response.ok){
                if (response.status === 200){ // 前往付款畫面
                    toastr.info(json.Message, json.Title);
                    setTimeout(() => {
                        location.replace(json.data.url);
                    }, 2000);
                }
            }else{
                if (response.status === 401){ // 未登入
                    sessionStorage.setItem('returnPath', location.pathname);
                    location.replace(json.path);
                }else{
                    toastr.error(json.Message, json.Title ?? globalLang.Error);
                }
                bt.html(html).removeAttr('disabled');
            }
        }).catch((error) => {
            console.error(error);
        });
    });
});