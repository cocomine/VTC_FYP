/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

define(['jquery', 'easymde', 'showdown', 'xss', 'media-select', 'media-select.upload', 'mapbox-gl', '@mapbox/mapbox-gl-geocoder', '@mapbox/mapbox-sdk'], function (jq, EasyMDE, Showdown, xss, media_select, media_upload, mapboxgl, MapboxGeocoder, mapboxSdk) {
    "use strict";
    mapboxgl.accessToken = 'pk.eyJ1IjoiY29jb21pbmUiLCJhIjoiY2xhanp1Ymh1MGlhejNvczJpbHhpdjV5dSJ9.oGNqsDB7ybqV5q6T961bqA';
    media_upload.setInputAccept("image/png, image/jpeg, image/gif, image/webp");

    /* Count content length */
    $('#event-summary, #event-precautions, #event-location').on('input focus', function () {
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
            maxHeight: "30rem",
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
    new EasyMDE({
        ...editor_options(filterXSS_description, jq_description, MD_converter),
        element: jq_description[0],
        autosave: {enabled: false},
        placeholder: "活動描述",
        initialValue: jq_description.val()
    })

    /* precautions markdown editor */
    const jq_precautions = $('#event-precautions')
    new EasyMDE({
        ...editor_options(filterXSS_precautions, jq_precautions, MD_converter),
        element: jq_precautions[0],
        autosave: {enabled: false},
        toolbar: ["bold", "italic", "heading", "strikethrough", "|",
            "unordered-list", "ordered-list", "|", "preview", "side-by-side", "fullscreen", "guide"],
        placeholder: "活動注意事項",
        maxHeight: "5rem",
        initialValue: jq_precautions.val()
    })

    /* Image select */
    let img_items = [];
    const jq_dropZone = $('#event-image-list');
    const jq_image = $('#event-image');
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
            jq_dropZone.html(tmp);

            //list up
            const tmp_img = tmp.map((value) => value.find('img'))
            img_items = tmp_img.map((value) => value[0])
            jq_image.val(JSON.stringify(tmp_img.map((value) => value.data('image-id'))))
        }, 5, /(image\/png)|(image\/jpeg)|(image\/gif)|(image\/webp)/)
    })

    /* Image drag drop */
    //Thx & ref: https://medium.com/@joie.software/exploring-the-html-drag-and-drop-api-using-plain-javascript-part-1-42f603cce90d
    let adjacentItem;
    let prevAdjacentItem;
    let selectedItem;

    //dragstart
    jq_dropZone.on('dragstart', 'img', function (e) {
        selectedItem = e.target;
        $(e.target).parents('.item').css('opacity', 0.4)
        e.originalEvent.dataTransfer.effectAllowed = 'move';
    })
    //dragover
    jq_dropZone.on('dragover', function (e) {
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
    jq_dropZone.on('drop', function (e) {
        e.preventDefault();

        if (adjacentItem !== null && img_items.includes(adjacentItem) || img_items.includes($(adjacentItem).parents('[draggable]'))) {
            console.log(jq_dropZone.find(adjacentItem).parents('.item'))
            jq_dropZone.find(adjacentItem).parents('.item').before($(selectedItem).parents('.item').css('opacity', 1))
            $(adjacentItem).parents('.item').css('transition', 'none').css('marginLeft', '0')

            //list up
            jq_image.val(JSON.stringify(
                jq_dropZone.find('[data-image-id]').map(
                    (index, elm) => elm.dataset.imageId).toArray()
            ))
        }
    })
    //dragend
    jq_dropZone.on('dragend', function () {
        $(selectedItem).parents('.item').css('opacity', 1)
        $(adjacentItem).parents('.item').css('marginLeft', '0')
    })

    /* Map */
    /* Load map */
    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v12',
        zoom: 1,
        center: [0, 0]
    });
    const map_client = mapboxSdk({accessToken: mapboxgl.accessToken});
    const jq_location = $('#event-location'), jq_longitude = $('#event-longitude'), jq_latitude = $('#event-latitude');

    /* Enable stars with reduced atmosphere */
    map.on('style.load', () => {
        map.setFog({'horizon-blend': 0.05});
    });

    /* Add Map Control */
    const map_marker = new mapboxgl.Marker({
        color: 'red',
        draggable: true
    }).setLngLat([0, 0]).addTo(map);
    const map_geo = new MapboxGeocoder({
        accessToken: mapboxgl.accessToken,
        marker: false,
        mapboxgl: mapboxgl,
        proximity: "ip",
    });
    map.addControl(map_geo);
    const map_track = new mapboxgl.GeolocateControl({showUserLocation: false, fitBoundsOptions: {zoom:15}});
    map.addControl(map_track);
    map.addControl(new mapboxgl.ScaleControl());
    map.addControl(new mapboxgl.NavigationControl());

    /* Update map marker */
    map_geo.on('result', ({result}) => {
        map_marker.setLngLat(result.center);
        jq_location.val(result.place_name);
        jq_longitude.val(result.center[0]);
        jq_latitude.val(result.center[1]);
    })
    map_marker.on('dragend', ({target}) => {
        jq_longitude.val(target.getLngLat().lng);
        jq_latitude.val(target.getLngLat().lat);
        getPoi(target.getLngLat().toArray()).then((poi) => {
            if (poi) jq_location.val(poi.place_name)
        })
    })
    map_track.on('geolocate', ({coords}) => {
        map_marker.setLngLat([coords.longitude, coords.latitude])
        jq_longitude.val(coords.longitude);
        jq_latitude.val(coords.latitude);
        getPoi([coords.longitude, coords.latitude]).then((poi) => {
            if (poi) jq_location.val(poi.place_name)
        })
    })

    /**
     * get Poi with longitude & latitude
     * @param {number[]} LngLat
     * @returns {Promise<null|Object>}
     */
    async function getPoi(LngLat) {
        const response = await map_client.geocoding.reverseGeocode({
            query: LngLat,
            types: ["poi"]
        }).send()

        return (response || response.body || response.body.features || response.body.features.length)
            ? response.body.features[0] : null;
    }
})