    <div class="px-3">

        <!-- Start Content-->
        <div class="container-fluid">

            <!-- start page title -->
            <div class="py-3 py-lg-4">
                <div class="row">
                    <div class="col-lg-6">
                        <h4 class="page-title mb-0">Form Elements</h4>
                    </div>
                    <div class="col-lg-6">
                        <div class="d-none d-lg-block">
                            <ol class="breadcrumb m-0 float-end">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                <li class="breadcrumb-item active">Form Elements</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-12">
                                    <div class="p-2">

                                        <form action="" method="post" enctype="multipart/form-data">
                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="alias">Alias</label>
                                                <div class="col-md-10">
                                                    <input type="text" name="alias" id="alias" class="form-control">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="simpleinput">Titulo de seccion</label>
                                                <div class="col-md-10">
                                                    <input type="text" name="titulos" id="simpleinput" class="form-control">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="simpleinput">Sub Titulo de seccion</label>
                                                <div class="col-md-10">
                                                    <input type="text" name="sub_titulo" id="simpleinput" class="form-control">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="example-textarea">Descripcion de seccion</label>
                                                <div class="col-md-10">
                                                    <textarea class="form-control" name="descripciones" id="example-textarea" rows="5"></textarea>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="simpleinput">Texto de Boton</label>
                                                <div class="col-md-10">
                                                    <input type="text" name="txt_btn" id="simpleinput" class="form-control">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="simpleinput">Link de seccion</label>
                                                <div class="col-md-10">
                                                    <input type="text" name="link_secc" id="simpleinput" class="form-control">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="example-fileinput">Imagen de fondo</label>
                                                <div class="col-md-10">
                                                    <input multiple type="file" name="img" class="form-control" id="example-fileinput">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="example-fileinput">Color de fondo</label>
                                                <div class="col-md-10">
                                                    <input multiple type="color" name="bg_color" class="form-control" id="example-fileinput">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="simpleinput">Orden</label>
                                                <div class="col-md-10">
                                                    <input type="number" name="orden" id="ordenId" class="form-control" value="<?= count($secciones) + 1 ?>" readonly>
                                                    <?php /* ?>
                                                    <select class="form-control" name="orden" id="" require>
                                                        <?php  foreach ($secciones as $key => $value) { ?>
                                                            <option value="<?= $key + 1 ?>"><?= $key + 1 ?></option>
                                                        <?php }  ?>
                                                        <option value="<?= count($secciones) + 1 ?>" selected><?= count($secciones) + 1 ?></option>
                                                    </select>
                                                    <?php */ ?>

                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="example-fileinput">Activo</label>
                                                <div class="col-md-10">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" name="activo" type="checkbox" role="switch" id="flexSwitchCheckChecked">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0 text-center">
                                                <button class="btn btn-primary w-100" type="submit">Cargar proyecto</button>
                                            </div>

                                        </form>

                                    </div>
                                </div>

                            </div>
                            <!-- end row -->
                        </div>
                    </div> <!-- end card -->
                </div><!-- end col -->
            </div>
            <!-- end row -->

        </div> <!-- container -->

    </div> <!-- content -->