<?php declare(strict_types=1);

// Incluir el router y controladores
require_once __DIR__ . '/../app/Router.php';
require_once __DIR__ . '/../app/Controllers/DashboardController.php';
require_once __DIR__ . '/../app/Controllers/ScanController.php';
require_once __DIR__ . '/../app/Controllers/SearchController.php';

// Crear instancia del router
$router = new Router();

// Definir rutas
// Rutas del dashboard
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

// Rutas de escaneo
$router->post('/api/scan/incremental', function() {
    $controller = new ScanController();
    $controller->incremental();
});

$router->post('/api/scan/rebuild', function() {
    $controller = new ScanController();
    $controller->rebuild();
});

$router->get('/api/scan/status', function() {
    $controller = new ScanController();
    $controller->status();
});

$router->get('/api/scan/files', function() {
    $controller = new ScanController();
    $controller->scannedFiles();
});

// Ruta de búsqueda
$router->get('/api/search', function() {
    $controller = new SearchController();
    $controller->search();
});

// Manejar la petición
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
