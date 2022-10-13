<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

/**
 * Created by IntelliJ IDEA.
 * User: user
 * Date: 10/12/2018
 * Time: 下午 11:08
 */
?>
<div class="go-top" style="display: none">
    <div class="row justify-content-center align-content-center h-100">
        <i class="col-auto ti-angle-up"></i>
    </div>
</div>
<footer>
    <div class="footer-area">
        <!-- footer content -->
        <p><?php echo date('Y') . ' ' . showText("footer.privacy") ?></p>
    </div>
</footer>
<!-- footer area end-->
</div>
<!-- page container area end -->
<!-- script load start-->
<!-- require js -->
<script src="/panel/assets/js/require.js"></script>
<script>
    require.config({
        baseUrl: "/panel/assets/js",
        paths: {
            jquery: "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min",
            bootstrap: "https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min",
            toastr: "https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min"
        },
        shim: {
            "owl.carousel.min": {
                deps: ["jquery"]
            },
            "jquery.slimscroll.min": {
                deps: ["jquery"]
            },
            "jquery.slicknav.min": {
                deps: ["jquery"]
            },
            "plugins": {
                deps: ["jquery"]
            },
            "scripts": {
                deps: ["jquery", "jquery.slicknav.min", "jquery.slimscroll.min", "owl.carousel.min", "metisMenu.min"]
            }
        }
    });
    require([
        "toastr",
        "myself/ajex",
        "jquery",
        "bootstrap",
        "owl.carousel.min",
        "metisMenu.min",
        "jquery.slimscroll.min",
        "jquery.slicknav.min",
        "plugins",
        "scripts"
    ], (toastr, ajex) => {
        toastr.options = {
            "progressBar": true,
            "positionClass": "toast-bottom-right",
            "showDuration": "400",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "easeOutQuint",
            "hideEasing": "easeInQuint",
            "showMethod": "slideDown",
            "hideMethod": "slideUp"
        };

        /* 自動跳轉登入前url */
        const returnPath = sessionStorage.getItem('returnPath');
        if(returnPath !== null) {
            ajex.ajexLoad(returnPath);
            ajex.updateNavBar(returnPath);
            sessionStorage.removeItem('returnPath');
        }
        else {
            ajex.ajexLoad('<?php echo $_SERVER['REQUEST_URI'] ?>');
            ajex.updateNavBar('<?php echo $_SERVER['REQUEST_URI'] ?>');
        }

        /* 註冊全景參數 */
        window.ajexLoad = ajex.ajexLoad;
        window.loadModules = ajex.loadModules;

        /* loading畫面 */
        $(window).on('load', function() {
            $('#preloader').fadeOut('slow', function() { $(this).remove(); });
        });
    })
</script>
<script src="/panel/assets/js/sw-register.min.js"></script>
<!-- script load end -->
</body>
</html>
