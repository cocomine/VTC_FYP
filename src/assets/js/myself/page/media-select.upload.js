/*
 * Copyright (c) 2022.
 * Create by cocomine
 * 1.0
 */

/*
 * css must be loaded before each use
 * <link rel="stylesheet" href="/panel/assets/css/myself/media-select.css">

 * To use multiple languages, you must have the following html
 * <pre id="media-select-LangJson" class="d-none">$LangJson</pre>
 * $LangJson => Place text in json format
 * json must conform to the structure, you can refer to lines 22 to 31
 */
define(['media-select'], function (media_select) {
    const jq_modal = media_select.data.jq_modal();
    let timeout;
    const drop_area = jq_modal.find('.modal-content')

    /* 多語言處理 */
    const Lang = {
        Media: "Media %s",
        title: "Select Media",
        upload: {
            Timeout: "Upload timed out",
            File_name_over: "File name is too long",
            Over_size: "File too large",
            File_type_not_mach: "File format does not match",
            Waiting: "Waiting for upload...",
            limit_type: "Accept: .jpg .png .webp .gif",
            drag: "Drag files here to upload",
            upload: "Upload",
            or: "Or",
            limit: 'The maximum size of a single file is 8MB'
        },
        ...JSON.parse($('#media-select-LangJson').text())
    };
    /* 添加html */
    drop_area.append(`
        <div class="upload-overly" style="display: none">
            <div class="row justify-content-center align-content-center h-100">
                <h3 class='col-auto text-light'>${Lang.upload.drag}</h3>
                <div class='w-100'></div>
                <p class='col-auto text-light'>${Lang.upload.limit_type}</p>
            </div>
        </div>`)
    drop_area.children('.modal-footer').prepend(`<p>${Lang.upload.drag}</p>`)
    drop_area.children('.modal-body').prepend(`
        <ul class="nav nav-tabs mb-1" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#Media-select-pane" type="button" role="tab" aria-controls="Media-select-pane" aria-selected="true">${Lang.title}</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#Media-upload-pane" type="button" role="tab" aria-controls="Media-upload-pane" aria-selected="false">${Lang.upload.upload}</button>
            </li>
        </ul>`)
    .children('.tab-content').append(`
        <div class="tab-pane fade" role="tabpanel" id="Media-upload-pane">
            <div class='row py-5 justify-content-center' style="border: 5px dashed #ccc; border-radius: 20px;">
                <h5 class='col-auto'>${Lang.upload.drag}</h5>
                <div class='w-100'></div>
                <p class='col-auto'>${Lang.upload.or}</p>
                <div class='w-100'></div>
                <div class='col-12 col-sm-4 '>
                    <input type='file' class='form-control' id='file-sel' multiple accept='${media_select.data}' />
                    <label for="file-sel" class="form-label">${Lang.upload.limit_type}</label>
                </div>
            </div>
            <p>${Lang.upload.limit}</p>
        </div>`)

    /* 拖拉處理 */
    drop_area.on('dragenter dragover dragleave drop', function (e) {
        e.preventDefault();
    });

    drop_area.on('dragenter dragover', function (e) {
        clearTimeout(timeout)
        drop_area.find('.upload-overly').fadeIn()
    })

    drop_area.on('dragleave drop', function (e) {
        timeout = setTimeout(() => {
            drop_area.find('.upload-overly').fadeOut();
        }, 100);
    })

    /* Upload */
    drop_area.on('drop', function (e) {
        handleFiles((e.originalEvent && e.originalEvent.dataTransfer.files));
    })
    $('#file-sel').change(function (e) {
        handleFiles(e.target.files);
        $('#file-sel').val('');
    })

    /**
     * 處理檔案
     * @param {FileList} files
     */
    function handleFiles(files) {
        let limit = 4;
        let upload_queue = [];

        for (let file of files) {
            /* 顯示進度 */
            const tmp = $(`
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="ratio ratio-1x1 media-list-focus">
                            <div class="overflow-hidden">
                                <div class='progress h-100'>
                                    <div class='progress-bar progress-bar-striped progress-bar-animated bg-info' role='progressbar' style='width: 100%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'>${Lang.upload.Waiting}</div>
                                </div>
                            </div>
                        </div>
                    </div>`);
            jq_modal.find('.media-list').prepend(tmp);
            const progressBar = tmp.find('.progress-bar');

            /* 進行檢查 */
            //檢查文件類型
            console.log()
            if (!media_select.data.filter_mime().test(file.type)) {
                progressBar.css('width', '100%');
                progressBar.addClass('bg-danger');
                progressBar.removeClass('progress-bar-striped');
                progressBar.removeClass('progress-bar-animated');
                progressBar.text(Lang.upload.File_type_not_mach);
                continue;
            }

            //8MB 限制
            if (file.size > 8388608) {
                progressBar.css('width', '100%');
                progressBar.addClass('bg-danger');
                progressBar.removeClass('progress-bar-striped');
                progressBar.removeClass('progress-bar-animated');
                progressBar.text(Lang.upload.Over_size);
                continue;
            }

            //檔案名稱20字或以下
            if (file.name.length > 100) {
                progressBar.css('width', '100%');
                progressBar.addClass('bg-danger');
                progressBar.removeClass('progress-bar-striped');
                progressBar.removeClass('progress-bar-animated');
                progressBar.text(Lang.upload.File_name_over);
                continue;
            }

            upload_queue.push({progressBar, file});

        }

        /* 限制同時上載檔案數量 */
        const Interval_id = setInterval(() => {
            if (limit > 0) {
                limit--;
                const tmp = upload_queue.shift();
                if (tmp !== undefined) upload(tmp.progressBar, tmp.file, () => limit++);
            }
            if (upload_queue.length <= 0) clearInterval(Interval_id)
        }, 1000)
    }

    /* 上傳 */
    function upload(progressBar, file, callback) {
        progressBar.removeClass('bg-info');

        /* 包裝form-data */
        const formData = new FormData();
        formData.append("file", file);

        /* 上傳 */
        $.ajax({
            url: "/panel/api/media",
            type: "POST",
            processData: false,
            contentType: false,
            data: formData,
            cache: false,
            timeout: 0,
            success: function (data) {
                /* 顯示完成 */
                progressBar.parents('.media-list-focus').attr('data-id', data.body);
                progressBar.parents('.overflow-hidden').html(`
                    <div class="media-list-center">
                        <img src="/panel/api/media/${data.body}" draggable="false" alt="${Lang.Media.replace('%s', data.body)}"/>
                    </div>`);
            },
            xhr: function () {
                /* 更新進度 */
                let xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (e) {
                    const progress = Math.round(e.loaded / e.total * 0.1 * 1000);
                    progressBar.attr('aria-valuenow', progress);
                    progressBar.css('width', progress + '%');
                    progressBar.text(progress + '%');
                }, false);
                return xhr;
            },
            error: function (xhr, textStatus) {
                /* 失敗 */
                progressBar.css('width', '100%');
                progressBar.addClass('bg-danger');
                progressBar.removeClass('progress-bar-striped');
                progressBar.removeClass('progress-bar-animated');

                if (textStatus === "error" && (xhr.status === 400 || xhr.status === 500)) {
                    let response = JSON.parse(xhr.responseText);
                    progressBar.text(response.Message);
                } else if (textStatus === "timeout") {
                    progressBar.text(Lang.Timeout);
                } else {
                    progressBar.text(Lang.Unknown_Error);
                }
            }
        }).always(callback);
    }
})