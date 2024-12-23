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
                                            <label class="col-md-2 col-form-label" for="nombre">Nombre del Rol</label>
                                            <div class="col-md-10">
                                                <input type="text" name="nombre" id="nombre" class="form-control" value="<?= $roles->nombre ?>">
                                            </div>
                                        </div>

                                        <div class="mb-2 row">
                                            <label class="col-md-2 col-form-label" for="descripcion">Descripcion del rol</label>
                                            <div class="col-md-10">
                                                <textarea class="form-control" name="descripcion" id="descripcion" rows="5"><?= $roles->descripcion ?></textarea>
                                            </div>
                                        </div>


                                        <h6>PERMISOS DE ROLES</h6>

                                        <div class="mb-2 row">
                                            <label class="form-label col-md-2 col-form-label">Ver</label> <br />
                                            <div class="col-md-10">
                                                <select class="no-shadow select-multiple" name="ver[]" id="select-ver" multiple>
                                                    <option value="" data-display="Select">Seleccione las secciones:</option>
                                                    <?php foreach ($secciones as $value) { ?>
                                                        <option value="<?= $value['alias'] ?>"
                                                            <?= in_array($value['alias'], $selectedVer) ? 'selected' : '' ?>>
                                                            <?= $value['titulo'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-2 row">
                                            <label class="form-label col-md-2 col-form-label">Crear</label> <br />
                                            <div class="col-md-10">
                                                <select class="no-shadow select-multiple" name="crear[]" id="select-crear" multiple>
                                                    <option value="" data-display="Select">Seleccione las secciones:</option>
                                                    <?php foreach ($secciones as $value) { ?>
                                                        <option value="<?= $value['alias'] ?>"
                                                            <?= in_array($value['alias'], $selectedCrear) ? 'selected' : '' ?>>
                                                            <?= $value['titulo'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-2 row">
                                            <label class="form-label col-md-2 col-form-label">Editar</label> <br />
                                            <div class="col-md-10">
                                                <select class="no-shadow select-multiple" name="editar[]" id="select-editar" multiple>
                                                    <option value="" data-display="Select">Seleccione las secciones:</option>
                                                    <?php foreach ($secciones as $value) { ?>
                                                        <option value="<?= $value['alias'] ?>"
                                                            <?= in_array($value['alias'], $selectedEditar) ? 'selected' : '' ?>>
                                                            <?= $value['titulo'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-2 row">
                                            <label class="form-label col-md-2 col-form-label">Borrar</label> <br />
                                            <div class="col-md-10">
                                                <select class="no-shadow select-multiple" name="borrar[]" id="select-borrar" multiple>
                                                    <option value="" data-display="Select">Seleccione las secciones:</option>
                                                    <?php foreach ($secciones as $value) { ?>
                                                        <option value="<?= $value['alias'] ?>"
                                                            <?= in_array($value['alias'], $selectedBorrar) ? 'selected' : '' ?>>
                                                            <?= $value['titulo'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>



                                        <div class="mb-2 row">
                                            <label class="col-md-2 col-form-label" for="example-fileinput">Activo</label>
                                            <div class="col-md-10">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" name="activo" type="checkbox" role="switch" id="flexSwitchCheckChecked" <?= ($roles->activo) ? 'checked' : ''; ?> </div>
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