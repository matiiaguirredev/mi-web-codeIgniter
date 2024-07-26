<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <base href="<?= base_url() ?>">

    <!-- FontAwesome CSS - loading as last, so it doesn't block rendering-->
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-solid.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-regular.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-light.css">

    <!-- Bootstraps icons-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- Bootstrap Min CSS -->
    <link rel="stylesheet" href="web/css/bootstrap.min.css">
    <!-- Jquery UI -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/smoothness/jquery-ui.css">
    <!-- Font Awesome Min CSS -->
    <!-- <link rel="stylesheet" href="web/css/fontawesome.min.css"> -->
    <!-- Magnific Popup Min CSS -->
    <link rel="stylesheet" href="web/css/magnific-popup.css">
    <!-- Owl Carousel Min CSS -->
    <link rel="stylesheet" href="web/css/owl.carousel.min.css">
    <!-- Owl theme Min CSS -->
    <link rel="stylesheet" href="web/css/owl.theme.default.min.css">
    <!-- Style CSS -->
    <link rel="stylesheet" href="web/css/style.css">
    <!-- Dark CSS -->
    <link rel="stylesheet" href="web/css/dark.css">
    <!-- Responsive Min CSS -->
    <link rel="stylesheet" href="web/css/responsive.css">

    <!-- mi css -->
    <link rel="stylesheet" href="assets/css/styles.css">

    <title>Matias Aguirre - Desarrollador Web</title>

    <link rel="icon" type="image/png" href="web/img/favicon.png">
</head>

<body data-bs-spy="scroll" data-bs-offset="70">

    <!-- Start Preloader Area -->
    <!-- <div class="preloader-area">
        <div class="preloader">
            <div class="loader">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </div> -->
    <!-- End Preloader Area -->

    <!-- Start Navbar Area -->
    <div class="navbar-area header-sticky">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div id="navbarNavDropdown" class="navbar-collapse collapse">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="#home" target="_blank">Inicio</a></li>
                        <?php foreach ($secciones as $key => $secc) { ?>
                            <?php if ($secc->txt_btn) { ?>
                                <li class="nav-item"><a class="nav-link" href="<?= $secc->link_secc ?>"><?= $secc->txt_btn ?></a></li>
                            <?php }; ?>
                        <?php }; ?>

                    </ul>
                </div>
            </nav>
        </div>
    </div>
    <!-- End Navbar Area -->

    <div class="alert-content"></div>