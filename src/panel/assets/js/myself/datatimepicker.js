/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'moment.min', 'bootstrap'], function (jq, moment, bootstrap) {
    "use strict";
    let selectDate = moment();
    let activateDate = moment();
    let minDate = null;
    let maxDate = null;

    /* 初始化 */
    const pickers = $('.date-picker').append('<div class="dropdown-menu date-calendar"></div>')
    pickers.map((index, picker) => {
        const children = $(picker).children('.date-picker-toggle');
        new bootstrap.Dropdown(children[0], {autoClose: 'outside'});
        children.val(activateDate.format('YYYY-MM-DD')).on('input focus', function () {
            //更新 activate day
            const temp = moment($(this).val());
            if (temp.isValid()) {
                activateDate = moment(temp)
                selectDate = moment(temp);
                minDate = $(this).attr('min')
                minDate = minDate === undefined ? null : moment(minDate);
                maxDate = $(this).attr('max')
                maxDate = maxDate === undefined ? null : moment(maxDate);
                $(picker).children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate));
            }
        })
    })

    /* 上一個月 */
    pickers.on('click', '[data-dt-type="last"]', (e) => {
        selectDate.subtract(1, 'months')
        $(e.delegateTarget).children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate))
    })

    /* 下一個月 */
    pickers.on('click', '[data-dt-type="next"]', (e) => {
        selectDate.add(1, 'months')
        $(e.delegateTarget).children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate))
    })

    /* 選擇日期 */
    pickers.on('click', '.day:not(.disable)', function (e) {
        const day = $(this).text();
        activateDate = moment(selectDate).set('date', parseInt(day));
        $(e.delegateTarget).children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate));
        $(e.delegateTarget).children('.date-picker-toggle').val(activateDate.format('YYYY-MM-DD'));
    })

    /* 本月 */
    pickers.on('click', '[data-dt-type="today"]', (e) => {
        selectDate = moment();
        activateDate = moment();
        $(e.delegateTarget).children('.date-calendar').html(calendar(selectDate, activateDate, minDate, maxDate))
        $(e.delegateTarget).children('.date-picker-toggle').val(activateDate.format('YYYY-MM-DD'));
    })

    /* 月曆 */
    function calendar(date, activateDate, minDate, maxDate) {
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
            const disable = (minDate != null && minDate.isAfter(correctDay, 'day')) || (maxDate != null && maxDate.isBefore(correctDay, 'day')) ? 'disable' : ''; //最少日期 || 最大日期
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
                            <th scope="col">Sun</th>
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
})