/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

define(['jquery', 'easymde', 'showdown', 'xss', 'media-select', 'media-select.upload'], function (jq, EasyMDE, Showdown, xss, media_select, media_upload) {
    "use strict";

    media_upload.setInputAccept("image/png, image/jpeg, image/gif, image/webp");

    /* Count content length */
    $('#event-summary, #event-precautions, #event-location').on('input focus', function (e) {
        const length = $(this).val().length
        $(this).parent('div').children('span').text(length + "/" + $(this).attr('maxlength'));
    })

    /* HTML filter xss */
    const filterXSS_description = new xss.FilterXSS({
        stripIgnoreTag: true,
        whiteList: {
            h1: [],
            h2: [],
            h3: [],
            h4: [],
            h5: [],
            h6: [],
            a: ["href", 'target'],
            strong: [],
            em: [],
            del: [],
            br: [],
            p: [],
            ul: ['class'],
            ol: [],
            li: [],
            table: [],
            thead: [],
            th: [],
            tbody: [],
            td: [],
            tr: [],
            blockquote: [],
            img: ["src", "alt"],
            hr: []
        }
    });
    const filterXSS_precautions = new xss.FilterXSS({
        stripIgnoreTag: true,
        whiteList: {
            strong: [],
            em: [],
            del: [],
            br: [],
            p: [],
            ul: ['class'],
            ol: [],
            li: []
        }
    });

    /* markdown converter */
    const MD_converter = new Showdown.Converter({
        excludeTrailingPunctuationFromURLs: true,
        noHeaderId: true,
        strikethrough: true,
        tables: true,
        smoothLivePreview: true,
        extensions: [{
            type: 'output',
            regex: new RegExp(`<ul(.*)>`, 'g'),
            replace: `<ul class="disc" $1>`
        }, {
            type: 'output',
            regex: new RegExp(`<a(.*)>`, 'g'),
            replace: `<a target="_blank" $1>`
        }]
    });

    /**
     * markdown editor options
     * @param {XSS.FilterXSS} filterXSS
     * @param {JQuery<HTMLElement>} jq_elm
     * @param {Showdown.Converter} converter
     * @return EasyMDE.Options
     */
    const editor_options = (filterXSS, jq_elm, converter) => {
        return {
            forceSync: true,
            autoDownloadFontAwesome: false,
            spellChecker: false,
            unorderedListStyle: "+",
            maxHeight: "500px",
            uploadImage: true,
            sideBySideFullscreen: false,
            tabSize: 4,
            styleSelectedText: false,
            toolbarButtonClassPrefix: "mde",
            previewRender: (text) => {
                return filterXSS.process(converter.makeHtml(text))
            },
            toolbar: [
                "bold", "italic", "heading", "strikethrough", "|",
                "quote", "unordered-list", "ordered-list", "table", "|",
                "horizontal-rule", "link", {
                    name: "Image",
                    action: function (editor) {
                        if (!editor.codemirror || editor.isPreviewActive()) return;

                        const doc = editor.codemirror.getDoc();
                        media_select.select_media((ids) => {
                            if (doc.somethingSelected()) {
                                const text = doc.getSelection();
                                doc.replaceSelection('![' + text + '](/panel/api/media/' + ids + ')', 'around')
                                editor.codemirror.focus()
                            } else {
                                const cur = doc.getCursor()
                                const end_cur = doc.getCursor('end')
                                const text = '![](/panel/api/media/' + ids + ')'
                                doc.replaceRange(text, cur)
                                editor.codemirror.focus()
                            }
                        }, 1)
                    },
                    className: "fa-solid fa-image",
                    title: "Add Image"
                }, "|",
                "preview", "side-by-side", "fullscreen", "guide"],
            shortcuts: {
                "Image": "Ctrl-Alt-I",
            },
            blockStyles: {
                italic: "_"
            },
            renderingConfig: {
                sanitizerFunction: (renderedHTML) => {
                    return filterXSS.process(renderedHTML)
                }
            },
            status: [
                "autosave", "lines", "words", "cursor", {
                    className: "count",
                    defaultValue: (el) => {
                        el.innerHTML = "0/" + jq_elm.attr('maxlength');
                    },
                    onUpdate: (el) => {
                        const length = jq_elm.val().length, maxlength = jq_elm.attr('maxlength');
                        el.innerHTML = length + "/" + maxlength;
                        if (length > maxlength) alert(`字數已超出了${maxlength}字限制! 如你繼續輸入, 內容有機會被截斷`)
                    }
                }]
        }
    }

    /* description markdown editor */
    const jq_description = $('#event-description')
    const md_description = new EasyMDE({
        ...editor_options(filterXSS_description, jq_description, MD_converter),
        element: jq_description[0],
        autosave: {
            enabled: true,
            uniqueId: "event-description",
        },
        placeholder: "活動描述"
    })
    md_description.codemirror.setValue($('#event-description-data').val())

    /* precautions markdown editor */
    const jq_precautions = $('#event-precautions')
    const md_precautions = new EasyMDE({
        ...editor_options(filterXSS_precautions, jq_precautions, MD_converter),
        element: jq_precautions[0],
        autosave: {
            enabled: true,
            uniqueId: "event-precautions"
        },
        toolbar: ["bold", "italic", "heading", "strikethrough", "|",
            "unordered-list", "ordered-list", "|", "preview", "side-by-side", "fullscreen", "guide"],
        placeholder: "活動注意事項",
        maxHeight: "100px",
    })
    md_precautions.codemirror.setValue($('#event-precautions-data').val())

    /* Image select */
    let img_items = [];
    const dropZone = $('#event-image-list')
    $('#image-select').click(() => {
        media_select.select_media((images) => {
            const tmp = images.map((id) =>
                $(`<div class="col-6 col-sm-4 col-md-3 col-lg-2 item">
                    <div class="ratio ratio-1x1 media-list-focus">
                        <div class="overflow-hidden">
                            <div class="media-list-center">
                                <img src="/panel/api/media/${id}" draggable="true" data-image-id="${id}" alt="${id}" />
                            </div>
                        </div>
                    </div>
                </div>`));
            dropZone.html(tmp);

            img_items = tmp.map((value) => value.find('img')[0])
        }, 5, /(image\/png)|(image\/jpeg)|(image\/gif)|(image\/webp)/)
    })

    /* Image drag drop */
    //Thx: https://medium.com/@joie.software/exploring-the-html-drag-and-drop-api-using-plain-javascript-part-1-42f603cce90d
    let adjacentItem;
    let prevAdjacentItem;
    let selectedItem;

    //dragstart
    dropZone.on('dragstart', 'img', function (e) {
        selectedItem = e.target;
        $(e.target).parents('.item').css('opacity', 0.4)
        e.originalEvent.dataTransfer.effectAllowed = 'move';
    })
    //dragover
    dropZone.on('dragover', function (e) {
        e.preventDefault();
        e.originalEvent.dataTransfer.dropEffect = "move"

        if(img_items.includes(e.target)){
            adjacentItem = e.target;

            if(adjacentItem !== prevAdjacentItem && prevAdjacentItem !== undefined){
                $(prevAdjacentItem).parents('.item').css('marginLeft', '0')
            }

            if(adjacentItem !== null && adjacentItem !== selectedItem && (img_items.includes(adjacentItem))){
                const item = $(adjacentItem).parents('.item')
                item.css('transition', 'all 1s ease').css('marginLeft', item.outerWidth()+'px')
            }

            prevAdjacentItem = adjacentItem;
        }
    })
    //drop
    dropZone.on('drop', function (e) {
        e.preventDefault();

        if (adjacentItem !== null && img_items.includes(adjacentItem) || img_items.includes($(adjacentItem).parents('[draggable]'))) {
            console.log(dropZone.find(adjacentItem).parents('.item'))
            dropZone.find(adjacentItem).parents('.item').before($(selectedItem).parents('.item').css('opacity', 1))
            $(adjacentItem).parents('.item').css('transition', 'none').css('marginLeft', '0')
        }
    })
    //dragend
    dropZone.on('dragend', function (e) {
        $(selectedItem).parents('.item').css('opacity', 1)
        $(adjacentItem).parents('.item').css('marginLeft', '0')
    })
})