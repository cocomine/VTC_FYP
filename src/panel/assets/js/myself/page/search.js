/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'moment.min', 'forge', 'toastr'], function (jq, moment, forge, toastr) {
    "use strict";

    let Lang = $('#langJson').text();
    Lang = JSON.parse(Lang);

    /* 反轉搜尋 */
    $('#reverse').click(function (e) {
        e.preventDefault();
        const Departure = $('#Departure');
        const Destination = $('#Destination')

        const temp = Departure.val();
        const temp2 = Destination.val();

        Departure.val(temp2);
        Destination.val(temp);

        $(this).children('i').animate({deg: "+=180"},
            {
                duration: 500, step: function (now) {
                    $(this).css({transform: 'rotate(' + now + 'deg)'});
                }
            })
    })

    /* set min date */
    const jqDate = $('#Date')
    jqDate.attr('min', moment().format('YYYY-MM-DD'));

    /* Search */
    const jqForm = $('form')
    jqForm.submit(function (e) {
        if (!e.isDefaultPrevented() && this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            const data = $(this).serializeObject();

            /* 封鎖按鈕 */
            const bt = $(this).find('.form-submit, .btn-secondary');
            const html = bt.html();
            bt.html('<div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>').attr('disabled', 'disabled');
            $('#result').html(loadPlaceholder());

            /*  */
            const hashtag = '/panel/search#' + forge.util.encode64(data.departure + '&' + data.destination + '&' + data.date + '&' + data.cabin);
            window.history.pushState({url: hashtag}, '', hashtag);

            /* send */
            fetch('/panel/search', {
                method: 'POST',
                redirect: 'error',
                headers: {
                    'Content-Type': 'application/json; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            }).then((response) => {
                response.json().then((json) => {
                    //console.log(json) //debug

                    if (json.code === 200) {
                        const flights = json.data.flights;
                        $('#result').html(readerRecord(flights));
                    } else {
                        toastr.error(json.Message, json.Title);
                    }
                })
            }).finally(() => {
                bt.html(html).removeAttr('disabled');
            }).catch((error) => {
                console.log(error)
            })
        }
        return false;
    })

    /* Autofill with url */
    const hashtag = location.hash;
    const data = forge.util.decode64(hashtag.slice(1)).split('&')
    $('#Departure').val(data[0]);
    $('#Destination').val(data[1]);
    jqDate.val(data[2]);
    $('#Cabin').val(data[3]);
    jqForm.delay(500).submit();

    /* 渲染結果 */
    function readerRecord(flights) {
        if(flights.length <= 0) return not_match
        return flights.map((item) => flightRecord(item.Flight, item.DateTime, formatPrice(item.Price), item.From, item.To, item.cabin)).join('');
    }

    /* html code */
    function flightRecord(flight, time, price, from, to, cabin) {
        cabin = cabin === 1 ? Lang.Cabin_type[1] : Lang.Cabin_type[2]
        time = moment(time).format("hh:mm A");
        return `
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-2">
                        <div class="row align-content-center h-100 justify-content-center">
                            <h4 class="col-auto">${flight}</h4>
                            <div class="w-100"></div>
                            <span class="col-auto">${time}</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="row align-content-center h-100 pe-4">
                            <div class="col-auto"><h3>${from}</h3></div>
                            <div class="col row align-content-center"><div class="fly-arrow"><div></div></div></div>
                            <div class="col-auto"><h3>${to}</h3></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="row align-content-center h-100 justify-content-center">
                            <h4 class="col-auto">$ ${price}</h4>
                            <div class="w-100"></div>
                            <span class="col-auto">${cabin}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>`
    }

    function loadPlaceholder() {
        let html = [0, 0, 0, 0, 0]
        html = html.map(() => `
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row placeholder-glow">
                            <div class="col-12 col-md-2">
                                <div class="row align-content-center h-100 justify-content-center">
                                    <h4 class="col-8 placeholder placeholder-lg"></h4>
                                    <div class="w-100 pt-1"></div>
                                    <span class="col-4 placeholder"></span>
                                    <div class="w-100 pt-1"></div>
                                    <span class="col-4 placeholder"></span>
                                </div>
                            </div>
                            <div class="col">
                                <div class="row align-content-center h-100 pe-4">
                                    <div class="col-2"><h3 class="placeholder placeholder-lg w-100"></h3></div>
                                    <div class="col row align-content-center"><div class="placeholder placeholder-sm w-100"></div></div>
                                    <div class="col-2"><h3 class="placeholder placeholder-lg w-100"></h3></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `)

        return html.join('');
    }

    function not_match() {
        return `
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row justify-content-center align-items-center">
                    <div class="col-auto"><lottie-player src="https://assets7.lottiefiles.com/packages/lf20_IIxb9U.json" background="transparent" speed="1" style="width: 120px; height: 120px;" autoplay></lottie-player></div>
                    <div class="col-auto h-auto"><h3 class="align-middle">${Lang.Not_match}</h3></div>
                </div>
            </div>
        </div>
    </div>`
    }
})