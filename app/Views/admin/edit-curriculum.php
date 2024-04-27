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
                                            <label class="col-md-2 col-form-label" for="simpleinput">Titulo del Estudio/Trabajo</label>
                                            <div class="col-md-10">
                                                <input type="text" name="titulo" id="simpleinput" class="form-control" value="<?= $curriculum->titulo ?>">
                                            </div>
                                        </div>

                                        <div class="mb-2 row">
                                            <label class="col-md-2 col-form-label" for="simpleinput">Sub titulo del Estudio/Trabajo</label>
                                            <div class="col-md-10">
                                                <input type="text" name="sub_titulo" id="simpleinput" class="form-control" value="<?= $curriculum->sub_titulo ?>">
                                            </div>
                                        </div>

                                        <div class="mb-2 row">
                                            <label class="col-md-2 col-form-label" for="simpleinput">Desde cuando:</label>
                                            <div class="col-md-10">
                                                <input type="number" name="rango_anos" id="simpleinput" class="form-control" min="2000" max="2100" value="<?= $curriculum->rango_anos ?>">
                                            </div>
                                        </div>

                                        <!-- <div class="mb-2 row">
                                            <label class="col-md-2 col-form-label" for="simpleinput">Desde cuando:</label>
                                            <div class="col-md-10">
                                                <input type="date" name="desde" id="simpleinput" class="form-control" min="2000" max="2100" value="<?= $curriculum->desde ?>">
                                            </div>
                                        </div>

                                        <div class="mb-2 row">
                                            <label class="col-md-2 col-form-label" for="simpleinput">Hasta cuando:</label>
                                            <div class="col-md-10">
                                                <input type="date" name="hasta" id="simpleinput" class="form-control" min="2000" max="2100" value="<?= $curriculum->hasta ?>">
                                            </div>
                                        </div> -->

                                        <div class="mb-2 row">
                                            <label class="col-md-2 col-form-label" for="simpleinput">Categoria</label>
                                            <div class="col-md-10">
                                                <select class="form-control" name="categoria" id="" require>
                                                    <option value="">Selecciona una categoria</option>
                                                    <option value="edu" <?= ($curriculum->categoria == 'edu') ? 'selected' : ''; ?>>Educacion</option>
                                                    <option value="exp" <?= ($curriculum->categoria == 'exp') ? 'selected' : ''; ?>>Experiencia</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-2 row">
                                            <label class="col-md-2 col-form-label" for="example-textarea">Text area</label>
                                            <div class="col-md-10">
                                                <textarea class="form-control" name="descripcion" id="example-textarea" rows="5"><?= $curriculum->descripcion ?></textarea>
                                            </div>
                                        </div>

                                        <div class="mb-2 row">
                                            <label class="col-md-2 col-form-label" for="example-fileinput">Activo</label>
                                            <div class="col-md-10">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" name="activo" type="checkbox" role="switch" id="flexSwitchCheckChecked" <?= ($curriculum->activo) ? 'checked' : ''; ?>>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-0 text-center">
                                            <button class="btn btn-primary w-100" type="submit">Modificar experiencia</button>
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