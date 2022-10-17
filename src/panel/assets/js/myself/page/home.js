/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery'], function () {
    "use strict";
    $('#reverse').click(function () {
        const temp = $('#Departure').val();
        const temp2 = $('#Destination').val();

        $('#Departure').val(temp2);
        $('#Destination').val(temp);

        $(this).children('i').animate({deg: "+=180"},
            {duration: 500, step: function(now){
                    $(this).css({ transform: 'rotate(' + now + 'deg)' });
                }
            })
    })
})