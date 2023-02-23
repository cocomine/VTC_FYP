/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'toastr'], function (jq, toastr) {
    "use strict";

    const Lang = JSON.parse($('#langJson').text());

    let owl = $('.owl-carousel');
    owl.owlCarousel({
        margin: 10,
        loop: true,
        responsive: {
            0: {
                items: 1
            },
            500: {
                items: 2
            },
            750: {
                items: 3
            },
            1000: {
                items: 4
            }
        }
    })
})