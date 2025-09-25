<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;

class ChartController extends Controller
{
    /**
     * Gráfico de usuários por Status (Barra)
     */
    public function bar_status()
    {
        $this->authorize();

        $userModel = new User();
        $statusOptions = ['Ativo', 'Inativo', 'Pendente', 'Banido'];

        $statusLabels = $statusOptions;
        $statusCounts = [];
        foreach ($statusOptions as $status) {
            $statusCounts[] = $userModel->countByStatus($status);
        }

        return $this->render('Admin/Charts/status_bar_chart', [
            'title' => 'Usuários por Status',
            'user' => $_SESSION['user'],
            'statusLabels' => $statusLabels,
            'statusCounts' => $statusCounts
        ]);
    }

    /**
     * Gráfico de usuários por Cargo (Pizza)
     */
    public function pie_cargos()
    {
        $this->authorize();

        $userModel = new User();
        $cargoOptions = ['Admin', 'Editor', 'Moderador', 'Usuário'];

        $cargoLabels = $cargoOptions;
        $cargoCounts = [];
        foreach ($cargoOptions as $cargo) {
            $cargoCounts[] = $userModel->countByCargo($cargo);
        }

        return $this->render('Admin/Charts/cargo_pie_chart', [//Caminho da View
            'title' => 'Usuários por Cargo',
            'user' => $_SESSION['user'],
            'cargoLabels' => $cargoLabels,
            'cargoCounts' => $cargoCounts
        ]);
    }

    /**
     * Gráfico de linha — Novos usuários por mês
     */
public function usuariosPorMes()
{
    $this->authorize(); // se quiser proteger só para admins

    $userModel = new User();
    $dados = $userModel->usuariosPorMes();
    $labels = array_column($dados, 'mes');
    $counts = array_column($dados, 'total');

    return $this->render('Admin/Charts/usuarios-por-mes', [ //Caminho da View
        'title' => 'Novos Usuários por Mês',
        'user' => $_SESSION['user'],
        'labels' => $labels,
        'counts' => $counts
    ]);
}

  /**
     * Gráfico de usuários por Status (Rosca)
     */
public function doughnut_status()
{
    $this->authorize();

    $userModel = new User();
    $statusOptions = ['Ativo', 'Inativo', 'Pendente', 'Banido'];

    $statusLabels = $statusOptions;
    $statusCounts = [];
    foreach ($statusOptions as $status) {
        $statusCounts[] = $userModel->countByStatus($status);
    }

    return $this->render('Admin/Charts/status_doughnut_chart', [ //Caminho da View
        'title' => 'Usuários por Status',
        'user' => $_SESSION['user'],
        'statusLabels' => $statusLabels,
        'statusCounts' => $statusCounts
    ]);
}

    /**
     * Gráfico de usuários por Status (Pizza)
     */
    public function pie_status()
    {
         $this->authorize();

    $userModel = new User();
    $statusOptions = ['Ativo', 'Inativo', 'Pendente', 'Banido'];

    $statusLabels = $statusOptions;
    $statusCounts = [];
    foreach ($statusOptions as $status) {
        $statusCounts[] = $userModel->countByStatus($status);
    }

    return $this->render('Admin/Charts/status_pie_chart', [ //Caminho da View
        'title' => 'Usuários por Status',
        'user' => $_SESSION['user'],
        'statusLabels' => $statusLabels,
        'statusCounts' => $statusCounts
    ]);
    }



    /**
     * Proteção de acesso
     */
    private function authorize()
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['cargo'] !== 'Admin') {
            set_flash('error', 'Acesso negado. Você precisa ser administrador!');
            header('Location: /login');
            exit;
        }
    }
}
