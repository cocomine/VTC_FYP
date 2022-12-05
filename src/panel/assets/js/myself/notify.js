/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

define(['jquery', 'myself/ajex', 'moment.min'], function (jq, ajex, moment) {
    let NotifyIDList = [];

    /* load notify when ready*/
    window.addEventListener('load', load_notify);

    /* load notify */
    function load_notify() {
        $.get({
            url: "/panel/api/notify",
            success: function (data) {
                const seenNotifyID = (localStorage.getItem('seenNotifyID') !== null) ? JSON.parse(localStorage.getItem('seenNotifyID')) : [];
                let notifyList = '';
                let newNotifyCount = 0;
                NotifyIDList = [];

                data.body.forEach(function (notifyItem) {
                    const time = moment(notifyItem.Time);
                    const elapsed = moment().diff(time);
                    let text;

                    if (!seenNotifyID.includes(notifyItem.notifyID)) newNotifyCount++;
                    NotifyIDList.push(notifyItem.notifyID);

                    if (elapsed <= 10 * 1000) { //in 10s
                        text = ajex.Lang.notify.just_now
                    }
                    if (elapsed > 10 * 1000 && elapsed <= 60 * 1000) { //in 60s(1min)
                        text = ajex.Lang.notify.few_seconds;
                    }
                    if (elapsed > 60 * 1000 && elapsed <= 60 * 60 * 1000) { //in 60min(1h)
                        text = moment().diff(time, 'minute') + " " + ajex.Lang.notify.minutes_ago;
                    }
                    if (elapsed > 60 * 60 * 1000 && elapsed <= 24 * 60 * 60 * 1000) { //in 24h(1d)
                        text = moment().diff(time, 'hour') + " " + ajex.Lang.notify.hour_ago
                    }
                    if (elapsed > 24 * 60 * 60 * 1000 && elapsed <= 7 * 24 * 60 * 60 * 1000) { //in 7d(1w)
                        text = moment().diff(time, 'day') + " " + ajex.Lang.notify.day_ago
                    }
                    if (elapsed > 7 * 24 * 60 * 60 * 1000 && elapsed <= 30 * 24 * 60 * 60 * 1000) { //in 1m
                        text = moment().diff(time, 'week') + " " + ajex.Lang.notify.week_ago
                    }
                    if (elapsed > 30 * 24 * 60 * 60 * 1000) { //More than 1m
                        text = moment().diff(time, 'month') + " " + ajex.Lang.notify.month_ago
                    }
                    //console.log(text);

                    const notify = `
                        <a href="${notifyItem.link}" class="notify-item">
                            <div class="notify-thumb">${notifyItem.icon}</div>
                            <div class="notify-text">
                                <p>${notifyItem.Msg}</p>
                                <span>${text}</span>
                            </div>
                        </a>`;
                    notifyList += notify;
                })
                $('#notify').html(notifyList);
                if (newNotifyCount > 0) $('#notify-bell').html('<span>' + newNotifyCount + '</span>');
            }
        })
    }

    /* notify bell click */
    $('#notify-bell').click(function () {
        $(this).html('');
        localStorage.setItem('seenNotifyID', JSON.stringify(NotifyIDList));
    });
});