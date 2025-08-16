<?php declare(strict_types=1);

// Incluir el router
require_once __DIR__ . '/../app/Router.php';

// Crear instancia del router
$router = new Router();

// Definir rutas
$router->get('/', function() {
    $title = 'Dashboard - CursoMy LMS Lite';
    $content = file_get_contents(__DIR__ . '/../app/Views/pages/dashboard.php');
    
    // Renderizar el layout
    include __DIR__ . '/../app/Views/partials/layout.php';
});

// Manejar la petición
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
