/*
 * Copyright (c) 2022.
 * Create by cocomine
 * for Horizontal Menu
 * v1.5
 */

define(['jquery', 'toastr'], function (jq, toastr) {
    "use strict";

    /* 語言載入 */
    let Lang = $('#globalLang').text();
    Lang = JSON.parse(Lang);

    /* go-top */
    $('.go-top').click(() => {
        $('html').animate({scrollTop: 0}, 200)
    })

    /* go-top & fixed-header display */
    let last_scroll = 0;
    document.addEventListener('scroll',() => {
        const scroll = window.scrollY;
        if(last_scroll - scroll > 0) $('.go-top').fadeIn();
        else $('.go-top').fadeOut();
        last_scroll = scroll;

        // fixed-header
        if(scroll > 100 && window.innerWidth < 576) $('#fixed-header').fadeIn();
        else $('#fixed-header').fadeOut();
    }, (Modernizr.passiveeventlisteners ? {passive: true} : false));

    /* 接管連結 */
    $(document).on("click", 'a[href]', function (e) {
        const link = $(this).attr('href');
        if (/^(\/)/.test(link)) {
            e.preventDefault();
            e.stopPropagation()
            ajexLoad(link);
        }
    });

    /* 上一頁事件監聽 */
    $(window).on("popstate", function (e){
        e.preventDefault();
        e.stopPropagation();
        console.log("User jump page to '"+e.originalEvent.state.url+"'. AJAX Load.");
        ajexLoad(e.originalEvent.state.url, false)
    });

    /* 加載模組 */
    let Modules = [];
    const loadModules = (modules, callBlack = () => null) => {
        require(modules, callBlack);
        Modules = modules;
    }

    /* requireJS 加載模組活動 */
    require.onResourceLoad = function (context, map, depArray) {
        console.debug(`➡ ${map.name} Modules Loaded`);
    };

    /* 卸載模組 */
    function unModules(){
        Modules.map(function(item){
            require.undef(item);
            console.debug(`⬅ ${item} Modules unLoaded`);
        });
    }

    /* 載入頁面 */
    const ajexLoad = (link, putState = true) => {
        if (!/[$\/]/.test(link)) link = link + '/';
        $('#content').html(loadingPlaceholder)

        /* send */
        $.ajax({
            type: 'GET',
            url: link,
            success: function (data) {
                unModules()

                $('title').html(data.title);
                $('#title').html(data.head);
                $('#path').html(data.path);
                $('#content').html(data.content)

                if (putState) window.history.pushState({url: link}, data.title, link);
                window.dispatchEvent(new Event('load'));

                if(link === '/'){
                    $(".heard-area").hide();
                }else{
                    $(".heard-area").show();
                }
            },
            error: (xhr, textStatus) => {
                if (textStatus === 'error') {
                    if (xhr.responseJSON.code) {
                        //known error
                        if (xhr.responseJSON.code === 404) {
                            $('#content').html(page404(xhr.responseJSON.Message));
                        } else if (xhr.responseJSON.code === 403) {
                            $('#content').html(page403(xhr.responseJSON.Message));
                        } else if (xhr.responseJSON.code === 500) {
                            $('#content').html(page500(xhr.responseJSON.Message));
                        } else if (xhr.responseJSON.code === 401){
                            sessionStorage.setItem('returnPath', location.pathname);
                            location.replace(xhr.responseJSON.path)
                        }

                        if (putState) window.history.pushState({url: link}, '', link);
                        window.dispatchEvent(new Event('load'));
                    } else toastr.error(Lang.Error);
                } else if (textStatus === 'timeout') toastr.error('Request Timeout', '408');
                else toastr.error(Lang.Error);
            }
        })
    }

    /* 展開 menu */
    const updateNavBar = (link) => {
        const meun = $('#nav_menu')
        const active = meun.find(`[href="${link}"]`);
        active.parents('li').addClass('active')
    }

    /* 格式化銀碼 */
    const formatPrice = (Str) => {
        Str = Str.toString();

        const digits = Str.toString().split('.'); // 先分左邊跟小數點
        const integerDigits = digits[0].split(""); // 獎整數的部分切割成陣列
        const threeDigits = []; // 用來存放3個位數的陣列

        // 當數字足夠，從後面取出三個位數，轉成字串塞回 threeDigits
        while (integerDigits.length > 3) {
            threeDigits.unshift(integerDigits.splice(integerDigits.length - 3, 3).join(""));
        }

        threeDigits.unshift(integerDigits.join(""));
        digits[0] = threeDigits.join(',');

        return digits.join(".");
    }

    /* html code */
    const loadingPlaceholder = `
                    <div class='col-12 mt-4 col-md-8'>
                        <div class="row gy-4 gx-0 m-0">
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class='placeholder-glow'>
                                            <h5 class="card-title">
                                                <span class="placeholder col-6"></span>
                                            </h5>
                                            <p class="card-text row m-0">
                                                <span class="placeholder col-2" style="margin-right: 1.5rem"></span>
                                                <span class="placeholder col" style="margin-right: 1.5rem"></span>
                                                <span class="placeholder col-2"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col-12'>
                                <div class="card ratio ratio-21x9 overflow-hidden placeholder-glow">
                                    <div class="card-body placeholder"></div>
                                </div>
                            </div>
                            <div class='col-12'>
                                <div class="card ">
                                    <div class='card-body'>
                                        <div class='placeholder-glow'>
                                            <h5 class="card-title">
                                                <span class="placeholder col-6"></span>
                                            </h5>
                                            <p class="card-text">
                                                <span class="placeholder col-6"></span>
                                                <span class="placeholder col-4"></span>
                                                <span class="placeholder col-8"></span>
                                                <span class="placeholder col-4"></span>
                                                <span class="placeholder col-7"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='col-12 mt-4 col-md-4'>
                        <div class="row gy-4 gx-0 m-0">
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class='col-12 placeholder-glow row justify-content-center'>
                                            <h2 class="card-title text-center">
                                                <span class="placeholder col-8"></span>
                                            </h2>
                                            <a href="#" tabindex="-1" class="btn btn-rounded btn-primary disabled placeholder col-6"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class='placeholder-glow'>
                                            <p class="card-text">
                                                <span class="placeholder col-12 placeholder-lg"></span>
                                                <span class="placeholder col-3 placeholder-sm"></span>
                                            </p>
                                            <p class="card-text">
                                                <span class="placeholder col-12 placeholder-lg"></span>
                                                <span class="placeholder col-3 placeholder-sm"></span>
                                            </p>
                                            <p class="card-text">
                                                <span class="placeholder col-12 placeholder-lg"></span>
                                                <span class="placeholder col-3 placeholder-sm"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`
    const page404 = (Msg) => `
                    <div class='col-12 mt-4'>
                        <div class="row gy-4 gx-0 m-0">
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class="row justify-content-center">
                                            <div class="col-auto">
                                                <lottie-player src="https://assets8.lottiefiles.com/packages/lf20_kcsr6fcp.json"  background="transparent"  speed="1"  style="width: 500px; height: 250px;"  loop  autoplay></lottie-player>
                                            </div>
                                            <div class="w-100"></div>
                                            <h2 class="col-auto">${Msg}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`
    const page403 = (Msg) => `
                    <div class='col-12 mt-4'>
                        <div class="row gy-4 gx-0 m-0">
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class="row justify-content-center">
                                            <div class="col-auto">
                                                <lottie-player src="https://assets5.lottiefiles.com/packages/lf20_UxVUy6KZG1.json"  background="transparent"  speed="1"  style="width: 300px; height: 200px;"  loop  autoplay></lottie-player>                                            </div>
                                            <div class="w-100"></div>
                                            <h2 class="col-auto">${Msg}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`
    const page500 = (Msg) => `
                    <div class='col-12 mt-4'>
                        <div class="row gy-4 gx-0 m-0">
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class="row justify-content-center">
                                            <div class="col-auto">
                                                <lottie-player src="https://assets5.lottiefiles.com/packages/lf20_8qMJfR.json"  background="transparent"  speed="1"  style="width: 300px; height: 300px;"  loop  autoplay></lottie-player>                                            <div class="w-100"></div>
                                            <h2 class="col-auto">${Msg}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`

    return {
        ajexLoad, loadModules, updateNavBar, formatPrice, Lang
    }
})