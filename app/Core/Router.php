<?php
namespace App\Core;

class Router
{
    private array $routes = [];
    private $notFoundHandler;

    /**
     * Registra uma rota (GET, POST etc)
     * Aceita paths como /admin/users/edit/{id}
     */
    public function add(string $method, string $path, $handler)
    {
        $method = strtoupper($method);

        // Prepara regex para parâmetros dinâmicos ex: {id}
        $pattern = preg_replace('#\{[\w]+\}#', '([\w\-]+)', $path);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
            'param_names' => $this->extractParamNames($path)
        ];
    }

    /**
     * Extrai nomes dos parâmetros das rotas
     */
    private function extractParamNames($path)
    {
        preg_match_all('#\{([\w]+)\}#', $path, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Define handler para 404
     */
    public function setNotFoundHandler($handler)
    {
        $this->notFoundHandler = $handler;
    }

    /**
     * Despacha rota
     */
    public function dispatch(string $uri, string $method)
    {
        $uri = strtok($uri, '?');
        $method = strtoupper($method);

        if (!empty($this->routes[$method])) {
            foreach ($this->routes[$method] as $route) {
                if (preg_match($route['pattern'], $uri, $matches)) {
                    array_shift($matches); // Remove full match
                    $params = [];
                    foreach ($route['param_names'] as $i => $name) {
                        $params[$name] = $matches[$i] ?? null;
                    }
                    $handler = $route['handler'];

                    // Controller@action
                    if (is_string($handler) && strpos($handler, '@') !== false) {
                        [$controller, $action] = explode('@', $handler);
                        $controllerClass = "\\App\\Controllers\\" . $controller;
                        if (class_exists($controllerClass)) {
                            $instance = new $controllerClass();
                            return $instance->$action(...array_values($params));
                        }
                    }
                    // Callable/closure
                    if (is_callable($handler)) {
                        return $handler($params);
                    }
                }
            }
        }

        // 404
        if ($this->notFoundHandler) {
            return call_user_func($this->notFoundHandler);
        }

        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
    }
}
