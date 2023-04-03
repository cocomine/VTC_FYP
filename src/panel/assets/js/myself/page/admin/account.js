/*
 * Copyright (c) 2022. 
 * Create by cocomine
 */

define([ 'jquery', 'toastr', 'bootstrap', 'full.jquery.crs.min', 'datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5' ], function (jq, toastr, bootstrap, crs){
    crs.init();
    const sex = { 1: "男", 0: "女" };

    /* dataTables */
    const table = $('#dataTable').DataTable({
        responsive: true,
        language: {
            loadingRecords: `<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>`,
            url: $('#datatables_lang_url').text()
        },
        order: [ [ 3, 'desc' ] ],
    });

    /* js資料 */
    const Lang = JSON.parse($('#LangJson').text());

    /* 增加帳號 */
    $('#add-ac').submit(function (e){
        console.log(this);
        if (!e.isDefaultPrevented() && this.checkValidity()){
            e.preventDefault();
            e.stopPropagation();
            const data = $(this).serializeObject();

            /* 封鎖按鈕 */
            const bt = $(this).find('.form-submit, .btn-secondary');
            const html = bt.html();
            bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

            /* send */
            fetch('/panel/admin/account/', {
                method: 'POST',
                redirect: 'error',
                headers: {
                    'Content-Type': 'application/json; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            }).then((response) => {
                response.json().then((json) => {
                    console.log(json); //debug

                    if (json.code === 207){
                        toastr.success(json.Message, json.Title);

                        //update table
                        const role = $(`#Role option[value="${data.role}"]`).text();
                        let row = [[
                            json.data.UUID,
                            `<a href='#' data-id='${json.data.UUID}' class='stretched-link'>${data.name}</a>`,
                            data.email,
                            '-', role,
                            '<span class="status-p bg-success">' + Lang.Activated + '</span>'
                        ]];
                        table.rows.add(row).draw();

                        $('#Name, #Email').val('');
                        $(this).removeClass('was-validated');
                    }else{
                        toastr.error(json.Message, json.Title);
                    }
                });
            }).finally(() => {
                bt.html(html).removeAttr('disabled');
            }).catch((error) => {
                console.log(error);
            });

        }
    });

    /* 查看帳戶詳情 */
    $('#dataTable').on('click', 'a[data-id]', function (e){
        e.preventDefault();
        const id = $(this).data('id');

        fetch('/panel/admin/account/?type=viewDetail', {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id })
        }).then(async (response) => {
            const json = await response.json();
            if (response.ok && json.code === 200){
                const data = json.data;

                //個人資料
                if (data.user_detail !== null){
                    $('#user-detail').show();
                    $('#user-detail-none').hide();

                    $('#lastname').val(data.user_detail.last_name);
                    $('#firstname').val(data.user_detail.first_name);
                    $('#phone').val('+' + data.user_detail.phone_code + ' ' + data.user_detail.phone);
                    $('#country').val(data.user_detail.country);
                    $('#sex').val(sex[data.user_detail.sex]);
                    $('#birth').val(data.user_detail.birth);
                }else{
                    $('#user-detail').hide();
                    $('#user-detail-none').show();
                }

                //組織資料
                if (data.organize_detail === false){
                    // 用戶是普通用戶
                    $('#organization-detail-tab').hide();
                    bootstrap.Tab.getOrCreateInstance($('#user-detail-tab')[0]).show();
                }else{
                    $('#organization-detail-tab').show();

                    if (data.organize_detail !== null){
                        $('#organize-detail').show();
                        $('#organize-detail-none').hide();

                        $('#organize-Name').val(data.organize_detail.organize);
                        $('#organize-phone').val('+' + data.organize_detail.phone_code + ' ' + data.organize_detail.phone);
                        $('#organize-country').val(data.organize_detail.country);
                        $('#organize-Address').val(data.organize_detail.address);
                        $('#organize-bankCode').val(data.organize_detail.SWIFTCode);
                        $('#organize-bankAccount').val(data.organize_detail.BankAccount);
                        $('#organize-prove').attr('href', "https://" + location.hostname + "/panel/api/media/" + data.organize_detail.prove);
                    }else{
                        $('#organize-detail').hide();
                        $('#organize-detail-none').show();
                    }
                }

                bootstrap.Modal.getOrCreateInstance($('#view-detail')[0]).show();
            }else{
                toastr.error(json.Message, json.Title ?? globalLang.Error);
            }
        }).catch((error) => {
            console.log(error);
        });
    });
});