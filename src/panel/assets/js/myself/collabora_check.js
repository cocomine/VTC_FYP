/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

define([ 'jquery', 'toastr' ], function (jq, toastr){
    'use strict';

    /* 檢查是否已經輸入了組織資料 */
    $(document).on('load', function (){
        $(document).ready(this);
        if (!/^\/panel\/ChangeSetting.*/.test(location.pathname)){
            fetch('/panel/api/collabora_check/', {
                method: 'GET',
                redirect: 'error',
            }).then(async (response) => {
                const json = await response.json();
                if (!response.ok){
                    if (response.status === 404){
                        window.ajexLoad('/panel/ChangeSetting');
                        toastr.warning(json.Message, json.Title);
                    }else{
                        toastr.error(json.Message, json.Title);
                    }
                }
            }).catch((error) => {
                console.log(error);
            });
        }
    });
});