/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'media-select', 'myself/datepicker'], function (jq, media_select) {

    $('#select').click(function () {
        media_select.select_media((ids) => {
            ids.forEach((id)=>{
                $('#show').append(`<img src="/panel/api/media/${id}" alt="${id}"/>`)
            })
        });
    })

    $('#abc')[0].disableDate = ["01-11-2023", "01-13-2023"]
    $('#ccc')[0].disableDate = ["01-11-2023", "01-13-2023"]
    $('#ccc')[0].drawDatePicker()
})