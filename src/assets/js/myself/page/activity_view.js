
define([ 'jquery', 'toastr', 'moment', 'datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5' ], function (jq, toastr, moment){
    const table = $('#dataTable').DataTable({
        responsive: true,
        ajax: {
            url: '/activity_view',
            type: 'POST',
            data: function (d){
                return JSON.stringify(d);
            }
        },
        language: {
            loadingRecords: `<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>`,
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
                        return data ? data.map((value) => `<b>${value.plan_name}:</b> <code class="bg-light">${value.pay_price}</code>`).join('<br>') : "沒有任何活動計劃"
                    }else{
                        return data ? data.map((value) => value.plan_name + ',' +value.pay_price).join(';') : "沒有任何活動計劃";
                    }
                }
            },
        ]
    });
});