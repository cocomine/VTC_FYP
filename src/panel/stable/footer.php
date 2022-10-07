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
        <p><?php echo showText("footer.privacy.0")?></p>
        <p>© <?php echo date('Y'); ?> Global blacklist. <?php echo showText("footer.privacy.1")?></p>
        <p><?php echo showText("footer.privacy.2")?></p>
    </div>
</footer>
<!-- footer area end-->
</div>
<!-- page container area end -->
<!-- script load start-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
<script src="/panel/assets/js/owl.carousel.min.js"></script>
<script src="/panel/assets/js/metisMenu.min.js"></script>
<script src="/panel/assets/js/jquery.slimscroll.min.js"></script>
<script src="/panel/assets/js/jquery.slicknav.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="/panel/assets/js/plugins.js"></script>
<script src="/panel/assets/js/scripts.js"></script>
<script src="/panel/assets/js/sw-register.min.js"></script>
<script src="/panel/assets/js/require.js"></script>
<script>
    require.config({
        baseUrl : "/panel/assets/js"
    });
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
    AJAX('<?php echo $_SERVER['REQUEST_URI'] ?>', 'GET');
</script>
<!-- script load end -->
</body>
</html>
