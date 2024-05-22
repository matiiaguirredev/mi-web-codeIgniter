<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;

class Api extends BaseController {

    // esta es la unica manera de utilizar variables entre funciones , son lo mimso q metodos o funciones
    private $db = null;
    private $key = null;
    private $system = null;
    private $lang = "es";
    private $currentDate = null;
    private $user = null;
    private $activo = 0;

    public function __construct() {

        $this->db = \Config\Database::connect();

        $this->key = getenv('KEYENCRIPT');

        $this->system = (object)[
            "title" => getenv('GENERAL_TITLE'),
            "descrip" => getenv('GENERAL_DESCRIP'),
            "register" => filter_var(getenv('GENERAL_REGISTER'), FILTER_VALIDATE_BOOLEAN), // esto conviente el valor de env en booleano
        ];

        // $this->currentDate = Time::now(); // funcion de codeige

        $hoy = getdate();
        $this->currentDate = $hoy['year'] . '-' . $hoy['mon'] . '-' . $hoy['mday'] . ' ' . $hoy['hours'] . ':' . $hoy['minutes'] . ':' . $hoy['seconds'];
    }

    public function index() {
        $this->valToken();
        json_debug($this->user);
    }

    public function register() {
        // esto es lo primero que se tiene que hacer en un registro !
        // debug($this->system->register);
        if (!$this->system->register) {
            // el error es de registro desactiv
            custom_error(207, $this->lang);
        }

        // $data = $this->request->getGetPost(); no sirve porque usamos base d datos estructurada mysql y no mongo.

        $require = [ // datos obligatorios
            "usuario" => "alias",
            "email" => "email",
            "pasw" => "password"
        ];

        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else { //  cuanod no te envian los datos requeridos se agrega en esta validacion // esto hace que si o si sea requerido
                $valRequire[] = $name; // lo pushea al ultimo
            }
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, $this->lang, $valRequire);
        }

        // almacenamiento temporario de los datos enviados por el form
        $usuario = $data['usuario'];
        $email = $data['email'];

        $query = $this->db->query("SELECT * FROM usuarios WHERE usuario = '$usuario' OR email = '$email'"); // estamos chekeando si existe el usuario o emial
        $checkUser = $query->getResult();
        if ($checkUser) {
            custom_error(208, $this->lang);
        }

        $optionals = [ // datos opcionales 
            "nombre",
            "apellido",
        ];

        foreach ($optionals as $name) {
            $data[$name] = $this->request->getGetPost($name);
        }

        $data['pasw'] = encode($data['pasw'], $this->key);

        $insert = $this->db->table("usuarios")->insert($data);
        // $insert = true; // este era de test
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $id = $this->db->insertID(); // ultimo identificador insertado !
        // $id = 5; // este era un test

        $data['token'] = encode(json_encode([
            'id' => $id,
            'date' => $this->currentDate
        ]), $this->key);

        unset($data['pasw']);

        $this->create_perfil($id);

        json_debug(array_merge(["id" => $id], $data));
    }

    public function login() {

        $require = [
            "usuario" => "alias",
            "pasw" => "password"
        ];

        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                // $data[$name] = validateValue($value, $type, $this->lang); esto se comento por que si ya se registro quiere decir que ya cumplio los parametros necesarios.
                $data[$name] = $value;
            } else {
                $valRequire[] = $name;
            }
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, $this->lang, $valRequire);
        }

        $usuario = $email = $data['usuario'];

        $query = $this->db->query("SELECT * FROM usuarios WHERE usuario = '$usuario' OR email = '$email'");
        $checkUser = $query->getResult();

        if (!$checkUser) {
            custom_error(201, $this->lang);
        }

        $checkUser = $checkUser[0];

        $passw = decode($checkUser->pasw, $this->key);

        if ($passw != $data['pasw']) {
            custom_error(202, $this->lang);
        }

        $checkUser->token = encode(json_encode([
            'id' => $checkUser->id,
            'date' => $this->currentDate
        ]), $this->key);

        unset($checkUser->pasw); // la borramos por que no necesitamos enviarle a nadie la pasw / no se hace publica

        json_debug($checkUser);
    }

    public function checkToken() {
        $require = [
            "token" => "text",
        ];

        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, $this->lang, $valRequire);
        }

        $valtoken = json_decode(decode($data['token'], $this->key));
        // debug($valtoken, false);

        if (!$valtoken) {
            custom_error(103, $this->lang, 'token');
        }

        // aqui va la validación de tiempo de token
        // debug($this->currentDate, false);

        $tokenTimestamp = strtotime($valtoken->date); // referencia al momento de creacion, la fecha que se puso en el momento de asignarlo
        $currentTimestamp = strtotime($this->currentDate); // este hace referencia al ahora mismo. a la facha y hora actual

        // Calcular la diferencia en segundos
        $difference = $currentTimestamp - $tokenTimestamp;

        $maxTime = getenv('SESION_TIME') * 60; // 60 minutos

        if ($difference > $maxTime) {
            // custom_error(502, $this->lang); esta comentado por el momento para que no expire el tiempo
        }

        $query = $this->db->query("SELECT * FROM usuarios WHERE id = '$valtoken->id'");
        $checkUser = $query->getResult(); // los datos del usuario
        if (!$checkUser) {
            custom_error(501, $this->lang);
        }

        $checkUser = $checkUser[0]; 
        // debug($checkUser);

        unset($checkUser->pasw);
        json_debug($checkUser);
        // $this->user = $checkUser;
    }

    // CRUD PROYECTOS
    public function create_proyect() {
        $this->valToken();

        $require = [
            "nombre" => "text",
            "descripcion" => "text",
            "url" => "url",
            "cat_id" => "number",

        ];

        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        $data["img_proyecto"] = $this->uploadImage("proyectos", "img_proyecto"); // nombre de carpeta y desp campo de bd 
        if (!$data["img_proyecto"]) {
            // $valRequire[] = "img_proyecto";
            $data["img_proyecto"] = "300x300.jpg"; // aca esta opcional y tenemos una por defecto, si queremos obligatoria descomentar arriba.
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $insert = $this->db->table("proyectos")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $data["img_proyecto"] = base_url() . "assets/images/proyectos/" . $data["img_proyecto"];

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_proyect($id = null) {
        $this->variables();

        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM proyectos ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "proyectos");
        }

        //esto es para agregar url a la imagen !
        foreach ($datos as $key => $value) {
            if ($value->img_proyecto) {
                $datos[$key]->img_proyecto = base_url() . "assets/images/proyectos/" . $value->img_proyecto;
            }
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_proyect($id) {
        $this->valToken();

        $query = "SELECT * FROM proyectos WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "proyectos");
        }

        $datos = $datos[0];


        $require = [
            "nombre" => "text",
            "descripcion" => "text",
            "url" => "url",
            "cat_id" => "number",
        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            }
        }

        $data["img_proyecto"] = $this->uploadImage("proyectos", "img_proyecto"); // nombre de carpeta y desp campo de bd 
        if (!$data["img_proyecto"]) {
            // $valRequire[] = "img_proyecto";
            $data["img_proyecto"] = $datos->img_proyecto;
        }

        $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;


        $update = $this->db->table("proyectos")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        if (!$update) {
            custom_error(506, $this->lang, "proyectos");
        }

        $data["img_proyecto"] = base_url() . "assets/images/proyectos/" . $data["img_proyecto"];

        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_proyect($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM proyectos WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "proyectos");
        }

        $delete = $this->db->table("proyectos")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "proyectos");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }
    // FIN CRUD PROYECTOS

    // CRUD LENGUAJES
    public function create_lenguaje() {
        $this->valToken();

        $require = [
            "nombre" => "text",
            // "descripcion" => "text",
            "porcentaje" => "number",
        ];

        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        $data["img_lenguajes"] = $this->uploadImage("lenguajes", "img_lenguajes"); // nombre de carpeta y desp campo de bd 
        if (!$data["img_lenguajes"]) {
            // $valRequire[] = "img_lenguajes";
            $data["img_lenguajes"] = "300x300.jpg"; // aca esta opcional y tenemos una por defecto, si queremos obligatoria descomentar arriba.
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $insert = $this->db->table("lenguajes")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }


        $data["img_lenguajes"] = base_url() . "assets/images/lenguajes/" . $data["img_lenguajes"];

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_lenguaje($id = null) {
        $this->variables();

        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM lenguajes ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "lenguajes");
        }

        //esto es para agregar url a la imagen !
        foreach ($datos as $key => $value) {
            if ($value->img_lenguajes) {
                $datos[$key]->img_lenguajes = base_url() . "assets/images/lenguajes/" . $value->img_lenguajes;
            }
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_lenguaje($id) {
        $this->valToken();

        $query = "SELECT * FROM lenguajes WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "lenguajes");
        }

        $datos = $datos[0];


        $require = [
            "nombre" => "text",
            // "descripcion" => "text",
            "porcentaje" => "number",
        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            }
        }

        $data["img_lenguajes"] = $this->uploadImage("lenguajes", "img_lenguajes"); // nombre de carpeta y desp campo de bd 
        if (!$data["img_lenguajes"]) {
            // $valRequire[] = "img_lenguajes";
            $data["img_lenguajes"] = $datos->img_lenguajes;
        }

        $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;


        $update = $this->db->table("lenguajes")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        if (!$update) {
            custom_error(506, $this->lang, "lenguajes");
        }

        $data["img_lenguajes"] = base_url() . "assets/images/lenguajes/" . $data["img_lenguajes"];

        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_lenguaje($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM lenguajes WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "lenguajes");
        }

        $delete = $this->db->table("lenguajes")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "lenguajes");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }
    // FIN CRUD LENGUAJES

    // CRUD REDES

    public function create_redes() {
        $this->valToken();

        $require = [
            "nombre" => "text",
            "url" => "url",
            "iconHTML" => "text",

        ];

        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        $data["icon"] = $this->uploadImage("redes", "icon"); // nombre de carpeta y desp campo de bd 
        if (!$data["icon"]) {
            // $valRequire[] = "icon";
            $data["icon"] = "300x300.jpg"; // aca esta opcional y tenemos una por defecto, si queremos obligatoria descomentar arriba.
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        // $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $nombre = $data['nombre'];
        $url = $data['url'];

        $query = $this->db->query("SELECT * FROM redes WHERE nombre = '$nombre' OR url = '$url'"); // estamos chekeando si existe el usuario o emial
        $checkRed = $query->getResult();
        if ($checkRed) {
            custom_error(208, $this->lang);
        }

        $insert = $this->db->table("redes")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $data["icon"] = base_url() . "assets/images/redes/" . $data["icon"];

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_redes($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM redes ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "redes");
        }

        //esto es para agregar url a la imagen !
        foreach ($datos as $key => $value) {
            if ($value->icon) {
                $datos[$key]->icon = base_url() . "assets/images/redes/" . $value->icon;
            }
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_redes($id) {
        $this->valToken();

        $query = "SELECT * FROM redes WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "redes");
        }

        $datos = $datos[0];



        $require = [
            "nombre" => "text",
            "url" => "url",
            "iconHTML" => "text",
            // "icon" => "text",
            // "descripcion" => "text",
            // "porcentaje" => "number",
        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            }
        }

        $data["icon"] = $this->uploadImage("redes", "icon"); // nombre de carpeta y desp campo de bd 
        if (!$data["icon"]) {
            // $valRequire[] = "icon";
            $data["icon"] = $datos->icon;
        }

        // $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        if ($this->request->getGetPost("nombre")) {
            $nombre = $data['nombre'];
            $query = $this->db->query("SELECT * FROM redes WHERE nombre = '$nombre' AND id <> '$id'"); // estamos chekeando si existe el usuario o emial
            $checkRed = $query->getResult();
            if ($checkRed) {
                custom_error(208, $this->lang);
            }
        }
        $update = $this->db->table("redes")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        if (!$update) {
            custom_error(506, $this->lang, "redes");
        }

        $data["icon"] = base_url() . "assets/images/redes/" . $data["icon"];

        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_redes($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM redes WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "redes");
        }

        $delete = $this->db->table("redes")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "redes");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    // FIN CRUD REDES

    // CRUD CATEGORIAS
    public function create_categorias() {
        $this->valToken();

        $require = [
            "nombre" => "text",

        ];


        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $nombre = $data['nombre'];

        $query = $this->db->query("SELECT * FROM categorias WHERE nombre = '$nombre'"); // estamos chekeando si existe el usuario o emial
        $checkRed = $query->getResult();
        if ($checkRed) {
            custom_error(208, $this->lang);
        }

        $insert = $this->db->table("categorias")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_categorias($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM categorias ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "categorias");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_categorias($id) {
        $this->valToken();

        $query = "SELECT * FROM categorias WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "categorias");
        }

        $datos = $datos[0];

        $require = [
            "nombre" => "text",

        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            }
        }

        $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        if ($this->request->getGetPost("nombre")) {
            $nombre = $data['nombre'];
            $query = $this->db->query("SELECT * FROM categorias WHERE nombre = '$nombre' AND id <> '$id'"); // estamos chekeando si existe el usuario o emial
            $checkRed = $query->getResult();
            if ($checkRed) {
                custom_error(208, $this->lang);
            }
        }


        $update = $this->db->table("categorias")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        if (!$update) {
            custom_error(506, $this->lang, "categorias");
        }


        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_categorias($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM categorias WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "categorias");
        }

        $delete = $this->db->table("categorias")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "categorias");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }
    // FIN CRUD CATEGORIAS

    // CRUD SERVICIOS
    public function create_servicios() {
        $this->valToken();

        $require = [
            "titulo" => "text",
            "descripcion" => "text",
            "iconHTML" => "text",

        ];


        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        // $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $titulo = $data['titulo'];

        $query = $this->db->query("SELECT * FROM servicios WHERE titulo = '$titulo'"); // estamos chekeando si existe el usuario o emial
        $checkServicio = $query->getResult();
        if ($checkServicio) {
            custom_error(208, $this->lang);
        }

        $insert = $this->db->table("servicios")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_servicios($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM servicios ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "categorias");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_servicios($id) {
        $this->valToken();

        $query = "SELECT * FROM servicios WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "servicios");
        }

        $datos = $datos[0];

        $require = [
            "titulo" => "text",
            "descripcion" => "text",
            "iconHTML" => "text",

        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            }
        }

        // $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        if ($this->request->getGetPost("titulo")) {
            $titulo = $data['titulo'];
            $query = $this->db->query("SELECT * FROM servicios WHERE titulo = '$titulo' AND id <> '$id'"); // estamos chekeando si existe el usuario o emial
            $checkServicios = $query->getResult();
            if ($checkServicios) {
                custom_error(208, $this->lang);
            }
        }


        $update = $this->db->table("servicios")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        if (!$update) {
            custom_error(506, $this->lang, "servicios");
        }


        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_servicios($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM servicios WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "servicios");
        }

        $delete = $this->db->table("servicios")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "servicios");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }
    // FIN CRUD SERVICIOS

    // CRUD CURRICULUM
    public function create_curriculum() {
        $this->valToken();

        $require = [
            "titulo" => "text",
            "sub_titulo" => "text",
            "rango_anos" => "number",
            // "desde" => "text",
            // "hasta" => "text",
            "descripcion" => "text",
            "categoria" => "text",

        ];


        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        // $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0; // si se mando por es 1, si no se envio es 0 (true false)

        $titulo = $data['titulo'];

        $query = $this->db->query("SELECT * FROM resume WHERE titulo = '$titulo'"); // estamos chekeando si existe ese titulo cargado
        $checkResume = $query->getResult();
        if ($checkResume) {
            custom_error(208, $this->lang); // el titulo debe ser unico, si existe da error
        }

        $insert = $this->db->table("resume")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang); // si no se inserto leemos el error
        }

        $id = $this->db->insertID(); // a la variable id le agregamos el valor del ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data)); // imprimos en json todos los datos agregados con su id
    }

    public function get_curriculum($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM resume ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "resume");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_curriculum($id) {
        $this->valToken();

        $query = "SELECT * FROM resume WHERE id = '$id'";
        $query = $this->db->query($query); // realizamos la operacion
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "resume");
        }

        $datos = $datos[0];

        $require = [
            "titulo" => "text",
            "sub_titulo" => "text",
            "rango_anos" => "number",
            // "desde" => "text",
            // "hasta" => "text",
            "descripcion" => "text",
            "categoria" => "text",

        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            }
        }

        // $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        if ($this->request->getGetPost("titulo")) {
            $titulo = $data['titulo'];
            $query = $this->db->query("SELECT * FROM resume WHERE titulo = '$titulo' AND id <> '$id'"); // estamos chekeando si existe el usuario o emial
            $checkResume = $query->getResult();
            if ($checkResume) {
                custom_error(208, $this->lang); // el titulo no puede ser modificado
            }
        }


        $update = $this->db->table("resume")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        if (!$update) {
            custom_error(506, $this->lang, "resume");
        }


        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_curriculum($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM resume WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "resume");
        }

        $delete = $this->db->table("resume")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "resume");
        }

        $datos = $datos[0];

        json_debug($datos);
    }
    // FIN CRUD CURRICULUM

    // CRUD PERFIL
    public function create_perfil($id = false) {
        if (!$id) {
            $this->valToken();
            $id = $this->user->id;
        }

        $query = "SELECT * FROM perfil WHERE user_id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if ($datos) {
            $this->update_perfil();
        }
        $require = [
            "nombre" => "text",
            "apellido" => "text",
            "edad" => "number",
            "tel" => "tel",
            "direc" => "text",
            "descripcion" => "text",

        ];

        $valRequire = [];
        // debug($this->request->getGetPost("tel"), false);
        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $data[$name] = null;
                // $valRequire[] = $name; // al comentar esta linea no se solicita requerido nada.
            }
        }
        // debug($data["tel"]);

        $data["img"] = $this->uploadImage("perfil", "img"); // nombre de carpeta y despues campo de base datos
        if (!$data["img"]) {
            // $valRequire[] = "img";
            $data["img"] = "300x300.png"; // aca esta opcional y tenemos una por defecto, si queremos obligatoria descomentar arriba.
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        $data["user_id"] = $this->user->id;
        // $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $insert = $this->db->table("perfil")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }


        $data["img"] = base_url() . "assets/images/perfil/" . $data["img"];

        $id = $this->db->insertID(); // obtenemos el ultimo identificador insertado !

        json_debug((object)array_merge(["id" => $id], $data));
    }

    public function get_perfil($id = null) {
        // $this->variables();
        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro
        // debug($this->user);
        $id = $this->user->id;
        $query = "SELECT * FROM perfil WHERE user_id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            $this->create_perfil();
        }
        // json_debug($this->user);

        $datos = $datos[0];

        $datos->img = base_url() . "assets/images/perfil/" . $datos->img;

        json_debug($datos);
    }

    public function update_perfil($id = null) {
        $this->valToken();

        $user_id = $this->user->id;
        $query = "SELECT * FROM perfil WHERE user_id = '$user_id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            $this->create_perfil();
        }

        $datos = $datos[0];
        $id = $datos->id;

        $require = [
            "nombre" => "text",
            "apellido" => "text",
            "edad" => "number",
            "tel" => "tel",
            "direc" => "text",
            "descripcion" => "text",

        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            }
        }
        $data["img"] = $this->uploadImage("perfil", "img"); // nombre de carpeta y despues campo de base datos
        if (!$data["img"]) {
            // $valRequire[] = "img";
            $data["img"] = $datos->img; // aca esta opcional y tenemos una por defecto, si queremos obligatoria descomentar arriba.
        }

        $data["user_id"] = $this->user->id;
        // $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;


        $update = $this->db->table("perfil")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        if (!$update) {
            custom_error(506, $this->lang, "perfil");
        }

        $data["img"] = base_url() . "assets/images/perfil/" . $data["img"];

        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_perfil($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM perfil WHERE user_id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "perfil");
        }

        $delete = $this->db->table("perfil")->delete(["user_id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "perfil");
        }

        $datos = $datos[0];

        json_debug($datos);
    }
    // FIN CRUD PERFIL

    // CRUD HOBIES
    public function create_hobies() {
        $this->valToken();

        $require = [
            "titulo" => "text",

        ];


        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        $data["descripcion"] = ($this->request->getGetPost("descripcion"));
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $titulo = $data['titulo'];

        $query = $this->db->query("SELECT * FROM hobies WHERE titulo = '$titulo'"); // estamos chekeando si existe el usuario o emial
        $checkHobies = $query->getResult();
        if ($checkHobies) {
            custom_error(208, $this->lang);
        }

        $insert = $this->db->table("hobies")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_hobies($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM hobies ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "hobies");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_hobies($id) {
        $this->valToken();

        $query = "SELECT * FROM hobies WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "hobies");
        }

        $datos = $datos[0];

        $require = [
            "titulo" => "text",
            "descripcion" => "text",

        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        $data["descripcion"] = ($this->request->getGetPost("descripcion"));
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        if ($this->request->getGetPost("titulo")) {
            $titulo = $data['titulo'];
            $query = $this->db->query("SELECT * FROM hobies WHERE titulo = '$titulo' AND id <> '$id'"); // estamos chekeando si existe el usuario o emial
            $checkHobies = $query->getResult();
            if ($checkHobies) {
                custom_error(208, $this->lang);
            }
        }


        $update = $this->db->table("hobies")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        if (!$update) {
            custom_error(506, $this->lang, "hobies");
        }


        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_hobies($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM hobies WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "hobies");
        }

        $delete = $this->db->table("hobies")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "hobies");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }
    // FIN CRUD HOBIES

    // CRUD CONTACTO
    public function create_contacto() {
        $this->valToken();

        $require = [
            "iconHTML" => "text",
            "info" => "text",
            "url" => "text",

        ];


        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        // $data["user_id"] = $this->user->id;
        $data["info_secundaria"] = ($this->request->getGetPost("info_secundaria"));
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $iconHTML = $data['iconHTML'];
        $url = $data['url'];

        $query = $this->db->query("SELECT * FROM contacto WHERE iconHTML = '$iconHTML' OR url = '$url'"); // estamos chekeando si existe el usuario o emial
        $checkContacto = $query->getResult();
        if ($checkContacto) {
            custom_error(208, $this->lang);
        }

        $insert = $this->db->table("contacto")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_contacto($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM contacto ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "contacto");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_contacto($id) {
        $this->valToken();

        $query = "SELECT * FROM contacto WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "contacto");
        }

        $datos = $datos[0];

        $require = [
            "iconHTML" => "text",
            "info" => "text",
            "url" => "text",

        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }
        // debug($value,false);


        // $data["user_id"] = $this->user->id;
        $data["info_secundaria"] = ($this->request->getGetPost("info_secundaria"));
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;
        // debug($data,false);


        if ($this->request->getGetPost("iconHTML")) {
            $iconHTML = $data['iconHTML'];
            $url = $data['url'];
            $query = $this->db->query("SELECT * FROM contacto WHERE iconHTML = '$iconHTML' AND id <> '$id' OR url = '$url' AND id <> '$id'"); // estamos chekeando si existe el usuario o emial
            $checkContacto = $query->getResult();
            if ($checkContacto) {
                custom_error(208, $this->lang);
            }
        }
        // debug($checkContacto);

        $update = $this->db->table("contacto")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        if (!$update) {
            custom_error(506, $this->lang, "contacto");
        }


        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_contacto($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM contacto WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "contacto");
        }

        $delete = $this->db->table("contacto")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "contacto");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }
    // FIN CRUD CONTACTO

    // CRUD SECCIONES TITULOS/DESCRIPTIONS Y MAS
    public function create_secciones() {
        $this->valToken();

        $require = [
            "alias" => "text",
            "orden" => "number",
        ];

        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        $optionals = [ // datos opcionales 
            "titulos",
            "descripciones",
            "bg_img",
            "bg_color",
        ];

        foreach ($optionals as $name) {
            $data[$name] = $this->request->getGetPost($name);
        }

        $data["bg_img"] = $this->uploadImage("secciones", "bg_img"); // nombre de carpeta y desp campo de bd 


        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $insert = $this->db->table("secciones")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $data["bg_img"] = base_url() . "assets/images/secciones/" . $data["bg_img"];
        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_secciones($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        // $query = "SELECT * FROM secciones ";
        $query = "SELECT * FROM `secciones` ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }

        $query .= " ORDER BY `secciones`.`orden` ASC";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "secciones");
        }

        //esto es para agregar url a la imagen !
        foreach ($datos as $key => $value) {
            if ($value->bg_img) {
                $datos[$key]->bg_img = base_url() . "assets/images/secciones/" . $value->bg_img;
            }
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_secciones($id) {
        $this->valToken();

        $query = "SELECT * FROM secciones WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "secciones");
        }

        $datos = $datos[0];


        $optionals = [ // datos opcionales 
            "titulos",
            "descripciones",
            "bg_img",
            "bg_color",
            "alias",
            "orden",
        ];

        if (!$this->request->getGetPost("notnull")) {
            foreach ($optionals as $name) {
                $data[$name] = $this->request->getGetPost($name);
            }
        }

        $data["bg_img"] = $this->uploadImage("secciones", "bg_img"); // nombre de carpeta y desp campo de bd 
        if (!$data["bg_img"]) {
            // $valRequire[] = "bg_img";
            $data["bg_img"] = $datos->bg_img;
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;
        // debug($this->request->getGetPost(), false);
        // debug($data);

        $update = $this->db->table("secciones")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        // debug($datos);
        if (!$update) {
            custom_error(506, $this->lang, "secciones");
        }

        $data["bg_img"] = base_url() . "assets/images/secciones/" . $data["bg_img"];
        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_secciones($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM secciones WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "secciones");
        }

        $delete = $this->db->table("secciones")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "general");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }
    // FIN CRUD SECCIONES TITULOS/DESCRIPTIONS Y MAS

    private function valToken() {
        $require = [
            "token" => "text",
        ];

        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, $this->lang, $valRequire);
        }

        $valtoken = json_decode(decode($data['token'], $this->key)); // decodificamos y validamos el token
        // debug($valtoken, false);

        if (!$valtoken) {
            custom_error(103, $this->lang, 'token'); // si el token no tiene formato correcto
        }

        // aqui va la validación de tiempo de token
        // debug($this->currentDate, false);

        $tokenTimestamp = strtotime($valtoken->date); // referencia al momento de creacion, la fecha que se puso en el momento de asignarlo
        $currentTimestamp = strtotime($this->currentDate); // este hace referencia al ahora mismo. a la facha y hora actual

        // Calcular la diferencia en segundos
        $difference = $currentTimestamp - $tokenTimestamp;

        $maxTime = getenv('SESION_TIME') * 60; // 60 minutos // esta funcion es para traer valores del arch env

        if ($difference > $maxTime) {
            // custom_error(502, $this->lang); esta comentado por el momento para que no expire el tiempo
        }

        $query = $this->db->query("SELECT * FROM usuarios WHERE id = '$valtoken->id'");
        $checkUser = $query->getResult();
        if (!$checkUser) {
            custom_error(501, $this->lang); // si el usuario no existe
        }

        $checkUser = $checkUser[0];
        // debug($checkUser);

        unset($checkUser->pasw);
        $this->user = $checkUser;
    }

    private function uploadImage($carpeta, $inputName) {
        $rutaCarpeta = 'assets/images/' . $carpeta;

        // Obtener el archivo cargado
        $file = $this->request->getFile($inputName);

        if (!$file) {
            return false;
        }

        if (!file_exists($rutaCarpeta)) {
            // Intentar crear la carpeta con permisos
            if (!mkdir($rutaCarpeta)) {
                // No se pudo crear la carpeta, devuelve false
                return false;
            }
        }

        // debug($file, false);
        // show_error($file);
        // Verificar si el archivo ha sido cargado
        if (!$file->isValid()) {
            return false;
        }

        // Generar un nombre encriptado para el archivo con extension
        $nombreEncriptado = md5($file->getName() . microtime()) . '.' . $file->getClientExtension();

        // Mover el archivo a la carpeta especificada
        $file->move($rutaCarpeta, $nombreEncriptado);


        // Devolver el nombre encriptado del archivo
        return $nombreEncriptado;
    }

    private function variables() {
        $this->activo = $this->request->getGetPost('activo');
        if ($this->activo && $this->activo != 1) {
            $this->activo = 1;
        }
    }

    public function test() {

        // debug($this->db);

        $query   = $this->db->query('SELECT * FROM hola');
        $results = $query->getResult();

        json_debug($results);

        // foreach ($results as $row) {
        //     echo $row->title;
        //     echo $row->name;
        //     echo $row->email;
        // }

        echo 'Total Results: ' . count($results);
    }
}
