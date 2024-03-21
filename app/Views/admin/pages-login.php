<html lang="en" data-bs-theme="dark" data-menu-color="brand" data-topbar-color="light" data-sidebar-size="full"><head>
    <meta charset="utf-8">
    <title>Log In | Dashtrap - Responsive Bootstrap 5 Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description">
    <meta content="Myra Studio" name="author">

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- App css -->
    <link href="assets/css/style.min.css" rel="stylesheet" type="text/css">
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css">
    <script src="assets/js/config.js"></script>
</head>

<body class="bg-primary d-flex justify-content-center align-items-center min-vh-100 p-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-4 col-md-5">
                <div class="card">
                    <div class="card-body p-4">

                        <div class="text-center w-75 mx-auto auth-logo mb-4">
                            <a class="logo-dark" href="/dashtrap/">
                                <span><img src="assets/images/logo-dark.png" alt="" height="22"></span>
                            </a>

                            <a class="logo-light" href="/dashtrap/">
                                <span><img src="assets/images/logo-light.png" alt="" height="22"></span>
                            </a>
                        </div>

                        <form action="#">

                            <div class="form-group mb-3">
                                <label class="form-label" for="username">Username</label>
                                <input class="form-control" type="text" id="username" required="" placeholder="Enter your username">
                            </div>

                            <div class="form-group mb-3">
                                <a class="text-muted float-end" href="/dashtrap/pages-recoverpw"><small></small></a>
                                <label class="form-label" for="password">Password</label>
                                <input class="form-control" type="password" required="" id="password" placeholder="Enter your password">
                            </div>

                            <div class="form-group mb-3">
                                <div class="">
                                    <input class="form-check-input" type="checkbox" id="checkbox-signin" checked="">
                                    <label class="form-check-label ms-2" for="checkbox-signin">Remember me</label>
                                </div>
                            </div>

                            <div class="form-group mb-0 text-center">
                                <button class="btn btn-primary w-100" type="submit"> Log In </button>
                            </div>

                        </form>
                    </div> <!-- end card-body -->
                </div>
                <!-- end card -->

                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <p class="text-white-50"> <a class="text-white-50 ms-1" href="/dashtrap/pages-register">Forgot your password?</a></p>
                        <p class="text-white-50">Don't have an account? <a class="text-white font-weight-medium ms-1" href="/dashtrap/pages-register">Sign Up</a></p>
                    </div> <!-- end col -->
                </div>
                <!-- end row -->

            </div> <!-- end col -->
        </div>
        <!-- end row -->
    </div>

    <!-- App js -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.js"></script>



</body></html>