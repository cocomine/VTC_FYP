/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['forge', 'jquery'], function (forge) {
    "use strict";

    /* 遞交表單 */
    $('#Login').submit(async function (e) {
        if (!e.isDefaultPrevented()  && this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            const data = $(this).serializeObject();

            /* 封鎖按鈕 */
            const bt = $('#form_submit');
            const html = bt.html();
            bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

            /* 加密 */
            const KeyResponse = await fetch('/panel/key.php')
            const key = await KeyResponse.text();
            const pk = forge.pki.publicKeyFromPem(key);
            data.password = forge.util.encode64(pk.encrypt(data.password))

            /* send */
            fetch('/panel/login', {
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
                        location.replace('.')
                    }else if(json.code === 100){
                        ResultMsg.html('<div class="alert alert-warning" role="alert">' + json.Message +'</div>')
                    }else if(json.code === 108){
                        location.reload();
                    }else{
                        ResultMsg.html('<div class="alert alert-danger" role="alert">' + json.Message +'</div>')
                    }
                })
            }).finally(() => {
                bt.html(html).removeAttr('disabled');
                $('#2FA_Code').val('')
            }).catch((error) => {
                console.log(error)
            })
        }
    })

    /* 2FA */
    $('#2FA').submit(async function (e) {
        if (!e.isDefaultPrevented() && this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            const data = $(this).serializeObject();

            /* 封鎖按鈕 */
            const bt = $('#form_submit');
            const html = bt.html();
            bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

            /* send */
            fetch('/panel/login', {
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
                        location.replace('.')
                    }else if(json.code === 103){
                        ResultMsg.html('<div class="alert alert-warning" role="alert">' + json.Message +'</div>')
                        setTimeout(() => {
                            location.reload()
                        }, 2000)
                    }else{
                        ResultMsg.html('<div class="alert alert-danger" role="alert">' + json.Message +'</div>')
                    }
                })
            }).finally(() => {
                bt.html(html).removeAttr('disabled');
                $('#Password').val('')
            }).catch((error) => {
                console.log(error)
            })
        }
    })

    $('#2FA_Code').on('input focus', function (e) {
        if($(this).val().length >= 6) $('#2FA').submit();
    })
})