<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title">Lista de redes sociales</h4>
                <p class="sub-header">
                    <!-- Add <code>.table-bordered</code> for borders on all sides of the table and cells. -->
                </p>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title">Redes para portfolio</h4>
                                <!-- <p class="text-muted font-size-13 mb-4">
                                    DataTables has most features enabled by default, so all you need to do to use it with your own tables is to call the construction
                                    function:
                                    <code>$().DataTable();</code>.
                                </p> -->

                                <table id="basic-datatable" class="table dt-responsive nowrap w-100 table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">Nombre de red social</th>
                                            <th class="text-center">HTML del icon directamente</th>
                                            <th class="text-center">Img del icon</th>
                                            <th class="text-center">URL</th>
                                            <?php if (in_array('redes', $user->editar)): ?>
                                                <th class="text-center">Opciones</th>
                                            <?php endif; ?>

                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        // debug($redes, false);
                                        foreach ($redes as $key => $red) {
                                            // debug($red, false);
                                        ?>
                                            <tr>
                                                <th class="text-center" scope="row"><?= $key + 1; ?></th>
                                                <td><?= $red->nombre; ?></td>
                                                <td><?= $red->iconHTML; ?></td>
                                                <td class="text-center"><a href="<?= ($red->icon) ? $red->icon : '#'; ?>" target="_blank">Ver</a></td>
                                                <td><a href="<?= $red->url; ?>" target="_blank"><?= $red->nombre; ?></a></td>
                                                <?php if (in_array('redes', $user->editar)): ?>
                                                    <td class="text-center d-flex justify-content-center gap-1" colspan="">
                                                        <a href="admin/update/redes/<?= $red->id; ?>"><i class="fa-sharp fa-solid fa-pen-to-square "></i></a>
                                                        <a href="" onclick="deleteredes(<?= $red->id; ?>)" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fa-sharp fa-solid fa-trash"></i></a>
                                                        <!-- <input class="ch-redes-<?= $red->id; ?>" onchange="changeactivo(<?= $red->id; ?>)" type="checkbox" data-switchery="" <?= ($red->activo) ? 'checked' : ''; ?> data-plugin="switchery" data-color="#64b0f2" data-size="small" /> -->
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input ch-redes-<?= $red->id; ?>" onchange="changeactivoredes(<?= $red->id; ?>)" type="checkbox" role="switch" id="flexSwitchCheckChecked" <?= ($red->activo) ? 'checked' : ''; ?>>
                                                        </div>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>

                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div>
                <!-- end row-->

            </div>
        </div> <!-- end card -->
    </div> <!-- end col -->
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="swal2-icon swal2-warning swal2-icon-show" style="display: flex;">
                    <div class="swal2-icon-content">!</div>
                </div>
                <h2 class="swal2-title" id="swal2-title" style="display: block;">Are you sure?</h2>
                <div class="swal2-content">
                    <div id="swal2-content" class="swal2-html-container" style="display: block;">You won't be able to revert this!</div>
                </div>
            </div>


            <div class="modal-footer swal2-actions">
                <a type="button" class="btn btn-primary todelete">Borrar</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>

        </div>
    </div>
</div>