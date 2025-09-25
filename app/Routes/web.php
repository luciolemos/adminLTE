<?php

use App\Core\Router;

$router->add('GET', '/', 'Site\\HomeController@index');
$router->add('GET', '/about', 'Site\\PageController@about');
$router->add('GET', '/contact', 'Site\\PageController@contact');
$router->add('GET', '/register', 'Site\\RegisterController@index');     // CORRIGIDO
$router->add('POST', '/register', 'Site\\RegisterController@register'); // CORRIGIDO
$router->add('GET', '/login', 'Auth\\LoginController@index');
$router->add('POST', '/login', 'Auth\\LoginController@login');

$router->add('GET',  '/profile',       'Site\\ProfileController@index');
$router->add('GET',  '/profile/edit',  'Site\\ProfileController@edit');
$router->add('POST', '/profile/edit',  'Site\\ProfileController@edit');

$router->add('GET', '/blog', 'Site\\BlogController@index');
$router->add('GET', '/blog/{slug}', 'Site\\BlogController@view');
