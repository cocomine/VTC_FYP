/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'toastr'], function (jq, toastr) {
    "use strict";

    $('#allAirBtn, #parachuteBtn, #paraglidingBtn, #bungyBtn, #otherAirBtn').click(function() {

        const activitiesSelection = $(this).attr('id');

        $('#allAirBtn, #parachuteBtn, #paraglidingBtn, #bungyBtn, #otherAirBtn').removeClass('btn-primary');
        $('#allAirBtn, #parachuteBtn, #paraglidingBtn, #bungyBtn, #otherAirBtn').addClass('btn-light');
        $(this).removeClass('btn-light');
        $(this).addClass('btn-primary');


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
            console.log(data);
            if (data.code === 200) {
                let map = '';
                $('#airEvent').empty();

                if (data.data.length <= 0){
                    map = `<div class="card" style="min-width: 100%;">
                              <div class="card-body">
                                <h1 style="font-size:300%; text-align: center; margin: 70px;">噢！沒有找到相關活動。。。</h1>
                              </div>
                            </div>`;
                } else {
                    map = data.data.map((value) => {

                        let rate, comments;

                        if (value.comments == 0) {
                            comments = "暫無評論";
                        } else {
                            comments = value.comments.toString() + "則評論";
                        }

                        if (value.rate != null) {
                            if(value.rate < 4) {
                                rate = "<span id='airRatingScore' class='fs-10'>" + value.rate.toString() + "</span><span class='fs-5'>/5.0</span><span class='fs-10'>&nbsp&nbsp&nbsp" + comments +  "</span>";
                            } else {
                                rate = "<span id='airRatingScoreOverEqual4' class='fs-10'>" + value.rate.toString() + "</span><span class='fs-5'>/5.0</span><span class='fs-10'>&nbsp&nbsp&nbsp" + comments +  "</span>";
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
                $('#airEvent').html(map);

            } else {
                toastr.error(data.code);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    /* 視差效果 */
    document.addEventListener('scroll', function(){
        //index-head Parallax scrolling
        const scroll = window.scrollY;
        const index_head = $('#airActivitiesBackground > div');
        index_head[0].style.transform = `translateY(${scroll * 0.4}px)`
    }, (Modernizr.passiveeventlisteners ? {passive: true} : false))
})