/*
 * Copyright (c) 2022. 
 * Create by cocomine
 */

define(['jquery', 'bootstrap'], function (jq, bootstrap) {
    let media_list;
    const Lang = JSON.parse($('#media-select-LangJson').text());
    //沒有任何圖片
    const empty = `<div class="col-auto"><lottie-player src="https://assets7.lottiefiles.com/packages/lf20_IIxb9U.json" background="transparent" speed="1" style="width: 120px; height: 120px;" autoplay></lottie-player></div>
                    <div class="col-auto h-auto"><h3 class="align-middle">${Lang.No_media}</h3></div>`

    /* lazy loading images
    ref: https://web.dev/lazy-loading-images/ */
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
        }
    }
})