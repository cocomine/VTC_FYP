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
                deps: ["jquery", "jquery.slicknav.min", "jquery.slimscroll.min", "owl.carousel.min"]
            }
        }
    });
    require([
        "toastr",
        "jquery",
        "bootstrap",
        "owl.carousel.min",
        "metisMenu.min",
        "jquery.slimscroll.min",
        "jquery.slicknav.min",
        "plugins",
        "scripts"
    ], (toastr) => {
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
    })
</script>
<script src="/panel/assets/js/sw-register.min.js"></script>

<!--<script>
    AJAX('<?php /*echo $_SERVER['REQUEST_URI'] */ ?>', 'GET');
</script>-->
<!-- script load end -->
</body>
</html>
