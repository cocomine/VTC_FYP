/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'moment', 'forge', 'bootstrap', 'zxcvbn', 'toastr'], function (jq, moment, forge, bootstrap, zxcvbn, toastr) {
    "use strict";

    const Lang = JSON.parse($('#langJson').text());

    /* 反轉搜尋 */
    $('#reverse').click(function (e) {
        e.preventDefault();
        const Departure = $('#Departure');
        const Destination = $('#Destination')

        const temp = Departure.val();
        const temp2 = Destination.val();

        Departure.val(temp2);
        Destination.val(temp);

        $(this).children('i').animate({deg: "+=180"},
            {
                duration: 500, step: function (now) {
                    $(this).css({transform: 'rotate(' + now + 'deg)'});
                }
            })
    })

    /* set min date */
    $('#Date').attr('min', moment().format('YYYY-MM-DD'))

    /* Search */
    $('#search').submit(function (e) {
        if (!e.isDefaultPrevented() && this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            const data = $(this).serializeObject();

            const hashtag = data.departure + '&' + data.destination + '&' + data.date + '&' + data.cabin;
            ajexLoad("/panel/search#" + forge.util.encode64(hashtag))
        }
    })

    /* 強制更改密碼 */
    const modal = bootstrap.Modal.getOrCreateInstance($('#SetPass')[0])
    fetch('/panel/', {
        method: 'POST',
        redirect: 'error',
        headers: {
            'Content-Type': 'application/json; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    }).then((response) => {
        response.json().then((json) => {
            if (json.state) modal.show();
        });
    }).catch((error) => {
        console.log(error)
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
            data.passwordOld = forge.util.encode64(pk.encrypt("IVEairline!"))

            /* send */
            fetch('/panel/?type=PassSet', {
                method: 'POST',
                redirect: 'error',
                headers: {
                    'Content-Type': 'application/json; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            }).then((response) => {
                response.json().then((json) => {
                    if(json.code === 510){
                        toastr.success(json.Message, json.Title);
                        modal.hide();
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

    /* 檢查限制 */
    $('#Password, #Password2').on("input focus", function () {
        const Pass = $('#Password');
        const CPass = $('#Password2');

        /* 密碼必須一樣 */
        if (Pass.val() !== CPass.val()) CPass[0].setCustomValidity('error');
        else CPass[0].setCustomValidity('');
    })

    /* 密碼強度 */
    $('#Password').on("input focus", function () {
        const val = $('#Password').val();
        const input = [$('#username').text()];
        const result = zxcvbn(val, input).score;
        const pass = $('#passStrength');
        const list = $('#passStrength-list li > span');

        /* 指示器 */
        pass.css({width: (100 / 4) * result + "%"})
        pass.text(Lang.strength[result]);

        if (result === 1) pass.css({'background-color': 'var(--bs-danger)'});
        if (result === 2) pass.css({'background-color': 'var(--bs-warning'});
        if (result === 3) pass.css({'background-color': 'var(--bs-info)'});
        if (result === 4) pass.css({'background-color': 'var(--bs-success)'});

        /* 條件指示 */
        if (/[A-Z]+/.test(val)) $(list[0]).removeClass('bg-danger').addClass('bg-success');
        else $(list[0]).removeClass('bg-success').addClass('bg-danger');

        if (/[a-z]+/.test(val)) $(list[1]).removeClass('bg-danger').addClass('bg-success');
        else $(list[1]).removeClass('bg-success').addClass('bg-danger');

        if (val.length >= 8) $(list[2]).removeClass('bg-danger').addClass('bg-success');
        else $(list[2]).removeClass('bg-success').addClass('bg-danger');

        if (!input.includes(val)) $(list[3]).removeClass('bg-danger').addClass('bg-success');
        else $(list[3]).removeClass('bg-success').addClass('bg-danger');
    });
})