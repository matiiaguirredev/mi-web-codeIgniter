<?php

namespace App\Controllers;

class Home extends BaseController {

    private $db = null;

    public function __construct() {

        $this->db = \Config\Database::connect();
        
    }

    public function index(){

        $this->header();
        echo view('index');
        $this->contact();
        $this->footer();

    }

    private function header(){
        echo view('header');
    }

    private function contact(){
        echo view('contact');
    }
    
    private function footer(){
        echo view('footer');
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
