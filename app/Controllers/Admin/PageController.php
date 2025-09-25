<?php
// app/Controllers/Admin/PageController.php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middlewares\AuthMiddleware;

class PageController extends Controller
{
    public function show(string $slug)
    {
        AuthMiddleware::handle();

        $viewPath = "Admin/pages/{$slug}";
        $file = __DIR__ . "/../../Views/Admin/pages/{$slug}.twig";

        if (!file_exists($file)) {
            return $this->render('errors/404');
        }

        return $this->render($viewPath);
    }

    public function blank() {
  return $this->render('Admin/pages/blank');
}

public function analytics() {
  return $this->render('Admin/pages/analytics');
}

public function reports() {
  return $this->render('Admin/pages/reports');
}

public function charts() {
  return $this->render('Admin/pages/charts');
}

public function advanced_table() {
  return $this->render('Admin/pages/advanced_table');
}

public function usuarios() {
  return $this->render('Admin/pages/advanced_table');
}

public function composer() {
  return $this->render('Admin/pages/composer');
}

}
