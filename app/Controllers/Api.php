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

    public function __construct() {

        $this->db = \Config\Database::connect();

        $this->key = getenv('KEYENCRIPT');

        $this->system = (object)[
            "title" => getenv('GENERAL_TITLE'),
            "descrip" => getenv('GENERAL_DESCRIP'),
            "register" => getenv('GENERAL_REGISTER'),
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
            if($value){
                $data[$name] = validateValue($value, $type, $this->lang);
            }else { //  cuanod no te envian los datos requeridos se agrega en esta validacion // esto hace que si o si sea requerido
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
            "nombre" , 
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
            if($value){
                // $data[$name] = validateValue($value, $type, $this->lang); esto se comento por que si ya se registro quiere decir que ya cumplio los parametros necesarios.
                $data[$name] = $value;
            }else {
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

    public function get_proyect ($id = null){

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM proyectos ";
        if($id){ // esto se utilza para consultar 1 especifico
            $query .= "WHERE id = '$id'"; 
        }
        $query = $this->db->query($query);
        $datos = $query->getResult();
        
        if (!$datos) {
            custom_error(504, $this->lang, "proyectos");
        }

        //esto es para agregar url a la imagen !
        foreach ($datos as $key => $value) {   
            if($value->img_proyecto){
                $datos[$key]->img_proyecto = base_url()."img/proyectos/" . $value->img_proyecto;
            }
        }

        if($id){
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    public function create_proyect () {
        $this->valToken();

        $require = [ 
            "nombre" => "text", 
            "descripcion" => "text",
            "url" => "url"
        ];

        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if($value){
                $data[$name] = validateValue($value, $type, $this->lang);
            }else {
                $valRequire[] = $name;
            }
        }

        $data["img_proyecto"] = $this->uploadImage("proyectos", "img_proyecto"); // nombre de carpeta y desp campo de bd 
        if(!$data["img_proyecto"]){
            // $valRequire[] = "img_proyecto";
            $data["img_proyecto"] = "300x300.jpg"; // aca esta opcional y tenemos una por defecto, si queremos obligatoria descomentar arriba.
        }
        
        if ($valRequire) {
            // validar error que te faltan datos
            custom_error(101,"es", $valRequire);
        }

        $data["user_id"] = $this->user->id;

        $insert = $this->db->table("proyectos")->insert($data);
        if (!$insert) {
            custom_error(204, $this->lang);
        }

        $data["img_proyecto"] = base_url()."img/proyectos/" . $data["img_proyecto"];

        $id = $this->db->insertID(); // ultimo identificador insertado !
        
        json_debug(array_merge(["id" => $id], $data));
    }

    public function update_proyect ($id) {
        $this->valToken();

        $query = "SELECT * FROM proyectos WHERE id = '$id'"; 
        $query = $this->db->query($query);
        $datos = $query->getResult();
        
        if (!$datos) {
            custom_error(504, $this->lang, "proyectos");
        }

        $datos = $datos[0];
        

        $opcional = [ 
            "nombre" => "text", 
            "descripcion" => "text",
            "url" => "url"
        ];

        foreach ($opcional as $name => $type) {
            $value = $this->request->getGetPost($name);
            if($value){
                $data[$name] = validateValue($value, $type, $this->lang);
            }
        }

        $data["img_proyecto"] = $this->uploadImage("proyectos", "img_proyecto"); // nombre de carpeta y desp campo de bd 
        if(!$data["img_proyecto"]){
            // $valRequire[] = "img_proyecto";
            $data["img_proyecto"] = $datos->img_proyecto; 
        }

        $data["user_id"] = $this->user->id;

        $update = $this->db->table("proyectos")->update($data, ["id"=>$id]); // ver query de mysql para entender bien cuales son los 2 paremetros q recibimos (set y where)
        if (!$update) {
            custom_error(506, $this->lang, "proyectos");
        }

        $data["img_proyecto"] = base_url()."img/proyectos/" . $data["img_proyecto"];

        json_debug(array_merge((array)$datos, $data));
    }

    public function delete_proyect ($id){

        $this->valToken(); // las unicas que no se pide el token son las consultas publicas,  login y registro

        $query = "SELECT * FROM proyectos WHERE id = '$id'"; 
        $query = $this->db->query($query);
        $datos = $query->getResult();
        
        if (!$datos) {
            custom_error(504, $this->lang, "proyectos");
        }

        $delete = $this->db->table("proyectos")->delete(["id"=>$id]);
        if (!$delete) {
            custom_error(507, $this->lang, "proyectos");
        }

        if($id){
            $datos = $datos[0];
        }

        json_debug($datos);
    }

    private function valToken() {
        $require = [
            "token" => "text",
        ];

        $valRequire = [];

        foreach ($require as $name => $type) {
            $value = $this->request->getGetPost($name);
            if($value){
                $data[$name] = validateValue($value, $type, $this->lang);
            }else {
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

        if($difference > $maxTime) {
            // custom_error(502, $this->lang); esta comentado por el momento para que no expire el tiempo
        }

        $query = $this->db->query("SELECT * FROM usuarios WHERE id = '$valtoken->id'");
        $checkUser = $query->getResult();
        if (!$checkUser) {
            custom_error(501, $this->lang);
        }

        $checkUser = $checkUser[0];
        // debug($checkUser);

        unset($checkUser->pasw);
        $this->user = $checkUser;
    }

    private function uploadImage($carpeta, $inputName) {
        $rutaCarpeta = 'img/' . $carpeta;

        // Obtener el archivo cargado
        $file = $this->request->getFile($inputName);

        if(!$file){
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
