<?php

namespace App\Controllers;

class Home extends BaseController {

    private $db = null;
    private $urlAPI = "http://mi-web/api/";
    private $data = null;

    public function __construct() {

        $this->db = \Config\Database::connect();
    }

    private function header() {
        $this->data['secciones'] = [];
        $secciones = json_decode(send_post($this->urlAPI . "secciones?activo=1")); //["token" => $token]
        // debug($secciones, false);
        if (isset($secciones->error)) {
            $this->data['error'] = $secciones->error;
        } else {
            $this->data['secciones'] = $secciones;
        }

        echo view('header', $this->data);
        
    }

    private function footer() {
        echo view('footer', $this->data);
    }

    public function index() {

        $this->data['perfil'] = [];
        $usuario = json_decode(send_post($this->urlAPI . "login", ["usuario" => "matiasaaa", "pasw" => "A123456789*"]));
        // debug($usuario, false);
        if (isset($usuario->error)) {
            $this->data['error'] = $usuario->error;
        } else {
            $perfil = json_decode(send_post($this->urlAPI . "perfil", ["token" => $usuario->token]));
            // debug($perfil);
            $this->data['perfil'] = $perfil;
            $this->data['perfil']->email = $usuario->email;
        }

        $this->data['redes'] = [];
        $redes = json_decode(send_post($this->urlAPI . "redes?activo=1")); //["token" => $token]
        // debug($redes, false);
        if (isset($redes->error)) {
            $this->data['error'] = $redes->error;
        } else {
            $this->data['redes'] = $redes;
        }

        $this->data['lenguajes'] = [];
        $lenguaje = json_decode(send_post($this->urlAPI . "lenguaje?activo=1"));
        // debug($lenguaje);
        if (isset($lenguaje->error)) {
            $this->data['error'] = $lenguaje->error;
        } else {
            $array_dividido = array_chunk($lenguaje, ceil(count($lenguaje) / 2));
            $this->data['lenguajesDiv'] = $array_dividido;
            $this->data['lenguajes'] = $lenguaje;
        }


        $this->data['secciones'] = [];
        $secciones = json_decode(send_post($this->urlAPI . "secciones?activo=1")); //["token" => $token]
        // debug($secciones, false);
        if (isset($secciones->error)) {
            $this->data['error'] = $secciones->error;
        } else {
            $this->data['secciones'] = $secciones;
        }

        $this->header();
        echo view('index', $this->data);
        // debug($secciones, false);

        foreach ($secciones as $secc) {
            // debug($secc);
            if (isset($secc->alias)) {
                $this->data["titulos"] = ($secc->titulos) ?? null;
                $this->data["sub_titulo"] = ($secc->sub_titulo) ?? null;
                $this->data["descripciones"] = ($secc->descripciones) ?? null;
                $this->data["img"] = ($secc->img) ?? null;
                $this->data["bg_color"] = ($secc->bg_color) ?? null;

                /* debug($secc->alias, false); */
                switch ($secc->alias) {
                    case "acerca":
                        // debug($secc, false);
                        $this->aboutmearea();
                        break;
                    case "servicios":
                        $this->servicesarea();
                        break;
                    case "clientes":
                        $this->funfactsarea();
                        break;
                    case "exp":
                        $this->resumearea();
                        break;
                    case "proyectos":
                        $this->portfolioarea();
                        break;
                    case "contratame":
                        $this->hireme();
                        break;
                    case "blog":
                        $this->blog();
                        break;
                    case "testimonios":
                        $this->testimonials();
                        break;
                    case "contactame":
                        $this->contact();
                        break;
                }
            }
        }

        $this->footer();
    }

    public function single_blog($id) {
        $token = $this->request->getCookie("token");

        $this->data = [];
        $this->header();
        // debug($this->data);

        $this->data['blog'] = [];
        $blog = json_decode(send_post($this->urlAPI . "blog/" . $id, ["token" => $token]));
        if (isset($blog->error)) {
            $this->data['error'] = $blog->error;
        } else {
            $this->data['blog'] = $blog;
        }

        $this->data['blogComm'] = [];
        $blogComm = json_decode(send_post($this->urlAPI . "blogComm", ["token" => $token]));
        if (isset($blogComm->error)) {
            $this->data['error'] = $blogComm->error;
        } else {
            $this->data['blogComm'] = $blogComm;
        }

        $this->data['blogCat'] = [];
        $blogCat = json_decode(send_post($this->urlAPI . "blogCat", ["token" => $token]));
        // debug($blogCat, false);
        if (isset($blogCat->error)) {
            $this->data['error'] = $blogCat->error;
        } else {
            $this->data['blogCat'] = $blogCat;
        }

        $this->data['redes'] = [];
        $redes = json_decode(send_post($this->urlAPI . "redes" , ["token" => $token]));
        if (isset($redes->error)) {
            $this->data['error'] = $redes->error;
        } else {
            $this->data['redes'] = $redes;
        }

        echo view('web/single-blog', $this->data);
        $this->footer();
    }

    //estan en orden como iria la web original !!!!
    private function aboutmearea() {

        $this->data['lenguajes'] = [];
        $lenguaje = json_decode(send_post($this->urlAPI . "lenguaje?activo=1"));
        // debug($lenguaje, false);
        if (isset($lenguaje->error)) {
            $this->data['error'] = $lenguaje->error;
        } else {
            $array_dividido = array_chunk($lenguaje, ceil(count($lenguaje) / 2));
            $this->data['lenguajesDiv'] = $array_dividido;
            $this->data['lenguajes'] = $lenguaje;
        }

        $this->data['hobies'] = [];
        $hobies = json_decode(send_post($this->urlAPI . "hobies?activo=1")); //["token" => $token]
        // debug($hobies, false);
        if (isset($hobies->error)) {
            $this->data['error'] = $hobies->error;
        } else {
            $this->data['hobies'] = $hobies;
        }

        // if (!empty($this->data['clientes'])) {
        echo view('web/about-me-area', $this->data);
        // }
    }

    private function servicesarea() {
        $this->data['servicios'] = [];
        $servicios = json_decode(send_post($this->urlAPI . "servicios?activo=1"));
        if (isset($servicios->error)) {
            $this->data['error'] = $servicios->error;
        } else {
            $this->data['servicios'] = $servicios;
        }

        if (!empty($this->data['servicios'])) {
            echo view('web/services-area', $this->data);
        }
    }

    private function funfactsarea() {

        $this->data['clientes'] = [];
        $clientes = json_decode(send_post($this->urlAPI . "clientes?activo=1"));
        if (isset($clientes->error)) {
            $this->data['error'] = $clientes->error;
        } else {
            $this->data['clientes'] = $clientes;
        }

        if (!empty($this->data['clientes'])) {
            echo view('web/fun-facts-area', $this->data);
        }
    }

    private function resumearea() {
        $this->data['curriculum'] = [];
        $curriculum = json_decode(send_post($this->urlAPI . "curriculum?activo=1"));
        if (isset($curriculum->error)) {
            $this->data['error'] = $curriculum->error;
        } else {
            $this->data['curriculum'] = $curriculum;
        }

        if (!empty($this->data['curriculum'])) {
            echo view('web/resume-area', $this->data);
        }
    }

    private function portfolioarea() {

        $this->data['categorias'] = [];
        $categorias = json_decode(send_post($this->urlAPI . "categorias?activo=1"));
        if (isset($categorias->error)) {
            $this->data['error'] = $categorias->error;
        } else {
            $this->data['categorias'] = $categorias;
        }

        $this->data['proyectos'] = [];
        $proyect = json_decode(send_post($this->urlAPI . "proyect?activo=1"));
        if (isset($proyect->error)) {
            $this->data['error'] = $proyect->error;
        } else {
            $this->data['proyectos'] = $proyect;
        }

        if (!empty($this->data['categorias']) && !empty($this->data['proyectos'])) {
            echo view('web/portfolio-area', $this->data);
        }
    }

    private function hireme() {

        echo view('web/hire-me', $this->data);
    }

    private function blog() {
        $this->data['blog'] = [];
        $blog = json_decode(send_post($this->urlAPI . "blog?activo=1"));
        // debug($blog, false);
        if (isset($blog->error)) {
            $this->data['error'] = $blog->error;
        } else {
            $this->data['blog'] = $blog;
        }

        if (!empty($this->data['blog'])) {
            echo view('web/blog', $this->data);
        }
    } // aca sigue la de single blog que esta arriba por que es publica
    
    private function testimonials() {
        $this->data['testimonios'] = [];
        $testimonios = json_decode(send_post($this->urlAPI . "testimonios?activo=1")); //["token" => $token]
        // debug($testimonios, false);
        if (isset($testimonios->error)) {
            $this->data['error'] = $testimonios->error;
        } else {
            $this->data['testimonios'] = $testimonios;
        }

        if (!empty($this->data['testimonios'])) {
            echo view('web/testimonials', $this->data);
        }
    }

    private function contact() {

        $this->data['contacto'] = [];
        $contacto = json_decode(send_post($this->urlAPI . "contacto?activo=1")); //["token" => $token]
        // debug($contacto, false);
        if (isset($contacto->error)) {
            $this->data['error'] = $contacto->error;
        } else {
            $this->data['contacto'] = $contacto;
        }

        if (!empty($this->data['contacto'])) {
            echo view('web/contact', $this->data);
        }
    }

    //  finish el orden de la web !!!!

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
