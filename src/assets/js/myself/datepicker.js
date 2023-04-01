/*
 * Copyright (c) 2023.
 * Create by cocomine
 * 1.2.8
 */

/*
 * css must be loaded before each use
 * <link rel="stylesheet" href="/panel/assets/css/myself/datetimepicker.css">
 */
define([ 'jquery', 'moment', 'bootstrap' ], function (jq, moment, bootstrap){
    "use strict";
    const pickers = $('.date-picker');

    /* 初始化 */
    pickers.each((index, picker) => {
        setup(picker);
    });

    function setup(picker){
        picker = $(picker);
        const children = picker.children('.date-picker-toggle');
        let selectDate = moment();
        let activateDate = moment();
        let minDate = children.attr('min');
        let maxDate = children.attr('max');
        picker[0].datepicker = { disableDate: [] };

        if (children.length <= 0){
            throw new Error('Children element class="date-picker-toggle" not found. Please check your code.');
        }

        /* 預設日期(今日) */
        if (children.val().length <= 0){
            children.val(activateDate.format('YYYY-MM-DD'));
        }

        /* 用戶點擊 */
        children.on('input focus change', function (){
            update();
        });

        /* 強制重新繪製 */
        picker[0].datepicker.draw = function (){
            minDate = children.attr('min');
            minDate = minDate === undefined ? null : moment(minDate);
            maxDate = children.attr('max');
            maxDate = maxDate === undefined ? null : moment(maxDate);
            picker.children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate, picker[0].datepicker.disableDate));
        };

        /* 是否使用dropdown */
        if (!picker.hasClass('date-picker-inline')){
            picker.append('<div class="dropdown-menu date-calendar"></div>');
            new bootstrap.Dropdown(children[0], { autoClose: 'outside' });
        }else{
            children.click((e) => e.preventDefault());
            update(children);
        }

        /* event */
        /* 上一個月 */
        picker.on('click', '[data-dt-type="last"]', (e) => {
            const prevDate = selectDate.clone();
            selectDate.subtract(1, 'months');
            picker.children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate, picker[0].datepicker.disableDate));
            picker.trigger('datepicker.prev_month', { prevDate: prevDate, newDate: selectDate });
        });

        /* 下一個月 */
        picker.on('click', '[data-dt-type="next"]', (e) => {
            const prevDate = selectDate.clone();
            selectDate.add(1, 'months');
            picker.children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate, picker[0].datepicker.disableDate));
            picker.trigger('datepicker.next_month', { prevDate: prevDate, newDate: selectDate });
        });

        /* 選擇日期 */
        picker.on('click', '.day:not(.disable)', function (e){
            const day = $(this).text();
            const prevDate = activateDate.clone();

            activateDate = selectDate.clone().set('date', parseInt(day));
            picker.children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate, picker[0].datepicker.disableDate));
            children.val(activateDate.format('YYYY-MM-DD')).trigger('input');
            picker.trigger('datepicker.select_date', { prevSelect: prevDate, newSelect: activateDate });
        });

        /* 今天 */
        picker.on('click', '[data-dt-type="today"]', (e) => {
            const prevDate = activateDate.clone();
            selectDate = moment();
            activateDate = moment();
            picker.children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate, picker[0].datepicker.disableDate));
            children.val(activateDate.format('YYYY-MM-DD')).trigger('input');
            picker.trigger('datepicker.select_today', { prevSelect: prevDate, newSelect: activateDate });
        });

        /**
         * 更新 html
         */
        function update(){
            /* 手機螢幕尺寸使用系統自帶 */
            if (!picker.hasClass('date-picker-inline')){
                if (window.innerWidth < 576) children.removeAttr('data-bs-toggle');
                else children.attr('data-bs-toggle', 'dropdown');
            }

            //更新 activate day
            const temp = moment(children.val());
            if (temp.isValid()){
                activateDate = moment(temp);
                selectDate = moment(temp);
                minDate = children.attr('min');
                minDate = minDate === undefined ? null : moment(minDate);
                maxDate = children.attr('max');
                maxDate = maxDate === undefined ? null : moment(maxDate);
                picker.children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate, picker[0].datepicker.disableDate));
            }
        }
    }

    /**
     * 添加新 date-picker
     * @param {jQuery<HTMLElement>} pickers
     */
    const addPicker = function (pickers){
        pickers.each((index, picker) => {
            setup(picker);
        });
    };

    /**
     * 檢查日期是否禁用
     * @param {moment.Moment} correctDay
     * @param {string[]} disableDates
     * @return boolean
     */
    function checkDisableDate(correctDay, disableDates){
        if (!disableDates) return false;

        const dates = disableDates.map((value) => moment(value));
        const tmp = dates.filter((value) => value.isSame(correctDay, 'day'));
        return tmp.length > 0;
    }

    /**
     * 月曆 html
     * @param {moment.Moment} date
     * @param {moment.Moment} activateDate
     * @param {moment.Moment|null} minDate
     * @param {moment.Moment|null} maxDate
     * @param {string[]} disableDates
     * @return {string}
     */
    function calendar(date, activateDate, minDate, maxDate, disableDates){
        let table = '<tr>';
        const endDay = date.clone().endOf('month');
        const startDay = date.clone().startOf('month');

        /* 開始空格 */
        const n = startDay.day();
        for (let i = 0; i < n; i++){
            table += '<td></td>';
        }

        /* 日期 */
        let correctDay = moment(startDay);
        while (true){
            const disable = (minDate != null && minDate.isAfter(correctDay, 'day'))
            || (maxDate != null && maxDate.isBefore(correctDay, 'day'))
            || (checkDisableDate(correctDay, disableDates)) ? 'disable' : '';

            const activate = activateDate.isSame(correctDay, 'day') ? 'activate' : ''; //當天

            table += `<td class="day ${activate} ${disable}">` + correctDay.date() + '</td>';
            if (correctDay.day() === 6) table += '</tr><tr>'; //逢週六下一行
            if (correctDay.date() + 1 > endDay.date()) break; //月尾結束
            correctDay.add(1, 'days');
        }
        table += '</tr>';

        const disable_last = minDate != null && minDate.isSameOrAfter(correctDay, 'month') ? 'disabled' : ''; //最少日期
        const disable_next = maxDate != null && maxDate.isSameOrBefore(correctDay, 'month') ? 'disabled' : ''; //最大日期
        return `
        <div class="row">
            <div class="col-auto">
                <div class="row justify-content-around mb-2 align-items-center">
                    <div class="col-auto"><button class="btn btn-outline-primary" data-dt-type="last" ${disable_last}><i class="fa-solid fa-chevron-left"></i></button></div>
                    <div class="col-auto"><span style="font-size: 1.5em">${date.format('YYYY M')}</span></div>
                    <div class="col-auto"><button class="btn btn-outline-primary" data-dt-type="next" ${disable_next}><i class="fa-solid fa-chevron-right"></i></button></div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col" style="color: red">Sun</th>
                            <th scope="col">Mon</th>
                            <th scope="col">Tue</th>
                            <th scope="col">Wed</th>
                            <th scope="col">Thu</th>
                            <th scope="col">Fri</th>
                            <th scope="col">Sat</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${table}
                    </tbody>
                </table>
                <div class="m-2"><button class="btn btn-outline-primary w-100" data-dt-type="today"><i class="fa fa-calendar-day me-2"></i>Today</button></div>
            </div>
        </div>`;
    }

    return { addPicker };
});