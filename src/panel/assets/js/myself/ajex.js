/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery'], function () {
    $(document).on("click", 'a[href]', function(e) {
        const link = $(this).attr('href');
        if(/^(\/)/.test(link)){
            e.preventDefault();
            AJAX(link,"GET");
        }
    });

    const ajexLoad = (link, putState = true) => {

    }
})