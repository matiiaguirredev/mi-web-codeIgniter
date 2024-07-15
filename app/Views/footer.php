<!-- Start Copyrigth Area -->
<footer class="copyright-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <p>© Phkr is Proudly Owned by <a href="https://envytheme.com/" target="_blank">EnvyTheme</a></p>
            </div>
        </div>
    </div>
</footer>
<!-- End Copyrigth Area -->

<div class="go-top"><i class="fa fa-angle-up"></i></div>

<!-- JS libreria TypeIt -->
<script src="https://unpkg.com/typeit@8.8.0/dist/index.umd.js"></script>
<!-- jQuery Min JS -->
<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
<script src="web/js/jquery.min.js"></script>
<!-- Bootstrap Min JS -->
<script src="web/js/bootstrap.bundle.min.js"></script>
<!-- Owl Carousel Min Js -->
<script src="web/js/owl.carousel.min.js"></script>
<!-- Jquery Magnific Popup Min Js -->
<script src="web/js/jquery.magnific-popup.min.js"></script>
<!-- Jquery Mixitup Min Js -->
<script src="web/js/jquery.mixitup.min.js"></script>
<!-- Particles Min JS -->
<script src="web/js/particles.min.js"></script>
<!-- Jquery Ripple Min JS -->
<script src="web/js/jquery.ripples-min.js"></script>
<!-- Form Validator Min JS -->
<script src="web/js/form-validator.min.js"></script>
<!-- Contact Form Min JS -->
<script src="web/js/contact-form-script.js"></script>
<!-- Main JS -->
<script src="web/js/main.js?v=<?= time() ?>"></script>

<?php if (isset($error)) :  ?>
    <script>
        let $error = '<?= $error; ?>';
        showAlert("danger", $error);
    </script>
<?php endif; ?>

<?php if (isset($success)) :  ?>
    <script>
        let $success = '<?= $success; ?>';
        showAlert("success", $success);
    </script>
<?php endif; ?>


</body>

</html>