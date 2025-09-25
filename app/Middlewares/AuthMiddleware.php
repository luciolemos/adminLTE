<?php
namespace App\Middlewares;

class AuthMiddleware
{
    public static function handle()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    }
} 