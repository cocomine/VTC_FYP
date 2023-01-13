/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

define(['jquery', 'simplemde', 'showdown', 'xss'], function (jq, SimpleMDE, showdown, xss) {
    "use strict";

    /* Count content length */
    $('#event-summary, #event-precautions').on('input focus', function (e) {
        const length = $(this).val().length
        $(this).parent('div').children('span').text(length + "/" + $(this).attr('maxlength'));
    })

    /* markdown editor */
    const jq_description = $('#event-description')
    const md_description = new SimpleMDE({
        element: jq_description[0],
        forceSync: true,
        autoDownloadFontAwesome: false,
        spellChecker: false,
        promptURLs: true,
        toolbar: [
            "bold", "italic", "heading", "strikethrough", "|",
            "heading-1", "heading-2", "heading-3", "|",
            "quote", "unordered-list", "ordered-list", "table", "|",
            "horizontal-rule", "link", {
                name: "Image",
                action: function (editor) {

                },
                className: "fa-solid fa-image",
                title: "Add Image"
            }, "|",
            "preview", "side-by-side", "fullscreen", "guide"],
        autosave: {
            enabled: true,
            delay: 20000,
            uniqueId: "event-description"
        },
        insertTexts: {
            image: ["![", "](//panel/api/media/)"],
        },
        previewRender: function (text) {
            const c = new showdown.Converter();
            return xss(c.makeHtml(text));
        },
        status: [
            "autosave", "lines", "words", "cursor", {
                className: "count",
                defaultValue: (el) => {
                    el.innerHTML = "0/" + jq_description.attr('maxlength');
                },
                onUpdate: (el) => {
                    const length = jq_description.val().length, maxlength = jq_description.attr('maxlength');
                    el.innerHTML = length + "/" + maxlength;
                    if(length > maxlength) alert(`字數已超出了${maxlength}字限制! 如你繼續輸入, 內容有機會被截斷`)
                }
            }]
    })
})