/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define([ 'jquery', 'toastr', 'chartjs', 'moment' ], function (jq, toastr, Chart){
    "use strict";

    const year_earned_chart = new Chart($('#year-earned-chart')[0].getContext('2d'), {
        // The type of chart we want to create
        type: 'line',
        // The data for our dataset
        data: {
            labels: [ "1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月" ],
            datasets: [ {
                label: "金額",
                backgroundColor: "rgba(104, 124, 247, 0.6)",
                borderColor: 'rgba(255,255,255,0.3)',
                data: [ 18, 41, 86, 49, 20, 35, 20, 50, 49, 30, 45, 25 ],
            } ]
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

    const year_order_chart = new Chart($('#year-order-chart')[0].getContext('2d'), {
        // The type of chart we want to create
        type: 'line',
        // The data for our dataset
        data: {
            labels: ["January", "February", "March", "April", "May", "June", "July", "January", "February", "March", "April", "May"],
            datasets: [{
                label: "Share",
                backgroundColor: "rgba(82,204,173,0.6)",
                borderColor: 'rgba(255,255,255,0.3)',
                data: [18, 41, 86, 49, 20, 35, 20, 50, 49, 30, 45, 25],
            }]
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

    const month_earned_chart = new Chart($('#month-earned-chart')[0].getContext('2d'), {
        // The type of chart we want to create
        type: 'line',
        // The data for our dataset
        data: {
            labels: ["January", "February", "March", "April", "May", "June", "July", "January", "February", "March", "April", "May"],
            datasets: [{
                label: "Share",
                backgroundColor: "rgba(204,146,143,0.6)",
                borderColor: 'rgba(255,255,255,0.3)',
                data: [18, 41, 86, 49, 20, 35, 20, 50, 49, 30, 45, 25],
            }]
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

    const month_order_chart = new Chart($('#month-order-chart')[0].getContext('2d'), {
        // The type of chart we want to create
        type: 'line',
        // The data for our dataset
        data: {
            labels: ["January", "February", "March", "April", "May", "June", "July", "January", "February", "March", "April", "May"],
            datasets: [{
                label: "Share",
                backgroundColor: "rgba(230,203,115,0.6)",
                borderColor: 'rgba(255,255,255,0.3)',
                data: [18, 41, 86, 49, 20, 35, 20, 50, 49, 30, 45, 25],
            }]
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

});