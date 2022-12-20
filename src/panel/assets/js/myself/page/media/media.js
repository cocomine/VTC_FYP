/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'toastr'], function (jq, toastr) {
    let media_list;
    /* 沒有任何圖片 */
    const empty = `<div class="col-auto"><lottie-player src="https://assets7.lottiefiles.com/packages/lf20_IIxb9U.json" background="transparent" speed="1" style="width: 120px; height: 120px;" autoplay></lottie-player></div>
                    <div class="col-auto h-auto"><h3 class="align-middle">No media</h3></div>`

    /* 圖片列表 */
    fetch('/panel/api/media/list',{
        method: 'GET',
        redirect: 'error'
    }).then(async (response) => {
        const data = await response.json();
        if(data.code === 200){
            /* 檢查是否沒有任何圖片 */
            if(data.body.length <= 0){
                $('#media-list').html(empty);
                return;
            }

            const map = data.body.map((value)=>
                `<div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="ratio ratio-1x1 position-relative img-focus" data-id="${value.id}">
                            <div class="overflow-hidden">
                                <div class="center-img">
                                    <img src="" draggable="false" alt="${value.id} Image" data-src="/panel/api/media/${value.id}" class="lazy"/>
                                </div>
                            </div>
                        </div>
                    </div>`
            )

            media_list = data.body
            $('#media-list').html(map)
            lazy_load();
        }else {
            toastr.error(data.Message)
        }
    })

    /* 延遲載入圖片
    * ref: https://web.dev/lazy-loading-images/
    */
    function lazy_load() {
        let lazyImages = [].slice.call(document.querySelectorAll("img.lazy"));

        if ("IntersectionObserver" in window) {
            let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let lazyImage = entry.target;
                        lazyImage.src = lazyImage.dataset.src;
                        lazyImage.classList.remove("lazy");
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            });

            lazyImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        }else{
            lazyImages.forEach(function (lazyImage) {
                lazyImage.src = lazyImage.dataset.src;
                lazyImage.classList.remove("lazy");
            })
        }
    }
})