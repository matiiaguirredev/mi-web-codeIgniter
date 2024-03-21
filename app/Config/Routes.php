<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('/test', 'Home::test'); // ruta de pruebas


// rutas administrador

$routes->group('admin', function ($routes) {

    // $routes->match(['get', 'post'], 'create', 'Books::create');
    // $routes->match(['get', 'post'], 'edit/(:segment)', 'Admin::edit/$1');
    // $routes->get('delete/(:segment)', 'Admin::delete/$1');
    
    // $routes->get('test', 'Admin::test');
    // $routes->get('(:segment)', 'Admin::details/$1');
    
    // $routes->get('login', 'Admin::login');
    $routes->get('pages-404', 'Admin::pages_404');
    $routes->get('pages-500', 'Admin::pages_500');
    $routes->match(['get', 'post'], 'login', 'Admin::login');
    $routes->match(['get', 'post'], 'userNew', 'Admin::userNew');
    $routes->match(['get', 'post'], 'newproyect', 'Admin::newproyect');
    $routes->get('tables-basic', 'Admin::tables_basic');
    $routes->get('tables-datatables', 'Admin::tables_datatables');
    $routes->get('forms-advanced', 'Admin::forms_advanced');
    $routes->get('forms-elements', 'Admin::forms_elements');
    $routes->get('forms-file-uploads', 'Admin::forms_file_uploads');
    $routes->get('forms-quilljs', 'Admin::forms_quilljs');

    $routes->get('/', 'Admin::index');
});

$routes->group('api', function ($routes) {

    $routes->match(['get', 'post'], 'register', 'Api::register');
    $routes->match(['get', 'post'], 'login', 'Api::login');
    // $routes->match(['get', 'post'], 'create/proyect', 'Api::create_proyect'); estasmos trabajando a la manera de alberto
    $routes->get('/', 'Api::index');



    $entitySegments = [
        'proyect',
    ];

    foreach ($entitySegments as $entitySegment) {

        // CRUD - Read All
        $routes->match(['get', 'post'], $entitySegment, "Api::get_" . $entitySegment);
        // CRUD - Read Single
        $routes->match(['get', 'post'], $entitySegment . '/(:segment)', "Api::get_" . $entitySegment . "/$1");

        // Grupos CRUD - Create
        $routes->match(['get', 'post'], 'create/' . $entitySegment, "Api::create_" . $entitySegment);

        // Grupos CRUD - Update
        $routes->match(['get', 'post'], 'update/' . $entitySegment . '/(:segment)', "Api::update_" . $entitySegment . "/$1");

        // Grupos CRUD - Delete
        $routes->match(['get', 'post'], 'delete/' . $entitySegment . '/(:segment)', "Api::delete_" . $entitySegment . "/$1");
    }
});