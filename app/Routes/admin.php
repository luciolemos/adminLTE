<?php

use App\Middlewares\AuthMiddleware;

// Só protege rotas realmente privadas
if (strpos($_SERVER['REQUEST_URI'], '/admin') === 0) {
    AuthMiddleware::handle('Admin'); // Exige Admin para qualquer /admin
}

// Dashboard admin
$router->add('GET', '/admin/dashboard', 'Admin\\DashboardController@index');
$router->add('GET', '/admin/documentacao', 'Admin\\DocController@index');
$router->add('GET', '/admin/caracteristicas', 'Admin\\CaracteristicasController@index');
$router->add('GET', '/admin/estrutura', 'Admin\\EstruturaController@index');
$router->add('GET', '/admin/composer', 'Admin\\ComposerController@index');


$router->add('GET', '/admin/datatable', 'Admin\\DatatableController@index');

$router->add('GET', '/admin/charts', 'Admin\\ChartController@index');

$router->add('GET', '/admin/status/bar', 'Admin\\ChartController@bar_status');
$router->add('GET', '/admin/chartbar', 'Admin\\BarController@index');
$router->add('GET', '/admin/cargos/pie', 'Admin\\ChartController@pie_cargos');
$router->add('GET', '/admin/usuarios-por-mes', 'Admin\\ChartController@usuariosPorMes');
$router->add('GET', '/admin/status/doughnut', 'Admin\\ChartController@doughnut_status');
$router->add('GET', '/admin/status/pie', 'Admin\\ChartController@pie_status');




// CRUD usuários (Admin)
$router->add('GET', '/admin/users', 'Admin\\UserController@index');
// Dashboard de usuários (Admin)
$router->add('GET', '/admin/users/dashboard', 'Admin\\UserController@dashboard');
$router->add('GET', '/admin/users/create', 'Admin\\UserController@create');
$router->add('POST', '/admin/users/create', 'Admin\\UserController@create');
$router->add('GET', '/admin/users/edit/{id}', 'Admin\\UserController@edit');
$router->add('POST', '/admin/users/edit/{id}', 'Admin\\UserController@edit');
$router->add('POST', '/admin/users/delete/{id}', 'Admin\\UserController@delete');
$router->add('GET',  '/admin/users/show/{id}',   'Admin\\UserController@show'); // <-- nova rota show


// Ferramentas CRUD (ToolController)

// CRUD Ferramentas (Admin\ToolController)
// Ferramentas CRUD
$router->add('GET',  '/admin/tools',             'Admin\\ToolController@index');
$router->add('GET',  '/admin/tools/dashboard',   'Admin\\ToolController@dashboard'); // <-- nova rota dashboard
$router->add('GET',  '/admin/tools/create',      'Admin\\ToolController@create');
$router->add('POST', '/admin/tools/create',      'Admin\\ToolController@create');
$router->add('GET',  '/admin/tools/edit/{id}',   'Admin\\ToolController@edit');
$router->add('POST', '/admin/tools/edit/{id}',   'Admin\\ToolController@edit');
$router->add('POST', '/admin/tools/delete/{id}', 'Admin\\ToolController@delete');
$router->add('GET',  '/admin/tools/show/{id}',   'Admin\\ToolController@show'); // rota show



// CRUD Posts (Admin)
$router->add('GET',  '/admin/posts',             'Admin\\PostController@index');
$router->add('GET',  '/admin/posts/dashboard',   'Admin\\PostController@dashboard'); // <-- nova rota dashboard
$router->add('GET',  '/admin/posts/create',      'Admin\\PostController@create');
$router->add('POST', '/admin/posts/create',      'Admin\\PostController@create');
$router->add('GET',  '/admin/posts/edit/{id}',   'Admin\\PostController@edit');
$router->add('POST', '/admin/posts/edit/{id}',   'Admin\\PostController@edit');
$router->add('POST', '/admin/posts/delete/{id}', 'Admin\\PostController@delete');
$router->add('GET',  '/admin/posts/show/{id}',   'Admin\\PostController@show'); // rota show
