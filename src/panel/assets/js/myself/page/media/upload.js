/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery'], function () {
    const Lang = JSON.parse($('#LangJson').text());

    const drop_area = $('#drop-area');
    drop_area.on('dragenter dragover dragleave drop', function (e) {
        e.preventDefault();
    });

    drop_area.on('dragenter dragover', function () {
        drop_area.css('border-color', 'var(--primary-color)')
    })

    drop_area.on('dragleave drop', function () {
        drop_area.css('border-color', '')
    })

    drop_area.on('drop', function (e) {
        handleFiles((e.originalEvent && e.originalEvent.dataTransfer.files));
    })
    $('#file-sel').change(function (e) {
        handleFiles(e.target.files);
        $('#file-sel').val('');
    })

    function handleFiles(files) {
        Array.from(files).forEach(function (file) {
            /* 顯示進度 */
            const id = make_id(10)
            $('#file-upload-list').append(`
                    <li class='list-group-item d-flex justify-content-between'>
                        <span style="max-width: 50%; overflow: hidden; height: 19px" title="${file.name}">${file.name}</span>
                        <div class='progress w-50'>
                            <div id='${id}' class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' style='width: 1%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'>0%</div>
                        </div>
                    </li>`);
            const progressBar = $(`#${id}`);

            /* 進行檢查 */
            //檢查文件類型
            if (!/(image\/jpeg)|(image\/png)|(image\/webp)|(image\/gif)/.test(file.type)) {
                progressBar.css('width', '100%');
                progressBar.addClass('bg-danger');
                progressBar.removeClass('progress-bar-striped');
                progressBar.removeClass('progress-bar-animated');
                progressBar.text(Lang.File_type_not_mach);
                return;
            }

            //8MB 限制
            if (file.size > 8388608) {
                progressBar.css('width', '100%');
                progressBar.addClass('bg-danger');
                progressBar.removeClass('progress-bar-striped');
                progressBar.removeClass('progress-bar-animated');
                progressBar.text(Lang.Over_size);
                return;
            }

            //檔案名稱20字或以下
            if (file.name.length > 100) {
                progressBar.css('width', '100%');
                progressBar.addClass('bg-danger');
                progressBar.removeClass('progress-bar-striped');
                progressBar.removeClass('progress-bar-animated');
                progressBar.text(Lang.File_name_over);
                return;
            }

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
                timeout: 40000,
                success: function (data) {
                    /* 顯示完成 */
                    progressBar.text(data.Message);
                    progressBar.css('width', '100%');
                    progressBar.addClass('bg-success');
                    progressBar.removeClass('progress-bar-striped');
                    progressBar.removeClass('progress-bar-animated');
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

                    if (textStatus === "error" && (xhr.status === 400 || xhr.status === 500) ) {
                        let response = JSON.parse(xhr.responseText);
                        progressBar.text(response.Message);
                    } else if (textStatus === "timeout") {
                        progressBar.text(Lang.Timeout);
                    } else {
                        progressBar.text(Lang.Unknown_Error);
                    }
                }
            });
        });
    }

    //random string
    function make_id(length) {
        var result = [];
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result.push(characters.charAt(Math.floor(Math.random() *
                charactersLength)));
        }
        return result.join('');
    }
});