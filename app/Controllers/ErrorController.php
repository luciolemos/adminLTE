<?php

namespace App\Controllers;

use App\Core\Controller;

class ErrorController extends Controller
{
    public function notFound()
    {
        http_response_code(404);
        return $this->render('errors/404');
    }

    // Futuro: handler para 500, 403 etc
    // public function internalError() { ... }
}
