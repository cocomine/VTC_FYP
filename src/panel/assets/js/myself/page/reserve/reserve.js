/*
 * Copyright (c) 2023. 
 * Create by cocomine
 */

define([ 'jquery', 'toastr', 'datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5' ], function (jq, toastr){
    const table = $('#dataTable').DataTable({
        responsive: true,
        ajax: {
            url: '/panel/reserve/',
            type: 'POST',
            data: function (d){
                return JSON.stringify(d);
            }
        },
        language: {
            url: $('#datatables_lang_url').text()
        },
        order: [
            [ 1, 'desc' ]
        ],
        columns: [
            {
                data: 'name',
                render: (data, type, row) => {
                    if (type === 'display'){
                        return `<div class="row">
                                    <div class="col-auto">
                                        <a href="/panel/reserve/${row.ID}">
                                            <div class="ratio ratio-16x9" style="width: 160px;">
                                                <img src="/panel/api/media/${row.thumbnail}" alt="${row.thumbnail}" class="w-auto mh-100 h-auto">
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col">
                                        <a href="/panel/reserve/${row.ID}">${data}</a><br>
                                        <p class="text-secondary" style="max-width: 300px">${row.summary}</p>
                                    </div>
                                </div>`;
                    }else{
                        return data + ';' + row.summary;
                    }
                }
            },
            {
                data: 'plan',
                render: (data, type) => {
                    if (type === 'display'){
                        console.log(data)

                        return data ? data.map((value) => `<b>${value.plan_name}:</b> <code>${value.total}</code>`).join('<br>') : "沒有任何活動計劃"
                    }else{
                        return data ? data.map((value) => value.plan_name + ',' +value.total).join(';') : "沒有任何活動計劃";
                    }
                }
            },
        ]
    });
});