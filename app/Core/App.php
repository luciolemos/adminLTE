<?php
namespace App\Core;

use App\Core\Router;

class App
{
    public Router $router;

    public function __construct()
{
    $this->router = new Router();

    // Torna $router disponível nos arquivos de rotas
    $router = $this->router;

    $routesPath = dirname(__DIR__) . '/Routes/';
    foreach (glob($routesPath . '*.php') as $routeFile) {
        require $routeFile; // Agora $router estará disponível
    }

    $this->router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
}

}
