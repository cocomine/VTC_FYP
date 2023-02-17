/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

define(['jquery', 'toastr'], function (jq, toastr) {

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
            fetch('/panel/admin/notify_mg/?type=sendNotify', {
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

        /* send */
        fetch('/panel/admin/notify_mg/?type=DelNotify', {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({id: id})
        }).then((response) => {
            response.json().then((json) => {

                if (json.code === 200) {
                    toastr.success(json.Message);
                    setTimeout(function () {
                        const val = $('#uuid').find(':selected').val();
                        load_table(val);
                    }, 100);
                } else {
                    toastr.error(json.Message);
                }
            })
        }).catch((error) => {
            console.log(error)
        })
    });

    /* 即時預覽圖標 */
    $('#Icon').on("input focus", function () {
        const val = $(this).val();
        $('#Icon-show').attr('class', val);
    })

    /* 自動偵測選擇User */
    $('#uuid').change(function () {
        const val = $(this).find(':selected').val();
        load_table(val);
    });

    /* 載入通知列表 */
    function load_table(val) {
        $('#list-notify').find('tbody').html("<tr><td colspan='6'> <div id='pre-submit-load' style='height: 40px; margin-top: -5px'> <div class='submit-load'><div></div><div></div><div></div><div></div></div> </div> </td></tr>")

        /* send */
        fetch('/panel/admin/notify_mg/?type=ShowNotify', {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({uuid: val})
        }).then((response) => {
            response.json().then((json) => {

                if (json.code === 200) {
                    let table = '';
                    json.data.forEach(function (raw) {
                        const tr = `<tr>
                                    <td>${raw.notifyID}</td>
                                    <td><div class="notify-thumb">${raw.icon}</div></td>
                                    <td>${raw.Msg}</td>
                                    <td>${raw.link}</td>
                                    <td>${raw.Time}</td>
                                    <td>
                                        <ul class="d-flex justify-content-center">
                                            <li><a href="#" class="text-danger" data-action="DeleteNotify" data-row-id="${raw.notifyID}"><i class="ti-trash"></i></a></li>
                                        </ul>
                                    </td>
                                </tr>`;
                        table += tr;
                    });
                    $('#list-notify').find('tbody').html(table);

                } else if (json.code === 201) {
                    toastr.warning(json.Message);
                } else {
                    toastr.error(json.Message);
                }
            })
        }).catch((error) => {
            console.log(error)
        })
    }
});