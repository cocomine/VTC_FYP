/*
 * Copyright (c) 2022. 
 * Create by cocomine
 */

define(['forge', 'zxcvbn', 'grecaptcha', 'jquery'], function (forge, zxcvbn) {
    "use strict";

    let Lang = $('#langJson').text();
    Lang = JSON.parse(Lang);

    /* 遞交表單 */
    $('form').submit(async function (e) {
        if (!e.isDefaultPrevented()  && this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            const data = $(this).serializeObject();

            /* 防止機械人 */
            const response = grecaptcha.getResponse();
            if (response.length <= 0) {
                $('#g-recaptcha').removeClass('is-valid').addClass('is-invalid')
                return;
            }

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

            /* send */
            fetch('/panel/register', {
                method: 'POST',
                redirect: 'error',
                headers: {
                    'Content-Type': 'application/json; charset=UTF-8'
                },
                body: JSON.stringify(data)
            }).then((response) => {
                response.json().then((json) => {
                    console.log(json) //debug
                    const ResultMsg = $('#ResultMsg');

                    if(json.code === 107) {
                        location.replace('.');
                    }else
                    if(json.code === 206) {
                        ResultMsg.html('<div class="alert alert-info" role="alert">' + json.Message +'</div>')
                    }else
                    if(json.code === 206){
                        ResultMsg.html('<div class="alert alert-success" role="alert">' + json.Message +'</div>')
                    }else{
                        ResultMsg.html('<div class="alert alert-danger" role="alert">' + json.Message +'</div>')
                    }
                })
            }).finally(() => {
                grecaptcha.reset();
                bt.html(html).removeAttr('disabled');
            }).catch((error) => {
                console.log(error)
            })
        }
    })

    /* 檢查限制 */
    $('#Password, #Password2').on("input focus", function () {
        const Pass = $('#Password');
        const CPass = $('#Password2');

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

    /* 外傳function */
    return {
        recaptchacall: () => {
            $('#g-recaptcha').removeClass('is-invalid').addClass('is-valid');
        }
    }
})