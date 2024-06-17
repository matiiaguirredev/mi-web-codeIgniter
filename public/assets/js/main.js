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

// verificaciones de clase de img 
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

$(document).on("click", ".del-img", function (e) {
    var ruta = $(this).attr("href");
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
            data = [];
            data["ruta"] = ruta;
            data["token"] = getCookie("token");

            $.ajax({
                async: true,
                type: "POST",
                dataType: "json",
                url:
                    "./api/delete/img/?token=" + 
                    getCookie("token") +
                    "&ruta=" +
                    ruta,
                data: data,
                contentType: "application/json; charset=utf-8",
                // contentType: false,
                processData: false,
            })
                .done(function (Response) {
                    Swal.fire(
                        '¡Eliminado!',
                        'Tu archivo ha sido eliminado.',
                        'success'
                    );
                })
                .fail(function (Response) {
                    Swal.fire(
                        'Abortado',
                        'El servidor no pudo eliminar el archivo',
                        'error'
                    );
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