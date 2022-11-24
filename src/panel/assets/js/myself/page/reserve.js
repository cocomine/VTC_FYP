/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'toastr', 'bootstrap', 'datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5'], function (jq, toastr, bootstrap) {
    /* datatables */
    const thead = $('#dataTable thead tr:first-child th')
    const table = $('#dataTable').DataTable({responsive: true});
    table.column('1').order('desc').draw()

    /* js資料 */
    const Lang = JSON.parse($('#LangJson').text());

    /* tooltips */
    function initTooltips() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        tooltipTriggerList.forEach(tooltipTriggerEl => bootstrap.Tooltip.getOrCreateInstance(tooltipTriggerEl))
    }
    initTooltips();

    /* responsive table head */
    table.on('responsive-resize', function (e, datatable, columns) {
        let count = columns.reduce((prev, col) => col === false ? prev + 1 : prev, 0);

        console.log(count)
        for (let i = thead.length - 1; i >= 0; i--) {
            let col = $(thead[i]);
            if (count > 0) col.hide();
            else col.show();
            count--;
        }

    });

    /* Modal */
    const editModal = bootstrap.Modal.getOrCreateInstance($('#editModal')[0]);
    const Confirm_modal = bootstrap.Modal.getOrCreateInstance($('#Confirm-modal')[0]);
    const Delete_modal = bootstrap.Modal.getOrCreateInstance($('#Delete-modal')[0]);

    /* edit get info */
    let md = {id: null}
    $("#dataTable tbody").on("click", "[data-action=\"edit\"]", function (e) {
        md.id = $(this).data('id')
        editModal.show();

        // 取得資料
        fetch('/panel/reserve/', {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                type: 'info', data: md
            })
        }).then(async function (response) {
            const json = await response.json();

            if (json.code === 200) {
                //有資料
                md = {
                    ...md,
                    ...json.data
                }

                console.log(md)
                const modal = $(editModal._element);
                modal.find('#flight').text(md.Flight);
                modal.find('#Business-count').text(md.Business);
                modal.find('#Economy-count').text(md.Economy);
                modal.find('#Meal').prop('checked', md.Meal);
                modal.find('#Save').prop('disabled', false)
                modal.find('#total').text('$ ' + formatPrice(md.BusinessPrice * md.Business + md.EconomyPrice * md.Economy))
            } else {
                editModal.hide();
                toastr.error(json.Message, json.Title)
            }
        }).catch((error) => {
            console.log(error);
        })
    })

    /* 前往取消確認介面 */
    .on("click", "[data-action=\"delete\"]", function (e) {
        md.id = $(this).data('id')
        Delete_modal.show();
    })

    /* 確認取消 */
    $('#delete').click(function () {
        /* 封鎖按鈕 */
        const bt = $(this)
        const html = bt.html();
        bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

        fetch(location.href, {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                type: 'delete', data: {...md}
            })
        }).then(async function (response) {
            const json = await response.json();

            if (json.code === 200) {
                toastr.success(json.Message, json.Title);
                Delete_modal.hide();

                //update table
                table.row($(`[data-id="${md.id}"]`).parents('tr')).remove().draw();
            } else {
                toastr.error(json.Message, json.Title);
                Delete_modal.hide();
            }
        }).catch((error) => {
            console.log(error)
        }).finally(() => {
            bt.html(html).removeAttr('disabled');
        });
    })

    /* 調整預定數量 */
    $("[data-reserve]").click(function () {
        const type = $(this).data('reserve');
        if (type === "Business-add" && md.Business + 1 <= md.LastBusiness) md.Business++;
        if (type === "Business-sub" && md.Business - 1 >= 0) md.Business--;
        if (type === "Economy-add" && md.Economy + 1 <= md.LastEconomy) md.Economy++;
        if (type === "Economy-sub" && md.Economy - 1 >= 0) md.Economy--;

        // show count
        $('#Economy-count').text(md.Economy);
        $('#Business-count').text(md.Business);
        $('#total').text('$ ' + formatPrice(md.BusinessPrice * md.Business + md.EconomyPrice * md.Economy))
    })

    /* 前往修改確認介面 */
    $('#Save').click(function () {
        if (md.Business > 0 || md.Economy > 0) {
            const modal = $(Confirm_modal._element);
            modal.find('#Confirm-Business').text(md.Business);
            modal.find('#Confirm-Economy').text(md.Economy);
            modal.find('#Confirm-meal').text(md.meal ? Lang.Need_reserve : Lang.No_Need_reserve)
            editModal.hide()
            Confirm_modal.show()
        } else {
            $('#Reserve').addClass('card-highlight')
            setTimeout(() => {
                $('#Reserve').removeClass('card-highlight')
            }, 1000)
        }
    });

    /* Meal 切換 */
    $('#Meal').change(() => md.Meal = this.checked);

    /* 確認修改 */
    $('#confirm').click(async function () {
        /* 封鎖按鈕 */
        const bt = $(this)
        const html = bt.html();
        bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');

        /* send */
        const response = await fetch(location.href, {
            method: 'POST',
            redirect: 'error',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                type: 'edit', data: {...md}
            })
        }).catch((error) => {
            console.log(error)
        }).finally(() => {
            bt.html(html).removeAttr('disabled');
        });

        const json = await response.json();
        if (json.code === 200) {
            toastr.success(json.Message, json.Title);
            Confirm_modal.hide();

            //update table
            let row = table.row($(`[data-id="${md.id}"]`).parents('tr'))

            //update display
            let rowData = row.data();
            rowData[4] = md.Business;
            rowData[5] = md.Economy;
            rowData[6] = mealStatus(md.Meal);
            rowData[7] = '$ ' + formatPrice(md.BusinessPrice * md.Business + md.EconomyPrice * md.Economy);
            row.data(rowData).draw();
            initTooltips();
        } else {
            toastr.error(json.Message, json.Title);
            Confirm_modal.hide();
        }
    });

    /* Meal status */
    const mealStatus = (state) => state ? '<span class="status-p bg-success"><i class="fa-solid fa-check"></i></span>' : '<span class="status-p bg-danger"><i class="fa-solid fa-xmark"></i></span>';
});