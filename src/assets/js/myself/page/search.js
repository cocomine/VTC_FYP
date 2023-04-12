define([ 'jquery', 'toastr', 'owl.carousel.min' ], function (jq, toastr){
    "use strict";
    const jq_getResult = $('#getResult');
    const searchInput = $('#getSearchInput').val();

    jq_getResult.html(
        `<div class="col-12">
            <div class="row justify-content-center">
                <div class="col-auto">
                    <lottie-player src="/assets/images/logo_lottie.json"  background="transparent"  speed="1"  style="width: 200px; height: 200px;" autoplay loop></lottie-player>
                </div>
            </div>
        </div>`
    )

    fetch(location.pathname, {
        method: 'POST',
        redirect: 'error',
        headers: {
            'Content-Type': 'application/json; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ searchInput })
    }).then(async (response) => {
        const data = await response.json();
        console.log(data);

        if (data.code === 200){
            let map = '';
            jq_getResult.empty();

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
                        <div class="card card-block mx-2">
                            <div class="ratio ratio-4x3 position-relative">
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
                });
            }
            jq_getResult.html(map);

        }else{
            toastr.error(data.code);
        }

    }).catch((error) => {
        console.log(error);
    });
});