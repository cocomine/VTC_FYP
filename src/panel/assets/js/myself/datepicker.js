/*
 * Copyright (c) 2022.
 * Create by cocomine
 * 1.0.2
 */

/*
 * css must be loaded before each use
 * <link rel="stylesheet" href="/panel/assets/css/myself/datetimepicker.css">
 */
define(['jquery', 'moment', 'bootstrap'], function (jq, moment, bootstrap) {
    "use strict";
    let selectDate = moment();
    let activateDate = moment();
    let minDate = null;
    let maxDate = null;
    const pickers = $('.date-picker')

    /* 初始化 */
    pickers.each((index, picker) => {
        setup(picker)
    });

    function setup(picker) {
        picker = $(picker);
        const children = picker.children('.date-picker-toggle');

        /* 觸發 */
        if (!children.val().length) {
            children.val(activateDate.format('YYYY-MM-DD')).on('input focus', function () {
                update($(this));
            })
        }

        /* 強制重新繪製 */
        children[0].drawDatePicker = function () {
            update($(this))
        }

        /* 是否使用dropdown */
        if (!picker.hasClass('date-picker-inline')) {
            picker.append('<div class="dropdown-menu date-calendar"></div>')
            new bootstrap.Dropdown(children[0], {autoClose: 'outside'});
        } else {
            children.click((e) => e.preventDefault())
            update(children)
        }

        /* click event */
        /* 上一個月 */
        picker.on('click', '[data-dt-type="last"]', (e) => {
            selectDate.subtract(1, 'months')
            const target = $(e.delegateTarget)
            target.children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate, target.children('.date-picker-toggle')[0].disableDate))
        })

        /* 下一個月 */
        picker.on('click', '[data-dt-type="next"]', (e) => {
            selectDate.add(1, 'months')
            const target = $(e.delegateTarget)
            $(e.delegateTarget).children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate, target.children('.date-picker-toggle')[0].disableDate))
        })

        /* 選擇日期 */
        picker.on('click', '.day:not(.disable)', function (e) {
            const day = $(this).text();
            const target = $(e.delegateTarget)
            activateDate = moment(selectDate).set('date', parseInt(day));
            target.children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate, target.children('.date-picker-toggle')[0].disableDate));
            target.children('.date-picker-toggle').val(activateDate.format('YYYY-MM-DD')).focus();
        })

        /* 本月 */
        picker.on('click', '[data-dt-type="today"]', (e) => {
            const target = $(e.delegateTarget)
            selectDate = moment();
            activateDate = moment();
            target.children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate, target.children('.date-picker-toggle')[0].disableDate))
            target.children('.date-picker-toggle').val(activateDate.format('YYYY-MM-DD')).focus();
        })
    }

    /**
     * 添加新 date-picker
     * @param {jQuery<HTMLElement>} pickers
     */
    const addPicker = function (pickers) {
        pickers.each((index, picker) => {
            setup(picker)
        });
    }

    /**
     * 更新 html
     * @param {jQuery<HTMLElement>} input
     */
    function update(input) {
        /* 手機螢幕尺寸使用系統自帶 */
        if (!input.parent('.date-picker').hasClass('date-picker-inline')) {
            if (window.innerWidth < 576) input.removeAttr('data-bs-toggle');
            else input.attr('data-bs-toggle', 'dropdown');
        }

        //更新 activate day
        const temp = moment(input.val());
        if (temp.isValid()) {
            activateDate = moment(temp)
            selectDate = moment(temp);
            minDate = input.attr('min')
            minDate = minDate === undefined ? null : moment(minDate);
            maxDate = input.attr('max')
            maxDate = maxDate === undefined ? null : moment(maxDate);
            input.parent('.date-picker').children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate, input[0].disableDate));
        }
    }

    /**
     * 檢查日期是否禁用
     * @param {moment.Moment} correctDay
     * @param {string[]} disableDates
     * @return boolean
     */
    function checkDisableDate(correctDay, disableDates) {
        if (!disableDates) return false;

        const dates = disableDates.map((value) => moment(value))
        const tmp = dates.filter((value) => value.isSame(correctDay, 'day'));
        return tmp.length > 0
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
    function calendar(date, activateDate, minDate, maxDate, disableDates) {
        let table = '<tr>'
        const endDay = moment(date).endOf('month');
        const startDay = moment(date).startOf('month');

        /* 開始空格 */
        const n = startDay.day()
        for (let i = 0; i < n; i++) {
            table += '<td></td>'
        }

        /* 日期 */
        let correctDay = moment(startDay);
        while (true) {
            const disable = (minDate != null && minDate.isAfter(correctDay, 'day')) || (maxDate != null && maxDate.isBefore(correctDay, 'day')) || (checkDisableDate(correctDay, disableDates)) ? 'disable' : ''; //最少日期 || 最大日期
            const activate = activateDate.isSame(correctDay, 'day') ? 'activate' : ''; //當天
            table += `<td class="day ${activate} ${disable}">` + correctDay.date() + '</td>'
            if (correctDay.day() === 6) table += '</tr><tr>' //逢週六下一行
            if (correctDay.date() + 1 > endDay.date()) break; //月尾結束
            correctDay.add(1, 'days');
        }
        table += '</tr>'

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

    return {addPicker}
})