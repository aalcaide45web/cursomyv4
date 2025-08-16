<?php declare(strict_types=1);

class Router
{
    private array $routes = [];
    private array $config;
    
    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/env.php';
    }
    
    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }
    
    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }
    
    public function patch(string $path, callable $handler): void
    {
        $this->routes['PATCH'][$path] = $handler;
    }
    
    public function dispatch(string $method, string $uri): void
    {
        // Limpiar la URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Buscar la ruta
        if (isset($this->routes[$method][$uri])) {
            $handler = $this->routes[$method][$uri];
            call_user_func($handler);
            return;
        }
        
        // Buscar rutas con parámetros
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = $this->routeToPattern($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remover el match completo
                call_user_func_array($handler, $matches);
                return;
            }
        }
        
        // Ruta no encontrada
        http_response_code(404);
        echo "404 - Página no encontrada";
    }
    
    private function routeToPattern(string $route): string
    {
        return '#^' . preg_replace('#\{([^}]+)\}#', '([^/]+)', $route) . '$#';
    }
    
    public function getConfig(): array
    {
        return $this->config;
    }
}
