/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['media-select'], function (media_select) {
    const jq_modal = media_select.data.jq_modal;
    const Lang = JSON.parse($('#media-select-LangJson').text());
    let timeout;

    const drop_area = jq_modal.find('.modal-content')
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

    drop_area.on('drop', function (e) {
        handleFiles((e.originalEvent && e.originalEvent.dataTransfer.files));
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
                                    <div class='progress-bar progress-bar-striped progress-bar-animated bg-info' role='progressbar' style='width: 100%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'>${Lang.Waiting}</div>
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
                progressBar.text(Lang.File_type_not_mach);
                continue;
            }

            //8MB 限制
            if (file.size > 8388608) {
                progressBar.css('width', '100%');
                progressBar.addClass('bg-danger');
                progressBar.removeClass('progress-bar-striped');
                progressBar.removeClass('progress-bar-animated');
                progressBar.text(Lang.Over_size);
                continue;
            }

            //檔案名稱20字或以下
            if (file.name.length > 100) {
                progressBar.css('width', '100%');
                progressBar.addClass('bg-danger');
                progressBar.removeClass('progress-bar-striped');
                progressBar.removeClass('progress-bar-animated');
                progressBar.text(Lang.File_name_over);
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