/*
 * Copyright (c) 2023. 
 * Create by cocomine
 */

define(['jquery', 'datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5'], function () {

    /* load user list */
    $('#dataTable').dataTable({
        responsive: true,
        ajax: {
            url: location.pathname + '/',
            type: 'POST',
            data: function (d) {
                return JSON.stringify(d);
            }
        },
        language: {
            url: $('#datatables_lang_url').text()
        },
        order: [
            [1, 'desc']
        ],
        columns: [
            {
                data: 'Name',
                render: function (data, type, row) {
                    if (type === 'display') {
                        return `<a href="javascript:void(0)" data-id="${row.ID}">${data} (${row.full_name})</a>`;
                    } else {
                        return data + ";" + row.full_name;
                    }
                }
            },
            {data: "book_date"},
            {
                data: "plan",
                render: function (data, type) {
                    if (type === 'display') {
                        return data ? data.map((value) => `<b>${value.plan_name}:</b> <code class="bg-light">${value.plan_people}</code>`).join('<br>') : "沒有任何活動計劃";
                    } else {
                        return data ? data.map((value) => value.plan_name + ',' + value.plan_people).join(';') : "沒有任何活動計劃";
                    }
                }
            }
        ]
    });

    /* show user more info */
    $('a[data-id]').click(function (e) {

    });
});