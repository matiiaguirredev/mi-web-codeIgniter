<div class="px-3">

    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class=" py-lg-4">
            <div class="row">
                <div class="col-lg-6">
                    <h4 class="page-title mb-0">Dashboard</h4>
                </div>
                <div class="col-lg-6">
                    <div class="d-none d-lg-block">
                        <ol class="breadcrumb m-0 float-end">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashtrap</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">

            <div class="col-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Stock</h4>
                        <p class="card-subtitle mb-4">Recent Stock</p>

                        <div class="text-center">
                            <div>
                                <img class="img-fluid" src="<?= $user->informacion->img_fondo; ?>" alt="">
                            </div>

                            <div style="margin-top: -60%;">
                                <img class="img-fluid" src="<?= $user->informacion->img; ?>" alt="">
                            </div>

                            <h5 class="text-muted mt-3">About me</h5>

                            <p class="text-muted w-75 mx-auto sp-line-2"><?= $user->informacion->descripcion; ?></p>

                            <div class="">
                                <strong>Full name: </strong> <?= $user->informacion->nombre; ?>, <?= $user->informacion->apellido; ?>
                            </div>
                            <div class="">
                                <strong>Mobile: </strong> <?= $user->informacion->tel; ?>
                            </div>
                            <div class="">
                                <strong>Email: </strong> <?= $user->email; ?>
                            </div>
                        </div>
                    </div> <!--end card body-->
                </div> <!-- end card-->
            </div>


            <div class="col-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-3">Basic Example</h4>

                        <form role="form" class=" row" method="POST" enctype="multipart/form-data">
                            <div class="col-12 mb-2 ">
                                <div class="mb-2">
                                    <label for="exampleInputEmail1" class="form-label">Sobre mi</label>
                                    <textarea class="form-control" name="descripcion" id="" cols="30" rows="10"><?= $user->informacion->descripcion; ?></textarea>
                                </div>
                            </div>
                            <div class="col-6 mb-2 ">
                                <div class="mb-2">
                                    <label for="exampleInputName" class="form-label">Name</label>
                                    <input type="text" name="nombre" class="form-control" id="exampleInputName" aria-describedby="emailHelp" value="<?= $user->informacion->nombre; ?>">
                                </div>
                            </div>
                            <div class="col-6 mb-2 ">
                                <div class="mb-2">
                                    <label for="exampleInputLast" class="form-label">Last Name</label>
                                    <input type="text" name="apellido" class="form-control" id="exampleInputLast" aria-describedby="emailHelp" value="<?= $user->informacion->apellido; ?>">
                                </div>
                            </div>
                            <div class="col-6 mb-2 ">
                                <label for="exampleInputAge" class="form-label">Edad</label>
                                <input type="number" name="edad" class="form-control" id="exampleInputAge" aria-describedby="emailHelp" value="<?= $user->informacion->edad; ?>">
                            </div>

                            <div class="col-6 mb-2 ">
                                <label for="exampleInputPassword1" class="form-label">Telefono</label>
                                <input type="tel" name="tel" class="form-control" id="exampleInputPassword1" value="<?= $user->informacion->tel; ?>">
                            </div>

                            <div class="col-6 mb-2">
                                <label class="mb-2" for="example-fileinput">Foto de perfil</label>
                                <div class="col">
                                    <input multiple type="file" name="img" class="form-control" id="example-fileinput" value="<?= $user->informacion->img; ?>">
                                </div>
                            </div>

                            <div class="col-6 mb-2">
                                <label class="mb-2" for="example-fileinput">Fondo del perfil</label>
                                <div class="col">
                                    <input multiple type="file" name="img_fondo" class="form-control" id="example-fileinput" value="<?= $user->informacion->img_fondo; ?>">
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="submit" class="form-label"></label>
                                <button class="btn btn-primary w-100" type="submit">Cargar datos</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
        <!--end col-->

        <!--end row-->

    </div> <!-- container -->

</div> <!-- content -->