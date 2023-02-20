/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

define([ 'jquery', 'easymde', 'showdown', 'xss', 'mapbox-gl', '@mapbox/mapbox-gl-geocoder', '@mapbox/mapbox-sdk', 'toastr', 'moment', 'jquery.crs.min', 'jquery.scrollbar.min' ],
    function (jq, EasyMDE, Showdown, xss, mapboxgl, MapboxGeocoder, mapboxSdk, toastr, moment, crs){
        "use strict";
        mapboxgl.accessToken = 'pk.eyJ1IjoiY29jb21pbmUiLCJhIjoiY2xhanp1Ymh1MGlhejNvczJpbHhpdjV5dSJ9.oGNqsDB7ybqV5q6T961bqA';
        crs.init()

        /**
         * fill up input data
         * @param {Object} draft
         */
        function fillData(draft){
            //活動資料
            $('#event-title').val(draft.title['event-title']);
            $('#event-summary').val(draft.data['event-summary'])[0].dispatchEvent(new Event('input', { bubbles: true }));
            MDE_description.codemirror.setValue(draft.data['event-description']);
            MDE_precautions.codemirror.setValue(draft.data['event-precautions']);

            //活動計劃
            jq_plan.html("");
            plans = [];
            draft.plan.forEach((plan) => {
                const tmp = plan_html(plan.id);
                tmp.find(`[name^="event-plan-name"]`).val(plan.name);
                tmp.find(`[name^="event-plan-max"]`).val(plan.max);
                tmp.find(`[name^="event-plan-max-each"]`).val(plan.max_each);
                tmp.find(`[name^="event-plan-price"]`).val(plan.price);
                tmp.appendTo(jq_plan);
                plans.push({ plan_id: plan.id, plan_name: plan.name });
            });

            //活動時段
            jq_schedule.html("");
            draft.schedule.forEach((schedule) => {
                const tmp = schedule_html(schedule.id);
                tmp.find(`[name^="event-schedule-start"]`).val(schedule.start);
                tmp.find(`[name^="event-schedule-end"]`).val(schedule.end);
                tmp.find(`[name^="event-schedule-time-start"]`).val(schedule.time_start);
                tmp.find(`[name^="event-schedule-time-end"]`).val(schedule.time_end);
                tmp.find(`[name^="event-schedule-plan"]`).val(schedule.plan);
                tmp.find(`[name^="event-schedule-type"]`).prop('checked', schedule.type);

                //時段類型重複 -> 顯示和取消禁用
                if (schedule.type){
                    tmp.find('.event-schedule-week, .event-schedule-end').show();
                }

                //week
                if (schedule.week !== null){
                    const week_part = tmp.find('.event-schedule-week');
                    schedule.week.forEach((value) => {
                        week_part.find(`[value='${value}']`).prop('checked', true);
                    });
                }

                tmp.appendTo(jq_schedule);
            });

            //活動圖片
            jq_dropZone.html("");
            jq_image.val(draft.image['event-image']); //set value
            if (draft.image['event-image'] !== ""){
                draft.image['event-image'].split(",").forEach((value) => {
                    const tmp = image_html(value);
                    tmp.appendTo(jq_dropZone);
                });
            }

            //活動地址
            $("#event-location").val(draft.location['event-location']);
            $("#event-country").val(draft.location['event-country'])[0].dispatchEvent(new Event('change', { "bubbles": true }));
            $("#event-region").val(draft.location['event-region']);
            $("#event-longitude").val(draft.location['event-longitude']);
            $("#event-latitude").val(draft.location['event-latitude']);
            if (draft.location['event-longitude'] !== "" && draft.location['event-latitude'] !== ""){
                map_marker.setLngLat([ draft.location['event-longitude'], draft.location['event-latitude'] ]);
                map.flyTo({
                    center: [ draft.location['event-longitude'], draft.location['event-latitude'] ],
                    zoom: 15
                });
            }

            //活動狀態
            $('#event-status').val(draft.status["event-status"]);
            $('#event-post-date').val(draft.status["event-post-date"]);
            $('#event-post-time').val(draft.status["event-post-time"]);

            //活動屬性
            $('#event-type').val(draft.attribute['event-type']);
            $('#event-tag').val("");
            $('#event-tag-list > [data-tag]').remove();
            if (draft.attribute['event-tag'] !== ""){
                draft.attribute['event-tag'].split(",").forEach((value) => {
                    addTag(value);
                });
            }

            //活動封面
            $('#event-thumbnail').val(draft.thumbnail['event-thumbnail']);
            $('#event-thumbnail-img').attr('src', '/panel/api/media/' + draft.thumbnail['event-thumbnail']).attr('alt', draft.thumbnail['event-thumbnail']);
        }

        /* 檢查草稿 */
        const found_draft = () => {
            //edit post - load post
            fetch(location.pathname + '/', {
                method: 'POST',
                redirect: 'error',
                headers: {
                    'Content-Type': 'application/json; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: "{}"
            }).then((response) => {
                response.json().then((json) => {
                    console.log(json);

                    if (json.code === 200){
                        fillData(json.data);
                    }else{
                        toastr.error(json.Message, json.Title);
                    }
                });
            }).catch((error) => {
                console.log(error);
            });
        };

        //###### 活動資料 #######
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
            simplifiedAutoLink: true,
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
                toolbar: [ "preview", "side-by-side", "fullscreen", "guide" ],
                blockStyles: {
                    italic: "_"
                },
                renderingConfig: {
                    sanitizerFunction: (renderedHTML) => {
                        return filterXSS.process(renderedHTML);
                    }
                },
                status: [ "lines", "words", "cursor", {
                        className: "count",
                        defaultValue: (el) => {
                            el.innerHTML = "0/" + jq_elm.attr('maxlength');
                            el._count = 0;
                        },
                        onUpdate: (el) => {
                            const length = jq_elm.val().length, maxlength = jq_elm.attr('maxlength');
                            el.innerHTML = length + "/" + maxlength;
                            if (length >= maxlength){
                                if (length > el._count) toastr.warning(`字數已超出了${maxlength}字限制! 如你繼續輸入, 內容有機會被截斷`);
                                el._count = length;
                            }
                        }
                    } ]
            };
        };

        /* description markdown editor */
        const jq_description = $('#event-description');
        const MDE_description = new EasyMDE({
            ...editor_options(filterXSS_description, jq_description, MD_converter),
            element: jq_description[0],
            autosave: { enabled: false },
            placeholder: "活動描述",
            initialValue: jq_description.val()
        });
        MDE_description.codemirror.setOption('readOnly', true)

        /* precautions markdown editor */
        const jq_precautions = $('#event-precautions');
        const MDE_precautions = new EasyMDE({
            ...editor_options(filterXSS_precautions, jq_precautions, MD_converter),
            element: jq_precautions[0],
            autosave: { enabled: false },
            placeholder: "活動注意事項",
            maxHeight: "5rem",
            initialValue: jq_precautions.val()
        });
        MDE_precautions.codemirror.setOption('readOnly', true)

        //######## 活動圖片 #######
        const jq_dropZone = $('#event-image-list');
        jq_dropZone.scrollbar();
        jq_dropZone.parents('.scroll-wrapper').removeClass('row mb-2 media-list');
        const jq_image = $('#event-image');

        /**
         * 圖片html
         * @param {string} id 圖片id
         * @return {JQuery<HTMLElement>}
         */
        function image_html(id){
            return $(`<div class="col-6 col-sm-4 col-md-3 col-lg-2 item">
                    <div class="ratio ratio-1x1 media-list-focus">
                        <div class="overflow-hidden">
                            <div class="media-list-center">
                                <img src="/panel/api/media/${id}" data-image-id="${id}" alt="${id}" />
                            </div>
                        </div>
                    </div>
                </div>`);
        }

        //####### 活動地址 ########
        /* Load map */
        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v12',
            zoom: 1,
            center: [ 0, 0 ]
        });

        /* Enable stars with reduced atmosphere */
        map.on('style.load', () => {
            map.setFog({ 'horizon-blend': 0.05 });
        });

        /* Add Map Control */
        const map_marker = new mapboxgl.Marker({
            color: 'red',
        }).setLngLat([ 0, 0 ]).addTo(map);
        map.addControl(new mapboxgl.ScaleControl());
        map.addControl(new mapboxgl.NavigationControl());

        //########## 計劃 ############
        const jq_plan = $('#event-form-plan'); //計劃
        const jq_schedule = $('#event-form-schedule'); //時段
        let plans = [];

        /**
         * 計劃HTML
         * @param {number|null}id 計劃id, null=自動生成
         * @return {JQuery<HTMLElement>}
         */
        function plan_html(id = null){
            id = id === null ? Math.floor(Math.random() * 9999) : id;
            return $(
                `<div class="col-12 mb-2 row g-1 border border-1 rounded p-2" data-plan="${id}">
                <input type="text" name="event-plan-id" class="d-none" value="${id}">
                <h5 class="col-12 text-muted"># ${id}</h5>
                <div class="col-12 col-lg-7">
                    <label for="event-plan-name-${id}" class="form-label">計畫名稱</label>
                    <input type="text" class="form-control form-rounded" name="event-plan-name-${id}" id="event-plan-name-${id}" maxlength="20" disabled>
                    <div class="invalid-feedback">這裏不能留空哦~~</div>
                </div>
                <div class="w-100"></div>
                <div class="col-6 col-md-2">
                    <label for="event-plan-max-${id}" class="form-label">計劃最大人數</label>
                    <input type="number" class="form-control form-rounded" name="event-plan-max-${id}" id="event-plan-max-${id}" min="1" disabled>
                    <div class="invalid-feedback">至少需要一位以上~~</div>
                </div>
                <div class="col-6 col-md-3">
                    <label for="event-plan-max-each-${id}" class="form-label">每個預約最大人數</label>
                    <input type="number" class="form-control form-rounded" name="event-plan-max-each-${id}" id="event-plan-max-each-${id}" min="1" disabled>
                    <div class="invalid-feedback">至少需要一位以上~~</div>
                </div>
                <div class="col-6 col-md-2">
                    <label for="event-plan-price-${id}" class="form-label">計劃金額</label>
                    <div class="input-group has-validation">
                        <span class="input-group-text form-rounded">$</span>
                        <input type="number" class="form-control form-rounded" name="event-plan-price-${id}" id="event-plan-price-${id}" min="0" value="0" step="0.01" disabled>
                        <div class="invalid-feedback">需要等於0或以上~~</div>
                    </div>
                </div>
            </div>`);
        }

        //############### 活動時段 #################

        /**
         * 時段HTML
         * @param {boolean} includeRemoveBtn 是否包含移除按鈕
         * @return {JQuery<HTMLElement>}
         */
        function schedule_html(includeRemoveBtn = true){
            const id = Math.floor(Math.random() * 9999);
            const min = moment().format('YYYY-MM-DD');
            const tmp = $(
                `<div class="col-12 mb-2 row g-1 border border-1 rounded p-2 align-items-center" data-schedule="${id}">
                    <input type="text" name="event-schedule-id" class="d-none" value="${id}" />
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="date-picker form-floating">
                            <input type="date" class="form-control form-rounded date-picker-toggle" name="event-schedule-start-${id}" id="event-schedule-start-${id}" disabled min="${min}">
                            <label for="event-schedule-start-${id}">開始日期</label>
                            <div class="invalid-feedback">必需要今天之後~~</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 event-schedule-end" style="display: none;">
                        <div class="date-picker form-floating">
                            <input type="date" class="form-control form-rounded date-picker-toggle" name="event-schedule-end-${id}" id="event-schedule-end-${id}" disabled min="${min}">
                            <label for="event-schedule-end-${id}">結束日期</label>
                            <div class="invalid-feedback">必需要開始日期之後~~</div>
                        </div>
                    </div>
                    <div class="col col-md-auto">
                        <div class="form-check form-switch float-end">
                            <input class="form-check-input" type="checkbox" role="switch" name="event-schedule-type-${id}" id="event-schedule-type-${id}" disabled>
                            <label class="form-check-label" for="event-schedule-type-${id}">重複</label>
                        </div>
                    </div>
                    <div class="w-100"></div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="form-floating">
                            <input type="text" class="form-control form-rounded" name="event-schedule-time-start-${id}" id="event-schedule-time-start-${id}" disabled value="${moment().format("HH:mm")}">
                            <label for="event-schedule-time-start-${id}">開始時間</label>
                            <div class="invalid-feedback">這裏不能留空哦~~</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="form-floating">
                            <input type="text" class="form-control form-rounded" name="event-schedule-time-end-${id}" id="event-schedule-time-end-${id}" disabled value="${moment().add(30, 'm').format("HH:mm")}">
                            <label for="event-schedule-time-end-${id}">結束時間</label>
                            <div class="invalid-feedback">這裏不能留空哦~~</div>
                        </div>
                    </div>
                    <div class="col-12 col-md event-schedule-week" style="display: none;">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="event-schedule-week-${id}" id="event-schedule-week-0-${id}" value="0" disabled>
                            <label class="form-check-label" for="event-schedule-week-0-${id}">週日</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="event-schedule-week-${id}" id="event-schedule-week-1-${id}" value="1" disabled>
                            <label class="form-check-label" for="event-schedule-week-1-${id}">週一</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="event-schedule-week-${id}" id="event-schedule-week-2-${id}" value="2" disabled>
                            <label class="form-check-label" for="event-schedule-week-2-${id}">週二</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="event-schedule-week-${id}" id="event-schedule-week-3-${id}" value="3" disabled>
                            <label class="form-check-label" for="event-schedule-week-3-${id}">週三</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="event-schedule-week-${id}" id="event-schedule-week-4-${id}" value="4" disabled>
                            <label class="form-check-label" for="event-schedule-week-4-${id}">週四</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="event-schedule-week-${id}" id="event-schedule-week-5-${id}" value="5" disabled>
                            <label class="form-check-label" for="event-schedule-week-5-${id}">週五</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="event-schedule-week-${id}" id="event-schedule-week-6-${id}" value="6" disabled>
                            <label class="form-check-label" for="event-schedule-week-6-${id}">週六</label>
                        </div>
                        <div class="invalid-feedback">至少選取一天</div>
                    </div>
                    <div class="w-100"></div>
                    <div class="col-12 col-md-6">
                        <select class="form-select form-rounded" name="event-schedule-plan-${id}" id="event-schedule-plan-${id}" disabled>
                            <option selected value="">選擇計劃</option>
                            ${plans.map((value) => `<option value="${value.plan_id}">${value.plan_id} - ${value.plan_name}</option>`)}
                        </select>
                        <div class="invalid-feedback">這裏必須選擇哦~~</div>
                    </div>
                </div>`);
            return tmp;
        }

        //############ 活動狀態 #################

        //############ 活動封面 ###############

        //############ 活動屬性 #############
        /* tag */
        const jq_addTag = $('#event-add-tag');
        const jq_tag = $('#event-tag');
        const jq_tagList = $('#event-tag-list');

        /**
         * add Tag html
         * @param {string} name
         */
        function addTag(name){
            jq_addTag.before(
                `<div class="col-auto event-tag" data-tag="${name}">${name}</div>`);
            updateTag();
        }

        /* update tag value */
        function updateTag(){
            const tag_elm = jq_tagList.children('[data-tag]');
            const list = tag_elm.map((index, elm) => elm.dataset.tag).toArray();
            const val = list.join(',');
            jq_tag.val(val);
        }

        return { found_draft };
    });