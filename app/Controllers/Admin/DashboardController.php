<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    private $statusOptions = ['Ativo', 'Inativo', 'Pendente', 'Banido'];
    private $cargoOptions  = ['Admin', 'Editor', 'Moderador', 'Usuário'];

    public function index()
    {
        $this->authorize();

        $userModel = new User();

        // Contadores por status
        $status_counts = [];
        foreach ($this->statusOptions as $status) {
            $status_counts[$status] = $userModel->countByStatus($status);
        }

        // Contadores por cargo
        $cargo_counts = [];
        foreach ($this->cargoOptions as $cargo) {
            $cargo_counts[$cargo] = $userModel->countByCargo($cargo);
        }

        $total_usuarios = $userModel->countAll();

            // MONTE O BREADCRUMB AQUI
    $breadcrumb = [
        ['title' => 'Dashboard', 'url' => null], // dashboard não tem link, pois já está nela
        // Se for um detalhe de usuário, adicione outros itens
        // ['title' => 'Usuários', 'url' => '/admin/users'],
        // ['title' => 'Detalhes do usuário', 'url' => null],
    ];
        

        return $this->render('Admin/dashboard', [
            'title'          => 'Dashboard',
            'user'           => $_SESSION['user'],
            'total_usuarios' => $total_usuarios,
            'status_options' => $this->statusOptions,
            'status_counts'  => $status_counts,
            'cargo_options'  => $this->cargoOptions,
            'cargo_counts'   => $cargo_counts,
            'success'        => get_flash('success'),
            'error'          => get_flash('error'),
             'breadcrumb' => $breadcrumb,
            
        ]);
    }

    /**
     * Garante que apenas admins possam acessar o dashboard
     */
    private function authorize()
{
    if (empty($_SESSION['user'])) {
        set_flash('error', 'Você precisa estar logado!');
        header('Location: /login');
        exit;
    }
    if ($_SESSION['user']['cargo'] !== 'Admin') {
        set_flash('error', 'Acesso negado. Você precisa ser administrador!');
        header('Location: /');
        exit;
    }
}

}
