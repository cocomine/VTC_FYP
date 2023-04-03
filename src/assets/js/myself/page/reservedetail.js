/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery'], function (jq) {

    /* Count content length */
    $('#summary').on('input focus', function (){
        const length = $(this).val().length;
        $(this).parent('div').children('span').text(length + "/" + $(this).attr('maxlength'));
    });

})
