/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery'], function (){
    let drop_area = $('#drop-area');
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
        Array.from(files).forEach(function (file){
            //顯示進度
            const id = makeid(10)
            $('#file-upload-list').append(`
                    <li class='list-group-item d-flex justify-content-between'>
                        <span style="max-width: 50%; overflow: hidden; height: 19px" title="${file.name}">${file.name}</span>
                        <div class='progress w-50'>
                            <div id='${id}' class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' style='width: 1%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'>0%</div>
                        </div>
                    </li>`);
            let progressBar = $(`#${id}`);

            //進行檢查(檔案類型 & 檔案大細)
            if(/(audio\/.+)|(video\/.+)|(image\/.+)/.test(file.type)){ //檢查文件類型
                if(file.size <= 8388608){ //8MB 限制
                    if(file.name.length <= 50) {//檔案名稱50字或以下

                        //上傳1
                        let formData = new FormData();
                        formData.append("file", file);
                        formData.append("data", JSON.stringify({test: 'test'}));
                        $.ajax({
                            url: "/panel/upload",
                            type: "POST",
                            processData: false,
                            contentType: false,
                            data: formData,
                            cache: false,
                            timeout: 40000,
                            success: function (data) {
                                //顯示完成
                                let progressBar = $(`#${id}`);
                                progressBar.text(data.Result);
                                progressBar.css('width', '100%');
                                progressBar.addClass('bg-success');
                                progressBar.removeClass('progress-bar-striped');
                                progressBar.removeClass('progress-bar-animated');
                            },
                            xhr: function () {
                                let xhr = new window.XMLHttpRequest();
                                xhr.upload.addEventListener("progress", function (e) {
                                    //更新進度
                                    let progress = Math.round(e.loaded / e.total * 0.1 * 1000);
                                    let progressBar = $(`#${id}`);
                                    progressBar.attr('aria-valuenow', progress);
                                    progressBar.css('width', progress + '%');
                                    progressBar.text(progress + '%');
                                }, false);
                                return xhr;
                            },
                            error: function (xhr, textStatus) {
                                //失敗
                                progressBar.css('width', '100%');
                                progressBar.addClass('bg-danger');
                                progressBar.removeClass('progress-bar-striped');
                                progressBar.removeClass('progress-bar-animated');

                                if (textStatus === "error" && xhr.status === 400) {
                                    let response = JSON.parse(xhr.responseText);
                                    progressBar.text(response.Result);
                                } else if (textStatus === "timeout") {
                                    progressBar.text("Media-upload.Timeout");
                                } else {
                                    progressBar.text("Media-upload.Unknown_Error");
                                }
                            }
                        });
                    }else{
                        //檔案名稱超過50個字
                        progressBar.css('width', '100%');
                        progressBar.addClass('bg-danger');
                        progressBar.removeClass('progress-bar-striped');
                        progressBar.removeClass('progress-bar-animated');
                        progressBar.text("Media-upload.File_name_over");
                    }
                }else{
                    //檔案超出限制大細
                    progressBar.css('width', '100%');
                    progressBar.addClass('bg-danger');
                    progressBar.removeClass('progress-bar-striped');
                    progressBar.removeClass('progress-bar-animated');
                    progressBar.text("Media-upload.Over_size");
                }
            }else{
                //檔案格式不符合
                progressBar.css('width', '100%');
                progressBar.addClass('bg-danger');
                progressBar.removeClass('progress-bar-striped');
                progressBar.removeClass('progress-bar-animated');
                progressBar.text("Media-upload.File_type_not_mach");
            }
        });
    }

    //random string
    function makeid(length) {
        var result           = [];
        var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for ( var i = 0; i < length; i++ ) {
            result.push(characters.charAt(Math.floor(Math.random() *
                charactersLength)));
        }
        return result.join('');
    }
});