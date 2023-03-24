/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define([ 'jquery', 'toastr', 'chartjs', 'moment'], function (jq, toastr, Chart, moment){
    "use strict";
    $('.today-order-list').scrollbar();
    const country = { HK: "香港", TW: "台灣", MO: "澳門", CN: "中國大陸" };

    /* 數據統計 */
    fetch('/panel/?type=count', {
        method: 'POST',
        redirect: 'error',
        headers: {
            'Content-Type': 'application/json; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    }).then(async (response) => {
        const json = await response.json();
        if (response.ok && json.code === 200){
            //console.log(json);

            // 本年賺取
            new Chart($('#year-earned-chart')[0].getContext('2d'), {
                // The type of chart we want to create
                type: 'line',
                // The data for our dataset
                data: {
                    labels: json.data.year.map((item) => item.text),
                    datasets: [{
                        label: "金額",
                        backgroundColor: "rgba(104, 124, 247, 0.6)",
                        borderColor: 'rgba(255,255,255,0.3)',
                        data: json.data.year.map((item) => item.total),
                    }]
                },
                // Configuration options go here
                options: {
                    plugins: {
                        legend: {
                            display: false
                        },
                    },
                    animation: {
                        easing: "easeInOutBack"
                    },
                    scales: {
                        y: {
                            display: !1,
                            ticks: {
                                fontColor: "rgba(0,0,0,0.5)",
                                fontStyle: "bold",
                                beginAtZero: !0,
                                maxTicksLimit: 5,
                                padding: 0
                            },
                            gridLines: {
                                drawTicks: !1,
                                display: !1
                            }
                        },
                        x: {
                            display: !1,
                            gridLines: {
                                zeroLineColor: "transparent"
                            },
                            ticks: {
                                padding: 0,
                                fontColor: "rgba(0,0,0,0.5)",
                                fontStyle: "bold"
                            }
                        }
                    },
                    elements: {
                        line: {
                            tension: 0, // disables bezier curves
                        }
                    }
                }
            });
            $('#year-earned').text(nFormatter(json.data.year.map((item) => item.total).reduce((a, b) => a + b, 0), 1));

            // 本年預約
            new Chart($('#year-order-chart')[0].getContext('2d'), {
                // The type of chart we want to create
                type: 'line',
                // The data for our dataset
                data: {
                    labels: json.data.year.map((item) => item.text),
                    datasets: [ {
                        label: "預約數",
                        backgroundColor: "rgba(82,204,173,0.6)",
                        borderColor: 'rgba(255,255,255,0.3)',
                        data: json.data.year.map((item) => item.count),
                    } ]
                },
                // Configuration options go here
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    animation: {
                        easing: "easeInOutBack"
                    },
                    scales: {
                        y: {
                            display: !1,
                            ticks: {
                                fontColor: "rgba(0,0,0,0.5)",
                                fontStyle: "bold",
                                beginAtZero: !0,
                                maxTicksLimit: 5,
                                padding: 0
                            },
                            gridLines: {
                                drawTicks: !1,
                                display: !1
                            }
                        },
                        x: {
                            display: !1,
                            gridLines: {
                                zeroLineColor: "transparent"
                            },
                            ticks: {
                                padding: 0,
                                fontColor: "rgba(0,0,0,0.5)",
                                fontStyle: "bold"
                            }
                        }
                    },
                    elements: {
                        line: {
                            tension: 0, // disables bezier curves
                        }
                    }
                }
            });
            $('#year-order').text(nFormatter(json.data.year.map((item) => item.count).reduce((a, b) => a + b, 0), 1))

            // 本月賺取
            new Chart($('#month-earned-chart')[0].getContext('2d'), {
                // The type of chart we want to create
                type: 'line',
                // The data for our dataset
                data: {
                    labels: json.data.month.map((item) => item.text),
                    datasets: [ {
                        label: "金額",
                        backgroundColor: "rgba(204,146,143,0.6)",
                        borderColor: 'rgba(255,255,255,0.3)',
                        data: json.data.month.map((item) => item.total),
                    } ]
                },
                // Configuration options go here
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    animation: {
                        easing: "easeInOutBack"
                    },
                    scales: {
                        y: {
                            display: !1,
                            ticks: {
                                fontColor: "rgba(0,0,0,0.5)",
                                fontStyle: "bold",
                                beginAtZero: !0,
                                maxTicksLimit: 5,
                                padding: 0
                            },
                            gridLines: {
                                drawTicks: !1,
                                display: !1
                            }
                        },
                        x: {
                            display: !1,
                            gridLines: {
                                zeroLineColor: "transparent"
                            },
                            ticks: {
                                padding: 0,
                                fontColor: "rgba(0,0,0,0.5)",
                                fontStyle: "bold"
                            }
                        }
                    },
                    elements: {
                        line: {
                            tension: 0, // disables bezier curves
                        }
                    }
                }
            });
            $('#month-earned').text(nFormatter(json.data.month.map((item) => item.total).reduce((a, b) => a + b, 0), 1))

            // 本月預約
            new Chart($('#month-order-chart')[0].getContext('2d'), {
                // The type of chart we want to create
                type: 'line',
                // The data for our dataset
                data: {
                    labels: json.data.month.map((item) => item.text),
                    datasets: [ {
                        label: "預約數",
                        backgroundColor: "rgba(230,203,115,0.6)",
                        borderColor: 'rgba(255,255,255,0.3)',
                        data: json.data.month.map((item) => item.count),
                    } ]
                },
                // Configuration options go here
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    animation: {
                        easing: "easeInOutBack"
                    },
                    scales: {
                        y: {
                            display: !1,
                            ticks: {
                                fontColor: "rgba(0,0,0,0.5)",
                                fontStyle: "bold",
                                beginAtZero: !0,
                                maxTicksLimit: 5,
                                padding: 0
                            },
                            gridLines: {
                                drawTicks: !1,
                                display: !1
                            }
                        },
                        x: {
                            display: !1,
                            gridLines: {
                                zeroLineColor: "transparent"
                            },
                            ticks: {
                                padding: 0,
                                fontColor: "rgba(0,0,0,0.5)",
                                fontStyle: "bold"
                            }
                        }
                    },
                    elements: {
                        line: {
                            tension: 0, // disables bezier curves
                        }
                    }
                }
            });
            $('#month-order').text(nFormatter(json.data.month.map((item) => item.count).reduce((a, b) => a + b, 0), 1))
        }else{
            toastr.error(json.Message, json.Title ?? globalLang.Error);
        }
    }).catch((error) => {
        console.log(error);
    });

    /* 今日預約 */
    fetch('/panel/?type=today', {
        method: 'POST',
        redirect: 'error',
        headers: {
            'Content-Type': 'application/json; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    }).then(async (response) => {
        const json = await response.json();
        if (response.ok && json.code === 200){
            const data = json.data;
            const jq_today_order = $('#today-order');
            //console.log(json);

            if(data.length <= 0){
                jq_today_order.html('<tr><td colspan="5"><div class="text-center text-muted">今日無預約</div></td></tr>');
                return;
            }

            jq_today_order.html(data.map((item) =>
                `<tr class="position-relative">
                    <td>${item.ID}</td>
                    <td>
                        <a href="/panel/reserve/${item.event_ID}#${item.ID}" class="stretched-link">${item.Name} (${item.last_name} ${item.first_name})</a>
                    </td>
                    <td>${item.eventName}</td>
                    <td>
                        ${item.plan.map((plan) => 
                            `<b>${plan.plan_name}:</b> <code class="bg-light">${plan.plan_people}</code>`
                        ).join('<br>')}
                    </td>
                    <td>
                        ${item.plan.map((plan) => 
                            `${plan.start_time}<i class="fa-solid fa-angles-right mx-2"></i>${plan.end_time}`
                        ).join('<br>')}
                    </td>
                </tr>`
            ));
        }else{
            toastr.error(json.Message, json.Title ?? globalLang.Error);
        }
    }).catch((error) => {
        console.log(error);
    });

    /* 顧客國家/地區 */
    fetch('/panel/?type=country', {
        method: 'POST',
        redirect: 'error',
        headers: {
            'Content-Type': 'application/json; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    }).then(async (response) => {
        const json = await response.json();
        if (response.ok && json.code === 200){
            const data = json.data;
            const jq_country = $('#country');
            //console.log(json);

            if(data.length <= 0){
                jq_country.replaceWith('<div class="text-muted py-5 text-center">無資料</div>');
                return;
            }

            new Chart(jq_country[0].getContext('2d'), {
                // The type of chart we want to create
                type: 'doughnut',
                // The data for our dataset
                data: {
                    labels: data.map((item) => country[item.country]),
                    datasets: [{
                        hoverBackgroundColor: [
                            "#8919FE",
                            "#12C498",
                            "#F8CB3F",
                            "#e34444"
                        ],
                        backgroundColor: [
                            "rgba(137,25,254,0.8)",
                            "rgba(18,196,152,0.8)",
                            "rgba(248,203,63,0.8)",
                            "rgba(227,68,68,0.8)"
                        ],
                        borderColor: "#fff",
                        hoverBorderColor: "#fff",
                        hoverOffset: 8,
                        data: data.map((item) => item.count),
                    }]
                },
                // Configuration options go here
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                    },
                    animation: {
                        easing: "easeInOutBack"
                    }
                }
            });
        }else{
            toastr.error(json.Message, json.Title ?? globalLang.Error);
        }
    }).catch((error) => {
        console.log(error);
    });

    /* 最熱門活動 */
    fetch('/panel/?type=top', {
        method: 'POST',
        redirect: 'error',
        headers: {
            'Content-Type': 'application/json; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    }).then(async (response) => {
        const json = await response.json();
        if (response.ok && json.code === 200){
            const data = json.data;
            const jq_heatEvent = $('#heat-event');
            //console.log(json);

            if(data.length <= 0){
                jq_heatEvent.replaceWith('<div class="text-muted py-5 text-center">無資料</div>');
                return;
            }

            new Chart(jq_heatEvent[0].getContext('2d'), {
                // The type of chart we want to create
                type: 'bar',
                // The data for our dataset
                data: {
                    labels: data.map((item) => item.Name),
                    datasets: [{
                        hoverBackgroundColor: [
                            "#8919FE",
                            "#12C498",
                            "#F8CB3F",
                            "#e34444",
                            "#19c1fe",
                        ],
                        backgroundColor: [
                            "rgba(137,25,254,0.8)",
                            "rgba(18,196,152,0.8)",
                            "rgba(248,203,63,0.8)",
                            "rgba(227,68,68,0.8)",
                            "rgba(25,193,254,0.8)",
                        ],
                        borderColor: "#fff",
                        hoverBorderColor: "#fff",
                        hoverOffset: 8,
                        label: '預約數',
                        data: data.map((item) => item.count),
                        barPercentage : 0.5,
                        categoryPercentage: 0.5,
                    }]
                },
                // Configuration options go here
                options: {
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        },
                    },
                    scales:{
                        y: {
                            grid: {
                                display: false,
                            },
                        },
                        x: {
                            ticks:{
                                stepSize: 1,
                            }
                        }
                    },
                    animation: {
                        easing: "easeInOutBack"
                    }
                }
            });
        }else{
            toastr.error(json.Message, json.Title ?? globalLang.Error);
        }
    }).catch((error) => {
        console.log(error);
    });

    /* 最近三日評論 */
    fetch('/panel/?type=comment', {
        method: 'POST',
        redirect: 'error',
        headers: {
            'Content-Type': 'application/json; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    }).then(async (response) => {
        const json = await response.json();
        if (response.ok && json.code === 200){
            const data = json.data;
            const jq_comment = $('#comment');
            //console.log(json);

            if(data.length <= 0){
                jq_comment.replaceWith('<div class="text-light py-5 text-center">最近沒有評論</div>');
                return;
            }

            jq_comment.html(data.map((item) =>
                `<div class="item">
                    <div class="rounded-circle overflow-hidden float-start me-2" style="max-width: 60px; height: auto">
                        <a href="https://${location.hostname}/activity_details/${item.event_ID}" class="stretched-link"><img class="owl-lazy" data-src="https://www.gravatar.com/avatar/${item.Email}?s=200" alt="avatar"></a>
                    </div>
                    <div class="text-light">
                        <h5><b>${item.Name}</b></h5>
                        <div>
                            ${Array.from({length: item.rate}, () => `<i class="fa-solid fa-star text-warning"></i>`).join('')}
                            ${Array.from({length: 5-item.rate}, () => `<i class="fa-regular fa-star"></i>`).join('')}
                        </div>
                        <span>${moment(item.DateTime).format("yyyy/M/DD")}</span>
                        <p class="text-light">${item.comment}</p>
                    </div>
                </div>`
            ));
            
            jq_comment.owlCarousel({
                margin: 15,
                nav: false,
                dots: true,
                autoplay: true,
                lazyLoad: true,
                autoplayHoverPause: true,
                responsive: {
                    0: {
                        items: 1
                    },
                    576: {
                        items: 2
                    },
                    768: {
                        items: 3
                    },
                    992: {
                        items: 1
                    },
                    1200:{
                        items: 2
                    },
                    1800:{
                        items: 3
                    }
                }
            });
        }else{
            toastr.error(json.Message, json.Title ?? globalLang.Error);
        }
    }).catch((error) => {
        console.log(error);
    });

    /**
     * 數量單位簡化<br>
     * ref: https://stackoverflow.com/questions/9461621/format-a-number-as-2-5k-if-a-thousand-or-more-otherwise-900
     * @param num
     * @param digits
     * @return {string|string}
     */
    function nFormatter(num, digits) {
        const lookup = [
            { value: 1, symbol: "" },
            { value: 1e3, symbol: "K" },
            { value: 1e6, symbol: "M" },
            { value: 1e9, symbol: "G" },
            { value: 1e12, symbol: "T" },
            { value: 1e15, symbol: "P" },
            { value: 1e18, symbol: "E" }
        ];
        const rx = /\.0+$|(\.[0-9]*[1-9])0+$/;
        var item = lookup.slice().reverse().find(function(item) {
            return num >= item.value;
        });
        return item ? (num / item.value).toFixed(digits).replace(rx, "$1") + item.symbol : "0";
    }
});