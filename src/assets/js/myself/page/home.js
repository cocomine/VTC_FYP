/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'toastr', 'owl.carousel.min'], function (jq, toastr) {
    "use strict";

    const Lang = JSON.parse($('#langJson').text());

    let owl = $('.owl-carousel');
    owl.owlCarousel({
        margin: 10,
        nav: true,
        dots: true,
        mergeFit:false,
        navText: [
            "<i class=\"fa-solid fa-chevron-left\"></i>",
            "<i class=\"fa-solid fa-chevron-right\"></i>"
        ],
        stagePadding: 40,
        lazyLoad:true,
        responsive: {
            0: {
                items: 1,
            },
            576: {
                items: 2,
            },
            992: {
                items: 3,
            },
            1200: {
                items: 4,
            }
        }
    })
})