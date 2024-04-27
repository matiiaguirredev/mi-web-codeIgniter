<?php

namespace App\Controllers;

class Admin extends BaseController {

    private $db = null;
    private $key = null;
    private $urlAPI = "http://mi-web/api/";
    private $user = null;
    private $data = null;

    public function __construct() {

        $this->db = \Config\Database::connect();
        $this->key = getenv('KEYENCRIPT');
    }

    public function login() {
        $this->data = [];
        if ($this->request->getGetPost()) {
            $login = json_decode(send_post($this->urlAPI . "login", $this->request->getGetPost()));
            // debug($this->urlAPI ."login", false);
            if (isset($login->error)) {
                $this->data['error'] = $login->error;
            } else {
                setcookie("token", $login->token, 0, "/");
                header("Location: /admin");
                exit();
            }
            // debug($login);
        }


        $this->header();
        // echo view('admin/login', $this->data);
        echo view('admin/pages-login', $this->data);
        $this->footer();
    }

    public function logout() {
        $tiempo = time() - 3600;
        setcookie("token", "", $tiempo, "/");
        header("Location: /admin/login");
        exit();
    }

    public function index() {

        $this->valtoken();
        $this->header();
        $this->sidebar();
        echo view('admin/index');
        $this->footer();
    }

    private function header() {
        $this->data["user"] = $this->user;
        echo view('admin/header', $this->data);
    }

    private function sidebar() {
        echo view('admin/sidebar', $this->data);
    }

    private function footer() {
        // $this->data["user"] = $this->user;
        echo view('admin/footer', $this->data);
    }

    public function pages_404() {

        $this->header();
        echo view('admin/pages-404');
        $this->footer();
    }

    public function pages_500() {

        $this->header();
        echo view('admin/pages-500');
        $this->footer();
    }

    public function tables_basic() {
        $this->valtoken();

        $this->header();
        $this->sidebar();
        echo view('admin/tables-basic');
        $this->footer();
    }

    public function tables_datatables() {
        $this->valtoken();

        $this->header();
        $this->sidebar();
        echo view('admin/tables-datatables');
        $this->footer();
    }

    public function forms_advanced() {
        $this->valtoken();

        $this->header();
        $this->sidebar();
        echo view('admin/forms-advanced');
        $this->footer();
    }

    public function forms_elements() {
        $this->valtoken();

        $this->header();
        $this->sidebar();
        echo view('admin/forms-elements');
        $this->footer();
    }

    public function forms_file_uploads() {
        $this->valtoken();

        $this->header();
        $this->sidebar();
        echo view('admin/forms-file-uploads');
        $this->footer();
    }

    public function forms_quilljs() {
        $this->valtoken();

        $this->header();
        $this->sidebar();
        echo view('admin/forms-quilljs');
        $this->footer();
    }

    public function components_sweet_alert() {
        $this->valtoken();

        $this->header();
        $this->sidebar();
        echo view('admin/components-sweet-alert');
        $this->footer();
    }

    public function register() {

        $this->data = [];
        if ($this->request->getGetPost()) {
            $register = json_decode(send_post($this->urlAPI . "register", $this->request->getGetPost()));
            if (isset($register->error)) {
                $this->data['error'] = $register->error;
            } else {
                header("Location: /admin/login");
                exit();
                // debug($register, false);
            }
        }

        $this->header();
        echo view('admin/pages-register', $this->data);
        $this->footer();
    }

    public function recover() {

        $this->header();
        echo view('admin/pages-recover');
        $this->footer();
    }

    // FUNCIONES PROYECTOS

    public function get_proyect() {
        $this->valtoken();
        $token = $this->request->getCookie("token");

        $this->data['proyectos'] = [];
        $proyect = json_decode(send_post($this->urlAPI . "proyect", ["token" => $token]));
        if (isset($proyect->error)) {
            $this->data['error'] = $proyect->error;
        } else {
            $this->data['proyectos'] = $proyect;
        }

        $view = true;

        if ($view) {
            $this->data['categorias'] = [];
            $categorias = json_decode(send_post($this->urlAPI . "categorias?activo=1"));
            // debug ($categorias,false);
            if (isset($categorias->error)) {
                $this->data['error'] = $categorias->error;
            } else {
                $this->data['categorias'] = $categorias;
            }
            $this->header();
            $this->sidebar();
            echo view('admin/proyects', $this->data);
            $this->footer();
        }
    }

    public function create_proyect() {

        $this->valtoken();
        $view = true;

        if ($this->request->getGetPost()) {
            $token = $this->request->getCookie("token");
            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $create_proyect = json_decode(send_post($this->urlAPI . "create/proyect", $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($create_proyect, false);
            if (isset($create_proyect->error)) {
                $this->data['error'] = $create_proyect->error;
            } else {
                $this->data['success'] = "Proyecto creado exitosamente";
                $this->get_proyect();
                $view = false;
            }
        }

        if ($view) {
            $this->data['categorias'] = [];
            $categorias = json_decode(send_post($this->urlAPI . "categorias?activo=1"));
            if (isset($categorias->error)) {
                $this->data['error'] = $categorias->error;
            } else {
                $this->data['categorias'] = $categorias;
            }
            $this->header();
            $this->sidebar();
            echo view('admin/newproyect', $this->data);
            $this->footer();
        }
    }

    public function update_proyect($id) {

        $this->valtoken();
        $view = true;
        $token = $this->request->getCookie("token");

        if ($this->request->getGetPost()) {

            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $update_proyect = json_decode(send_post($this->urlAPI . "update/proyect/" . $id, $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($update_proyect, false);
            if (isset($update_proyect->error)) {
                $this->data['error'] = $update_proyect->error;
            } else {
                $this->data['success'] = "Proyecto modificado exitosamente";
                $this->get_proyect();
                $view = false;
            }
        }

        if ($view) {
            $this->data['proyecto'] = [];
            $proyect = json_decode(send_post($this->urlAPI . "proyect/" . $id, ["token" => $token]));
            if (isset($proyect->error)) {
                $this->data['error'] = $proyect->error;
            } else {
                $this->data['proyecto'] = $proyect;
            }
            $this->data['categorias'] = [];
            $categorias = json_decode(send_post($this->urlAPI . "categorias?activo=1"));
            if (isset($categorias->error)) {
                $this->data['error'] = $categorias->error;
            } else {
                $this->data['categorias'] = $categorias;
            }
            $this->header();
            $this->sidebar();
            echo view('admin/edit-proyect');
            $this->footer();
        }
    }

    public function delete_proyect($id) {
        // $this->valtoken();
        $token = $this->request->getCookie("token");

        // $this->data = [];
        if ($id) {
            $requestData = ["token" => $token]; // Datos para enviar a la API
            $delete_proyect = json_decode(send_post($this->urlAPI . "delete/proyect/" . $id, $requestData));

            if (isset($delete_proyect->error)) {
                $this->data['error'] = $delete_proyect->error;
            } else {
                // El proyecto se eliminó exitosamente
                $this->data['success'] = "Proyecto eliminado exitosamente";
            }
        }
        $this->get_proyect();
        // header("Location: /admin/proyects");
        // exit();
    }

    // FIN FUNCIONES PROYECTOS

    // FUNCIONES LENGUAJES
    public function get_lenguaje() {
        $this->valtoken();
        $token = $this->request->getCookie("token");

        $this->data['lenguajes'] = [];
        $lenguaje = json_decode(send_post($this->urlAPI . "lenguaje", ["token" => $token]));
        if (isset($lenguaje->error)) {
            $this->data['error'] = $lenguaje->error;
        } else {
            $this->data['lenguajes'] = $lenguaje;
        }

        $this->header();
        $this->sidebar();
        echo view('admin/lenguaje', $this->data);
        $this->footer();
    }

    public function create_lenguaje() {

        $this->valtoken();
        $view = true;

        if ($this->request->getGetPost()) {
            $token = $this->request->getCookie("token");
            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            // debug($requestData,false);

            $create_lenguaje = json_decode(send_post($this->urlAPI . "create/lenguaje", $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($create_lenguaje, false);
            if (isset($create_lenguaje->error)) {
                $this->data['error'] = $create_lenguaje->error;
            } else {
                $this->data['success'] = "Lenguaje creado exitosamente";
                $this->get_lenguaje();
                $view = false;
            }
            // debug($create_lenguaje, false);

        }

        if ($view) {
            $this->header();
            $this->sidebar();
            echo view('admin/newlenguaje');
            $this->footer();
        }
    }

    public function update_lenguaje($id) {

        $this->valtoken();
        $view = true;
        $token = $this->request->getCookie("token");

        if ($this->request->getGetPost()) {

            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $update_lenguaje = json_decode(send_post($this->urlAPI . "update/lenguaje/" . $id, $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($update_lenguaje, false);
            if (isset($update_lenguaje->error)) {
                $this->data['error'] = $update_lenguaje->error;
            } else {
                $this->data['success'] = "Lenguaje modificado exitosamente";
                $this->get_lenguaje();
                $view = false;
            }
        }

        if ($view) {
            $this->data['lenguajes'] = [];
            $proyect = json_decode(send_post($this->urlAPI . "lenguaje/" . $id, ["token" => $token]));
            if (isset($proyect->error)) {
                $this->data['error'] = $proyect->error;
            } else {
                $this->data['lenguaje'] = $proyect;
            }
            $this->header();
            $this->sidebar();
            echo view('admin/edit-lenguaje');
            $this->footer();
        }
    }

    public function delete_lenguaje($id) {
        // $this->valtoken();
        $token = $this->request->getCookie("token");

        // $this->data = [];
        if ($id) {
            $requestData = ["token" => $token]; // Datos para enviar a la API
            $delete_lenguaje = json_decode(send_post($this->urlAPI . "delete/lenguaje/" . $id, $requestData));

            if (isset($delete_lenguaje->error)) {
                $this->data['error'] = $delete_lenguaje->error;
            } else {
                // El proyecto se eliminó exitosamente
                $this->data['success'] = "Lenguaje eliminado exitosamente";
            }
        }
        $this->get_lenguaje();
        // header("Location: /admin/proyects");
        // exit();
    }

    // FIN FUNCIONES LENGUAJES

    // FUNCIONES REDES
    public function get_redes() {
        $this->valtoken();
        $token = $this->request->getCookie("token");

        $this->data['redes'] = [];
        $redes = json_decode(send_post($this->urlAPI . "redes", ["token" => $token]));
        if (isset($redes->error)) {
            $this->data['error'] = $redes->error;
        } else {
            $this->data['redes'] = $redes;
        }

        $this->header();
        $this->sidebar();
        echo view('admin/redes', $this->data);
        $this->footer();
    }

    public function create_redes() {

        $this->valtoken();
        $view = true;

        if ($this->request->getGetPost()) {
            $token = $this->request->getCookie("token");
            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES,false);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $create_redes = json_decode(send_post($this->urlAPI . "create/redes", $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($create_redes, false);
            if (isset($create_redes->error)) {
                $this->data['error'] = $create_redes->error;
            } else {
                $this->data['success'] = "Social media creada exitosamente";
                $this->get_redes();
                $view = false;
            }
            // debug($create_lenguaje, false);

        }

        if ($view) {
            $this->header();
            $this->sidebar();
            echo view('admin/newredes');
            $this->footer();
        }
    }

    public function update_redes($id) {

        $this->valtoken();
        $view = true;
        $token = $this->request->getCookie("token");

        if ($this->request->getGetPost()) {

            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $update_redes = json_decode(send_post($this->urlAPI . "update/redes/" . $id, $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($update_redes, false);
            if (isset($update_redes->error)) {
                $this->data['error'] = $update_redes->error;
            } else {
                $this->data['success'] = "Social media modificada exitosamente";
                $this->get_redes();
                $view = false;
            }
            // debug($update_redes, false);

        }

        if ($view) {
            $this->data['redes'] = [];
            $redes = json_decode(send_post($this->urlAPI . "redes/" . $id, ["token" => $token]));
            if (isset($redes->error)) {
                $this->data['error'] = $redes->error;
            } else {
                $this->data['redes'] = $redes;
            }
            $this->header();
            $this->sidebar();
            echo view('admin/edit-redes', $this->data);
            $this->footer();
        }
    }

    public function delete_redes($id) {
        // $this->valtoken();
        $token = $this->request->getCookie("token");

        // $this->data = [];
        if ($id) {
            $requestData = ["token" => $token]; // Datos para enviar a la API
            $delete_redes = json_decode(send_post($this->urlAPI . "delete/redes/" . $id, $requestData));

            if (isset($delete_redes->error)) {
                $this->data['error'] = $delete_redes->error;
            } else {
                // El proyecto se eliminó exitosamente
                $this->data['success'] = "Social media eliminada exitosamente";
            }
        }
        $this->get_redes();
        // header("Location: /admin/proyects");
        // exit();
    }

    // FIN FUNCIONES LENGUAJES

    // FUNCIONES CATEGORIAS
    public function get_categorias() {
        $this->valtoken();
        $token = $this->request->getCookie("token");

        $this->data['categorias'] = [];
        $categorias = json_decode(send_post($this->urlAPI . "categorias", ["token" => $token]));
        if (isset($categorias->error)) {
            $this->data['error'] = $categorias->error;
        } else {
            $this->data['categorias'] = $categorias;
        }

        $this->header();
        $this->sidebar();
        echo view('admin/categorias', $this->data);
        $this->footer();
    }

    public function create_categorias() {

        $this->valtoken();
        $view = true;

        if ($this->request->getGetPost()) {
            $token = $this->request->getCookie("token");
            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES,false);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $create_categorias = json_decode(send_post($this->urlAPI . "create/categorias", $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($create_categorias, false);
            if (isset($create_categorias->error)) {
                $this->data['error'] = $create_categorias->error;
            } else {
                $this->data['success'] = "Categoria creada exitosamente";
                $this->get_categorias();
                $view = false;
            }
            // debug($create_categorias, false);

        }

        if ($view) {
            $this->header();
            $this->sidebar();
            echo view('admin/newcategorias');
            $this->footer();
        }
    }

    public function update_categorias($id) {

        $this->valtoken();
        $view = true;
        $token = $this->request->getCookie("token");

        if ($this->request->getGetPost()) {

            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $update_categorias = json_decode(send_post($this->urlAPI . "update/categorias/" . $id, $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($update_categorias, false);
            if (isset($update_categorias->error)) {
                $this->data['error'] = $update_categorias->error;
            } else {
                $this->data['success'] = "Categoria modificada exitosamente";
                $this->get_categorias();
                $view = false;
            }
            // debug($update_redes, false);

        }

        if ($view) {
            $this->data['categorias'] = [];
            $categorias = json_decode(send_post($this->urlAPI . "categorias/" . $id, ["token" => $token]));
            if (isset($categorias->error)) {
                $this->data['error'] = $categorias->error;
            } else {
                $this->data['categorias'] = $categorias;
            }
            $this->header();
            $this->sidebar();
            echo view('admin/edit-categorias');
            $this->footer();
        }
    }

    public function delete_categorias($id) {
        // $this->valtoken();
        $token = $this->request->getCookie("token");

        // $this->data = [];
        if ($id) {
            $requestData = ["token" => $token]; // Datos para enviar a la API
            $delete_categorias = json_decode(send_post($this->urlAPI . "delete/categorias/" . $id, $requestData));

            if (isset($delete_categorias->error)) {
                $this->data['error'] = $delete_categorias->error;
            } else {
                // El proyecto se eliminó exitosamente
                $this->data['success'] = "Categoria eliminada exitosamente";
            }
        }
        $this->get_categorias();
        // header("Location: /admin/proyects");
        // exit();
    }

    // FIN FUNCIONES CATEGORIAS

    // FUNCIONES SERVICIOS
    public function get_servicios() {
        $this->valtoken();
        $token = $this->request->getCookie("token");

        $this->data['servicios'] = [];
        $servicios = json_decode(send_post($this->urlAPI . "servicios", ["token" => $token]));
        if (isset($servicios->error)) {
            $this->data['error'] = $servicios->error;
        } else {
            $this->data['servicios'] = $servicios;
        }

        $this->header();
        $this->sidebar();
        echo view('admin/servicios', $this->data);
        $this->footer();
    }

    public function create_servicios() {

        $this->valtoken();
        $view = true;

        if ($this->request->getGetPost()) {
            $token = $this->request->getCookie("token");
            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES,false);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $create_servicios = json_decode(send_post($this->urlAPI . "create/servicios", $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($create_servicios, false);
            if (isset($create_servicios->error)) {
                $this->data['error'] = $create_servicios->error;
            } else {
                $this->data['success'] = "Servicio creada exitosamente";
                $this->get_servicios();
                $view = false;
            }
            // debug($create_servicios, false);

        }

        if ($view) {
            $this->header();
            $this->sidebar();
            echo view('admin/newservicios');
            $this->footer();
        }
    }

    public function update_servicios($id) {

        $this->valtoken();
        $view = true;
        $token = $this->request->getCookie("token");

        if ($this->request->getGetPost()) {

            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $update_servicios = json_decode(send_post($this->urlAPI . "update/servicios/" . $id, $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($update_servicios, false);
            if (isset($update_servicios->error)) {
                $this->data['error'] = $update_servicios->error;
            } else {
                $this->data['success'] = "Categoria modificada exitosamente";
                $this->get_servicios();
                $view = false;
            }
            // debug($update_servicios, false);

        }

        if ($view) {
            $this->data['servicios'] = [];
            $servicios = json_decode(send_post($this->urlAPI . "servicios/" . $id, ["token" => $token]));
            if (isset($servicios->error)) {
                $this->data['error'] = $servicios->error;
            } else {
                $this->data['servicios'] = $servicios;
            }
            $this->header();
            $this->sidebar();
            echo view('admin/edit-servicios');
            $this->footer();
        }
    }

    public function delete_servicios($id) {
        // $this->valtoken();
        $token = $this->request->getCookie("token");

        // $this->data = [];
        if ($id) {
            $requestData = ["token" => $token]; // Datos para enviar a la API
            $delete_servicios = json_decode(send_post($this->urlAPI . "delete/servicios/" . $id, $requestData));

            if (isset($delete_servicios->error)) {
                $this->data['error'] = $delete_servicios->error;
            } else {
                // El proyecto se eliminó exitosamente
                $this->data['success'] = "Servicio eliminado exitosamente";
            }
        }
        $this->get_servicios();
    }

    // FIN FUNCIONES SERVICIOS

    // FUNCIONES CURRICULUM
    public function get_curriculum() {
        $this->valtoken();
        $token = $this->request->getCookie("token");

        $this->data['curriculum'] = [];
        $curriculum = json_decode(send_post($this->urlAPI . "curriculum", ["token" => $token]));
        if (isset($curriculum->error)) {
            $this->data['error'] = $curriculum->error;
        } else {
            $this->data['curriculum'] = $curriculum;
        }

        $this->header();
        $this->sidebar();
        echo view('admin/curriculum', $this->data);
        $this->footer();
    }

    public function create_curriculum() {

        $this->valtoken();
        $view = true;

        if ($this->request->getGetPost()) {
            $token = $this->request->getCookie("token");
            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES,false);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $create_curriculum = json_decode(send_post($this->urlAPI . "create/curriculum", $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($create_curriculum, false);
            if (isset($create_curriculum->error)) {
                $this->data['error'] = $create_curriculum->error;
            } else {
                $this->data['success'] = "Curriculum creado exitosamente";
                $this->get_curriculum();
                $view = false;
            }
            // debug($create_curriculum, false);

        }

        if ($view) {
            $this->header();
            $this->sidebar();
            echo view('admin/newcurriculum');
            $this->footer();
        }
    }

    public function update_curriculum($id) {

        $this->valtoken();
        $view = true;
        $token = $this->request->getCookie("token");

        if ($this->request->getGetPost()) {

            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $update_curriculum = json_decode(send_post($this->urlAPI . "update/curriculum/" . $id, $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($update_curriculum, false);
            if (isset($update_curriculum->error)) {
                $this->data['error'] = $update_curriculum->error;
            } else {
                $this->data['success'] = "Curriculum modificado exitosamente";
                $this->get_curriculum();
                $view = false;
            }
            // debug($update_curriculum, false);

        }

        if ($view) {
            $this->data['curriculum'] = [];
            $curriculum = json_decode(send_post($this->urlAPI . "curriculum/" . $id, ["token" => $token]));
            if (isset($curriculum->error)) {
                $this->data['error'] = $curriculum->error;
            } else {
                $this->data['curriculum'] = $curriculum;
            }
            $this->header();
            $this->sidebar();
            echo view('admin/edit-curriculum');
            $this->footer();
        }
    }

    public function delete_curriculum($id) {
        // $this->valtoken();
        $token = $this->request->getCookie("token");

        // $this->data = [];
        if ($id) {
            $requestData = ["token" => $token]; // Datos para enviar a la API
            $delete_curriculum = json_decode(send_post($this->urlAPI . "delete/curriculum/" . $id, $requestData));

            if (isset($delete_curriculum->error)) {
                $this->data['error'] = $delete_curriculum->error;
            } else {
                // El proyecto se eliminó exitosamente
                $this->data['success'] = "Curriculum eliminado exitosamente";
            }
        }
        $this->get_curriculum();
    }

    // FIN FUNCIONES CURRICULUM

    // FUNCIONES PERFIL

    public function perfil() {
        $this->valtoken();
        // debug($this->user);

        if ($this->request->getGetPost()) {
            $token = $this->request->getCookie("token");
            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }

            $checkPerfil = json_decode(send_post($this->urlAPI . "create/perfil", $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($checkPerfil);
            // debug(send_post($this->urlAPI . "create/perfil", $requestData));
            if (!isset($checkPerfil->error)) {
                $this->user->informacion = $checkPerfil;
            }
        }
        $this->header();
        $this->sidebar();
        echo view('admin/perfil');
        $this->footer();
    }

    // FUNCIONES HOBIES
    public function get_hobies() {
        $this->valtoken();
        $token = $this->request->getCookie("token");

        $this->data['hobies'] = [];
        $hobies = json_decode(send_post($this->urlAPI . "hobies", ["token" => $token]));
        if (isset($hobies->error)) {
            $this->data['error'] = $hobies->error;
        } else {
            $this->data['hobies'] = $hobies;
        }

        $this->header();
        $this->sidebar();
        echo view('admin/hobies', $this->data);
        $this->footer();
    }

    public function create_hobies() {

        $this->valtoken();
        $view = true;

        if ($this->request->getGetPost()) {
            $token = $this->request->getCookie("token");
            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES,false);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $create_hobies = json_decode(send_post($this->urlAPI . "create/hobies", $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($create_hobies, false);
            if (isset($create_hobies->error)) {
                $this->data['error'] = $create_hobies->error;
            } else {
                $this->data['success'] = "Hobbie creado exitosamente";
                $this->get_hobies();
                $view = false;
            }
            // debug($create_hobies, false);

        }

        if ($view) {
            $this->header();
            $this->sidebar();
            echo view('admin/newhobies');
            $this->footer();
        }
    }

    public function update_hobies($id) {

        $this->valtoken();
        $view = true;
        $token = $this->request->getCookie("token");

        if ($this->request->getGetPost()) {

            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $update_hobies = json_decode(send_post($this->urlAPI . "update/hobies/" . $id, $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($update_hobies, false);
            if (isset($update_hobies->error)) {
                $this->data['error'] = $update_hobies->error;
            } else {
                $this->data['success'] = "Hobbie modificado exitosamente";
                $this->get_hobies();
                $view = false;
            }
            // debug($update_hobies, false);

        }

        if ($view) {
            $this->data['hobies'] = [];
            $hobies = json_decode(send_post($this->urlAPI . "hobies/" . $id, ["token" => $token]));
            if (isset($hobies->error)) {
                $this->data['error'] = $hobies->error;
            } else {
                $this->data['hobies'] = $hobies;
            }
            $this->header();
            $this->sidebar();
            echo view('admin/edit-hobies');
            $this->footer();
        }
    }

    public function delete_hobies($id) {
        // $this->valtoken();
        $token = $this->request->getCookie("token");

        // $this->data = [];
        if ($id) {
            $requestData = ["token" => $token]; // Datos para enviar a la API
            $delete_hobies = json_decode(send_post($this->urlAPI . "delete/hobies/" . $id, $requestData));

            if (isset($delete_hobies->error)) {
                $this->data['error'] = $delete_hobies->error;
            } else {
                // El proyecto se eliminó exitosamente
                $this->data['success'] = "Hobbie eliminado exitosamente";
            }
        }
        $this->get_hobies();
    }

    // FIN FUNCIONES HOBIES

    // FUNCIONES CONTACTO
    public function get_contacto() {
        $this->valtoken();
        $token = $this->request->getCookie("token");

        $this->data['contacto'] = [];
        $contacto = json_decode(send_post($this->urlAPI . "contacto", ["token" => $token]));
        if (isset($contacto->error)) {
            $this->data['error'] = $contacto->error;
        } else {
            $this->data['contacto'] = $contacto;
        }

        $this->header();
        $this->sidebar();
        echo view('admin/contacto', $this->data);
        $this->footer();
    }

    public function create_contacto() {

        $this->valtoken();
        $view = true;

        if ($this->request->getGetPost()) {
            $token = $this->request->getCookie("token");
            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES,false);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $create_contacto = json_decode(send_post($this->urlAPI . "create/contacto", $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($create_contacto, false);
            if (isset($create_contacto->error)) {
                $this->data['error'] = $create_contacto->error;
            } else {
                $this->data['success'] = "Metodo de contacto creado exitosamente";
                $this->get_contacto();
                $view = false;
            }
            // debug($create_contacto, false);

        }

        if ($view) {
            $this->header();
            $this->sidebar();
            echo view('admin/newcontacto');
            $this->footer();
        }
    }

    public function update_contacto($id) {

        $this->valtoken();
        $view = true;
        $token = $this->request->getCookie("token");

        if ($this->request->getGetPost()) {

            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            // debug($_FILES);
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $update_contacto = json_decode(send_post($this->urlAPI . "update/contacto/" . $id, $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($update_contacto, false);
            if (isset($update_contacto->error)) {
                $this->data['error'] = $update_contacto->error;
            } else {
                $this->data['success'] = "Metodo de contacto modificado exitosamente";
                $this->get_contacto();
                $view = false;
            }
            // debug($update_contacto, false);

        }

        if ($view) {
            $this->data['contacto'] = [];
            $contacto = json_decode(send_post($this->urlAPI . "contacto/" . $id, ["token" => $token]));
            if (isset($contacto->error)) {
                $this->data['error'] = $contacto->error;
            } else {
                $this->data['contacto'] = $contacto;
            }
            $this->header();
            $this->sidebar();
            echo view('admin/edit-contacto');
            $this->footer();
        }
    }

    public function delete_contacto($id) {
        // $this->valtoken();
        $token = $this->request->getCookie("token");

        // $this->data = [];
        if ($id) {
            $requestData = ["token" => $token]; // Datos para enviar a la API
            $delete_contacto = json_decode(send_post($this->urlAPI . "delete/contacto/" . $id, $requestData));

            if (isset($delete_contacto->error)) {
                $this->data['error'] = $delete_contacto->error;
            } else {
                // El proyecto se eliminó exitosamente
                $this->data['success'] = "Metodo de contacto eliminado exitosamente";
            }
        }
        $this->get_contacto();
    }

    // FIN FUNCIONES CONTACTO

    // FUNCIONES SECCIONES TITULOS/DESCRIPTIONS Y MAS
    public function get_secciones() {
        $this->valtoken();
        $token = $this->request->getCookie("token");

        $this->data['secciones'] = [];
        $secciones = json_decode(send_post($this->urlAPI . "secciones", ["token" => $token]));
        if (isset($secciones->error)) {
            $this->data['error'] = $secciones->error;
        } else {
            $this->data['secciones'] = $secciones;
        }

        $this->header();
        $this->sidebar();
        echo view('admin/secciones', $this->data);
        $this->footer();
    }

    public function create_secciones() {

        $this->valtoken();
        $view = true;

        if ($this->request->getGetPost()) {
            $token = $this->request->getCookie("token");
            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            $create_secciones = json_decode(send_post($this->urlAPI . "create/secciones", $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($create_secciones, false);
            if (isset($create_secciones->error)) {
                $this->data['error'] = $create_secciones->error;
            } else {
                $this->data['success'] = "Informacion de secciones creada exitosamente";
                $this->get_secciones();
                $view = false;
            }
            // debug($create_secciones, false);

        }

        if ($view) {
            $this->header();
            $this->sidebar();
            echo view('admin/newsecciones');
            $this->footer();
        }
    }

    public function update_secciones($id) {

        $this->valtoken();
        $view = true;
        $token = $this->request->getCookie("token");

        if ($this->request->getGetPost()) {

            $requestData = array_merge($this->request->getGetPost(), ["token" => $token]); // en una variable tengo que guardar el merge 
            foreach ($_FILES as $k => $v) {
                if (strlen($v['name'])) {
                    $requestData[$k] = curl_file_create($v['tmp_name'], $v['type'], basename($v['name']));
                }
            }
            // debug($requestData, false);
            $update_secciones = json_decode(send_post($this->urlAPI . "update/secciones/" . $id, $requestData)); // envio directamente la variable que tiene todo ya concatenado
            // debug($update_secciones, false);
            if (isset($update_secciones->error)) {
                $this->data['error'] = $update_secciones->error;
            } else {
                $this->data['success'] = "Informacion de secciones modificada exitosamente";
                $this->get_secciones();
                $view = false;
            }
            // debug($update_secciones, false);

        }

        if ($view) {
            $this->data['secciones'] = [];
            $secciones = json_decode(send_post($this->urlAPI . "secciones/" . $id, ["token" => $token]));
            if (isset($secciones->error)) {
                $this->data['error'] = $secciones->error;
            } else {
                $this->data['secciones'] = $secciones;
            }
            $this->header();
            $this->sidebar();
            echo view('admin/edit-secciones');
            $this->footer();
        }
    }

    public function delete_secciones($id) {
        // $this->valtoken();
        $token = $this->request->getCookie("token");

        // $this->data = [];
        if ($id) {
            $requestData = ["token" => $token]; // Datos para enviar a la API
            $delete_secciones = json_decode(send_post($this->urlAPI . "delete/secciones/" . $id, $requestData));

            if (isset($delete_secciones->error)) {
                $this->data['error'] = $delete_secciones->error;
            } else {
                // El proyecto se eliminó exitosamente
                $this->data['success'] = "Informacion de secciones eliminada exitosamente";
            }
        }
        $this->get_secciones();
    }

    // FIN FUNCIONES SECCIONES TITULOS/DESCRIPTIONS Y MAS

    private function valtoken() {

        $token = $this->request->getCookie("token");
        if (!$token) {
            header("Location: /admin/login");
            exit();
        }
        // debug($token,false);
        $checkToken = json_decode(send_post($this->urlAPI . "checktoken", ["token" => $token]));
        // debug($this->urlAPI ."checkToken", false);
        if (isset($checkToken->error)) {
            header("Location: /admin/login");
            exit();
        } else {
            $this->user = $checkToken;
            $checkToken = json_decode(send_post($this->urlAPI . "perfil", ["token" => $token]));
            // debug(send_post($this->urlAPI . "perfil", ["token" => $token]));
            // debug($checkToken);
            if (!isset($checkToken->error)) {
                $this->user->informacion = $checkToken;
            }
        }
        // debug($checkToken);
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
