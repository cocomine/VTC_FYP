/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'toastr', 'zxcvbn', 'forge'], (jq, toastr, zxcvbn, forge) => {
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
            const bt = $('#form_submit');
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
                    let code = key.privateKey.decrypt(window.atob(data.Data.secret)); //解密

                    /* 輸出 */
                    //modal.find('#qr').attr('src', window.atob(data.Data.qr)).removeClass('visually-hidden');
                    const img = jQuery('<img />',{
                        src: window.atob(data.Data.qr),
                        width: 250,
                        height: 250,
                        alt: 'QRcode'
                    });
                    modal.find('#qr').html(img)
                    modal.find('#secret').text(code);
                    modal.find('.btn-primary').removeAttr('disabled');
                    //$('#2FA_Code').focus();
                }
            });
        });
    })



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