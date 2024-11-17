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
    private $captchaUrl = 'https://www.google.com/recaptcha/api/siteverify';

    private $perm = [
        "Root" => null,
        "Administrador" => null,
        "UsuarioEstandar" => null,
    ];

    public function __construct() {

        $this->db = \Config\Database::connect();

        $this->key = getenv('KEYENCRIPT');

        $this->system = (object)[
            "title" => getenv('GENERAL_TITLE'),
            "descrip" => getenv('GENERAL_DESCRIP'),
            "register" => filter_var(getenv('GENERAL_REGISTER'), FILTER_VALIDATE_BOOLEAN), // esto conviente el valor de env en booleano
        ];

        // debug($this->perm, false);
        foreach ($this->perm as $key => $value) {
            $this->perm[$key] = getenv("ROLE_ID_$key");
        }
        // debug($this->perm);

        // $this->currentDate = Time::now(); // funcion de codeige

        $hoy = getdate();
        // es para establecer fecha actual de toda la ejecucion, asi evitamos problemas.
        $this->currentDate = $hoy['year'] . '-' . $hoy['mon'] . '-' . $hoy['mday'] . ' ' . $hoy['hours'] . ':' . $hoy['minutes'] . ':' . $hoy['seconds'];
    }

    public function index() {
        // $this->valToken();
        // json_debug($this->user);
        echo view('admin/pages-404');

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

    public function checktoken() {
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

        $query = $this->db->query("SELECT usuarios.*, roles.nombre, roles.ver, roles.crear, roles.editar, roles.borrar FROM usuarios, roles WHERE usuarios.role_id = roles.id AND usuarios.id = '$valtoken->id'");
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

    // CRUD proyect
    public function create_proyect() {
        $this->valToken();

        // intentado poner los permisos
        $rolesAllowed = [
            $this->perm['Root'],
        ];

        if (!in_array($this->user->role_id, $rolesAllowed)) {
            // Sin permisos
            custom_error(403, "es", "create/proyect");
        }

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

        $data["img"] = $this->uploadImage("proyect", "img"); // nombre de carpeta y desp campo de bd 
        if (!$data["img"]) {
            // $valRequire[] = "img";
            $data["img"] = "300x300.jpg"; // aca esta opcional y tenemos una por defecto, si queremos obligatoria descomentar arriba.
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $insert = $this->db->table("proyect")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $data["img"] = base_url() . "assets/images/proyect/" . $data["img"];

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_proyect($id = null) {
        $this->variables();

        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM proyect ";
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
            custom_error(504, $this->lang, "proyect");
        }

        //esto es para agregar url a la imagen !
        foreach ($datos as $key => $value) {
            if ($value->img) {
                $datos[$key]->img = base_url() . "assets/images/proyect/" . $value->img;
            }
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_proyect($id) {
        $this->valToken();

        $query = "SELECT * FROM proyect WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "proyect");
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

        $data["img"] = $this->uploadImage("proyect", "img"); // nombre de carpeta y desp campo de bd 
        if (!$data["img"]) {
            // $valRequire[] = "img";
            $data["img"] = $datos->img;
        }

        $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;


        $update = $this->db->table("proyect")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        if (!$update) {
            custom_error(506, $this->lang, "proyect");
        }

        $data["img"] = base_url() . "assets/images/proyect/" . $data["img"];

        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_proyect($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM proyect WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "proyect");
        }

        $delete = $this->db->table("proyect")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "proyect");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }
    // FIN CRUD proyect

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
            if (isset($value->img_lenguajes)) {
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


        $data["user_id"] = $this->user->id;
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;


        $update = $this->db->table("lenguajes")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        if (!$update) {
            custom_error(506, $this->lang, "lenguajes");
        }


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

        $data["img_fondo"] = $this->uploadImage("perfil", "img_fondo"); // nombre de carpeta y despues campo de base datos
        if (!$data["img_fondo"]) {
            // $valRequire[] = "img_fondo";
            $data["img_fondo"] = "300x300.png"; // aca esta opcional y tenemos una por defecto, si queremos obligatoria descomentar arriba.
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
        $data["img_fondo"] = base_url() . "assets/images/perfil/" . $data["img_fondo"];

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
        $datos->img_fondo = base_url() . "assets/images/perfil/" . $datos->img_fondo;

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

        $data["img_fondo"] = $this->uploadImage("perfil", "img_fondo"); // nombre de carpeta y despues campo de base datos
        if (!$data["img_fondo"]) {
            // $valRequire[] = "img_fondo";
            $data["img_fondo"] = $datos->img_fondo; // aca esta opcional y tenemos una por defecto, si queremos obligatoria descomentar arriba.
        }

        $data["user_id"] = $this->user->id;
        // $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;


        $update = $this->db->table("perfil")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        if (!$update) {
            custom_error(506, $this->lang, "perfil");
        }

        $data["img"] = base_url() . "assets/images/perfil/" . $data["img"];
        $data["img_fondo"] = base_url() . "assets/images/perfil/" . $data["img_fondo"];


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
            }
        }
        // debug($value,false);

        // $data["user_id"] = $this->user->id;

        if (!$this->request->getGetPost("notnull")) {
            $data["info_secundaria"] = ($this->request->getGetPost("info_secundaria"));
        }
        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;
        //debug($data);


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
            "sub_titulo",
            "descripciones",
            "img",
            "bg_color",
            "txt_btn",
            "link_secc",
        ];

        foreach ($optionals as $name) {
            $data[$name] = $this->request->getGetPost($name);
        }

        $data["img"] = $this->uploadImage("secciones", "img"); // nombre de carpeta y desp campo de bd 

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $insert = $this->db->table("secciones")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $data["img"] = base_url() . "assets/images/secciones/" . $data["img"];
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
            if ($value->img) {
                $datos[$key]->img = base_url() . "assets/images/secciones/" . $value->img;
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

        $datos = $datos[0]; // por que estamos seleccionadno desde un identificdor y siempre el resltado es unico 

        $optionals = [ // datos opcionales 
            "titulos",
            "sub_titulo",
            "descripciones",
            "img",
            "bg_color",
            "txt_btn",
            "link_secc",
            "alias",
            "orden",
        ];

        // json_debug($this->request->getGetPost());

        if (!$this->request->getGetPost("notnull")) {
            foreach ($optionals as $name) {
                $data[$name] = $this->request->getGetPost($name);
            }
        }

        $data["img"] = $this->uploadImage("secciones", "img"); // nombre de carpeta y desp campo de bd 
        if (!$data["img"]) {
            // $valRequire[] = "img";
            $data["img"] = $datos->img;
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;
        // debug($this->request->getGetPost(), false);
        // debug($data);

        // data es un parametro y representa el set
        // luego separado por , tenemos el where
        $update = $this->db->table("secciones")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        // debug($datos);
        if (!$update) {
            custom_error(506, $this->lang, "secciones");
        }

        $data["img"] = base_url() . "assets/images/secciones/" . $data["img"];
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

    // CRUD TEXTO BANNER
    public function create_txtbanner() {
        $this->valToken();

        $require = [
            "texto" => "text",
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
            "cambio1",
            "cambio2",
            "delete1",
        ];

        foreach ($optionals as $name) {
            $data[$name] = $this->request->getGetPost($name);
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $insert = $this->db->table("txtbanner")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_txtbanner($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        // $query = "SELECT * FROM txtbanner ";
        $query = "SELECT * FROM `txtbanner` ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }

        $query .= " ORDER BY `txtbanner`.`id` ASC";
        // debug($query);
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "txtbanner");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_txtbanner($id) {
        $this->valToken();

        $query = "SELECT * FROM txtbanner WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "txtbanner");
        }

        $datos = $datos[0];

        $require = [
            "texto" => "text",

        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        $optionals = [ // datos opcionales 
            "cambio1",
            "cambio2",
            "delete1",
        ];

        if (!$this->request->getGetPost("notnull")) {
            foreach ($optionals as $name) {
                $data[$name] = $this->request->getGetPost($name);
            }
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;
        // debug($this->request->getGetPost(), false);
        // debug($data);

        $update = $this->db->table("txtbanner")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        // debug($datos);
        if (!$update) {
            custom_error(506, $this->lang, "txtbanner");
        }

        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_txtbanner($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM txtbanner WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "txtbanner");
        }

        $delete = $this->db->table("txtbanner")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "general");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }
    // FIN CRUD TEXTO BANNER

    // CRUD CLIENTES
    public function create_clientes() {
        $this->valToken();

        $require = [
            "titulo" => "text",
            "cant" => "text",
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
            "orden",

        ];

        foreach ($optionals as $name) {
            $data[$name] = $this->request->getGetPost($name);
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $insert = $this->db->table("clientes")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_clientes($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        // $query = "SELECT * FROM txtbanner ";
        $query = "SELECT * FROM `clientes` ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }

        $query .= " ORDER BY `clientes`.`id` ASC";
        // debug($query);
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "clientes");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_clientes($id) {
        $this->valToken();

        $query = "SELECT * FROM clientes WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "clientes");
        }

        $datos = $datos[0];

        $require = [
            "titulo" => "text",
            "cant" => "text",
        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        $optionals = [ // datos opcionales 
            "orden",

        ];

        if (!$this->request->getGetPost("notnull")) {
            foreach ($optionals as $name) {
                $data[$name] = $this->request->getGetPost($name);
            }
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;
        // debug($this->request->getGetPost(), false);
        // debug($data);

        $update = $this->db->table("clientes")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        // debug($datos);
        if (!$update) {
            custom_error(506, $this->lang, "clientes");
        }

        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_clientes($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM clientes WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "clientes");
        }

        $delete = $this->db->table("clientes")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "general");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    // FIN CRUD CLIENTES

    // CRUD TESTIMONIOS

    public function create_testimonios() {
        $this->valToken();

        // json_debug([
        //     "post"=>$this->request->getGetPost(),
        //     "file"=>$_FILES
        // ]);

        $require = [
            "nombre" => "text",
            "rama_laboral" => "text",
            "descrip_exp" => "text",
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

        // debug($data);
        $data["img"] = $this->uploadImage("testimonios", "img"); // nombre de carpeta y desp campo de bd 

        if (!$data["img"]) {
            $valRequire[] = "img";
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;
        // json_debug($data);
        $insert = $this->db->table("testimonios")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $data["img"] = base_url() . "assets/images/testimonios/" . $data["img"];
        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_testimonios($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        // $query = "SELECT * FROM txtbanner ";
        $query = "SELECT * FROM `testimonios` ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }

        $query .= " ORDER BY `testimonios`.`id` ASC";
        // debug($query);
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "testimonios");
        }

        foreach ($datos as $key => $value) {
            if ($value->img) {
                $datos[$key]->img = base_url() . "assets/images/testimonios/" . $value->img;
            }
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_testimonios($id) {
        $this->valToken();

        $query = "SELECT * FROM testimonios WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "testimonios");
        }

        $datos = $datos[0];

        $require = [
            "nombre" => "text",
            "rama_laboral" => "text",
            "descrip_exp" => "text",
        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }

        $data["img"] = $this->uploadImage("testimonios", "img"); // nombre de carpeta y desp campo de bd 
        if (!$data["img"]) {
            // $valRequire[] = "img";
            $data["img"] = $datos->img;
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;
        // debug($this->request->getGetPost(), false);
        // debug($data);

        $update = $this->db->table("testimonios")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        // debug($datos);
        if (!$update) {
            custom_error(506, $this->lang, "testimonios");
        }

        $data["img"] = base_url() . "assets/images/testimonios/" . $data["img"];

        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_testimonios($id) {
        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM testimonios WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "testimonios");
        }

        $delete = $this->db->table("testimonios")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "general");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    // FIN CRUD TESTIMONIOS

    // CRUD BLOG

    public function create_blog() {
        $this->valToken();

        $require = [
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

        $data["img"] = $this->uploadImage("blog", "img"); // nombre de carpeta y desp campo de bd 
        if (!$data["img"]) {
            // $valRequire[] = "img";
            $data["img"] = "300x300.jpg"; // aca esta opcional y tenemos una por defecto, si queremos obligatoria descomentar arriba.
        }

        $data["img_post"] = $this->uploadImage("blog", "img_post"); // nombre de carpeta y desp campo de bd 
        if (!$data["img_post"]) {
            // $valRequire[] = "img_post";
            $data["img_post"] = "300x300.jpg"; // aca esta opcional y tenemos una por defecto, si queremos obligatoria descomentar arriba.
        }

        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        $optionals = [ // datos opcionales 
            "titulo",
            "descrip_corta",
            "contenido",
        ];

        foreach ($optionals as $name) {
            $data[$name] = $this->request->getGetPost($name);
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $insert = $this->db->table("blog")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $data["img"] = base_url() . "assets/images/blog/" . $data["img"];
        $data["img_post"] = base_url() . "assets/images/blog/" . $data["img_post"];
        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_blog($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        // $query = "SELECT * FROM secciones ";
        $query = "SELECT * FROM `blog` ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }

        $query .= " ORDER BY `blog`.`id` ASC";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "blog");
        }

        //esto es para agregar url a la imagen !
        foreach ($datos as $key => $value) {
            if ($value->img) {
                $datos[$key]->img = base_url() . "assets/images/blog/" . $value->img;
            }
            if ($value->img_post) {
                $datos[$key]->img_post = base_url() . "assets/images/blog/" . $value->img_post;
            }
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_blog($id) {
        $this->valToken();

        $query = "SELECT * FROM blog WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "blog");
        }

        $datos = $datos[0]; // por que estamos seleccionadno desde un identificdor y siempre el resltado es unico 

        $require = [
            "categoria" => "text",
        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            }
        }

        $optionals = [ // datos opcionales 
            "titulo",
            "descrip_corta",
            "contenido",
        ];
        // json_debug($this->request->getGetPost());

        if (!$this->request->getGetPost("notnull")) {
            foreach ($optionals as $name) {
                $data[$name] = $this->request->getGetPost($name);
            }
        }

        $data["img"] = $this->uploadImage("blog", "img"); // nombre de carpeta y desp campo de bd 
        if (!$data["img"]) {
            // $valRequire[] = "img";
            $data["img"] = $datos->img;
        }

        $data["img_post"] = $this->uploadImage("blog", "img_post"); // nombre de carpeta y desp campo de bd
        if (!$data["img_post"]) {
            // $valRequire[] = "img_post";
            $data["img_post"] = $datos->img_post;
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;
        $data["edit_at"] = $this->currentDate;

        // debug($this->request->getGetPost(), false);
        // debug($data);

        // data es un parametro y representa el set
        // luego separado por , tenemos el where
        $update = $this->db->table("blog")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        // debug($datos);

        if (!$update) {
            custom_error(506, $this->lang, "blog");
        }

        $data["img"] = base_url() . "assets/images/blog/" . $data["img"];
        $data["img_post"] = base_url() . "assets/images/blog/" . $data["img_post"];
        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_blog($id) {
        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM blog WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "blog");
        }

        $delete = $this->db->table("blog")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "general");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    // FIN CRUD BLOG

    // CRUD BLOG CATEGORIAS

    public function create_blogCat() {
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

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $insert = $this->db->table("blogCat")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_blogCat($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        // $query = "SELECT * FROM secciones ";
        $query = "SELECT * FROM `blogCat` ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }

        $query .= " ORDER BY `blogCat`.`id` ASC";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "blogCat");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_blogCat($id) {
        $this->valToken();

        $query = "SELECT * FROM `blogCat` WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "blogCat");
        }

        $datos = $datos[0]; // por que estamos seleccionadno desde un identificdor y siempre el resltado es unico 

        $require = [
            "nombre" => "text",
        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            }
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        // debug($this->request->getGetPost(), false);
        // debug($data);

        // data es un parametro y representa el set
        // luego separado por , tenemos el where
        $update = $this->db->table("blogCat")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        // debug($datos);
        if (!$update) {
            custom_error(506, $this->lang, "blogCat");
        }

        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_blogCat($id) {
        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM `blogCat` WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "blogCat");
        }

        $delete = $this->db->table("blogCat")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "general");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    // FIN CRUD BLOG CATEGORIAS

    // CRUD BLOG COMENTARIOS

    public function create_blogComm() {
        $this->valToken();

        $require = [
            "titulo_blog" => "text",
            "comentario" => "text",
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

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $insert = $this->db->table("blogComm")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_blogComm($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        // $query = "SELECT * FROM secciones ";
        $query = "SELECT * FROM `blogComm` ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }

        $query .= " ORDER BY `blogComm`.`id` ASC";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "blogComm");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_blogComm($id) {
        $this->valToken();

        $query = "SELECT * FROM blogComm WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "blogComm");
        }

        $datos = $datos[0]; // por que estamos seleccionadno desde un identificdor y siempre el resltado es unico 

        $require = [
            "titulo_blog" => "text",
            "comentario" => "text",
        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            }
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;
        $data["edit_at"] = $this->currentDate;

        $update = $this->db->table("blogComm")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        // debug($datos);
        if (!$update) {
            custom_error(506, $this->lang, "blogComm");
        }

        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_blogComm($id) {
        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM blogComm WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "blogComm");
        }

        $delete = $this->db->table("blogComm")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "general");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    // FIN CRUD BLOG COMENTARIOS

    //PRUEBA DE VER SI ME SALE ALGO JAJA
    public function create_blogComm2() {

        $this->valToken();

        $parametros = [
            "secret" => getenv('CAPTCHA_KEY'),
            "response" => $this->request->getGetPost("g-recaptcha-response"),
        ];

        $captcha = json_decode(send_post($this->captchaUrl, $parametros));
        // debug($this->request->getGetPost(), false);
        // debug($captcha);
        if (!$captcha->success) {
            custom_error(107, $this->lang);
        }

        $require = [
            "name" => "text",
            "email" => "email",
            "message" => "text",
            "id_post" => "number",
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

        $data["activo"] = 0;

        $insert = $this->db->table("blogComm2")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_blogComm2($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        // $query = "SELECT * FROM secciones ";
        $query = "SELECT * FROM `blogComm2` ";

        $query .= " ORDER BY `blogComm2`.`id` ASC";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "blogComm2");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    // HASTA ACA PRUEBA DE VER SI ME SALE ALGO JAJA

    // CRUD USUARIOS

    public function create_usuarios() {
        $this->valToken();

        $require = [ // datos obligatorios
            "usuario" => "alias",
            "email" => "email",
            "pasw" => "password",
            "role_id" => "number",

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

        // $usuario = $data['usuario'];
        // $email = $data['email'];

        // $query = $this->db->query("SELECT * FROM usuarios WHERE usuario = '$usuario' OR email = '$email'"); // estamos chekeando si existe el usuario o emial
        // $checkUser = $query->getResult();
        // if ($checkUser) {
        //     custom_error(208, $this->lang);
        // }

        $usuario = $data['usuario'];
        $email = $data['email'];

        $query = $this->db->table('usuarios')
            ->where('usuario', $usuario)
            ->orWhere('email', $email)
            ->get();

        $checkUser = $query->getResult();

        if ($checkUser) {
            custom_error(208, $this->lang); // Error: El usuario o el email ya existen
        }


        $optionals = [ // datos opcionales 
            "nombre",
            "apellido",
        ];

        foreach ($optionals as $name) {
            $data[$name] = $this->request->getGetPost($name);
        }

        $data['pasw'] = encode($data['pasw'], $this->key);

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $insert = $this->db->table("usuarios")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $id = $this->db->insertID(); // ultimo identificador insertado !

        // $data['token'] = encode(json_encode([
        //     'id' => $id,
        //     'date' => $this->currentDate
        // ]), $this->key);

        unset($data['pasw']);

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_usuarios($id = null) {
        $this->variables();

        // esta hicimos con alberto, la de abajo es chatgpt
        $query = "SELECT usuarios.*, roles.nombre AS role_nombre FROM `usuarios`, roles WHERE 1=1";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= " AND usuarios.id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : "";
            $query .= " activo = 1";
        }

        $query .= " AND usuarios.role_id = roles.id AND usuarios.id <> 1 ORDER BY `usuarios`.`id` ASC";

        // devuelve esto
        // SELECT usuarios.*, roles.nombre AS role_nombre FROM `usuarios`, roles WHERE 1=1 AND usuarios.role_id = roles.id AND usuarios.id <> 1 ORDER BY `usuarios`.`id` ASC

        // json_debug($query);

        // $query = "SELECT usuarios.*, roles.nombre FROM usuarios INNER JOIN roles ON usuarios.role_id = roles.id WHERE usuarios.id <> 1";
        // if ($id) { // Esto se utiliza para consultar un usuario específico
        //     $query .= " AND usuarios.id = '$id'";
        // }

        // if ($this->activo) {
        //     $query .= " AND usuarios.activo = 1";
        // }

        // $query .= " ORDER BY usuarios.id ASC";


        // Ejecutar la consulta
        $query = $this->db->query($query);
        $datos = $query->getResult();

        // Manejo de error si no hay datos
        if (!$datos) {
            custom_error(504, $this->lang, "usuarios");
        }

        // Si se proporciona un id específico, devolver solo el primer resultado
        if ($id) {
            $datos = $datos[0];
        }

        // Devolver los datos en formato JSON
        json_debug($datos);
    }

    public function update_usuarios($id) {
        $this->valToken();

        $query = "SELECT usuarios.*, roles.nombre AS role_nombre FROM `usuarios`, roles WHERE 1=1";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= " AND usuarios.id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : "";
            $query .= " activo = 1";
        }

        $query .= " AND usuarios.role_id = roles.id AND usuarios.id <> 1 ORDER BY `usuarios`.`id` ASC";


        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "usuarios");
        }

        $datos = $datos[0]; // por que estamos seleccionadno desde un identificdor y siempre el resltado es unico 

        $require = [ // datos obligatorios
            "usuario" => "alias",
            "email" => "email",
            "pasw" => "password",
            "role_id" => "number",

        ];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            }
        }

        $optionals = [ // datos opcionales 
            "nombre",
            "apellido",
        ];

        // json_debug($this->request->getGetPost());

        if (!$this->request->getGetPost("notnull")) {
            foreach ($optionals as $name) {
                $data[$name] = $this->request->getGetPost($name);
            }
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;
        $data["edit_at"] = $this->currentDate;

        // debug($this->request->getGetPost(), false);
        // debug($data);

        // data es un parametro y representa el set
        // luego separado por , tenemos el where
        $update = $this->db->table("usuarios")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        // debug($datos);
        if (!$update) {
            custom_error(506, $this->lang, "usuarios");
        }

        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_usuarios($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM usuarios WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "usuarios");
        }

        $delete = $this->db->table("usuarios")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "general");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    // FIN CRUD USUARIOS

    // CRUD USUARIOS

    public function create_roles() {
        $this->valToken();

        $require = [ // datos obligatorios
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

        $nombre = $data['nombre'];
        $query = $this->db->query("SELECT * FROM roles WHERE nombre = '$nombre'"); // estamos chekeando si existe el nombre
        $checkUser = $query->getResult();
        if ($checkUser) {
            custom_error(209, $this->lang);
        }

        $optionals = [ // datos opcionales 
            "descripcion",
            "ver",
            "crear",
            "editar",
            "borrar",
        ];

        foreach ($optionals as $name) {
            $data[$name] = $this->request->getGetPost($name);

            if (is_array($data[$name])) {
                $data[$name] = implode(',', $data[$name]);
            }
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;

        $insert = $this->db->table("roles")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $id = $this->db->insertID(); // ultimo identificador insertado !

        json_debug(array_merge(["id" => $id], $data));
    }

    public function get_roles($id = null) {
        $this->variables();
        // $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        // $query = "SELECT * FROM secciones ";
        $query = "SELECT * FROM `roles` ";
        if ($id) { // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'";
        }
        if ($this->activo) {
            $query .= ($id) ? " AND" : " WHERE";
            $query .= " activo = 1";
        }

        $query .= " ORDER BY `roles`.`id` ASC";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "roles");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function update_roles($id) {
        $this->valToken();

        $query = "SELECT * FROM roles WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "roles");
        }

        $datos = $datos[0]; // por que estamos seleccionadno desde un identificdor y siempre el resltado es unico 

        $require = [ // datos obligatorios
            "nombre" => "text",
        ];


        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $data[$name] = validateValue($value, $type, $this->lang);
            }
        }

        $optionals = [ // datos opcionales 
            "descripcion",
            "ver",
            "crear",
            "editar",
            "borrar",
        ];

        // json_debug($this->request->getGetPost());

        if (!$this->request->getGetPost("notnull")) {
            foreach ($optionals as $name) {
                $data[$name] = $this->request->getGetPost($name);
            }
        }

        $data["activo"] = ($this->request->getGetPost("activo")) ? 1 : 0;
        // debug($this->request->getGetPost(), false);
        // debug($data);

        // data es un parametro y representa el set
        // luego separado por , tenemos el where
        $update = $this->db->table("roles")->update($data, ["id" => $id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        // debug($datos);
        if (!$update) {
            custom_error(506, $this->lang, "roles");
        }

        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_roles($id) {

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM roles WHERE id = '$id'";
        $query = $this->db->query($query);
        $datos = $query->getResult();

        if (!$datos) {
            custom_error(504, $this->lang, "roles");
        }

        $delete = $this->db->table("roles")->delete(["id" => $id]);
        if (!$delete) {
            custom_error(507, $this->lang, "general");
        }

        if ($id) {
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    // FIN CRUD USUARIOS

    public function mailing() {
        $this->valToken();

        $this->captchaUrl;

        $parametros = [
            "secret" => getenv('CAPTCHA_KEY'),
            "response" => $this->request->getGetPost("g-recaptcha-response"),
        ];

        $captcha = json_decode(send_post($this->captchaUrl, $parametros));
        // debug($this->request->getGetPost(), false);
        // debug($captcha);
        if (!$captcha->success) {
            custom_error(107, $this->lang);
        }

        $email = \Config\Services::email();

        // $email->setFrom('matiasagui93@gmail.com', 'Matias Aguirre');
        $email->setTo('mati_aa93@outlook.com');
        $email->setCC($this->request->getGetPost('email'));
        $email->setSubject('Contacto mi Web');

        // Construcción del mensaje con HTML
        $tabla = '
            <html>
                <head>
                    <style>
                        .table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        .table th, .table td {
                            padding: 8px;
                            text-align: left;
                            border: 1px solid #ddd;
                        }
                        .table th {
                            background-color: #f4f4f4;
                        }
                    </style>
                </head>
                <body>
                    <h1>Contacto Web</h1>
                    <table class="table">
                        <tbody>
                            <tr>
                                <th scope="row">Nombre:</th>
                                <td>' . htmlspecialchars($this->request->getGetPost('name')) . '</td>
                            </tr>
                            <tr>
                                <th scope="row">Email:</th>
                                <td>' . htmlspecialchars($this->request->getGetPost('email')) . '</td>
                            </tr>
                            <tr>
                                <th scope="row">Asunto:</th>
                                <td>' . htmlspecialchars($this->request->getGetPost('subject')) . '</td>
                            </tr>
                            <tr>
                                <th scope="row">Mensaje:</th>
                                <td>' . htmlspecialchars($this->request->getGetPost('message')) . '</td>
                            </tr>
                        </tbody>
                    </table>
                </body>
            </html>';

        $email->setMessage($tabla);

        if (!$email->send()) {
            custom_error(108, $this->lang);
        }

        $data = [
            "success" => true,
            "name" => $this->request->getGetPost('name'),
            "email" => $this->request->getGetPost('email'),
            "subject" => $this->request->getGetPost('subject'),
            "message" => $this->request->getGetPost('message'),
        ];

        json_debug($data);
    }

    public function delete_img() {
        // $postData = $_POST;
        // $getData = $_GET;
        // $requestData = $_REQUEST;
        // $fileData = $_FILES;

        // // Para debuggear los datos recibidos
        // json_debug([
        //     "post" => $postData,
        //     "get" => $getData,
        //     "request" => $requestData,
        //     "file" => $fileData
        // ]);

        // Procesar los datos según sea necesario

        $require = [
            "seccion" => "text",
            "id" => "number",

        ];

        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if ($value) {
                $req[$name] = validateValue($value, $type, $this->lang);
            } else {
                $valRequire[] = $name;
            }
        }
        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101, "es", $valRequire);
        }

        // $funcion = $this->request->getGetPost("funcion");
        $seccion = $req["seccion"];
        $id = $req["id"];
        // $ruta = $this->request->getGetPost("ruta"); ya no lo usamos

        $query = "SELECT * FROM $seccion WHERE id = '$id'";
        // debug($query);
        $query = $this->db->query($query);
        $datos = $query->getResult();
        // debug($datos);

        if (!$datos) {
            custom_error(504, $this->lang, $seccion);
        }

        $datos = $datos[0];
        $data = [];
        $img = null; // esta variable es para guardar el valor la img para envair a la papelera

        if (isset($datos->bg_img)) {
            $img = $datos->bg_img;
            $data['bg_img'] = null;
        }

        if (isset($datos->img)) {
            $img = $datos->img;
            $data['img'] = null;
        }

        if (isset($datos->img_fondo)) {
            $img = $datos->img_fondo;
            $data['img_fondo'] = null;
        }

        if (isset($datos->img_proyecto)) {
            $img = $datos->img_proyecto;
            $data['img_proyecto'] = null;
        }

        if (isset($datos->{'img_' . $seccion})) {
            $img = $datos->{'img_' . $seccion};
            $data['img_' . $seccion] = null;
        }

        if ($data) {
            // en el update
            // data es un parametro y representa el set
            // luego separado por , tenemos el where
            $update = $this->db->table($seccion)->update($data, ["id" => $id]);
            if (!$update) {
                custom_error(507, $this->lang, $seccion);
            }
        }

        if ($img) {
            $this->TrashFIle($seccion, $img);
        }

        json_debug(array_merge((array)$datos, $data));
    }

    public function bgimg($width, $height, $color = '808080') {
        // Verificar que width y height sean números válidos
        if (!is_numeric($width) || !is_numeric($height) || $width <= 0 || $height <= 0) {
            return $this->response->setStatusCode(400)->setBody('Invalid dimensions');
        }

        // Eliminar el símbolo # si está presente en el color
        $color = str_replace('#', '', $color);

        // Verificar que el color tenga exactamente 6 caracteres
        if (strlen($color) !== 6) {
            return $this->response->setStatusCode(400)->setBody('Invalid color format');
        }

        // Convertir el color hexadecimal a RGB
        $red = hexdec(substr($color, 0, 2));
        $green = hexdec(substr($color, 2, 2));
        $blue = hexdec(substr($color, 4, 2));

        // Crear una imagen en blanco
        $image = imagecreatetruecolor($width, $height);

        // Asignar el color a la imagen
        $backgroundColor = imagecolorallocate($image, $red, $green, $blue);
        imagefill($image, 0, 0, $backgroundColor);

        // Enviar los encabezados adecuados para que el navegador sepa que es una imagen
        header('Content-Type: image/png');

        // Generar la imagen
        imagepng($image);

        // Liberar la memoria
        imagedestroy($image);

        // Detener la ejecución para no enviar datos adicionales después de la imagen
        exit;
    }

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

    private function TrashFIle($carpeta, $name) {
        // Verificar si el nombre del archivo es válido
        if (empty($name)) {
            return false;
        }

        // Rutas de las carpetas
        $carpetaOrigen = 'assets/images/' . $carpeta . '/';
        $carpetaDestino = 'assets/images/papelera/';

        // Ruta completa de los archivos
        $rutaArchivoOrigen = $carpetaOrigen . $name;
        $rutaArchivoDestino = $carpetaDestino . $name;

        // Verificar si el archivo existe en la carpeta de origen
        if (file_exists($rutaArchivoOrigen)) {
            // Mover el archivo a la carpeta de destino
            if (rename($rutaArchivoOrigen, $rutaArchivoDestino)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false; // El archivo no existe en la carpeta de origen
        }
    }

    private function variables() {
        $this->activo = $this->request->getGetPost('activo');
        if ($this->activo && $this->activo != 1) {
            $this->activo = 1;
        }
    }

    // public function test() {

    //     // $P = true;
    //     // $Q = true;
    //     // $R = true;

    //     // $var1 = ($P || $Q) && (!$R || $Q);
    //     // $var2 = (!($P && !$Q) || $R) || (!$P != $Q);
    //     // $var3 = !$P && ($Q && $R);
    //     // $var4 = !$P || ($Q && $R);
    //     // $var5 = ($P && $Q) && $R;
    //     // $var6 = (!$P || $Q) && !$R;
    //     // $var7 = ($P != $Q) || ($Q && $R);
    //     // $var8 = !$P || (!$Q && $R);

    //     // echo $var1 .  '';

    //     // Definir las variables posibles (0 o 1)
    //     $values = [1, 0];

    //     // Imprimir el encabezado de la tabla
    //     echo "<style> table {text-align: center;} </style>";
    //     echo "<table border='1'>";
    //     echo "<tr><th>P</th><th>Q</th><th>R</th><th>S</th>";
    //     // echo "<th>(P ∨ Q) ∧ (¬R ∨ Q)</th>";
    //     // echo "<th>((P ∧ ¬Q) → R) ∨ (¬P ⊕ Q)</th>";
    //     // echo "<th>¬P ∧ (Q ∧ R)</th>";
    //     // echo "<th>¬P v (Q ∧ R)</th>";
    //     // echo "<th>(P ∧ Q) ∧ R</th>";
    //     // echo "<th>(¬P ∨ Q) ∧ ¬R</th>";
    //     // echo "<th>(P ⊕ Q) ∨ (Q ∧ R)</th>";
    //     // echo "<th> P → (¬Q ∧ R)</th>";

    //     //ejer2
    //     // echo "<th> (¬P ∧ Q) → (P ∨ ¬R) </th>";
    //     // echo "<th> ((P ∧ R) → Q) ∨ ¬(P ∨ R) </th>";
    //     // echo "<th>  (P ∧ ¬Q) ∧ (Q ⊕ ¬R) </th>";
    //     // echo "<th> ¬(P ⊕ Q) ∨ (R ∧ ¬P) </th>";
    //     // echo "<th> ¬(P ∨ Q) ∧ (Q ∨ R) </th>";
    //     // echo "<th> (P → Q) ∧ (¬R ∨ P) </th>";
    //     // echo "<th> ¬(P ∧ ¬Q) ∨ (R ∧ Q) </th>";
    //     // echo "<th> (P ∨ ¬Q) → (¬R ∨ P) </th>";
    //     // echo "<th> ((¬P ∧ Q) ∨ R) ∧ (P → ¬R) </th>";
    //     // echo "<th> (P ∨ Q) ⊕ (¬Q ∧ R) </th>";

    //     //ejer3
    //     // echo "<th> (P ∨ ¬R) ∧ ¬(Q → R) </th>";
    //     // echo "<th> ¬(P ∧ ¬R) ∨ (Q ∧ ¬P) </th>";
    //     // echo "<th> (P ⊕ Q) ∧ (¬R ∨ ¬P) </th>";
    //     // echo "<th> (P ∨ R) → (¬Q ∧ P) </th>";
    //     // echo "<th> ¬(P ∨ Q) ∧ (R → ¬Q) </th>";
    //     // echo "<th> ¬(P ∨ R) ∧ (Q ∨ ¬R) </th>";
    //     // echo "<th> (P → ¬Q) ∨ (R ∧ ¬P) </th>";
    //     // echo "<th> (¬P ∧ Q) ∧ (R ∨ ¬Q) </th>";
    //     // echo "<th> (P ∨ ¬R) ∧ (¬Q → P) </th>";
    //     // echo "<th> (P ⊕ R) ∧ (¬Q ∨ P) </th>";
    //     // echo "<th> ¬(P ∨ ¬Q) ∧ (Q ∨ R) </th>";
    //     // echo "<th> (P ∧ Q) ∨ ¬(R ∨ P) </th>";
    //     // echo "<th> (P ∧ ¬R) ∨ (¬Q ⊕ R) </th>";
    //     // echo "<th> (¬P → Q) ∧ (R ∨ ¬P) </th>";
    //     // echo "<th> ¬(P ∨ ¬Q) → (R ∧ ¬P) </th>";
    //     // echo "<th> ¬(P ∨ ¬Q) → (R ∧ ¬P) </th>";
    //     // echo "<th> (P ∨ ¬R) ∧ (¬Q → P) </th>";
    //     // echo "<th> (P ∨ ¬R) ∧ (¬Q → P) </th>";
    //     // echo "<th> (¬P → Q) ∧ (R ∨ ¬P) </th>";
    //     // echo "<th> (¬P → Q) ∧ (R ∨ ¬P) </th>";


    //     // echo "<th> (P ∧ ¬Q) ∨ (R ∧ ¬S) </th>";
    //     // echo "<th> (P → Q) ∧ (¬R ∨ S) </th>";
    //     // echo "<th>  (¬P ∧ Q) ⊕ (R ∨ ¬S) </th>";
    //     // echo "<th> (P ∧ Q) → (¬R ∨ S) </th>";
    //     // echo "<th> (P ∨ ¬Q) ∧ (¬R ∨ S) </th>";

    //     // echo "<th> (¬P ∨ ¬Q) ∧ (R ∧ S) </th>";
    //     // echo "<th> ¬((P ∧ Q) ∨ (R ∧ S)) </th>";
    //     // echo "<th> ((P ⊕ Q) ∧ ¬R) ∨ (S → P) </th>";
    //     // echo "<th> ¬(P → Q) ∧ (R ∨ ¬S) </th>";
    //     // echo "<th> (P ∧ Q) ∨ (¬R ∧ ¬S) </th>";


    //     // echo "<th> (P ∨ Q) ∧ ¬(R ⊕ S) </th>";
    //     // echo "<th> ((P → Q) ∧ (¬R ∨ S)) ∧ ¬(P ∧ R) </th>";
    //     // echo "<th> ¬(P ⊕ Q) ∨ (R ∧ S) </th>";
    //     // echo "<th> (P ∨ ¬Q) ∧ (R ⊕ ¬S) </th>";
    //     // echo "<th> ((P ∧ S) ∨ (¬Q ∧ R)) ∧ ¬(P → ¬R) </th>";

    //     // echo "<th> (P ⊕ Q) → (R ∨ ¬S) </th>";
    //     // echo "<th> (¬P → Q) ∧ (R ⊕ S) </th>";
    //     // echo "<th> ((P ∨ ¬Q) ⊕ (R ∧ S)) → P </th>";
    //     // echo "<th> ¬((P ⊕ R) → (Q ∧ S)) </th>";
    //     // echo "<th> (P ∧ ¬Q) ⊕ (R → S) </th>";

    //     // echo "<th> (P → Q) ∧ (R ⊕ ¬S) </th>";
    //     // echo "<th> ((P ∨ Q) → (¬R ⊕ S)) ∧ ¬P </th>";
    //     // echo "<th> (¬P ⊕ Q) ∨ (R → S) </th>";
    //     // echo "<th> (P → ¬Q) ∧ (R ⊕ ¬S) </th>";
    //     // echo "<th> ((¬P → Q) ∧ (R ⊕ S)) ⊕ ¬R </th>";

    //     // echo "<th> ¬((P ⊕ Q) ∨ (R → ¬S)) </th>";
    //     // echo "<th> (P → Q) ⊕ (R ∨ ¬S) </th>";
    //     // echo "<th> ((¬P → R) ⊕ (Q ∨ ¬S)) ∧ P </th>";
    //     // echo "<th> (P ⊕ Q) → (¬R ∨ S) </th>";
    //     // echo "<th> ((P → ¬Q) ∨ (R ⊕ S)) </th>";

    //     // echo "<th> ((¬P ∧ Q) → (¬R ⊕ S)) ∨ ((¬P ⊕ Q) ∧ (¬R → S)) ∧ ¬(P ∧ ¬R) </th>";
    //     // echo "<th> (¬P ∨ (¬Q → ¬R)) ∧ ((¬S ⊕ P) → (¬R ∧ Q)) ∨ ¬((¬P → Q) ⊕ (R ∨ ¬S)) </th>";
    //     // echo "<th> (¬P → (Q ⊕ ¬R)) ∧ (¬S → (P ∧ ¬Q)) ∨ ((¬R ⊕ S) ∧ (¬P ∨ Q)) </th>";
    //     // echo "<th> ((¬P ∧ ¬Q) ⊕ (¬R → S)) ∧ ((¬P ∨ R) → (¬Q ⊕ S)) ∧ ¬((P → ¬Q) ∨ ¬R) </th>";
    //     // echo "<th> (¬P ⊕ (¬Q → ¬R)) ∧ ((¬S ∧ P) → (¬Q ⊕ ¬R)) ∨ ((P → ¬Q) ∧ (¬R ⊕ S)) </th>";



    //     echo "</tr>";

    //     // Generar todas las combinaciones de P, Q, R
    //     foreach ($values as $P) {
    //         foreach ($values as $Q) {
    //             foreach ($values as $R) {
    //                 foreach ($values as $S) {
    //                     //             $result1 = ($P || $Q) && (!$R || $Q) ? "1" : "0";
    //                     //             $result2 = (!($P && !$Q) || $R) || (!$P != $Q) ? "1" : "0";
    //                     //             $result3 = !$P && ($Q && $R) ? "1" : "0";
    //                     //             $result4 = !$P || ($Q && $R) ? "1" : "0";
    //                     //             $result5 = ($P && $Q) && $R ? "1" : "0";
    //                     //             $result6 = (!$P || $Q) && !$R ? "1" : "0";
    //                     //             $result7 = ($P != $Q) || ($Q && $R) ? "1" : "0";
    //                     //             $result8 = !$P || (!$Q && $R) ? "1" : "0";


    //                     //ejer 3 (el ejer 2 esta hecho con for abajo)
    //                     // $result1 = ($P || !$R) && !(!$Q || $R) ? "1" : "0";
    //                     // $result2 = !($P && !$R) || ($Q && !$P) ? "1" : "0";
    //                     // $result3 = ($P != $Q) && (!$R || !$P) ? "1" : "0";
    //                     // $result4 = !($P || $R) || (!$Q && $P) ? "1" : "0";
    //                     // $result5 = !($P || $Q) && (!$R || !$Q) ? "1" : "0";
    //                     // $result6 = !($P||$R) && ($Q|| !$R) ? "1" : "0";
    //                     // $result7 = (!$P || !$Q) || ($R && !$P) ? "1" : "0";
    //                     // $result8 = (!$P && $Q) && ($R || !$Q) ? "1" : "0";
    //                     // $result9 = ($P || !$R) && !(!$Q || $P) ? "1" : "0";
    //                     // $result10 = ($P != $R) && (!$Q || $P) ? "1" : "0";
    //                     // $result11 = !($P || !$Q) && ($Q || $R) ? "1" : "0";
    //                     // $result12 = ($P && $Q) || !($R || $P) ? "1" : "0";
    //                     // $result13 = ($P && $R) || (!$Q != $R) ? "1" : "0";
    //                     // $result14 = !(!$P || $Q) && ($R || !$P) ? "1" : "0";
    //                     // $result15 = !(!($P || !$Q)) || ($R && !$P) ? "1" : "0"; // ES EL MISMO QUE EL DE ABAJO PERO VERSION EXTENDIDA JAJA
    //                     // $result16 = ($P || !$Q) || ($R && !$P) ? "1" : "0";
    //                     // $result17 = ($P || !$R) && ($Q || $P) ? "1" : "0"; // la diferencia esta en las negaciones que se cancelan, negacion y negacion se elimina.
    //                     // $result17segunda = ($P || !$R) && (!(!$Q) || $P) ? "1" : "0";
    //                     // $result18 = ($P || $Q) && ($R || !$P) ? "1" : "0"; // la diferencia esta en las negaciones que se cancelan, negacion y negacion se elimina.
    //                     // $result18segunda = (!(!$P) || $Q) && ($R || !$P) ? "1" : "0";


    //                     // $result19 = ($P && !$Q) || ($R && !$S) ? "1" : "0";
    //                     // $result20 = (!$P || $Q) && (!$R || $S) ? "1" : "0";
    //                     // $result21 = (!$P && $Q) != ($R || !$S) ? "1" : "0";
    //                     // $result22 = !($P && $Q) || (!$R || $S) ? "1" : "0";
    //                     // $result23 = ($P || !$Q) && (!$R || $S) ? "1" : "0";

    //                     // $result24 = (!$P || !$Q) &&  ($R && $S) ? "1" : "0";
    //                     // $result25 = !(($P && $Q) || ($R&&$S)) ? "1" : "0";
    //                     // $result26 = (($P != $Q) && !$R) || (!$S || $P ) ? "1" : "0";
    //                     // $result27 = !(!$P || $Q) && ($R || !$S) ? "1" : "0";
    //                     // $result28 = ($P && $Q) || (!$R && !$S) ? "1" : "0";

    //                     // $result29 = ($P || $Q) && !($R != $S) ? "1" : "0";
    //                     // $result30 = ((!$P || $Q) && (!$R || $S)) && !($P && $R) ? "1" : "0";
    //                     // $result31 = !($P != $Q) || ($R && $S) ? "1" : "0";
    //                     // $result32 = ($P || !$Q) && ($R != !$S) ? "1" : "0";
    //                     // $result33 = (($P && $S) || (!$Q && $R)) && ($P || !$R) ? "1" : "0";

    //                     // $result34 = !($P != $Q) || ($R || !$S) ? "1" : "0";
    //                     // $result35 = !(!$P || $Q) && ($R != $S) ? "1" : "0";
    //                     // $result36 = !(($P || !$Q) != ($R && $S)) || $P  ? "1" : "0";
    //                     // $result37 = ( $P != $R) || ($Q && $S)  ? "1" : "0";
    //                     // $result38 = ($P && !$Q) != (!$R || $S) ? "1" : "0";

    //                     // $result39 = (!$P || $Q) && ($R != !$S) ? "1" : "0";
    //                     // $result40 = (!($P || $Q) || (!$R != $S)) && !$P ? "1" : "0";
    //                     // $result41 = (!$P != $Q) || (!$R || $S) ? "1" : "0";
    //                     // $result42 = (!$P || !$Q) && ($R != !$S) ? "1" : "0";
    //                     // $result43 = (($P || $Q) && ($R != $S)) != !$R ? "1" : "0";

    //                     // $result44 = !($P != $Q) || (!$R || !$S) ? "1" : "0";
    //                     // $result45 = (!$P || $Q) != ($R || !$S) ? "1" : "0";
    //                     // $result46 = ($P || $R) != ($Q || !$S) && $P ? "1" : "0";
    //                     // $result47 = !($P !=$Q )  || (!$R || $S) ? "1" : "0";
    //                     // $result48 = ((!$P || !$Q) || ($R != $S)) ? "1" : "0";

    //                     // $result49 = (!(!$P && $Q)) || (!$R != $S) || ((!$P != $Q) && ($R || $S)) && !($P && !$R) ? "1" : "0";
    //                     // $result50 = (!$P || ($Q || !$R)) && ( !(!$S != $P) || (!$R && $Q)) || !(($P || $Q) != ($R || !$S)) ? "1" : "0";
    //                     // $result51 = ($P || ($Q != !$R)) && ($S || ($P && !$Q)) || ((!$R != $S) && (!$P || $Q)) ? "1" : "0";
    //                     // $result52 = ((!$P && !$Q) != ($R || $S)) && (!(!$P || $R) || (!$Q != $S)) && !((!$P || !$Q) || !$R) ? "1" : "0";
    //                     // $result53 = (!$P != ($Q || !$R)) && (( ! (!$S && $P)) || (!$Q != !$R)) || ((!$P || !$Q) && (!$R != $S))  ? "1" : "0";


    //                     // Imprimir la fila de la tabla
    //                     echo "<tr>";
    //                     echo "<td>$P</td>";
    //                     echo "<td>$Q</td>";
    //                     echo "<td>$R</td>";
    //                     echo "<td>$S</td>";
    //                     // echo "<td>$result1</td>";
    //                     // echo "<td>$result2</td>";
    //                     // echo "<td>$result3</td>";
    //                     // echo "<td>$result4</td>";
    //                     // echo "<td>$result5</td>";
    //                     // echo "<td>$result6</td>";
    //                     // echo "<td>$result7</td>";
    //                     // echo "<td>$result8</td>";
    //                     // echo "<td>$result9</td>";
    //                     // echo "<td>$result10</td>";
    //                     // echo "<td>$result11</td>";
    //                     // echo "<td>$result12</td>";
    //                     // echo "<td>$result13</td>";
    //                     // echo "<td>$result14</td>";
    //                     // echo "<td>$result15</td>";
    //                     // echo "<td>$result16</td>";
    //                     // echo "<td>$result17</td>";
    //                     // echo "<td>$result17segunda</td>";
    //                     // echo "<td>$result18</td>";
    //                     // echo "<td>$result18segunda</td>";

    //                     // echo "<td>$result19</td>";
    //                     // echo "<td>$result20</td>";
    //                     // echo "<td>$result21</td>";
    //                     // echo "<td>$result22</td>";
    //                     // echo "<td>$result23</td>";

    //                     // echo "<td>$result24</td>";
    //                     // echo "<td>$result25</td>";
    //                     // echo "<td>$result26</td>";
    //                     // echo "<td>$result27</td>";
    //                     // echo "<td>$result28</td>";

    //                     // echo "<td>$result29</td>";
    //                     // echo "<td>$result30</td>";
    //                     // echo "<td>$result31</td>";
    //                     // echo "<td>$result32</td>";
    //                     // echo "<td>$result33</td>";

    //                     // echo "<td>$result34</td>";
    //                     // echo "<td>$result35</td>";
    //                     // echo "<td>$result36</td>";
    //                     // echo "<td>$result37</td>";
    //                     // echo "<td>$result38</td>";

    //                     // echo "<td>$result39</td>";
    //                     // echo "<td>$result40</td>";
    //                     // echo "<td>$result41</td>";
    //                     // echo "<td>$result42</td>";
    //                     // echo "<td>$result43</td>";

    //                     // echo "<td>$result44</td>";
    //                     // echo "<td>$result45</td>";
    //                     // echo "<td>$result46</td>";
    //                     // echo "<td>$result47</td>";
    //                     // echo "<td>$result48</td>";

    //                     // echo "<td>$result49</td>";
    //                     // echo "<td>$result50</td>";
    //                     // echo "<td>$result51</td>";
    //                     // echo "<td>$result52</td>";
    //                     // echo "<td>$result53</td>";


    //                     echo "</tr>";
    //                 }
    //             }
    //         }
    //     }


    //     // for ($P = 1; $P >= 0; $P--) {
    //     //     for ($Q = 1; $Q >= 0; $Q--) {
    //     //         for ($R = 1; $R >= 0; $R--) {

    //     //             // $result1 = !( !$P && $Q )  || ($P || !$R) ? "1" : "0";
    //     //             // $result2 = (! ( $P && $R) || $Q) || !($P || $R) ? "1" : "0";
    //     //             // $result3 = ( $P && !$Q) && ( $Q != !$R) ? "1" : "0";
    //     //             // $result4 = !( $P != $Q) || ( $R && !$P ) ? "1" : "0";
    //     //             // $result5 = !($P || $Q) && ($Q || $R) ? "1" : "0";
    //     //             // $result6 = (!$P || $Q ) && (!$R || $P) ? "1" : "0";
    //     //             // $result7 = !($P && !$Q) ? "1" : "0";
    //     //             // $result8 = !($P || !$Q) || (!$R || $P) ? "1" : "0";
    //     //             // $result9 = ((!$P && $Q) || $R) && ( !$P || !$R) ? "1" : "0";
    //     //             // $result10 = ( $P || $Q ) != ( !$Q &&  $R) ? "1" : "0";


    //     //             echo "<tr>";
    //     //             echo "<td>$P</td>";
    //     //             echo "<td>$Q</td>";
    //     //             echo "<td>$R</td>";
    //     //             echo "<td>$result1</td>";
    //     //             echo "<td>$result2</td>";
    //     //             echo "<td>$result3</td>";
    //     //             echo "<td>$result4</td>";
    //     //             echo "<td>$result5</td>";
    //     //             echo "<td>$result6</td>";
    //     //             echo "<td>$result7</td>";
    //     //             echo "<td>$result8</td>";
    //     //             echo "<td>$result9</td>";
    //     //             echo "<td>$result10</td>";
    //     //             echo "</tr>";
    //     //         }
    //     //     }
    //     // }

    //     echo "</table>";
    // }

}
