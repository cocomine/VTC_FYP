/*
 * Copyright (c) 2023. 
 * Create by cocomine
 */

define([ 'jquery', 'datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5' ], function (){
    $('#dataTable').dataTable({
        responsive: true,
        ajax: {
            url: location.pathname + '/',
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
                data: 'Name',

            },
            {
                data: "book_date",
            },
            {
                data: "plan",
            }
        ]
    });
});