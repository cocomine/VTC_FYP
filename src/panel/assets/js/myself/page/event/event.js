/*
 * Copyright (c) 2023. 
 * Create by cocomine
 */

define(['jquery', 'toastr', 'datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5'], function (jq, toastr){
    const table = $('#dataTable').DataTable({
        responsive: true,
        ajax: {
            url: '/panel/event/',
            type: 'POST',
            data: function ( d ) {
                return JSON.stringify(d)
            }
        },
        columns: [
            {data: 'name'},
            {data: 'type'},
            {data: 'tag'},
            {data: 'state'},
            {data: 'review', searchable: false},
            {data: 'post_time'},
        ]
    });
    table.column('post_time').order('desc').draw()
})