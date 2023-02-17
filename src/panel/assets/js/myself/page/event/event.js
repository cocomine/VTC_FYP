/*
 * Copyright (c) 2023. 
 * Create by cocomine
 */

define([ 'jquery', 'toastr', 'datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5' ], function (jq, toastr){
    const table = $('#dataTable').DataTable({
        responsive: true,
        ajax: {
            url: '/panel/event/',
            type: 'POST',
            data: function (d){
                return JSON.stringify(d);
            }
        },
        language: {
            url: $('#datatables_lang_url').text()
        },
        order: [
            [ 3, 'desc' ]
        ],
        columns: [
            {
                data: 'name',
                render: (data, type, row) => {
                    if (type === 'display'){
                        return `<div class="row">
                                    <div class="col-auto">
                                        <a href="/panel/event/post/${row.ID}">
                                            <div class="ratio ratio-16x9" style="width: 160px;">
                                                <img src="/panel/api/media/${row.thumbnail}" alt="${row.thumbnail}" class="w-auto mh-100 h-auto">
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col">
                                        <a href="/panel/event/post/${row.ID}">${data}</a><br>
                                        <p class="text-secondary">${row.summary}</p>
                                    </div>
                                </div>`;
                    }else{
                        return data + ';' + row.summary;
                    }
                }
            },
            {
                data: 'type',
                render: (data) =>
                    data === 0 ? '水上活動' : data === 1 ? '陸上活動' : '空中活動'

            },
            { data: 'tag' },
            { data: 'post_time' },
            {
                data: 'state',
                searchable: false,
                render: (data, type) => {
                    if (type === 'display'){
                        if (data === 0) return `<span class="status-p bg-info text-center">排程</span>`;
                        if (data === 1) return `<span class="status-p bg-primary text-center">公開</span>`;
                        if (data === 2) return `<span class="status-p bg-secondary text-center">不公開</span>`;
                    }else{
                        if (data === 0) return `排程`;
                        if (data === 1) return `公開`;
                        if (data === 2) return `不公開`;
                    }
                }
            },
            {
                data: 'review',
                searchable: false,
                render: (data, type) => {
                    if (type === 'display'){
                        if (data === 0) return `<span class="status-p bg-secondary text-center">未審核</span>`;
                        if (data === 1) return `<span class="status-p bg-success text-center">已審核</span>`;
                        if (data === 2) return `<span class="status-p bg-danger text-center">未通過</span>`;
                    }else{
                        if (data === 0) return `未審核`;
                        if (data === 1) return `已審核`;
                        if (data === 2) return `未通過`;
                    }
                }
            },
        ]
    });
});