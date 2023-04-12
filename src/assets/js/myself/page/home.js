/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define([ 'jquery', 'toastr', 'owl.carousel.min' ], function (jq, toastr){
    "use strict";
    const jq_activitiesResult = $('#activitiesResult');

    $('#hotHkCanoeing, #hotHkClimbing, #hotHkDiving, #hotHkParagliding, #hotHkHiking, #hotCnCanoeing, #hotCnClimbing, #hotCnHotAirBalloon, #hotCnMountaineering, #hotCnParagliding, #hotCnSkiing, #hotCnHiking, #hotMoBungy, #hotMoClimbing, #hotTwCanoeing, #hotTwClimbing, #hotTwDiving, #hotTwMountaineering, #hotTwParachute, #hotTwParagliding, #hotTwHiking').click(function (){
        const activitiesSelection = $(this).attr('id');

        // Loading
        jq_activitiesResult.html(
            `<div class="row gy-4">
                <div class="col-12">
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <lottie-player src="/assets/images/logo_lottie.json"  background="transparent"  speed="1"  style="width: 200px; height: 200px;" autoplay loop></lottie-player>
                        </div>
                    </div>
                </div>
            </div>`
        );

        fetch(location.pathname, {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ activitiesSelection })
        }).then(async (response) => {
            const data = await response.json();
            if (data.code === 200){
                let map = '';
                let start = "<h3><b>" + data.country + "熱門活動-" + data.type + "</b></h3></br><div class='row g-4'>";
                let end = "</div>";
                jq_activitiesResult.empty();

                if (data.data.length <= 0){
                    map =
                        `<div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row justify-content-center">
                                        <div class="col-auto">
                                            <lottie-player src="/assets/images/shake-a-empty-box.lottie.json"  background="transparent"  speed="1"  style="width: 300px; height: 300px;" autoplay loop></lottie-player>
                                        </div>
                                        <div class="w-100"></div>
                                        <h2 class="col-auto">噢! 沒有找到相關活動... (ノへ￣、)</h2>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                }else{

                    // Sort by rate
                    data.data.sort(function (a, b){
                        let keyA = Number(a.rate);
                        let keyB = Number(b.rate);
                        if (keyA < keyB)
                            return 1;
                        if (keyA > keyB)
                            return -1;
                        return 0;
                    });

                    // Map
                    map = data.data.map((value) => {
                        let rate;
                        if (value.rate != null){
                            if (value.rate < 4){
                                rate = "<span id='searchRatingScore'>" + value.rate + "(" + value.comments + ")</span>";
                            }else{
                                rate = "<span id='searchRatingScoreOverEqual4'>" + value.rate + "(" + value.comments + ")</span>";
                            }
                        }else{
                            rate = "<span>-/(0)</span>";
                        }

                        return `<div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                            <div class="card card-block overflow-hidden">
                                <div class="ratio ratio-4x3 card-img-top">
                                    <img src="/panel/api/media/${value.link}" class="owl-lazy" alt="${value.link}">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">${value.title}</h5>
                                    <p class="card-text">${value.summary}</p>
                                    <div class="w-100 text-end">
                                        <i class="fs-10 fa-solid fa-star text-warning me-1"></i>${rate}
                                    </div>
                                    <a href="/details/${value.id}" class="btn btn-primary stretched-link btn-rounded">了解更多</a>
                                </div>
                            </div>
                        </div>`;
                    }).join('');
                }
                jq_activitiesResult.html(start + map + end);
            }else{
                toastr.error(data.Message, data.Title);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#allNew').click(function (){
        const activitiesSelection = $(this).attr('id');

        // Loading
        jq_activitiesResult.html(
            `<div class="row gy-4">
                <div class="col-12">
                    <div class="row justify-content-center">
                        <div class="col-auto">
                            <lottie-player src="/assets/images/logo_lottie.json"  background="transparent"  speed="1"  style="width: 200px; height: 200px;" autoplay loop></lottie-player>
                        </div>
                    </div>
                </div>
            </div>`
        );

        fetch(location.pathname, {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ activitiesSelection })
        }).then(async (response) => {
            const data = await response.json();
            console.log(data);
            if (data.code === 200){
                let map = '';
                let start = '<div class="row gy-4">';
                let end = '</div>';

                for (let i = 0; i < data.country.length; i++){
                    map += '<div class="col-12"><h3><b>' + data.country[i] + '地區最新活動</b></h3></br><div class="owl-carousel owl-theme">';
                    map += data.data[i].map((value) => {
                        return `<div class="item">
                                    <div class="card card-block mx-2" style="min-width: 300px;">
                                        <div class="ratio ratio-4x3 card-img-top overflow-hidden">
                                            <img class="owl-lazy" data-src="panel/api/media/${value.link}" alt="${value.link}">
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title">${value.title}</h5>
                                            <p class="card-text">${value.summary}</p>
                                            <a href="/details/${value.id}" class="btn btn-primary stretched-link btn-rounded">了解更多</a>
                                        </div>
                                    </div>
                                </div>`;
                    }).join('');
                    map += '</div></div>';
                }

                jq_activitiesResult.html(start + map + end);
                owlCarousel();
            }else{
                toastr.error(data.Message, data.Title);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    /* 輪播圖 */
    function owlCarousel(){
        let owl = $('.owl-carousel');
        owl.owlCarousel({
            margin: 10,
            nav: true,
            dots: true,
            mergeFit: false,
            navText: [
                "<i class=\"fa-solid fa-chevron-left\"></i>",
                "<i class=\"fa-solid fa-chevron-right\"></i>"
            ],
            stagePadding: 40,
            lazyLoad: true,
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
        });
    }

    /* 視差效果 */
    document.addEventListener('scroll', function (){
        //index-head Parallax scrolling
        try{
            const scroll = window.scrollY;
            const index_head = $('#homeBackground > div');
            index_head[0].style.transform = `translateY(${scroll * 0.4}px)`;
        }catch (e){
            console.debug(e);
        }
    }, (Modernizr.passiveeventlisteners ? { passive: true } : false));

    owlCarousel();
});