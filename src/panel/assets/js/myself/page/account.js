/*
 * Copyright (c) 2022. 
 * Create by cocomine
 */

define(['jquery', 'toastr', 'datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5'], function (jq, toastr) {
    const table = $('#dataTable').DataTable({responsive: true});
    table.column('3').order('desc').draw()

    $('#AddAC').submit(function (e) {
        if (!e.isDefaultPrevented() && this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            const data = $(this).serializeObject();

            /* 封鎖按鈕 */
            const bt = $(this).children('.form-submit');
            const html = bt.html();
            bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

            /* send */
            fetch('/panel/account', {
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

                    if(json.code === 207){
                        toastr.success(json.Message);
                        ajexLoad('/panel/account/', false);
                    }else{
                        toastr.error(json.Message);
                    }
                })
            }).finally(() => {
                bt.html(html).removeAttr('disabled');
            }).catch((error) => {
                console.log(error)
            })

        }
    })
})