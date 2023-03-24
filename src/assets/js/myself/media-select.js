/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

/*
 * css must be loaded before each use
 * <link rel="stylesheet" href="/panel/assets/css/myself/media-select.css">

 * To use multiple languages, you must have the following html
 * <pre id="media-select-LangJson" class="d-none">$LangJson</pre>
 * $LangJson => Place text in json format
 * json must conform to the structure, you can refer to lines 21 to 25
 */
define(['jquery', 'bootstrap'], function (jq, bootstrap) {
    let max_sel, callback, filter_mime;
    let selected_list = [];

    /* 多語言處理 */
    const Lang = {
        No_media: "No Media",
        Media: "Media %s",
        Unknown_Error: "An unknown error occurred!!",
        title: "Select Media",
        Select: ["Select", "Media"],
        ...JSON.parse($('#media-select-LangJson').text())
    }

    //沒有任何圖片
    const empty = `<div class="col-auto"><lottie-player src="https://assets7.lottiefiles.com/packages/lf20_IIxb9U.json" background="transparent" speed="1" style="width: 120px; height: 120px;" autoplay></lottie-player></div>
                    <div class="col-auto h-auto"><h3 class="align-middle">${Lang.No_media}</h3></div>`
    //modal html
    const html = `
        <div id='Media-select-modal' class='modal fade' tabindex='-1'>
            <div class='modal-dialog modal-xl modal-dialog-scrollable'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'><b>${Lang.title}</b></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class='modal-body'>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" role="tabpanel" id="Media-select-pane">
                                <div class="row gy-4 align-items-center media-list select-mode">
                                    <lottie-player src="https://assets7.lottiefiles.com/packages/lf20_j3ndxy3v.json" background="transparent" speed="1" style="width: 200px; height: 200px;" loop autoplay></lottie-player>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-rounded" disabled>${Lang.Select[0]} <span>0</span> ${Lang.Select[1]}</button>
                    </div>
                </div>
            </div>
        </div>`;

    /* 如果存在 */
    if (document.getElementById('Media-select-modal') !== null) $('#Media-select-modal').remove();
    //jquery
    const jq_modal = $(html);
    //bootstrap
    const bs_modal = bootstrap.Modal.getOrCreateInstance(jq_modal[0]);
    /* add in body */
    $('body').append(jq_modal);

    /* 當關閉彈出視窗 */
    jq_modal.on('hidden.bs.modal', () => {
        jq_modal.find('.media-list').html(`<lottie-player src="https://assets7.lottiefiles.com/packages/lf20_j3ndxy3v.json" background="transparent" speed="1" style="width: 200px; height: 200px;" loop autoplay></lottie-player>`)
        jq_modal.find('.btn-primary > span').text('0');
        jq_modal.find('.btn-primary').attr('disabled', 'disabled')
        selected_list = [];
    })

    /* 選取圖片 */
    jq_modal.find('.media-list').on('click', '[data-id]', function () {
        const elm = $(this);
        const id = elm.data('id');

        /* 選取 */
        if (selected_list.includes(id)) {
            //is selected
            elm.removeClass('selected')
            selected_list.splice(selected_list.indexOf(id), 1);
        } else {
            //not selected
            if (selected_list.length >= max_sel && max_sel !== 0) return; //超出數量
            elm.addClass('selected')
            selected_list.push(id);
        }

        jq_modal.find('.btn-primary > span').text(selected_list.length.toString());
    })

    /* 確認選取 */
    jq_modal.find('.btn-primary').click(function () {
        callback([...selected_list])
        bs_modal.hide();
    });

    /**
     * 列表過濾MIME媒體類型
     */
    function load_list() {
        fetch('/panel/api/media/list', {
            method: 'GET',
            redirect: 'error'
        }).then(async (response) => {
            const data = await response.json();
            if (data.code === 200) {
                // 過濾類型
                data.body = data.body.filter((value) => filter_mime.test(value.mime));
                // 檢查是否沒有任何圖片
                if (data.body.length <= 0) {
                    jq_modal.find('.media-list').html(empty);
                    return;
                }

                //html
                const map = data.body.map((value) =>
                    `<div class="col-6 col-sm-4 col-md-3 col-lg-2 col-xxl-1">
                        <div class="ratio ratio-1x1 media-list-focus" data-id="${value.id}">
                            <div class="overflow-hidden">
                                <div class="media-list-center">
                                    <img src="/panel/assets/images/image_loading.webp" draggable="false" alt="${Lang.Media.replace('%s', value.id)}" data-src="/panel/api/media/${value.id}" class="lazy"/>
                                </div>
                            </div>
                        </div>
                    </div>`
                )

                //print out
                jq_modal.find('.media-list').html(map)
                jq_modal.find('.btn-primary').removeAttr('disabled')
                lazy_load(); //lazy loading
            } else {
                toastr.error(data.Message)
            }
        }).catch((error) => {
            console.log(error)
        });
    }

    /**
     * 選擇媒體
     * @param {number} max 最多可選擇媒體, 0=無限
     * @param {RegExp} mime 列表過濾MIME媒體類型
     * @param {([{id: string, name: string}])=>void} selected_media 回傳選擇媒體id
     */
    const select_media = function(selected_media, max = 0, mime = /.*/) {
        max_sel = max;
        filter_mime = mime;
        callback = selected_media;
        load_list()
        bs_modal.show();
    }


    /**
     * lazy loading images
     * ref: https://web.dev/lazy-loading-images/
     */
    function lazy_load() {
        let lazyImages = [].slice.call(document.querySelectorAll("img.lazy"));

        if ("IntersectionObserver" in window) {
            let lazyImageObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        let lazyImage = entry.target;
                        lazyImage.src = lazyImage.dataset.src;
                        lazyImage.classList.remove("lazy");
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            });

            lazyImages.forEach(function (lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        }
    }

    /* 外部function */
    return {
        select_media, data: {jq_modal: () => jq_modal, filter_mime: () => filter_mime},
    }
})