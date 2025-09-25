<?php

// app/Controllers/Site/HomeController.php
namespace App\Controllers\Site;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return $this->render('Site/home', [
            'title' => 'Página Inicial',
            'user'  => $_SESSION['user'] ?? null,
            'success' => get_flash('success'),
            'error'   => get_flash('error'),
            'breadcrumb' => [
            ['title' => 'Home', 'url' => null]
        ]
        ]);
    }
}
