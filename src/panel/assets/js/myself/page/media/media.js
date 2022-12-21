/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'toastr', 'bootstrap'], function (jq, toastr, bootstrap) {
    let media_list;
    const Lang = JSON.parse($('#LangJson').text());
    //沒有任何圖片
    const empty = `<div class="col-auto"><lottie-player src="https://assets7.lottiefiles.com/packages/lf20_IIxb9U.json" background="transparent" speed="1" style="width: 120px; height: 120px;" autoplay></lottie-player></div>
                    <div class="col-auto h-auto"><h3 class="align-middle">${Lang.No_media}</h3></div>`

    /* 圖片列表 */
    fetch('/panel/api/media/list', {
        method: 'GET',
        redirect: 'error'
    }).then(async (response) => {
        const data = await response.json();
        if (data.code === 200) {
            // 檢查是否沒有任何圖片
            if (data.body.length <= 0) {
                $('#media-list').html(empty);
                return;
            }

            //html
            const map = data.body.map((value) =>
                `<div class="col-6 col-sm-4 col-md-3 col-lg-2">
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
            media_list = data.body
            $('#media-list').html(map)
            window.dispatchEvent(new Event('load'));
        } else {
            toastr.error(data.Message)
        }
    }).catch((error) => {
        console.log(error)
    });

    /* 選擇模式切換 */
    let select_mod = false;
    $('#switch-mode').click(switch_mode)

    function switch_mode() {
        if (deleting) return;

        select_mod = !select_mod;
        $('#switch-mode').text(select_mod ? Lang.Select_off : Lang.Select_on);

        if (select_mod) {
            $('.media-list').addClass('select-mode');
            $('#del-media').show();
        } else {
            $('.media-list').removeClass('select-mode');
            $('.media-list .media-list-focus.selected').removeClass('selected');
            $('#del-media').hide().children('span').text(0)
            selected_list = [];
        }
    }

    /* 選擇圖片 */
    let selected_list = [];
    $('#media-list').on('click', '.media-list-focus', function () {
        const elm = $(this);
        const id = elm.data('id');

        if (select_mod) {
            /* 選取模式 */
            if (selected_list.includes(id)) {
                //is selected
                elm.removeClass('selected')
                selected_list.splice(selected_list.indexOf(id), 1);
            } else {
                //not selected
                elm.addClass('selected')
                selected_list.push(id);
            }

            $('#del-media > span').text(selected_list.length)
        } else {
            /* 展示模式 */
            const modal = $('#Media-modal')
            const bs_modal = bootstrap.Modal.getOrCreateInstance(modal[0]);

            modal.find('.modal-title > b > span').text(id)
            modal.find('img').attr('src', '/panel/api/media/' + id).attr('alt', 'Media %s'.replace('%s', id))

            const media = media_list.filter((value) => value.id === id)[0]
            const detail = $('#Media-modal-detail');
            detail.find('p:nth-child(1) > span').text(id)
            detail.find('p:nth-child(2) > span').text(media.datetime)
            detail.find('p:nth-child(3) > span').text(media.mime)
            detail.find('p:nth-child(4) > code').text(location.origin + '/panel/api/media/' + id);
            detail.find('a').attr('href', location.origin + '/panel/api/media/' + id);
            detail.find('button').attr('data-id', id);

            bs_modal.show()
        }
    })

    /* 刪除圖片 */
    let deleting = false;
    $('#del-media').click(function () {
        if (!select_mod) return;
        deleting = true;

        /* 封鎖按鈕 */
        const bt = $(this)
        const html = bt.html();
        bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

        fetch('/panel/api/media', {
            method: 'DELETE',
            redirect: 'error',
            body: JSON.stringify(selected_list),
            headers: {
                'Content-Type': 'text/json; charset=UTF-8'
            }
        }).then(async (response) => {
            const data = await response.json();

            if (data.code === 200) {
                toastr.success(data.Message)
            } else if (data.code === 210) {
                toastr.warning(data.Message)
            } else {
                toastr.error(data.Message)
            }

            // 刪除圖片
            if (data.body) {
                data.body.forEach((value) => {
                    $(`[data-id='${value}']`).parent().remove()
                });
            }

            // 取消選取狀態
            deleting = false;
            switch_mode();
        }).catch((error) => {
            console.log(error)
        }).finally(() => {
            bt.html(html).removeAttr('disabled');
        });
    });

    /* 刪除單個 */
    $('#Media-modal-detail > button').click(function () {
        /* 封鎖按鈕 */
        const bt = $(this)
        const html = bt.html();
        bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

        const id = bt.data('id');
        fetch('/panel/api/media/' + id, {
            method: 'DELETE',
            redirect: 'error',
        }).then(async (response) => {
            const data = await response.json();

            if (data.code === 200) {
                toastr.success(data.Message)

                $(`[data-id='${id}']`).parent().remove()
                const bs_modal = bootstrap.Modal.getOrCreateInstance($('#Media-modal')[0]);
                bs_modal.hide()
            } else {
                toastr.error(data.Message)
            }
        }).catch((error) => {
            console.log(error)
        }).finally(() => {
            bt.html(html).removeAttr('disabled');
        });
    })
})