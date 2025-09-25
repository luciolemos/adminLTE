<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class BlankController extends Controller
{
    public function index()
    {
        return $this->render('Admin/blank_view');
    }
}