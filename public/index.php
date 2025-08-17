<?php declare(strict_types=1);

// Incluir el router y controladores
require_once __DIR__ . '/../app/Router.php';
require_once __DIR__ . '/../app/Controllers/DashboardController.php';
require_once __DIR__ . '/../app/Controllers/ScanController.php';
require_once __DIR__ . '/../app/Controllers/SearchController.php';
require_once __DIR__ . '/../app/Controllers/PlayerController.php';

// Crear instancia del router
$router = new Router();

// Definir rutas
// Rutas del dashboard
$router->get('/', function() {
    $controller = new DashboardController();
    $controller->index();
});

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

// Rutas del reproductor
$router->get('/lesson', function() {
    $controller = new PlayerController();
    $controller->index();
});

$router->get('/api/lesson/info', function() {
    $controller = new PlayerController();
    $controller->getLessonInfo();
});

$router->post('/api/lesson/progress', function() {
    $controller = new PlayerController();
    $controller->saveProgress();
});

$router->get('/api/lesson/notes', function() {
    $controller = new PlayerController();
    $controller->getNotes();
});

$router->post('/api/lesson/notes', function() {
    $controller = new PlayerController();
    $controller->saveNote();
});

$router->get('/api/lesson/comments', function() {
    $controller = new PlayerController();
    $controller->getComments();
});

$router->post('/api/lesson/comments', function() {
    $controller = new PlayerController();
    $controller->saveComment();
});

// Manejar la petición
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
