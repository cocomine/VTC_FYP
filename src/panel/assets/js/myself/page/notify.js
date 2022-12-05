/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

define(['jquery', 'toastr'], function (jq, toastr){

    /* 添加通知 */
    $('form').submit(function (e) {
        if (!e.isDefaultPrevented() && this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            const data = $(this).serializeObject();

            /* 封鎖按鈕 */
            const bt = $(this).children('.form-submit');
            const html = bt.html();
            bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

            /* send */
            fetch('/panel/notify?type=sendNotify', {
                method: 'POST',
                redirect: 'error',
                headers: {
                    'Content-Type': 'application/json; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            }).then((response) => {
                response.json().then((json) => {

                    if (json.code === 200) {
                        toastr.success(json.Message);
                        $(this).removeClass('was-validated')
                    } else {
                        toastr.error(json.Message);
                    }
                })
            }).finally(() => {
                bt.html(html).removeAttr('disabled');
            }).catch((error) => {
                console.log(error)
            })
        }
    });

    /* 刪除通知 */
    $('#list-notify').on('click', 'a[data-action="DeleteNotify"]', function (e) {
        e.preventDefault();
        let id = $(this).attr('data-row-id')
        $.post({
            url: '/panel/notify/?type=DelNotify',
            headers: {
                'AJAX': 'true'
            },
            data: JSON.stringify({id: id}),
            contentType: "text/json",
            success: function (data) {
                if (data.code === 200) {
                    toastr.success(data.Message);
                }
                if (data.code === 500) {
                    toastr.error(data.Message);
                }
                if (data.modal !== true) {
                    setTimeout(function () {
                        const val = $('#uuid').find(':selected').val();
                        load_table(val);
                    }, 100);
                }
            }
        })
    });

    /* 即時預覽圖標 */
    $('#Icon').on("input focus", function (){
        const val = $(this).val();
        $('#Icon-show').attr('class', val);
    })

    /* 自動偵測選擇User */
    $('#uuid').change(function (){
        const val = $(this).find(':selected').val();
        load_table(val);
    });

    function load_table(val){
        $('#list-notify').find('tbody').html("<tr><td colspan='6'> <div id='pre-submit-load' style='height: 40px; margin-top: -5px'> <div class='submit-load'><div></div><div></div><div></div><div></div></div> </div> </td></tr>")

        $.post({
            url: '/panel/notify/?type=ShowNotify',
            headers: {
                'AJAX': 'true'
            },
            data: JSON.stringify({uuid: val}),
            contentType: "text/json",
            success: function (data){
                if (data.code === 500) {
                    toastr.error(data.Message);
                    return
                }
                if (data.code === 200) {
                    toastr.warning(data.Message);
                    return
                }

                let table = '';
                data.forEach(function (raw){
                    const data = new Date(raw.Time*1000);
                    const tr = `<tr>
                                    <td>${raw.notifyID}</td>
                                    <td><div class="notify-thumb">${raw.icon}</div></td>
                                    <td>${raw.Msg}</td>
                                    <td>${raw.link}</td>
                                    <td>${data.toLocaleString()}</td>
                                    <td>
                                        <ul class="d-flex justify-content-center">
                                            <li><a href="#" class="text-danger" data-action="DeleteNotify" data-row-id="${raw.notifyID}"><i class="ti-trash"></i></a></li>
                                        </ul>
                                    </td>
                                </tr>`;
                    table += tr;
                });
                $('#list-notify').find('tbody').html(table);
            }
        });
    }
});