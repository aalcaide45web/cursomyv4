<?php declare(strict_types=1);

// Incluir el router y controladores
require_once __DIR__ . '/../app/Router.php';
require_once __DIR__ . '/../app/Controllers/DashboardController.php';

// Crear instancia del router
$router = new Router();

// Definir rutas
$router->get('/', function() {
    $controller = new DashboardController();
    $controller->index();
});

// Rutas de API para el dashboard
$router->get('/api/dashboard/stats', function() {
    $controller = new DashboardController();
    $controller->getStats();
});

$router->get('/api/dashboard/courses', function() {
    $controller = new DashboardController();
    $controller->getCourses();
});

// Manejar la petición
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
