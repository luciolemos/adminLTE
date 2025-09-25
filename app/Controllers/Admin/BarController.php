<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;

class BarController extends Controller
{
    public function index()
    {
        $this->authorize(); // se for necessário

        $userModel = new User();
        $statusOptions = ['Ativo', 'Inativo', 'Pendente', 'Banido'];

        $statusLabels = $statusOptions;
        $statusCounts = [];
        foreach ($statusOptions as $status) {
            $statusCounts[] = $userModel->countByStatus($status);
        }

        return $this->render('Admin/chartbar', [
            'title' => 'Usuários por Status',
            'user' => $_SESSION['user'],
            'statusLabels' => $statusLabels,
            'statusCounts' => $statusCounts
        ]);
    }

    private function authorize()
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['cargo'] !== 'Admin') {
            set_flash('error', 'Acesso negado. Você precisa ser administrador!');
            header('Location: /login');
            exit;
        }
    }
}
