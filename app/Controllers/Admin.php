<?php

namespace App\Controllers;

class Admin extends BaseController {

    private $db = null;
    private $key = null;
    private $urlAPI = "http://mi-web/api/";

    public function __construct() {

        $this->db = \Config\Database::connect();
        $this->key = getenv('KEYENCRIPT');

    }

    public function index() {

        $this->header();
        $this->sidebar();   
        echo view('admin/index');
        $this->footer();
    }

    public function pages_404() {
        
        $this->header();
        echo view('admin/pages-404');
    }

    public function pages_500() {
        
        $this->header();
        echo view('admin/pages-500');
    }
    
    public function tables_basic() {
        
        $this->header();
        $this->sidebar();   
        echo view('admin/tables-basic');
        $this->footer();
    }
    
    public function tables_datatables() {
        
        $this->header();
        $this->sidebar();   
        echo view('admin/tables-datatables');
        $this->footer();
    }

    public function forms_advanced() {
        
        $this->header();
        $this->sidebar();   
        echo view('admin/forms-advanced');
        // $this->footer();
    }

    public function forms_elements() {
        
        $this->header();
        $this->sidebar();   
        echo view('admin/forms-elements');
        // $this->footer();
    }

    public function forms_file_uploads() {
        
        $this->header();
        $this->sidebar();   
        echo view('admin/forms-file-uploads');
        // $this->footer();
    }

    public function forms_quilljs() {
        
        $this->header();
        $this->sidebar();   
        echo view('admin/forms-quilljs');
        // $this->footer();
    }

    public function login() {
        $data = [];
        if($this->request->getGetPost()){
            $login = json_decode(send_post($this->urlAPI ."login", $this->request->getGetPost()));
            // debug($this->urlAPI ."login", false);
            if(isset($login->error)){
                $data['error'] = $login->error; 
            }else {
                // Si el inicio de sesión es correcto, redireccionar al "home"
                header("Location: /admin"); 
                exit(); // Asegurarse de que el script se detenga después de enviar la redirección
            }
            // debug($login);
        }


        $this->header();
        // echo view('admin/login', $data);
        echo view('admin/pages-login', $data);
        // $this->footer();

    }

    public function userNew() {

        $this->header();

        // $query   = $this->db->query('SELECT * FROM hola');
        // $data['custom_variable'] = $query->getResult(); 

        // echo view('admin/userNew', $data);

        $datospost = $this->request->getPost();

        if ($datospost) {

            $require = [ // datos obligatorios
                "nombre" => "text", // key posicion - value valor
                "apellido" => "text",
                "usuario" => "alias",
                "email" => "email",
                "pasw" => "password"
            ];

            foreach ($require as $key => $value) {
                $data[$key] = validateValue($datospost[$key], $value);
                unset($require[$key]);
            }

            if ($require) {
                // validar error que te faltan datos
                custom_error(101,"es", $require);
            }

            $data['pasw'] = encode($data['pasw'], $this->key);

            // validateValue
            $this->db->table("usuarios")->insert($data);
        }

        echo view('admin/userNew'); // todo lo que tiene data lo enviamos a la vista //registro ! 

        $this->footer();
    }

    public function newproyect() {

        $this->header();

        $proypost = $this->request->getPost();

        if ($proypost) {

            $require = [ 

                "nombre" => "text", 
                "descripcion" => "text",
                "url" => "url",

            ];

            foreach ($require as $key => $value) {
                $data[$key] = validateValue($proypost[$key], $value);
                unset($require[$key]);
            }

            
            $data["img_proyecto"] = $this->uploadImage("proyectos", "img_proyecto");
            if(!$data["img_proyecto"]){
                $require["img_proyecto"] = true;
            }

            if ($require) {
                // validar error que te faltan datos
                custom_error(101,"es", $require);
            }


            // validateValue
            $this->db->table("proyectos")->insert($data);
        }

        echo view('admin/newproyect'); // todo lo que tiene data lo enviamos a la vista //registro ! 

        $this->footer();
    }
    
    private function header() {
        echo view('admin/header');
    }

    private function sidebar() {
        echo view('admin/sidebar');
    }

    private function footer() {
        echo view('admin/footer');
    }

    private function uploadImage($carpeta, $inputName) {
        $rutaCarpeta = 'img/' . $carpeta;

        // Obtener el archivo cargado
        $file = $this->request->getFile($inputName);

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
