/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define([ 'jquery', 'toastr', 'media-select', 'media-select.upload' ], function (jq, toastr, media_select, media_select_upload){
    const jq_rate_start = $(`[data-rate]`);
    media_select_upload.setInputAccept("image/png, image/jpeg, image/gif, image/webp")

    /* Count content length */
    $('#review-comment').on('input focus', function (){
        const length = $(this).val().length;
        $(this).parent('div').children('span').text(length + "/" + $(this).attr('maxlength'));
    });

    /* rate */
    jq_rate_start.click(function (){
        const rate = $(this).data('rate');

        // set star
        jq_rate_start.each(function (index, elm){
            if(index < rate){
                $(elm).removeClass('text-muted fa-regular').addClass('text-warning fa-solid');
            }else{
                $(elm).addClass('text-muted fa-regular').removeClass('text-warning fa-solid');
            }
        });
        $('#review-rate').val(rate); // set value
    });

    /* submit */
    $('#review').submit(function (e){
        const from = $(this);
        if (!e.isDefaultPrevented() && this.checkValidity()){
            e.preventDefault();
            e.stopPropagation();
            const data = from.serializeObject();

            /* 封鎖按鈕 */
            const bt = from.children('.form-submit');
            const html = bt.html();
            bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

            /* send */
            fetch(location.pathname, {
                method: 'POST',
                redirect: 'error',
                headers: {
                    'Content-Type': 'application/json; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            }).then(async (response) => {
                const json = await response.json();
                if (response.ok && json.code === 200){
                    toastr.success(json.Message, json.Title);
                    ajexLoad(location.pathname);
                }else{
                    toastr.error(json.Message, json.Title ?? globalLang.Error);
                }
            }).finally(() => {
                bt.html(html).removeAttr('disabled');
            }).catch((error) => {
                console.log(error);
            });
        }
    });

    /* 選擇圖片 */
    $('#review-img-sel').click(function (){
        media_select.select_media((medias) => {
            $('#review-img-preview').empty();

            medias.forEach(({ id, name }) => {
                const img = $(`
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 item">
                        <div class="ratio ratio-1x1">
                            <img src="/panel/api/media/${id}" alt="${name}" class="rounded">
                        </div>
                    </div>`);
                $('#review-img-preview').append(img);
            });

            $('#review-img').val(medias.map(({id}) => id).join(','));
        }, 5, /(image\/png)|(image\/jpeg)|(image\/gif)|(image\/webp)/)
    })
});
