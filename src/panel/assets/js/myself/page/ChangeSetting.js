/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'toastr', 'zxcvbn', 'forge', 'bootstrap'], (jq, toastr, zxcvbn, forge, bootstrap) => {
    "use strict";

    let Lang = $('#langJson').text();
    Lang = JSON.parse(Lang);

    /* 資料修改 */
    $('#DataSet').submit(function (e) {
        if (!e.isDefaultPrevented() && this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            const data = $(this).serializeObject();

            /* 封鎖按鈕 */
            const bt = $(this).children('.form-submit');
            const html = bt.html();
            bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

            /* send */
            fetch('/panel/ChangeSetting?type=DataSet', {
                method: 'POST',
                redirect: 'error',
                headers: {
                    'Content-Type': 'application/json; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            }).then((response) => {
                response.json().then((json) => {
                    console.log(json) //debug

                    if(json.code === 503 || json.code === 502){
                        toastr.success(json.Message, json.Title);
                        $("#username").text(json.Data.name)
                        $(this).removeClass('was-validated')
                    }else{
                        toastr.error(json.Message, json.Title);
                    }
                })
            }).finally(() => {
                bt.html(html).removeAttr('disabled');
            }).catch((error) => {
                console.log(error)
            })
        }
    })

    /* 修改密碼 */
    $('#PassSet').submit(async function (e) {
        if (!e.isDefaultPrevented() && this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            const data = $(this).serializeObject();
            console.log(data)

            /* 封鎖按鈕 */
            const bt = $(this).children('.form-submit');
            const html = bt.html();
            bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

            /* 加密 */
            const KeyResponse = await fetch('/panel/key.php')
            const key = await KeyResponse.text();
            const pk = forge.pki.publicKeyFromPem(key);
            data.password = forge.util.encode64(pk.encrypt(data.password))
            data.password2 = forge.util.encode64(pk.encrypt(data.password2))
            data.passwordOld = forge.util.encode64(pk.encrypt(data.passwordOld))

            /* send */
            fetch('/panel/ChangeSetting?type=PassSet', {
                method: 'POST',
                redirect: 'error',
                headers: {
                    'Content-Type': 'application/json; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            }).then((response) => {
                response.json().then((json) => {
                    console.log(json) //debug

                    if(json.code === 510){
                        toastr.success(json.Message, json.Title);
                        $("#Password, #Old_Pass, #Password2").val('')
                        $(this).removeClass('was-validated')
                    }else {
                        toastr.error(json.Message, json.Title);
                    }
                })
            }).finally(() => {
                bt.html(html).removeAttr('disabled');
            }).catch((error) => {
                console.log(error)
            })
        }
    });

    /* 註冊2FA */
    $('#TwoFA_register').on('shown.bs.modal', function () {
        const modal = $(this);

        forge.pki.rsa.generateKeyPair({bits: 2048, workers: 2}, function (err, key) {
            $.ajax({
                type: 'POST',
                url: '/panel/ChangeSetting?type=2FASet',
                contentType: 'text/json; charset=utf-8',
                data: JSON.stringify({'puKey': forge.pki.publicKeyToPem(key.publicKey), 'DoAction': true}),
                success: function (data) {
                    if(data.code === 521){
                        let code = key.privateKey.decrypt(window.atob(data.Data.secret)); //解密

                        /* 分割代碼 */
                        code = code.split('')
                        const temp = []
                        while (code.length > 0){
                            temp.push(code.splice(0, 4).join(''));
                        }
                        const temp2 = '<span class="pe-2">'+temp.join('</span><span class="pe-2">')+'</span>'

                        /* 輸出 */
                        //modal.find('#qr').attr('src', window.atob(data.Data.qr)).removeClass('visually-hidden');
                        const img = jQuery('<img />',{
                            src: window.atob(data.Data.qr),
                            width: 250,
                            height: 250,
                            alt: 'QRcode'
                        });
                        modal.find('#qr').html(img)
                        modal.find('#secret').html(temp2);
                        modal.find('.form-submit').removeAttr('disabled');
                        $("#2FA_Code").val('').focus();
                    }else {
                        toastr.error(json.Message, json.Title);
                    }
                }
            });
        });
    })

    /* 重置2FA */
    $('#2FAReset').click(function () {
        /* 封鎖按鈕 */
        const bt = $('#TwoFA_confirm_off').find('.btn-secondary, .btn-danger');
        const html = bt.html();
        bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

        $.ajax({
            type: 'POST',
            url: '/panel/ChangeSetting?type=2FASet',
            contentType: 'text/json; charset=utf-8',
            data: JSON.stringify({'DoAction': false}),
            success: function (json) {
                if(json.code === 525){
                    toastr.success(json.Message, json.Title);
                    bootstrap.Modal.getInstance($('#TwoFA_confirm_off')[0]).hide()
                    ajexLoad('/panel/ChangeSetting')
                }else {
                    toastr.error(json.Message, json.Title);
                }
            }
        }).always(() => {
            bt.html(html).removeAttr('disabled');
        });
    })

    /* 2FA自動submit */
    $('#2FA_Code').on('input focus', function () {
        if($(this).val().length >=6) $('#TwoFASet').submit();
    });

    /* 2FA確認代碼 */
    $('#TwoFASet').submit(function (e) {
        if (!e.isDefaultPrevented() && this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            const data = $(this).serializeObject();
            console.log(data)

            /* 封鎖按鈕 */
            const bt = $(this).find('.form-submit, .btn-secondary');
            const html = bt.html();
            bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

            /* send */
            fetch('/panel/ChangeSetting?type=TwoFACheck', {
                method: 'POST',
                redirect: 'error',
                headers: {
                    'Content-Type': 'application/json; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            }).then((response) => {
                response.json().then((json) => {
                    console.log(json) //debug

                    if(json.code === 523){
                        toastr.success(json.Message, json.Title);
                        bootstrap.Modal.getInstance($('#TwoFA_register')[0]).hide()
                        ajexLoad('/panel/ChangeSetting')
                    }else {
                        toastr.error(json.Message, json.Title);
                        $("#2FA_Code").val('')
                    }
                })
            }).finally(() => {
                bt.html(html).removeAttr('disabled');
            }).catch((error) => {
                console.log(error)
            })
        }
    })

    /*  */
    $('#TwoFA_BackupCode').on('shown.bs.modal', function (e) {
        const modal = $(this);

        forge.pki.rsa.generateKeyPair({bits: 2048, workers: 2}, function (err, key) {
            $.ajax({
                type: 'POST',
                url: '/panel/ChangeSetting?type=2FABackupCode',
                contentType: 'text/json',
                data: JSON.stringify({'puKey': forge.pki.publicKeyToPem(key.publicKey)}),
                success: function (json) {
                    if(json.code === 528){
                        /* 解密訊息 */
                        const codes = json.Data.code.map((item) => key.privateKey.decrypt(window.atob(item.Code)))
                        console.log(codes)

                        /* 排列table */
                        let table = '';
                        let saveText = '';
                        for (let i=0;i<json.Data.code.length;i = i+2){
                            table += '<tr>';
                            for(let a=i;a<i+2;a++){
                                if(json.Data.code[a].used){
                                    table += `<td class="text-decoration-line-through bg-secondary bg-opacity-50"><s>${codes[a]}</s></td>`; //寫入table
                                }else{
                                    saveText += codes[a]+"\n"; //寫入txt
                                    table += `<td>${codes[a]}</td>`; //寫入table
                                }
                            }
                            table += '</tr>';
                        }

                        /* 輸出 */
                        modal.find('#BackupCodeShowArea').html("<table class='table table-hover table-bordered text-center'>"+table+"</table>");
                        modal.find('.btn-primary').removeAttr('disabled');
                        modal.find('#BackupCodeLoading').remove();
                    }else{
                        toastr.error(json.Message, json.Title);
                    }
                }
            });
        });
    });

    /* 檢查限制 */
    $('#Password, #Old_Pass, #Password2').on("input focus", function () {
        const Pass = $('#Password');
        const OldPass = $('#Old_Pass');
        const CPass = $('#Password2');

        /* 不可以與舊密碼相同 */
        if(Pass.val() === OldPass.val()) Pass[0].setCustomValidity('error');
        else Pass[0].setCustomValidity('');

        /* 密碼必須一樣 */
        if (Pass.val() !== CPass.val()) CPass[0].setCustomValidity('error');
        else CPass[0].setCustomValidity('');
    })

    /* 密碼強度 */
    $('#Password, #Name, #Email').on("input focus", function () {
        const val = $('#Password').val();
        const input = [$('#Name').val(), $('#Email').val()];
        const result = zxcvbn(val, input).score;
        const pass = $('#passStrength');
        const list = $('#passStrength-list li');

        /* 指示器 */
        pass.css({width: (100 / 4) * result + "%"})
        pass.text(Lang.strength[result]);

        if (result === 1) pass.css({'background-color': 'var(--bs-danger)'});
        if (result === 2) pass.css({'background-color': 'var(--bs-warning'});
        if (result === 3) pass.css({'background-color': 'var(--bs-info)'});
        if (result === 4) pass.css({'background-color': 'var(--bs-success)'});

        /* 條件指示 */
        if (/[A-Z]+/.test(val)) $(list[0]).addClass('text-success');
        else $(list[0]).removeClass('text-success');

        if (/[a-z]+/.test(val)) $(list[1]).addClass('text-success');
        else $(list[1]).removeClass('text-success');

        if (val.length >= 8) $(list[2]).addClass('text-success');
        else $(list[2]).removeClass('text-success');

        if (!input.includes(val)) $(list[3]).addClass('text-success');
        else $(list[3]).removeClass('text-success');
    });
})