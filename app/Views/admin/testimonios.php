<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title">Lista de Testimonios</h4>
                <p class="sub-header">
                    <!-- Add <code>.table-bordered</code> for borders on all sides of the table and cells. -->
                </p>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title">Testimonios de portfolio</h4>
                                <!-- <p class="text-muted font-size-13 mb-4">
                                    DataTables has most features enabled by default, so all you need to do to use it with your own tables is to call the construction
                                    function:
                                    <code>$().DataTable();</code>.
                                </p> -->

                                <table id="basic-datatable" class="table dt-responsive nowrap w-100 table-bordered">
                                    <thead>
                                        <tr>
                                            <!-- <th class="text-center">Posicion</th> -->
                                            <th class="text-center">Imagen</th>
                                            <th class="text-center">Nombre</th>
                                            <th class="text-center">Rama Laboral</th>
                                            <th class="text-center">Descripcion Experiencia</th>
                                            <th class="text-center">Opciones</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        // debug($testimonios, false);
                                        foreach ($testimonios as $key => $tes) {
                                            // debug($tes, false);
                                        ?>
                                            <tr class="tr-<?= $tes->id ; ?>">
                                                <!-- <th class="text-center" scope="row"><?= $key ; ?></th> -->
                                                <td class="text-center"><a href="<?= ($tes->img) ? $tes->img : '#'; ?>" target="_blank">Ver</a></td>
                                                <td><?= $tes->nombre; ?></td>
                                                <td><?= $tes->rama_laboral; ?></td>
                                                <td><?= $tes->descrip_exp; ?></td>
                                                <td class="text-center d-flex justify-content-center gap-1" colspan="">
                                                    <a href="admin/update/testimonios/<?= $tes->id; ?>"><i class="fa-sharp fa-solid fa-pen-to-square "></i></a>
                                                    <a href="" onclick="deletetestimonios(<?= $tes->id; ?>)" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fa-sharp fa-solid fa-trash"></i></a>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input ch-testimonios-<?= $tes->id; ?>" onchange="changeactivotestimonios(<?= $tes->id; ?>)" type="checkbox" role="switch" id="flexSwitchCheckChecked" <?= ($tes->activo) ? 'checked' : ''; ?>>
                                                    </div>
                                                </td>
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


