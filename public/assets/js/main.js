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
