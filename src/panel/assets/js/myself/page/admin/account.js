/*
 * Copyright (c) 2022. 
 * Create by cocomine
 */

define(['jquery', 'toastr', 'bootstrap', 'datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5'], function (jq, toastr, bootstrap) {
    /* dataTables */
    const table = $('#dataTable').DataTable({
        responsive: true,
        language:{
            loadingRecords: `<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>`,
            url: $('#datatables_lang_url').text()
        },
        order: [[3, 'desc']],
    });

    /* js資料 */
    const Lang = JSON.parse($('#LangJson').text());

    /* 增加帳號 */
    $('#AddAC').submit(function (e) {
        if (!e.isDefaultPrevented() && this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            const data = $(this).serializeObject();

            /* 封鎖按鈕 */
            const bt = $(this).find('.form-submit, .btn-secondary');
            const html = bt.html();
            bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

            /* send */
            fetch('/panel/account/', {
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

                    if (json.code === 207) {
                        toastr.success(json.Message, json.Title);

                        //update table
                        const role = $(`#Role option[value="${data.role}"]`).text();
                        let row = [[json.data.UUID, data.name, data.email, '-', role, '<span class="status-p bg-success">' + Lang.Activated + '</span>']]
                        table.rows.add(row).draw();

                        $('#Name, #Email').val('');
                        $(this).removeClass('was-validated')
                    } else {
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

    bootstrap.Modal.getOrCreateInstance($('#view-detail')[0]).show()
})