/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'moment.min', 'forge'], function (jq, moment, forge) {
    "use strict";

    /* 反轉搜尋 */
    $('#reverse').click(function (e) {
        e.preventDefault();
        const Departure = $('#Departure');
        const Destination = $('#Destination')

        const temp = Departure.val();
        const temp2 = Destination.val();

        Departure.val(temp2);
        Destination.val(temp);

        $(this).children('i').animate({deg: "+=180"},
            {
                duration: 500, step: function (now) {
                    $(this).css({transform: 'rotate(' + now + 'deg)'});
                }
            })
    })

    /* set min date */
    //$('#Date').attr('min', moment().format('YYYY-MM-DD'))

    /* Search */
    $('form').submit(function (e) {
        if (!e.isDefaultPrevented() && this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            const data = $(this).serializeObject();

            const hashtag = data.departure + '&' + data.destination + '&' + data.date + '&' + data.cabin;
            ajexLoad("/panel/search#" + forge.util.encode64(hashtag))
        }
    })
})