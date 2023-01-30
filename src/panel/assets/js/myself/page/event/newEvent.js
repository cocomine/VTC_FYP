/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

define([ 'jquery', 'easymde', 'showdown', 'xss', 'media-select', 'media-select.upload', 'mapbox-gl', '@mapbox/mapbox-gl-geocoder', '@mapbox/mapbox-sdk', 'moment', 'myself/datepicker', 'jquery.crs.min', 'timepicker' ],
    function (jq, EasyMDE, Showdown, xss, media_select, media_upload, mapboxgl, MapboxGeocoder, mapboxSdk, moment, datepicker, crs){
        "use strict";
        mapboxgl.accessToken = 'pk.eyJ1IjoiY29jb21pbmUiLCJhIjoiY2xhanp1Ymh1MGlhejNvczJpbHhpdjV5dSJ9.oGNqsDB7ybqV5q6T961bqA';
        media_upload.setInputAccept("image/png, image/jpeg, image/gif, image/webp");
        crs.init();
        const support_country = [ 'hk', 'mo', 'tw', 'cn' ]

        /* Count content length */
        $('#event-summary, #event-precautions, #event-location').on('input focus', function (){
            const length = $(this).val().length;
            $(this).parent('div').children('span').text(length + "/" + $(this).attr('maxlength'));
        });

        /* jquery timepicker */
        $("input[name^='event-schedule-time'], #event-post-time").timepicker({
            show2400: true,
            className: "dropdown-menu",
            closeOnScroll: true,
            timeFormat: "H:i",
        });

        /* set time to today */
        $('#event-post-time').timepicker('setTime', new Date());
        $('#event-schedule-time-start-1').timepicker('setTime', new Date());
        $('#event-schedule-time-end-1').timepicker('setTime', moment().add(30, 'minute').toDate());

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
                a: [ "href", 'target' ],
                strong: [],
                em: [],
                del: [],
                br: [],
                p: [],
                ul: [ 'class' ],
                ol: [],
                li: [],
                table: [],
                thead: [],
                th: [],
                tbody: [],
                td: [],
                tr: [],
                blockquote: [],
                img: [ "src", "alt" ],
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
                ul: [ 'class' ],
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
            extensions: [ {
                type: 'output',
                regex: new RegExp(`<ul(.*)>`, 'g'),
                replace: `<ul class="disc" $1>`
            }, {
                type: 'output',
                regex: new RegExp(`<a(.*)>`, 'g'),
                replace: `<a target="_blank" $1>`
            } ]
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
                    return filterXSS.process(converter.makeHtml(text));
                },
                toolbar: [
                    "bold", "italic", "heading", "strikethrough", "|",
                    "quote", "unordered-list", "ordered-list", "table", "|",
                    "horizontal-rule", "link", {
                        name: "Image",
                        action: function (editor){
                            if (!editor.codemirror || editor.isPreviewActive()) return;

                            const doc = editor.codemirror.getDoc();
                            media_select.select_media((ids) => {
                                if (doc.somethingSelected()){
                                    const text = doc.getSelection();
                                    doc.replaceSelection('![' + text + '](/panel/api/media/' + ids + ')', 'around');
                                    editor.codemirror.focus();
                                }else{
                                    const cur = doc.getCursor();
                                    const text = '![](/panel/api/media/' + ids + ')';
                                    doc.replaceRange(text, cur);
                                    editor.codemirror.focus();
                                }
                            }, 1);
                        },
                        className: "fa-solid fa-image",
                        title: "Add Image"
                    }, "|",
                    "preview", "side-by-side", "fullscreen", "guide" ],
                shortcuts: {
                    "Image": "Ctrl-Alt-I",
                },
                blockStyles: {
                    italic: "_"
                },
                renderingConfig: {
                    sanitizerFunction: (renderedHTML) => {
                        return filterXSS.process(renderedHTML);
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
                            if (length > maxlength) alert(`字數已超出了${maxlength}字限制! 如你繼續輸入, 內容有機會被截斷`);
                        }
                    } ]
            };
        };

        /* description markdown editor */
        const jq_description = $('#event-description');
        new EasyMDE({
            ...editor_options(filterXSS_description, jq_description, MD_converter),
            element: jq_description[0],
            autosave: { enabled: false },
            placeholder: "活動描述",
            initialValue: jq_description.val()
        });

        /* precautions markdown editor */
        const jq_precautions = $('#event-precautions');
        new EasyMDE({
            ...editor_options(filterXSS_precautions, jq_precautions, MD_converter),
            element: jq_precautions[0],
            autosave: { enabled: false },
            toolbar: [ "bold", "italic", "heading", "strikethrough", "|",
                "unordered-list", "ordered-list", "|", "preview", "side-by-side", "fullscreen", "guide" ],
            placeholder: "活動注意事項",
            maxHeight: "5rem",
            initialValue: jq_precautions.val()
        });

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
                const tmp_img = tmp.map((value) => value.find('img'));
                img_items = tmp_img.map((value) => value[0]);
                jq_image.val((tmp_img.map((value) => value.data('image-id'))).join(','));
            }, 5, /(image\/png)|(image\/jpeg)|(image\/gif)|(image\/webp)/);
        });

        /* Image drag drop */
        //Thx & ref: https://medium.com/@joie.software/exploring-the-html-drag-and-drop-api-using-plain-javascript-part-1-42f603cce90d
        let adjacentItem;
        let prevAdjacentItem;
        let selectedItem;

        //dragstart
        jq_dropZone.on('dragstart', 'img', function (e){
            selectedItem = e.target;
            $(e.target).parents('.item').css('opacity', 0.4);
            e.originalEvent.dataTransfer.effectAllowed = 'move';
        });
        //dragover
        jq_dropZone.on('dragover', function (e){
            e.preventDefault();
            e.originalEvent.dataTransfer.dropEffect = "move";

            if (img_items.includes(e.target)){
                adjacentItem = e.target;

                if (adjacentItem !== prevAdjacentItem && prevAdjacentItem !== undefined){
                    $(prevAdjacentItem).parents('.item').css('marginLeft', '0');
                }

                if (adjacentItem !== null && adjacentItem !== selectedItem && (img_items.includes(adjacentItem))){
                    const item = $(adjacentItem).parents('.item');
                    item.css('transition', 'all 1s ease').css('marginLeft', item.outerWidth() + 'px');
                }

                prevAdjacentItem = adjacentItem;
            }
        });
        //drop
        jq_dropZone.on('drop', function (e){
            e.preventDefault();

            if (adjacentItem !== null && img_items.includes(adjacentItem) || img_items.includes($(adjacentItem).parents('[draggable]'))){
                jq_dropZone.find(adjacentItem).parents('.item').before($(selectedItem).parents('.item').css('opacity', 1));
                $(adjacentItem).parents('.item').css('transition', 'none').css('marginLeft', '0');

                //list up
                jq_image.val((
                    jq_dropZone.find('[data-image-id]').map(
                        (index, elm) => elm.dataset.imageId).toArray()
                ).join(','));
            }
        });
        //dragend
        jq_dropZone.on('dragend', function (){
            $(selectedItem).parents('.item').css('opacity', 1);
            $(adjacentItem).parents('.item').css('marginLeft', '0');
        });

        /* =================活動地址================= */
        /* Load map */
        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v12',
            zoom: 1,
            center: [ 0, 0 ]
        });
        const map_client = mapboxSdk({ accessToken: mapboxgl.accessToken });
        const jq_location = $('#event-location'), jq_longitude = $('#event-longitude'),
            jq_latitude = $('#event-latitude');

        /* Enable stars with reduced atmosphere */
        map.on('style.load', () => {
            map.setFog({ 'horizon-blend': 0.05 });
        });

        /* Add Map Control */
        const map_marker = new mapboxgl.Marker({
            color: 'red',
            draggable: true
        }).setLngLat([ 0, 0 ]).addTo(map);
        const map_geo = new MapboxGeocoder({
            accessToken: mapboxgl.accessToken,
            marker: false,
            mapboxgl: mapboxgl,
            proximity: "ip",
        });
        map.addControl(map_geo);
        const map_track = new mapboxgl.GeolocateControl({ showUserLocation: false, fitBoundsOptions: { zoom: 15 } });
        map.addControl(map_track);
        map.addControl(new mapboxgl.ScaleControl());
        map.addControl(new mapboxgl.NavigationControl());

        /* Update map marker */
        map_geo.on('result', ({ result }) => {
            map_marker.setLngLat(result.center);
            jq_longitude.val(result.center[0].toFixed(4));
            jq_latitude.val(result.center[1].toFixed(4));
            getPoi(result.center).then(setLocalValue);
        });
        map_marker.on('dragend', ({ target }) => {
            jq_longitude.val(target.getLngLat().lng.toFixed(4));
            jq_latitude.val(target.getLngLat().lat.toFixed(4));
            getPoi(target.getLngLat().toArray()).then(setLocalValue);
        });
        map_track.on('geolocate', ({ coords }) => {
            map_marker.setLngLat([ coords.longitude, coords.latitude ]);
            jq_longitude.val(coords.longitude.toFixed(4));
            jq_latitude.val(coords.latitude.toFixed(4));
            getPoi([ coords.longitude, coords.latitude ]).then(setLocalValue);
        });

        /**
         * get Poi with longitude & latitude
         * @param {number[]} LngLat
         * @returns {Promise<any[] | any | null>}
         */
        async function getPoi(LngLat){
            const response = await map_client.geocoding.reverseGeocode({
                query: LngLat,
                types: [ "poi", "region", 'country' ]
            }).send();

            return (response || response.body || response.body.features || response.body.features.length)
                ? response.body.features : null;
        }

        /**
         * 設置地區輸入欄
         * @param {any[] | any} poi
         */
        function setLocalValue(poi){
            const country = poi.filter((val) => val.place_type.includes('country'))
            if (country.length > 0 && support_country.includes(country[0].properties.short_code)){
                //set country
                $('#event-country').val(country[0].properties.short_code.toUpperCase())[0].dispatchEvent(new Event('change', { "bubbles": true }))
                jq_location.val(poi[0].place_name.slice(0, 50))[0].dispatchEvent(new Event('input', { "bubbles": true }));

                //set region
                const region = poi.filter((val) => val.place_type.includes('region'))
                if (region.length > 0) $('#event-region').val(region[0].text)

                $('#invalid-feedback').hide()
            }else{
                $('#invalid-feedback').show()
            }
        }

        /* ============計劃========== */
        const jq_plan = $('#event-form-plan'); //計劃
        const jq_schedule = $('#event-form-schedule'); //時段
        const plan = [];

        /* 增加計劃 */
        $('#event-plan-add').click(function (){
            const id = Math.floor(Math.random() * 9999);
            jq_plan.append(
                `<div class="col-12 mb-2 row g-1 border border-1 rounded p-2" data-plan="${id}">
                <input type="text" name="event-plan-id" class="d-none" value="${id}">
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
                    <div class="invalid-feedback">至少需要一位以上~~</div>
                </div>
                <div class="col-6 col-md-3">
                    <label for="event-plan-max-each-${id}" class="form-label">每個預約最大人數</label>
                    <input type="number" class="form-control form-rounded" name="event-plan-max-each-${id}" id="event-plan-max-each-${id}" min="1" required>
                    <div class="invalid-feedback">至少需要一位以上~~</div>
                </div>
                <div class="col-6 col-md-2">
                    <label for="event-plan-price-${id}" class="form-label">計劃金額</label>
                    <div class="input-group has-validation">
                        <span class="input-group-text form-rounded">$</span>
                        <input type="number" class="form-control form-rounded" name="event-plan-price-${id}" id="event-plan-price-${id}" min="0" value="0" required>
                        <div class="invalid-feedback">正數必須約簡至兩位小數</div>
                    </div>
                </div>
                <div class="col text-end align-self-end align-self-lg-auto" style="margin-top: -10px">
                    <button type="button" class="btn-close" aria-label="Close"></button>
                </div>
            </div>`);
        });

        /* 刪除計劃 */
        jq_plan.on('click', 'button', function (){
            const plan_id = $(this).parents('[data-plan]').data('plan');
            const plan_select = jq_schedule.find("[name^='event-schedule-plan']");
            $(this).parents('[data-plan]').remove();

            // 時段計劃
            const index = plan.findIndex((value) => value.plan_id === plan_id);
            if (index >= 0){
                plan.splice(index, 1);
                plan_select.find(`[value='${plan_id}']`).remove();
            }
        });

        /* 計劃轉移 */
        jq_plan.on('blur', `[name^='event-plan-name']`, function (){
            const plan_name = $(this).val(), plan_id = $(this).parents('[data-plan]').data('plan');
            const plan_select = jq_schedule.find("[name^='event-schedule-plan']");
            const index = plan.findIndex((value) => value.plan_id === plan_id);

            // 時段計劃
            if (index >= 0){
                if (plan_name === ""){
                    //if blank
                    plan_select.find(`[value='${plan_id}']`).remove()
                    plan.splice(index, 1);
                }else{
                    plan_select.find(`[value='${plan_id}']`).text(plan_id + ' - ' + plan_name); //存在
                    plan[index] = { plan_id, plan_name }
                }
            }else{
                plan_select.append(`<option value="${plan_id}">${plan_id} - ${plan_name}</option>`); //不存在
                plan.push({ plan_id, plan_name });
            }
        });

        /* ============活動時段============== */
        /* 增加時段 */
        $('#event-schedule-add').click(function (){
            const id = Math.floor(Math.random() * 9999);
            const min = moment().format('YYYY-MM-DD');
            const tmp = $(
                `<div class="col-12 mb-2 row g-1 border border-1 rounded p-2 align-items-center" data-schedule="${id}">
                      <input type="text" name="event-schedule-id" class="d-none" value="${id}">
                      <div class="col-12 col-sm-6 col-md-3">
                          <div class="date-picker form-floating">
                              <input type="date" class="form-control form-rounded date-picker-toggle" name="event-schedule-start-${id}" id="event-schedule-start-${id}" required min="${min}">
                              <label for="event-schedule-start-${id}">開始日期</label>
                              <div class="invalid-feedback">必需要今天之後~~</div>
                          </div>
                      </div>
                      <div class="col-12 col-sm-6 col-md-3 event-schedule-end" style="display: none;">
                          <div class="date-picker form-floating">
                              <input type="date" class="form-control form-rounded date-picker-toggle" name="event-schedule-end-${id}" id="event-schedule-end-${id}" required min="${min}">
                              <label for="event-schedule-end-${id}">結束日期</label>
                              <div class="invalid-feedback">必需要開始日期之後~~</div>
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
                              <input type="text" class="form-control form-rounded" name="event-schedule-time-start-${id}" id="event-schedule-time-start-${id}" required value="${moment().format("HH:mm")}">
                              <label for="event-schedule-time-start-${id}">開始時間</label>
                              <div class="invalid-feedback">這裏不能留空哦~~</div>
                          </div>
                      </div>
                      <div class="col-12 col-sm-6 col-md-3">
                          <div class="form-floating">
                              <input type="text" class="form-control form-rounded" name="event-schedule-time-end-${id}" id="event-schedule-time-end-${id}" required value="${moment().add(30, 'm').format("HH:mm")}">
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
                          <div class="invalid-feedback">至少選取一天</div>
                      </div>
                      <div class="w-100"></div>
                      <div class="col-12 col-md-6">
                          <select class="form-select form-rounded" name="event-schedule-plan-${id}" id="event-schedule-plan-${id}">
                              <option selected value="">選擇計劃</option>
                              ${plan.map((value) => `<option value="${value.plan_id}">${value.plan_id} - ${value.plan_name}</option>`)}
                          </select>
                          <div class="invalid-feedback">這裏必須選擇哦~~</div>
                      </div>
                      <div class="col text-end align-self-end">
                          <button type="button" class="btn-close" aria-label="Close"></button>
                      </div>
                  </div>`);

            tmp.appendTo(jq_schedule);
            datepicker.addPicker(tmp.find('.date-picker')); //add date-picker
            tmp.find("[name^='event-schedule-time']").timepicker({
                show2400: true,
                className: "dropdown-menu",
                closeOnScroll: true,
                timeFormat: "H:i"
            }); //add timepicker
        });

        /* 刪除時段 */
        jq_schedule.on('click', 'button', function (){
            $(this).parents('[data-schedule]').remove();
        });

        /* 切換時段類型 */
        jq_schedule.on('change', "[name^='event-schedule-type']", function (){
            const parent = $(this).parents('[data-schedule]');
            const elm = parent.find('.event-schedule-end, .event-schedule-week');
            if (this.checked){
                //重複
                elm.find("[name^='event-schedule-end'], [name^='event-schedule-week']").prop('disabled', false); //Enable 結束日期

                //auto set select week
                const sel_date = moment(parent.find("[name^='event-schedule-start']").val());
                elm.find(`[value='${sel_date.weekday()}']`).prop('checked', true);

                elm.show();
            }else{
                //單日
                elm.find("[name^='event-schedule-end'], [name^='event-schedule-week']").prop('disabled', true); //Enable 結束日期
                elm.hide();
            }
        });

        /* 結束日期min調整 */
        jq_schedule.on('focus', "[name^='event-schedule-start']", function (){
            const value = $(this).val();
            const parent = $(this).parents('[data-schedule]');
            const elm = parent.find("[name^='event-schedule-end']");
            elm.attr('min', value);
        })

        /* 週期選擇提醒 (必須要至少選擇一天) */
        jq_schedule.on('change', "[name^='event-schedule-week']", function (){
            const parent = $(this).parents('.event-schedule-week');
            const chiller = parent.find("[name^='event-schedule-week']");
            const checked = chiller.filter((index, elm) => elm.checked)
            if (checked.length <= 0){
                parent.children('.invalid-feedback').show()
                chiller.each(function (){
                    this.setCustomValidity('error')
                })
            }else{
                chiller.each(function (){
                    this.setCustomValidity('')
                })
                parent.children('.invalid-feedback').hide()
            }
        })

        /* ==============活動狀態=============== */
        const jq_form = {
            jq_title: $('#event-form-title'),
            jq_data: $('#event-form-data'),
            jq_plan: jq_plan,
            jq_schedule: jq_schedule,
            jq_form_image: $('#event-form-image'),
            jq_form_location: $('#event-form-location'),
            jq_status: $('#event-form-status'),
            jq_attribute: $('#event-form-attribute'),
            jq_thumbnail: $('#event-form-thumbnail'),
        }

        /* 儲存草稿 */
        $('#event-daft').click(function (){
            const form = {
                title: jq_form.jq_title.serializeObject(),
                data: jq_form.jq_data.serializeObject(),
                plan: jq_form.jq_plan.serializeObject(),
                schedule: jq_form.jq_schedule.serializeObject(),
                image: jq_form.jq_form_image.serializeObject(),
                location: jq_form.jq_form_location.serializeObject(),
                status: jq_form.jq_status.serializeObject(),
                attribute: jq_form.jq_attribute.serializeObject(),
                thumbnail: jq_form.jq_thumbnail.serializeObject(),
            }

            console.log(form)

            if(typeof form.plan['event-plan-id'] === 'object'){
                form.plan = form.plan['event-plan-id'].map((value) => {
                    return {
                        id: value,
                        name: '',
                        max: 0,
                        max_each: 0,
                        price: 0
                    }
                })
            }
            console.log(form)
        });

        /* 發佈 */
        $('#event-post').click(function (){
            for (let jqFormKey in jq_form){
                jq_form[jqFormKey].addClass('was-validated')
            }
        });

        /* 移到回收桶 */
        $('#event-recycle').click(function (){
            //todo
        });

        /* ===========活動封面=============== */
        /* 更改封面圖片 */
        $('#event-thumbnail-change').click(function (e){
            e.preventDefault();

            media_select.select_media(function (img){
                $('#event-thumbnail-img').attr('src', '/panel/api/media/' + img[0]).attr('alt', img[0]);
                $('#event-thumbnail').val(img[0]);
            }, 1, /(image\/png)|(image\/jpeg)|(image\/gif)|(image\/webp)/);
        });

        /* =============活動屬性============== */
        /* add tag */
        const jq_addTag = $('#event-add-tag');
        jq_addTag.on('input focus', function (){
            const elm = $(this);
            const val = elm.val();

            if (/(.+),/.test(val)){
                addTag(val.slice(0, -1));
                elm.val('');
            }else if (val === ","){
                elm.val('');
            }
        }).blur(function (){
            const elm = $(this);
            const val = elm.val();

            if (/(.+)/.test(val)){
                addTag(val);
                elm.val('');
            }
        }).keyup(function (e){
            const elm = $(this);
            const val = elm.val();

            if (e.key === "Enter"){
                if (/(.+)/.test(val)){
                    addTag(val);
                    elm.val('');
                }
            }
        });

        /**
         * add Tag
         * @param {string} name
         */
        function addTag(name){
            jq_addTag.before(
                `<div class="col-auto event-tag" data-tag="${name}">${name}<i class="ms-2 fa-regular fa-circle-xmark"></i></div>`);
            updateTag();
        }

        /* delete tag */
        const jq_tagList = $('#event-tag-list');
        jq_tagList.on('click', '[data-tag] > i', function (){
            $(this).parent().remove();
            updateTag()
        });

        /* update tag value */
        function updateTag(){
            const tag_elm = jq_tagList.children('[data-tag]');
            const list = tag_elm.map((index, elm) => elm.dataset.tag).toArray()
            $('#event-tag').val(list.join(','))
        }
    });