function getCookie(nombre) {
    var nombreCookie = nombre + "=";
    var cookies = document.cookie.split(";");

    for (var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i].trim();
        if (cookie.indexOf(nombreCookie) == 0) {
            return cookie.substring(nombreCookie.length, cookie.length);
        }
    }

    return ""; // Retorna una cadena vacía si no se encuentra la cookie
}

function showAlert(type, message) {
    let errorMessagePrefix = message.split(":")[0];
    let content = `
    <div class="text-light alert bg-${type} alert-dismissible fade show" role="alert">
        <strong>${errorMessagePrefix}</strong>:${message.substring(
        errorMessagePrefix.length + 1
    )}
        <button type="button" class="btn-close close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    `;
    $(".alert-content").html(content);
    setTimeout(() => {
        $(".alert-content .close").click();
    }, 3000);
}

function deleteproyect(id) {
    $(".todelete").attr("href", "admin/delete/proyect/" + id);
}

function changeactivo(id) {
    data = [];
    data["activo"] = $(".ch-proyect-" + id).is(":checked") ? 1 : 0;
    data["token"] = getCookie("token");
    activo = $(".ch-proyect-" + id).is(":checked") ? 1 : 0;
    msj = activo ? "Activado" : "Desactivado";
    type = activo ? "success" : "danger";

    console.log("data", data);

    $.ajax({
        async: true,
        type: "POST",
        dataType: "json",
        url:
            "./api/update/proyect/" +
            id +
            "?token=" +
            getCookie("token") +
            "&activo=" +
            activo,
        data: data,
        contentType: "application/json; charset=utf-8",
        // contentType: false,
        processData: false,
    })
        .done(function (Response) {
            //do something when get response
            showAlert(type, `${Response.nombre} ${msj} correctamente`);
            console.log(Response);
        })
        .fail(function (Response) {
            //do something when any error occurs.
            showAlert("danger", Response.error);
            console.error(Response);
        });
}

function deletelenguaje(id) {
    $(".todelete").attr("href", "admin/delete/lenguaje/" + id);
}

function changeactivolenguaje(id) {
    data = [];
    data["activo"] = $(".ch-lenguaje-" + id).is(":checked") ? 1 : 0;
    data["token"] = getCookie("token");
    activo = $(".ch-lenguaje-" + id).is(":checked") ? 1 : 0;
    msj = activo ? "Activado" : "Desactivado";
    type = activo ? "success" : "danger";

    console.log("data", data);

    $.ajax({
        async: true,
        type: "POST",
        dataType: "json",
        url:
            "./api/update/lenguaje/" +
            id +
            "?token=" +
            getCookie("token") +
            "&activo=" +
            activo,
        data: data,
        contentType: "application/json; charset=utf-8",
        // contentType: false,
        processData: false,
    })
        .done(function (Response) {
            //do something when get response
            showAlert(type, `${Response.nombre} ${msj} correctamente`);
            console.log(Response);
        })
        .fail(function (Response) {
            //do something when any error occurs.
            showAlert("danger", Response.error);
            console.error(Response);
        });
}

function deleteredes(id) {
    $(".todelete").attr("href", "admin/delete/redes/" + id);
}

function changeactivoredes(id) {
    data = [];
    data["activo"] = $(".ch-redes-" + id).is(":checked") ? 1 : 0;
    data["token"] = getCookie("token");
    activo = $(".ch-redes-" + id).is(":checked") ? 1 : 0;
    msj = activo ? "Activado" : "Desactivado";
    type = activo ? "success" : "danger";

    console.log("data", data);

    $.ajax({
        async: true,
        type: "POST",
        dataType: "json",
        url:
            "./api/update/redes/" +
            id +
            "?token=" +
            getCookie("token") +
            "&activo=" +
            activo,
        data: data,
        contentType: "application/json; charset=utf-8",
        // contentType: false,
        processData: false,
    })
        .done(function (Response) {
            //do something when get response
            showAlert(type, `${Response.nombre} ${msj} correctamente`);
            console.log(Response);
        })
        .fail(function (Response) {
            //do something when any error occurs.
            showAlert("danger", Response.error);
            console.error(Response);
        });
}

function deletecategorias(id) {
    $(".todelete").attr("href", "admin/delete/categorias/" + id);
}

function changeactivocategorias(id) {
    data = [];
    data["activo"] = $(".ch-categorias-" + id).is(":checked") ? 1 : 0;
    data["token"] = getCookie("token");
    activo = $(".ch-categorias-" + id).is(":checked") ? 1 : 0;
    msj = activo ? "Activado" : "Desactivado";
    type = activo ? "success" : "danger";

    console.log("data", data);

    $.ajax({
        async: true,
        type: "POST",
        dataType: "json",
        url:
            "./api/update/categorias/" +
            id +
            "?token=" +
            getCookie("token") +
            "&activo=" +
            activo,
        data: data,
        contentType: "application/json; charset=utf-8",
        // contentType: false,
        processData: false,
    })
        .done(function (Response) {
            //do something when get response
            showAlert(type, `${Response.nombre} ${msj} correctamente`);
            console.log(Response);
        })
        .fail(function (Response) {
            //do something when any error occurs.
            showAlert("danger", Response.error);
            console.error(Response);
        });
}

function deleteservicios(id) {
    $(".todelete").attr("href", "admin/delete/servicios/" + id);
}

function changeactivoservicios(id) {
    data = [];
    data["activo"] = $(".ch-servicios-" + id).is(":checked") ? 1 : 0;
    data["token"] = getCookie("token");
    activo = $(".ch-servicios-" + id).is(":checked") ? 1 : 0;
    msj = activo ? "Activado" : "Desactivado";
    type = activo ? "success" : "danger";

    console.log("data", data);

    $.ajax({
        async: true,
        type: "POST",
        dataType: "json",
        url:
            "./api/update/servicios/" +
            id +
            "?token=" +
            getCookie("token") +
            "&activo=" +
            activo,
        data: data,
        contentType: "application/json; charset=utf-8",
        // contentType: false,
        processData: false,
    })
        .done(function (Response) {
            //do something when get response
            showAlert(
                type,
                `El proyecto ${Response.titulo} se ha ${msj} correctamente`
            );
            console.log(Response);
        })
        .fail(function (Response) {
            //do something when any error occurs.
            showAlert("danger", Response.error);
            console.error(Response);
        });
}

function deletecurriculum(id) {
    $(".todelete").attr("href", "admin/delete/curriculum/" + id);
}

function changeactivocurriculum(id) {
    data = [];
    data["activo"] = $(".ch-curriculum-" + id).is(":checked") ? 1 : 0;
    data["token"] = getCookie("token");
    activo = $(".ch-curriculum-" + id).is(":checked") ? 1 : 0;
    msj = activo ? "Activado" : "Desactivado";
    type = activo ? "success" : "danger";

    console.log("data", data);

    $.ajax({
        async: true,
        type: "POST",
        dataType: "json",
        url:
            "./api/update/curriculum/" +
            id +
            "?token=" +
            getCookie("token") +
            "&activo=" +
            activo,
        data: data,
        contentType: "application/json; charset=utf-8",
        // contentType: false,
        processData: false,
    })
        .done(function (Response) {
            //do something when get response
            showAlert(
                type,
                `El proyecto ${Response.titulo} se ha ${msj} correctamente`
            );
            console.log(Response);
        })
        .fail(function (Response) {
            //do something when any error occurs.
            showAlert("danger", Response.error);
            console.error(Response);
        });
}

function deletehobies(id) {
    $(".todelete").attr("href", "admin/delete/hobies/" + id);
}

function changeactivohobies(id) {
    data = [];
    data["activo"] = $(".ch-hobies-" + id).is(":checked") ? 1 : 0;
    data["token"] = getCookie("token");
    activo = $(".ch-hobies-" + id).is(":checked") ? 1 : 0;
    msj = activo ? "Activado" : "Desactivado";
    type = activo ? "success" : "danger";

    console.log("data", data);

    $.ajax({
        async: true,
        type: "POST",
        dataType: "json",
        url:
            "./api/update/hobies/" +
            id +
            "?token=" +
            getCookie("token") +
            "&activo=" +
            activo,
        data: data,
        contentType: "application/json; charset=utf-8",
        // contentType: false,
        processData: false,
    })
        .done(function (Response) {
            //do something when get response
            showAlert(
                type,
                `El hobbie ${Response.titulo} se ha ${msj} correctamente`
            );
            console.log(Response);
        })
        .fail(function (Response) {
            //do something when any error occurs.
            showAlert("danger", Response.error);
            console.error(Response);
        });
}

function deletecontacto(id) {
    $(".todelete").attr("href", "admin/delete/contacto/" + id);
}

function changeactivocontacto(id) {
    data = [];
    data["activo"] = $(".ch-contacto-" + id).is(":checked") ? 1 : 0;
    data["token"] = getCookie("token");
    activo = $(".ch-contacto-" + id).is(":checked") ? 1 : 0;
    msj = activo ? "Activado" : "Desactivado";
    type = activo ? "success" : "danger";

    console.log("data", data);

    $.ajax({
        async: true,
        type: "POST",
        dataType: "json",
        url:
            "./api/update/contacto/" +
            id +
            "?token=" +
            getCookie("token") +
            "&activo=" +
            activo,
        data: data,
        contentType: "application/json; charset=utf-8",
        // contentType: false,
        processData: false,
    })
        .done(function (Response) {
            //do something when get response
            showAlert(
                type,
                `El contacto ${Response.titulo} se ha ${msj} correctamente`
            );
            console.log(Response);
        })
        .fail(function (Response) {
            //do something when any error occurs.
            showAlert("danger", Response.error);
            console.error(Response);
        });
}

function deletesecciones(id) {
    $(".todelete").attr("href", "admin/delete/secciones/" + id);
}

function changeactivosecciones(id) {
    data = [];
    data["activo"] = $(".ch-secciones-" + id).is(":checked") ? 1 : 0;
    data["notnull"] = 1;
    data["token"] = getCookie("token");
    activo = $(".ch-secciones-" + id).is(":checked") ? 1 : 0;
    msj = activo ? "Activado" : "Desactivado";
    type = activo ? "success" : "danger";

    console.log("data", data);

    $.ajax({
        async: true,
        type: "POST",
        dataType: "json",
        url:
            "./api/update/secciones/" +
            id +
            "?token=" +
            getCookie("token") +
            "&activo=" +
            activo +
            "&notnull=1",
        data: data,
        contentType: "application/json; charset=utf-8",
        // contentType: false,
        processData: false,
    })
        .done(function (Response) {
            //do something when get response
            showAlert(
                type,
                `La seccion ${Response.titulos} se ha ${msj} correctamente`
            );
            console.log(Response);
        })
        .fail(function (Response) {
            //do something when any error occurs.
            showAlert("danger", Response.error);
            console.error(Response);
        });
}

function deletetxtbanner(id) {
    $(".todelete").attr("href", "admin/delete/txtbanner/" + id);
}

function changeactivotxt(id) {
    data = [];
    data["activo"] = $(".ch-txtbanner-" + id).is(":checked") ? 1 : 0;
    data["notnull"] = 1;
    data["token"] = getCookie("token");
    activo = $(".ch-txtbanner-" + id).is(":checked") ? 1 : 0;
    msj = activo ? "Activado" : "Desactivado";
    type = activo ? "success" : "danger";

    console.log("data", data);

    $.ajax({
        async: true,
        type: "POST",
        dataType: "json",
        url:
            "./api/update/txtbanner/" +
            id +
            "?token=" +
            getCookie("token") +
            "&activo=" +
            activo +
            "&notnull=1",
        data: data,
        contentType: "application/json; charset=utf-8",
        // contentType: false,
        processData: false,
    })
        .done(function (Response) {
            //do something when get response
            showAlert(
                type,
                `El texto ${Response.txtBanner} se ha ${msj} correctamente`
            );
            console.log(Response);
        })
        .fail(function (Response) {
            //do something when any error occurs.
            showAlert("danger", Response.error);
            console.error(Response);
        });
}

//MODAL DE DESCRIPCIONES DE PROYECTOS.
$(document).on("click", ".showdescrip", function (e) {
    e.preventDefault();
    Swal.fire({ title: $(this).data("description") });
});

// VERIFICACIONES DE CLASE IMG PARA AGREGADO DE BTN
// console.log("cantidad", $(".input-group-text").length );
if ($(".input-group-text").length > 0) {

    $(".input-group-text").each(function () { // recorremos cada una de la que exista con la correspondiente url
        var img = $(this).attr("href"); // con el atributo href obtnemos la url de la clase
        // console.log("imagen", img);
        if (img) {
            var html = `
            <button class="btn btn-outline-secondary del-img" type="button" data-img="${img}">
                <i class="fa fa-trash"></i>
            </button>
            `;
            $(this).parent().append(html);
        }
    });
}

// ajax para borrar las img
$(document).on("click", ".del-img", function (e) {
    e.preventDefault(); // Evitar que se siga el enlace por defecto

    var ruta = $(this).data("img"); // Obtener la ruta de la imagen a eliminar
    // console.log("href", ruta);
    Swal.fire({
        title: '¿Estás seguro?',
        text: '¡No podrás revertir esto!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminarlo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Capturar la URL actual
            const url = window.location.href;

            // Crear un elemento URL para descomponerla
            const urlObj = new URL(url);
            // console.log("utl", urlObj);
            
            // Separando el pathname en partes individuales
            const pathSegments = urlObj.pathname.split('/').filter(segment => segment.length > 0);

            // console.log("path", pathSegments);
            // Obtener update, secciones e id
            const funcion = pathSegments[1];
            const seccion = pathSegments[2];
            const id = pathSegments[3];

            // Construir el objeto data con todos los datos necesarios
            // Objeto de datos original
            const data = {
                funcion: funcion,
                seccion: seccion,
                id: id,
                token: getCookie("token"),
                ruta: ruta
            };
            // console.log("data", data);
            // Crear un nuevo FormData
            const formData = new FormData();

            // Iterar sobre el objeto data y añadir cada par clave-valor al FormData
            $get = ''
            for (let key in data) {
                if (!$get) {
                    $get += '?'
                }
                $get += `${key}=${data[key]}&`;
                formData.append(key, data[key]);
                // console.log("key", key);

            }
            // console.log("get", $get);
            // Construir la URL para la solicitud AJAX
            const ajaxUrl = `./api/delete/img/`;

            $.ajax({
                url: ajaxUrl + $get,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false, // Evitar que jQuery convierta los datos en una cadena de consulta
                beforeSend: function () {

                },
                success: function (response) {
                    console.log('Respuesta del servidor:', response);
                    Swal.fire(
                        '¡Eliminado!',
                        'Tu archivo ha sido eliminado.',
                        'success'
                    );
                },
                error: function (xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', error);
                    Swal.fire(
                        'Abortado',
                        'El servidor no pudo eliminar el archivo',
                        'error'
                    );
                }
            });

        } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire(
                'Cancelado',
                'Tu archivo está a salvo :)',
                'error'
            );
        }
    });
});


