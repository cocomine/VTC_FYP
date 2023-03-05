/*
 * Copyright (c) 2023. 
 * Create by cocomine
 */

define([ 'jquery', 'toastr', 'moment', 'datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5' ], function (jq, toastr, moment){
    const dataTable_Options = {
        ajax: {
            url: location.pathname + '/',
            type: 'POST',
            data: function (d){
                return JSON.stringify(d);
            },
        },
        responsive: true,
        language: {
            url: $('#datatables_lang_url').text()
        },
        columns: [
            {
                data: 'Name',
                render: function (data, type, row){
                    if (type === 'display'){
                        return `<a href="#" data-id="${row.ID}">${data} (${row.full_name})</a>`;
                    }else{
                        return data + ";" + row.full_name;
                    }
                }
            },
            { data: "book_date" },
            {
                data: "plan",
                render: function (data, type){
                    if (type === 'display'){
                        return data ? data.map((value) => `<b>${value.plan_name}:</b> <code class="bg-light">${value.plan_people}</code>`).join('<br>') : "沒有任何活動計劃";
                    }else{
                        return data ? data.map((value) => value.plan_name + ',' + value.plan_people).join(';') : "沒有任何活動計劃";
                    }
                }
            }
        ]
    };
    const country = { HK: "香港", TW: "台灣", MO: "澳門", CN: "中國大陸" };
    const sex = { 1: "男", 0: "女" };

    /* load user list */
    $('#dataTable').dataTable({
        ...dataTable_Options,
        ajax: {
            ...dataTable_Options.ajax,
            dataSrc: function (json){
                return json.data.filter((data) => {
                    const book_date = moment(data.book_date);
                    return moment().isSameOrBefore(book_date);
                });
            }
        },
        order: [
            [ 1, 'asc' ]
        ],
    });

    /* 過去預約用戶 */
    $('#dataTable2').dataTable({
        ...dataTable_Options,
        ajax: {
            ...dataTable_Options.ajax,
            dataSrc: function (json){
                return json.data.filter((data) => {
                    const book_date = moment(data.book_date);
                    return moment().isAfter(book_date);
                });
            }
        },
        order: [
            [ 1, 'desc' ]
        ],
    });

    /* show user more info */
    $("#dataTable, #dataTable2").on('click', 'a[data-id]', function (e){
        e.preventDefault();
        const id = $(this).data('id');

        fetch('/panel/reserve/?type=detail', {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id })
        }).then((response) => {
            response.ok && response.json().then((json) => {
                if (json.code === 200){
                    $('[data-select]').remove();
                    $('[data-detail]').show();
                    const data = json.data;

                    //load data
                    $('#lastname').val(data.last_name);
                    $('#firstname').val(data.first_name);
                    $('#email').val(data.Email);
                    $('#phone').val('+' + data.phone_code + ' ' + data.phone);
                    $('#country').val(country[data.country]);
                    $('#sex').val(sex[data.sex]);
                    $('#birth').val(data.birth);

                    $('#reserve_date').text(moment(data.book_date).format('YYYY/M/DD'));
                    $('#order_time').text(data.order_datetime);
                    $('#reserve_detail').html(data.plan.map((value) => {
                        return `<tr>
                                    <td>${value.plan_name}</td>  
                                    <td>${value.start_time}<i class="fa-solid fa-angles-right mx-2"></i>${value.end_time}</td>
                                    <td>${value.plan_people}</td>
                                </tr>`
                    }));

                }else{
                    toastr.error(json.Message, json.Title);
                }
            });
        }).catch((error) => {
            console.log(error);
        });
    });
});