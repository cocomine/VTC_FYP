/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define([ 'jquery', 'toastr', 'chartjs', 'moment', 'jquery.scrollbar.min' ], function (jq, toastr, Chart, moment){
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
            console.log(json);

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
                        label: "訂單數",
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
                        label: "Share",
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
                        label: "Share",
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
            toastr.error(json.Message, json.Title);
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
            console.log(json);

            if(data.length <= 0){
                $('#today-order').html('<tr><td colspan="4"><div class="text-center text-muted">今日無預約</div></td></tr>');
                return;
            }

            $('#today-order').html(data.map((item) =>
                `<tr>
                    <td>${item.ID}</td>
                    <td>
                        <a href="/panel/reserve/${item.event_ID}#${item.ID}">${item.Name} (${item.last_name} ${item.first_name})</a>
                    </td>
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
            toastr.error(json.Message, json.Title);
        }
    }).catch((error) => {
        console.log(error);
    });

    new Chart($('#county')[0].getContext('2d'), {
        // The type of chart we want to create
        type: 'doughnut',
        // The data for our dataset
        data: {
            labels: ["香港", "澳門", "台灣", "中國大陸"],
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
                data: [810, 410, 260, 150],
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