/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'toastr', 'owl.carousel.min'], function (jq, toastr) {
    "use strict";

    /* 輪播圖 */
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

    /* 視差效果 */
    document.addEventListener('scroll', function(){
        //index-head Parallax scrolling
        const scroll = window.scrollY;
        const index_head = $('#homeBackground > div');
        index_head[0].style.transform = `translateY(${scroll * 0.4}px)`
    }, (Modernizr.passiveeventlisteners ? {passive: true} : false))
})