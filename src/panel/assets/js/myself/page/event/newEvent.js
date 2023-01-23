/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

define(['jquery', 'easymde', 'showdown', 'xss', 'media-select', 'media-select.upload', 'mapbox-gl', '@mapbox/mapbox-gl-geocoder', '@mapbox/mapbox-sdk', 'moment', 'myself/datepicker', 'timepicker'], function (jq, EasyMDE, Showdown, xss, media_select, media_upload, mapboxgl, MapboxGeocoder, mapboxSdk, moment, datepicker) {
    "use strict";
    mapboxgl.accessToken = 'pk.eyJ1IjoiY29jb21pbmUiLCJhIjoiY2xhanp1Ymh1MGlhejNvczJpbHhpdjV5dSJ9.oGNqsDB7ybqV5q6T961bqA';
    media_upload.setInputAccept("image/png, image/jpeg, image/gif, image/webp");

    /* Count content length */
    $('#event-summary, #event-precautions, #event-location').on('input focus', function () {
        const length = $(this).val().length
        $(this).parent('div').children('span').text(length + "/" + $(this).attr('maxlength'));
    })

    /* jquery timepicker */
    $("input[name^='event-schedule-time'], #event-post-time").timepicker({
        show2400: true,
        className: "dropdown-menu",
        closeOnScroll: true,
        timeFormat: "H:i"
    })

    /* ============活動資料============== */
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

    /* =============活動圖片============== */
    /* Image select */
    let img_items = [];
    const jq_dropZone = $('#event-image-list');
    const jq_image = $('#event-image');
    $('#event-image-select').click(() => {
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

        if (img_items.includes(e.target)) {
            adjacentItem = e.target;

            if (adjacentItem !== prevAdjacentItem && prevAdjacentItem !== undefined) {
                $(prevAdjacentItem).parents('.item').css('marginLeft', '0')
            }

            if (adjacentItem !== null && adjacentItem !== selectedItem && (img_items.includes(adjacentItem))) {
                const item = $(adjacentItem).parents('.item')
                item.css('transition', 'all 1s ease').css('marginLeft', item.outerWidth() + 'px')
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

    /* =================活動地址================= */
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
    const map_track = new mapboxgl.GeolocateControl({showUserLocation: false, fitBoundsOptions: {zoom: 15}});
    map.addControl(map_track);
    map.addControl(new mapboxgl.ScaleControl());
    map.addControl(new mapboxgl.NavigationControl());

    /* Update map marker */
    map_geo.on('result', ({result}) => {
        map_marker.setLngLat(result.center);
        jq_location.val(result.place_name);
        jq_longitude.val(result.center[0].toFixed(4));
        jq_latitude.val(result.center[1].toFixed(4));
    })
    map_marker.on('dragend', ({target}) => {
        jq_longitude.val(target.getLngLat().lng.toFixed(4));
        jq_latitude.val(target.getLngLat().lat.toFixed(4));
        getPoi(target.getLngLat().toArray()).then((poi) => {
            if (poi) jq_location.val(poi.place_name)
        })
    })
    map_track.on('geolocate', ({coords}) => {
        map_marker.setLngLat([coords.longitude, coords.latitude])
        jq_longitude.val(coords.longitude.toFixed(4));
        jq_latitude.val(coords.latitude.toFixed(4));
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

    const jq_plan = $('#event-form-plan') //計劃
    const jq_schedule = $('#event-form-schedule') //時段
    const plan = [];

    /* ============計劃========== */
    /* 增加計劃 */
    $('#event-plan-add').click(function () {
        const id = Math.floor(Math.random() * 9999);
        jq_plan.append(
            `<div class="col-12 mb-2 row g-1 border border-1 rounded p-2" data-plan="${id}">
                <h5 class="col-12 text-muted"># ${id}</h5>
                <div class="col-12 col-lg-7">
                    <label for="event-plan-name-${id}" class="form-label">計畫名稱</label>
                    <input type="text" class="form-control form-rounded" name="event-plan-name-${id}" id="event-plan-name-${id}" maxlength="20" required>
                    <div class="invalid-feedback">這裏不能留空哦~~</div>
                </div>
                <div class="w-100"></div>
                <div class="col-6 col-md-2">
                    <label for="event-plan-max-${id}" class="form-label">計劃最大人數</label>
                    <input type="number" class="form-control form-rounded" name="event-plan-max-${id}" id="event-plan-max-${id}" min="1" required>
                    <div class="invalid-feedback">這裏不能留空哦~~</div>
                </div>
                <div class="col-6 col-md-3">
                    <label for="event-plan-max-each-${id}" class="form-label">每個預約最大人數</label>
                    <input type="number" class="form-control form-rounded" name="event-plan-max-each-${id}" id="event-plan-max-each-${id}" min="1" required>
                    <div class="invalid-feedback">這裏不能留空哦~~</div>
                </div>
                <div class="col-6 col-md-2">
                    <label for="event-plan-price-${id}" class="form-label">計劃金額</label>
                    <div class="input-group">
                        <span class="input-group-text form-rounded">$</span>
                        <input type="number" class="form-control form-rounded" name="event-plan-price-${id}" id="event-plan-price-${id}" min="0" required>
                    </div>
                    <div class="invalid-feedback">這裏不能留空哦~~</div>
                </div>
                <div class="col text-end align-self-end align-self-lg-auto" style="margin-top: -10px">
                    <button type="button" class="btn-close" aria-label="Close"></button>
                </div>
            </div>`)
    })

    /* 刪除計劃 */
    jq_plan.on('click', 'button', function () {
        const plan_id = $(this).parents('[data-plan]').data('plan');
        const plan_select = jq_schedule.find("[name^='event-schedule-plan']")
        $(this).parents('[data-plan]').remove()

        // 時段計劃
        const index = plan.findIndex((value) => value.plan_id === plan_id)
        plan.splice(index, 1);
        plan_select.find(`[value='${plan_id}']`).remove()
        console.log(plan)
    })

    /* 計劃轉移 */
    jq_plan.on('blur', `[name^='event-plan-name']`, function () {
        const plan_name = $(this).val(), plan_id = $(this).parents('[data-plan]').data('plan');
        const plan_select = jq_schedule.find("[name^='event-schedule-plan']")

        // 時段計劃
        const tmp = plan.filter((value) => value.plan_id === plan_id)
        if (tmp.length > 0) {
            plan_select.find(`[value='${plan_id}']`).text(plan_id + ' - ' + plan_name); //存在
        } else {
            plan_select.append(`<option value="${plan_id}">${plan_id} - ${plan_name}</option>`); //不存在
            plan.push({plan_id, plan_name});
        }
        console.log(plan)
    })

    /* ============活動時段============== */
    /* 增加時段 */
    $('#event-schedule-add').click(function () {
        const id = Math.floor(Math.random() * 9999);
        const min = moment().format('YYYY-MM-DD')
        const tmp = $(
            `<div class="col-12 mb-2 row g-1 border border-1 rounded p-2 align-items-center" data-schedule="${id}">
                      <div class="col-12 col-sm-6 col-md-3">
                          <div class="date-picker form-floating">
                              <input type="date" class="form-control form-rounded date-picker-toggle" name="event-schedule-start-${id}" id="event-schedule-start-${id}" required min="${min}">
                              <label for="event-schedule-start-${id}">開始日期</label>
                              <div class="invalid-feedback">這裏不能留空哦~~</div>
                          </div>
                      </div>
                      <div class="col-12 col-sm-6 col-md-3 event-schedule-end" style="display: none;">
                          <div class="date-picker form-floating">
                              <input type="date" class="form-control form-rounded date-picker-toggle" name="event-schedule-end-${id}" id="event-schedule-end-${id}" required>
                              <label for="event-schedule-end-${id}">結束日期</label>
                              <div class="invalid-feedback">這裏不能留空哦~~</div>
                          </div>
                      </div>
                      <div class="col col-md-auto">
                          <div class="form-check form-switch float-end">
                              <input class="form-check-input" type="checkbox" role="switch" name="event-schedule-type-${id}" id="event-schedule-type-${id}">
                              <label class="form-check-label" for="event-schedule-type-${id}">重複</label>
                          </div>
                      </div>
                      <div class="w-100"></div>
                      <div class="col-12 col-sm-6 col-md-3">
                          <div class="form-floating">
                              <input type="text" class="form-control form-rounded" name="event-schedule-time-start-${id}" id="event-schedule-time-start-${id}" required>
                              <label for="event-schedule-time-start-${id}">開始時間</label>
                              <div class="invalid-feedback">這裏不能留空哦~~</div>
                          </div>
                      </div>
                      <div class="col-12 col-sm-6 col-md-3">
                          <div class="form-floating">
                              <input type="text" class="form-control form-rounded" name="event-schedule-time-end-${id}" id="event-schedule-time-end-${id}" required>
                              <label for="event-schedule-time-end-${id}">結束時間</label>
                              <div class="invalid-feedback">這裏不能留空哦~~</div>
                          </div>
                      </div>
                      <div class="col-12 col-md event-schedule-week" style="display: none;">
                          <div class="form-check form-check-inline">
                              <input class="form-check-input" type="checkbox" name="event-schedule-week-${id}" id="event-schedule-week-0-${id}" value="0">
                              <label class="form-check-label" for="event-schedule-week-0-${id}">週日</label>
                          </div>
                          <div class="form-check form-check-inline">
                              <input class="form-check-input" type="checkbox" name="event-schedule-week-${id}" id="event-schedule-week-1-${id}" value="1">
                              <label class="form-check-label" for="event-schedule-week-1-${id}">週一</label>
                          </div>
                          <div class="form-check form-check-inline">
                              <input class="form-check-input" type="checkbox" name="event-schedule-week-${id}" id="event-schedule-week-2-${id}" value="2">
                              <label class="form-check-label" for="event-schedule-week-2-${id}">週二</label>
                          </div>
                          <div class="form-check form-check-inline">
                              <input class="form-check-input" type="checkbox" name="event-schedule-week-${id}" id="event-schedule-week-3-${id}" value="3">
                              <label class="form-check-label" for="event-schedule-week-3-${id}">週三</label>
                          </div>
                          <div class="form-check form-check-inline">
                              <input class="form-check-input" type="checkbox" name="event-schedule-week-${id}" id="event-schedule-week-4-${id}" value="4">
                              <label class="form-check-label" for="event-schedule-week-4-${id}">週四</label>
                          </div>
                          <div class="form-check form-check-inline">
                              <input class="form-check-input" type="checkbox" name="event-schedule-week-${id}" id="event-schedule-week-5-${id}" value="5">
                              <label class="form-check-label" for="event-schedule-week-5-${id}">週五</label>
                          </div>
                          <div class="form-check form-check-inline">
                              <input class="form-check-input" type="checkbox" name="event-schedule-week-1" id="event-schedule-week-6-${id}" value="6">
                              <label class="form-check-label" for="event-schedule-week-6-${id}">週六</label>
                          </div>
                      </div>
                      <div class="w-100"></div>
                      <div class="col-12 col-md-6">
                          <select class="form-select form-rounded" name="event-schedule-plan-${id}" id="event-schedule-plan-${id}">
                              <option selected disabled value="">選擇計劃</option>
                              ${plan.map((value) => `<option value="${value.plan_id}">${value.plan_id} - ${value.plan_name}</option>`)}
                          </select>
                          <div class="invalid-feedback">這裏不能留空哦~~</div>
                      </div>
                      <div class="col text-end align-self-end">
                          <button type="button" class="btn-close" aria-label="Close"></button>
                      </div>
                  </div>`)

        tmp.appendTo(jq_schedule)
        datepicker.addPicker(tmp.find('.date-picker')) //add date-picker
        tmp.find("[name^='event-schedule-time']").timepicker({
            show2400: true,
            className: "dropdown-menu",
            closeOnScroll: true,
            timeFormat: "H:i"
        }) //add timepicker
    })

    /* 刪除時段 */
    jq_schedule.on('click', 'button', function () {
        $(this).parents('[data-schedule]').remove()
    })

    /* 切換時段類型 */
    jq_schedule.on('change', "[name^='event-schedule-type']", function () {
        const parent = $(this).parents('[data-schedule]');
        const elm = parent.find('.event-schedule-end, .event-schedule-week');
        if (this.checked) {
            //重複
            elm.find("[name^='event-schedule-end'], [name^='event-schedule-week']").prop('disabled', false); //Enable 結束日期

            //auto set select week
            const sel_date = moment(parent.find("[name^='event-schedule-start']").val());
            elm.find(`[value='${sel_date.weekday()}']`).prop('checked', true)

            elm.show()
        } else {
            //單日
            elm.find("[name^='event-schedule-end'], [name^='event-schedule-week']").prop('disabled', true); //Enable 結束日期
            elm.hide()
        }
    })

    /* ==============活動狀態=============== */
    /* set post time to today */
    $('#event-post-time').timepicker('setTime', new Date());

    /* 儲存草稿 */
    $('#event-daft').click(function () {
        //todo
    })

    /* 發佈 */
    $('#event-post').click(function () {
        //todo
    })

    /* 移到回收桶 */
    $('#event-recycle').click(function () {
        //todo
    })

    /* =========== */
})