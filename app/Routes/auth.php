<?php
use App\Core\Router;

// Exibe tela de login
$router->add('GET', '/login', 'Auth\\LoginController@index');

// Processa login
$router->add('POST', '/login', 'Auth\\LoginController@login');

$router->add('GET', '/logout', 'Auth\\LogoutController@index');
