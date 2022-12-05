/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

define(['jquery', 'toastr'], function (jq, toastr){

    /* 添加通知 */

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
                if (data.return) {
                    window.location.href = data.return;
                }
                if (data.status === 'Success') {
                    toastr.success(data.content, data.title);
                }
                if (data.status === 'Error') {
                    toastr.error(data.content, data.title);
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
        $('#list-notify').find('tbody').html("<tr><td> <div id='pre-submit-load' style='height: 40px; margin-top: -5px'> <div class='submit-load'><div></div><div></div><div></div><div></div></div> </div> </td></tr>")

        $.post({
            url: '/panel/notify/?type=ShowNotify',
            headers: {
                'AJAX': 'true'
            },
            data: JSON.stringify({uuid: val}),
            contentType: "text/json",
            success: function (data){
                if (data.status === 'Error') {
                    toastr.error(data.content, data.title);
                    return
                }
                if (data.status === 'Warning') {
                    toastr.warning(data.content, data.title);
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