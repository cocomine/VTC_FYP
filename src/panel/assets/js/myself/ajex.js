/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

define(['jquery', 'toastr'], function (jq, toastr) {
    $(document).on("click", 'a[href]', function (e) {
        const link = $(this).attr('href');
        if (/^(\/)/.test(link)) {
            e.preventDefault();
            ajexLoad(link);
        }
    });

    const ajexLoad = (link, putState = true) => {
        if (!/[$\/]/.test(link)) link = link + '/';
        $('#content').html(loadingPlaceholder)

        /* send */
        $.ajax({
            type: 'GET',
            url: link,
            headers: {'AJAX': 'true'},
            success: function (data) {
                console.log(data)

                $('title').text(data.title);
                $('#title').text(data.head);
                $('#path').html(data.path);
                $('#content').html(data.content)

                $(document).trigger('ready')
                if(putState) window.history.pushState({url: link}, data.title, link);
            },
            error: (xhr, textStatus) => {
                if (textStatus === 'error') {
                    if (xhr.responseJSON.code) {
                        if (xhr.responseJSON.code === 404) {
                            $('#content').html(page404(xhr.responseJSON.Message));
                        } else if (xhr.responseJSON.code === 403) {
                            $('#content').html(page403(xhr.responseJSON.Message));
                        } else if (xhr.responseJSON.code === 500) {
                            $('#content').html(page500(xhr.responseJSON.Message));
                        }
                    } else toastr.error('Unknown error!!');
                } else if (textStatus === 'timeout') toastr.error('Request Timeout', '408');
                else toastr.error('Unknown error!!');
            }
        });
    }

    const loadingPlaceholder = `
                    <div class='col-12 mt-4 col-md-8'>
                        <div class="row gy-4 gx-0 m-0">
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class='placeholder-glow'>
                                            <h5 class="card-title">
                                                <span class="placeholder col-6"></span>
                                            </h5>
                                            <p class="card-text row m-0">
                                                <span class="placeholder col-2" style="margin-right: 1.5rem"></span>
                                                <span class="placeholder col" style="margin-right: 1.5rem"></span>
                                                <span class="placeholder col-2"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col-12'>
                                <div class="card ratio ratio-21x9 overflow-hidden placeholder-glow">
                                    <div class="card-body placeholder"></div>
                                </div>
                            </div>
                            <div class='col-12'>
                                <div class="card ">
                                    <div class='card-body'>
                                        <div class='placeholder-glow'>
                                            <h5 class="card-title">
                                                <span class="placeholder col-6"></span>
                                            </h5>
                                            <p class="card-text">
                                                <span class="placeholder col-6"></span>
                                                <span class="placeholder col-4"></span>
                                                <span class="placeholder col-8"></span>
                                                <span class="placeholder col-4"></span>
                                                <span class="placeholder col-7"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='col-12 mt-4 col-md-4'>
                        <div class="row gy-4 gx-0 m-0">
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class='col-12 placeholder-glow row justify-content-center'>
                                            <h2 class="card-title text-center">
                                                <span class="placeholder col-8"></span>
                                            </h2>
                                            <a href="#" tabindex="-1" class="btn btn-rounded btn-primary disabled placeholder col-6"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class='placeholder-glow'>
                                            <p class="card-text">
                                                <span class="placeholder col-12 placeholder-lg"></span>
                                                <span class="placeholder col-3 placeholder-sm"></span>
                                            </p>
                                            <p class="card-text">
                                                <span class="placeholder col-12 placeholder-lg"></span>
                                                <span class="placeholder col-3 placeholder-sm"></span>
                                            </p>
                                            <p class="card-text">
                                                <span class="placeholder col-12 placeholder-lg"></span>
                                                <span class="placeholder col-3 placeholder-sm"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`

    const page404 = (Msg) => `
                    <div class='col-12 mt-4'>
                        <div class="row gy-4 gx-0 m-0">
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class="row justify-content-center">
                                            <div class="col-auto">
                                                <lottie-player src="https://assets8.lottiefiles.com/packages/lf20_kcsr6fcp.json"  background="transparent"  speed="1"  style="width: 500px; height: 250px;"  loop  autoplay></lottie-player>
                                            </div>
                                            <div class="w-100"></div>
                                            <h2 class="col-auto">${Msg}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`

    const page403 = (Msg) => `
                    <div class='col-12 mt-4'>
                        <div class="row gy-4 gx-0 m-0">
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class="row justify-content-center">
                                            <div class="col-auto">
                                                <lottie-player src="https://assets5.lottiefiles.com/packages/lf20_UxVUy6KZG1.json"  background="transparent"  speed="1"  style="width: 300px; height: 200px;"  loop  autoplay></lottie-player>                                            </div>
                                            <div class="w-100"></div>
                                            <h2 class="col-auto">${Msg}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`

    const page500 = (Msg) => `
                    <div class='col-12 mt-4'>
                        <div class="row gy-4 gx-0 m-0">
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class="row justify-content-center">
                                            <div class="col-auto">
                                                <lottie-player src="https://assets5.lottiefiles.com/packages/lf20_8qMJfR.json"  background="transparent"  speed="1"  style="width: 300px; height: 300px;"  loop  autoplay></lottie-player>                                            <div class="w-100"></div>
                                            <h2 class="col-auto">${Msg}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`

    return {
        ajexLoad
    }
})