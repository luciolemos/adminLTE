<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class CaracteristicasController extends Controller
{
    public function index()
    {
        $user = $_SESSION['user'] ?? null;
        // Protege a rota: só admin
        if (!$user || $user['cargo'] !== 'Admin') {
            // Redireciona para dashboard, ou mostre 403
            header('Location: /admin/dashboard');
            exit;
        }
        // (Opcional) Leitura de arquivo, ou dados da documentação
        // $conteudo = file_get_contents(...);

        return $this->render('Admin/caracteristicas', [
            'title' => 'Características do Sistema',
            'user'  => $user,
            // 'conteudo' => $conteudo
        ]);
    }
}
