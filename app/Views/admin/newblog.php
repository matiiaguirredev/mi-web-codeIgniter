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
                                                <label class="col-md-2 col-form-label" for="simpleinput">Titulo del post</label>
                                                <div class="col-md-10">
                                                    <input type="text" name="titulo" id="simpleinput" class="form-control">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="example-textarea">Descripcion</label>
                                                <div class="col-md-10">
                                                    <textarea class="form-control" name="descrip_corta" id="example-textarea" rows="1"></textarea>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="example-fileinput">Imagen</label>
                                                <div class="col-md-10">
                                                    <input multiple type="file" name="img" class="form-control" id="example-fileinput">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="example-fileinput">Imagen dentro del post</label>
                                                <div class="col-md-10">
                                                    <input multiple type="file" name="img_post" class="form-control" id="example-fileinput">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label class="form-label col-md-2 col-form-label">Categorias</label> <br />
                                                <div class="col-md-10">
                                                    <select name="categoria" id="selectize-select">
                                                        <option value="" data-display="Select">Selecciona un categoria.</option>
                                                        <?php foreach ($blogCat as $key => $bCat) { ?>
                                                            <option value="<?= $bCat->nombre ?>"><?= $bCat->nombre ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <hr>

                                            <div class="row">
                                                <div class="col-12">
                                                    <h4 class="header-title">Contenido del post</h4>
                                                    <p class="sub-header">Diseña tu post.</p>
                                                    <!-- <div id="editor" style="height: 300px;"></div> -->
                                                    <textarea class="form-control" name="contenido" id="content" rows="5"></textarea>
                                                </div>
                                            </div>

                                            <hr>

                                            <div class="mb-2 row">
                                                <label class="col-md-2 col-form-label" for="example-fileinput">Activo</label>
                                                <div class="col-md-10">
                                                    <div class="form-check form-switch col-form-label">
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