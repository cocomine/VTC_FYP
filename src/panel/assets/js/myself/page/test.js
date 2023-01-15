/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'media-select'], function (jq, media_select) {
    $('#select').click(function () {
        media_select.select_media((ids) => {
            ids.forEach((id)=>{
                $('#show').append(`<img src="/panel/api/media/${id}" alt="${id}"/>`)
            })
        });
    })
})