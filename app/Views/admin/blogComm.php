<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title">Lista de Comentarios de blog</h4>
                <p class="sub-header">
                    <!-- Add <code>.table-bordered</code> for borders on all sides of the table and cells. -->
                </p>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title">Comentarios de blog</h4>
                                <!-- <p class="text-muted font-size-13 mb-4">
                                    DataTables has most features enabled by default, so all you need to do to use it with your own tables is to call the construction
                                    function:
                                    <code>$().DataTable();</code>.
                                </p> -->

                                <table id="basic-datatable" class="table dt-responsive nowrap w-100 table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha Create</th>
                                            <th class="text-center">Fecha Edit</th>
                                            <!-- <th class="text-center">Autor</th> -->
                                            <th class="text-center">Blog</th>
                                            <th class="text-center">Comentario</th>
                                            <th class="text-center">Opciones</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        // debug($this->data);
                                        foreach ($blogComm as $key => $b) {
                                            // debug($b, false);
                                        ?>
                                                <tr class="tr-<?= $b->id ; ?>">
                                                    <!-- <th class="text-center" scope="row"><?= $key; ?></th> -->
                                                    <td><?= $b->create_at; ?></td>
                                                    <td><?= $b->edit_at; ?></td>
                                                    <!-- <td><?/* = $b->edit_at; */ ?></td> -->
                                                    <td><?= $b->titulo_blog; ?></td>
                                                    <td class="text-center "><a class="showdescrip" data-description="<?= $b->comentario; ?>">Ver comentario</a></td>
                                                    <td class="text-center d-flex justify-content-center gap-1" colspan="">
                                                        <a href="admin/update/blogComm/<?= $b->id; ?>"><i class="fa-sharp fa-solid fa-pen-to-square "></i></a>
                                                        <a href="" onclick="deleteblogComm(<?= $b->id; ?>)" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fa-sharp fa-solid fa-trash"></i></a>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input ch-blogComm-<?= $b->id; ?>" onchange="changeactivoblogComm(<?= $b->id; ?>)" type="checkbox" role="switch" id="flexSwitchCheckChecked" <?= ($b->activo) ? 'checked' : ''; ?>>
                                                        </div>
                                                    </td>
                                                </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>

                                    <!-- <tbody>
                                        <?php /*
                                        // Supongamos que $categorias es un array de objetos de categoría
                                        // y $blog es un array de objetos de blog.

                                        // Primero, construir un mapa de categorías por ID para acceso rápido
                                        $categoriaMap = [];
                                        foreach ($categorias as $cat) {
                                            $categoriaMap[$cat->id] = $cat->nombre;
                                        }

                                        // debug($blog, false);
                                        foreach ($blog as $key => $b) {
                                            // Mostrar solo el blog actual para depuración
                                            // debug($b, false);

                                            // Si existe una categoría para el ID del blog, imprímelo
                                            if (isset($categoriaMap[$b->categoria])) {
                                                $nombreCategoria = $categoriaMap[$b->categoria];
                                        ?>
                                                <tr class="tr-<?= $b->id ; ?>">
                                                    <!-- <th class="text-center" scope="row"><?= $key; ?></th> -->
                                                    <td><?= $b->create_at; ?></td>
                                                    <td><?= $b->titulo; ?></td>
                                                    <td class="text-center "><a class="showdescrip" data-description="<?= $b->descripcion; ?>">Ver descripción</a></td>
                                                    <td class="text-center"><a href="<?= ($b->img) ? $b->img : '#'; ?>" target="_blank">Ver</a></td>
                                                    <td><?= $nombreCategoria; ?></td>
                                                    <td class="text-center d-flex justify-content-center gap-1" colspan="">
                                                        <a href="admin/update/blog/<?= $b->id; ?>"><i class="fa-sharp fa-solid fa-pen-to-square "></i></a>
                                                        <a href="" onclick="deleteblog(<?= $b->id; ?>)" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fa-sharp fa-solid fa-trash"></i></a>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input ch-blog-<?= $b->id; ?>" onchange="changeactivoblog(<?= $b->id; ?>)" type="checkbox" role="switch" id="flexSwitchCheckChecked" <?= ($b->activo) ? 'checked' : ''; ?>>
                                                        </div>
                                                    </td>
                                                </tr>
                                        <?php
                                            }
                                        }
                                        */ ?>
                                    </tbody> -->

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