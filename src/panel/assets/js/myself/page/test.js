/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'media-select'], function (jq, media_select) {
    $('#select').click(function () {
        media_select.select_media(2, /(image\/.*)/, (ids) => {
            ids.forEach((id)=>{
                $('#show').append(`<img src="/panel/api/media/${id}" alt="${id}"/>`)
            })
        });
    })
})