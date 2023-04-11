/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'toastr', 'owl.carousel.min'], function (jq, toastr) {
    "use strict";


    $('#hotHkCanoeing, #hotHkClimbing, #hotHkDiving, #hotHkParagliding, #hotHkHiking, #hotCnCanoeing, #hotCnClimbing, #hotCnHotAirBalloon, #hotCnMountaineering, #hotCnParagliding, #hotCnSkiing, #hotCnHiking, #hotMoBungy, #hotMoClimbing, #hotTwCanoeing, #hotTwClimbing, #hotTwDiving, #hotTwMountaineering, #hotTwParachute, #hotTwParagliding, #hotTwHiking').click(function() {
        const activitiesSelection = $(this).attr('id');

        fetch(/*Here type url*/location.pathname, {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({activitiesSelection})
        }).then(async (response) => {
            const data = await response.json();
            if (data.code === 200) {
                let map = '';
                let topicStart = "<h3><b>" + data.country + "熱門活動-" + data.type + "</b></h3></br><div class='row row-cols-1 row-cols-md-4 g-4'>";
                let topicEnd = "</div>";
                $('#activitiesResult').empty();
                console.log(data);
                if (data.data.length <= 0) {
                    map = `<div class="row gy-4" style="min-width: 100%;">
                              <div class="col-12">
                                <h1 style="font-size:300%; text-align: center; margin: 70px;">噢！沒有找到相關活動。。。</h1>
                              </div>
                            </div>`;
                } else {
                    data.data.sort(function (a, b) {
                       let keyA = new Number(a.rate);
                       let keyB = new Number(b.rate);
                       if(keyA < keyB)
                           return 1;
                       if(keyA > keyB)
                           return -1;
                       return 0;
                    });

                    map = data.data.map((value) => {
                        let rate, comments;
                        if (value.comments == 0) {
                            comments = "暫無評論";
                        } else {
                            comments = value.comments.toString() + "則評論";
                        }

                        if (value.rate != null) {
                            if(value.rate < 4) {
                                rate = "<span id='ratingScore' class='fs-10'>" + value.rate.toString() + "</span><span class='fs-5'>/5.0</span><span class='fs-10'>&nbsp&nbsp&nbsp" + comments +  "</span>";
                            } else {
                                rate = "<span id='ratingScoreOverEqual4' class='fs-10'>" + value.rate.toString() + "</span><span class='fs-5'>/5.0</span><span class='fs-10'>&nbsp&nbsp&nbsp" + comments +  "</span>";
                            }

                        } else {
                            rate = "<span class='fs-10'>-</span><span class='fs-5'>/5.0</span><span class='fs-10'>&nbsp&nbsp&nbsp" + comments +  "</span>";
                        }

                        return `<div class="col-auto">
                                   <div class="item">
                                     <div class="card card-block mx-2" style="min-width: 300px;">
                                       <div class="ratio ratio-4x3 position-relative">
                                         <div class="overflow-hidden card-img-top">
                                           <div class="media-list-center">
                                             <img src="/panel/api/media/${value.link}" class="owl-lazy" alt="${value.link}">
                                           </div>
                                         </div>
                                       </div>
                                       <div class="card-body">
                                        <h5 class="card-title">${value.title}</h5>
                                        <p class="card-text">${value.summary}</p>
                                        <div class="row align-items-center">
                                          <div class="col-auto">
                                            <i class="fs-10 fa-solid fa-star text-warning"></i>` + rate + `</div>
                                        </div>
                                        <a href="/details/${value.id}" class="btn btn-primary stretched-link btn-rounded">了解更多</a>
                                      </div>
                                    </div>
                                  </div>
                                </div>`;
                    });
                }
                $('#activitiesResult').html(topicStart + map + topicEnd);
            } else {
                toastr.error(data.code);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

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