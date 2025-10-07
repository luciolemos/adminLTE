<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;

/**
 * Controller responsável pelo gerenciamento (CRUD) de usuários no painel admin.
 */
class UserController extends Controller
{
    private $statusOptions = ['Ativo', 'Inativo', 'Pendente', 'Banido'];
    private $cargoOptions  = ['Admin', 'Editor', 'Moderador', 'Usuário'];

    /**
     * Dashboard de Usuários (cards/kpis).
     * GET /admin/users/dashboard
     */
    public function dashboard()
    {
        $this->requirePermission('user_view');

        $userModel = new User();

        // Totais gerais
        $total_usuarios = $userModel->countAll();

        // Totais por status
        $status_counts = [];
        foreach ($this->statusOptions as $s) {
            $status_counts[$s] = $userModel->countByStatus($s);
        }

        // Totais por cargo
        $cargo_counts = [];
        foreach ($this->cargoOptions as $c) {
            $cargo_counts[$c] = $userModel->countByCargo($c);
        }

        // KPIs extras
        $recent_30_days_count = $userModel->countCreatedLastDays(30);
        $recent_users         = $userModel->getRecent(5);

        $breadcrumb = [
            ['title' => 'Dashboard de usuários', 'url' => '/admin/users/dashboard'],
            ['title' => 'Usuários', 'url' => '/admin/users'],
            ['title' => 'Visão geral', 'url' => null],
        ];

        return $this->render('Admin/Users/dashboard', [
            'title'                => 'Dashboard de Usuários',
            'user'                 => $_SESSION['user'],
            'breadcrumb'           => $breadcrumb,
            'total_usuarios'       => $total_usuarios,
            'status_options'       => $this->statusOptions,
            'status_counts'        => $status_counts,
            'cargo_options'        => $this->cargoOptions,
            'cargo_counts'         => $cargo_counts,
            'recent_30_days_count' => $recent_30_days_count,
            'recent_users'         => $recent_users,
        ]);
    }

    /**
     * Lista de usuários com filtros opcionais (?status=Ativo&cargo=Admin&recent_days=30).
     */
    public function index()
    {
        $this->requirePermission('user_view');

        $userModel = new User();

        $status = $_GET['status'] ?? null;
        $cargo  = $_GET['cargo']  ?? null;
        $recent = $_GET['recent_days'] ?? null;

        if ($status && in_array($status, $this->statusOptions, true)) {
            $usuarios = $userModel->findByStatus($status);
        } elseif ($cargo && in_array($cargo, $this->cargoOptions, true)) {
            $usuarios = $userModel->findByCargo($cargo);
        } elseif ($recent) {
            // precisa do método getCreatedLastDays no model
            $usuarios = $userModel->getCreatedLastDays((int) $recent);
        } else {
            $usuarios = $userModel->getAll();
        }

        $breadcrumb = [
            ['title' => 'Dashboard de usuários', 'url' => '/admin/users/dashboard'],
            ['title' => 'Usuários',  'url' => null],
        ];

        return $this->render('Admin/Users/index', [
            'breadcrumb' => $breadcrumb,
            'title'      => 'Usuários',
            'usuarios'   => $usuarios,
            'user'       => $_SESSION['user'],
            'success'    => get_flash('success'),
            'error'      => get_flash('error'),
        ]);
    }

    /**
     * Criação de usuário.
     */
    public function create()
    {
        $this->requirePermission('user_create');
        require_once __DIR__ . '/../../Helpers/csrf.php';
        require_once __DIR__ . '/../../Helpers/validate.php';

        $errors  = [];
        $usuario = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate($_POST['csrf_token'] ?? '')) {
                $errors['csrf'] = 'Token CSRF inválido. Recarregue a página.';
            }

            $data = $_POST;
            $avatar = validate_avatar_upload($_FILES['avatar'] ?? []);
            if ($avatar) {
                $data['avatar'] = $avatar;
            }

            $userModel = new User();
            if ($userModel->create($data, $errors)) {
                set_flash('success', 'Usuário criado com sucesso!');
                $this->redirect('/admin/users');
            }
            $usuario = $data;
        }

        $breadcrumb = [
            ['title' => 'Dashboard de usuários', 'url' => '/admin/users/dashboard'],
            ['title' => 'Usuários',  'url' => '/admin/users'],
            ['title' => 'Novo usuário', 'url' => null],
        ];

        return $this->render('Admin/Users/create', [
            'breadcrumb' => $breadcrumb,
            'title'      => 'Novo usuário',
            'user'       => $_SESSION['user'],
            'usuario'    => $usuario,
            'errors'     => $errors,
            'csrf_token' => csrf_token(),
        ]);
    }

    /**
     * Edição de usuário.
     */
    public function edit($id)
    {
        $this->requirePermission('user_edit');
        require_once __DIR__ . '/../../Helpers/csrf.php';
        require_once __DIR__ . '/../../Helpers/validate.php';

        $errors    = [];
        $userModel = new User();
        $usuario   = $userModel->findById($id);

        if (!$usuario) {
            set_flash('error', 'Usuário não encontrado!');
            $this->redirect('/admin/users');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate($_POST['csrf_token'] ?? '')) {
                $errors['csrf'] = 'Token CSRF inválido. Recarregue a página.';
            }

            $data = $_POST;
            $avatar = validate_avatar_upload($_FILES['avatar'] ?? []);
            if ($avatar) {
                $data['avatar'] = $avatar;
            }

            if ($userModel->update($id, $data, $errors)) {
                set_flash('success', 'Usuário atualizado com sucesso!');

                // Se o usuário logado foi alterado, atualiza a sessão
                if ($_SESSION['user']['id'] == $id) {
                    $_SESSION['user'] = $userModel->findById($id);
                }

                $this->redirect("/admin/users/show/{$id}");
            }
            $usuario = array_merge($usuario, $data);
        }

        $breadcrumb = [
            ['title' => 'Dashboard de usuários', 'url' => '/admin/users/dashboard'],
            ['title' => 'Usuários',  'url' => '/admin/users'],
            ['title' => 'Editar usuário', 'url' => null],
        ];

        return $this->render('Admin/Users/edit', [
            'breadcrumb' => $breadcrumb,
            'title'      => 'Editar usuário',
            'usuario'    => $usuario,
            'user'       => $_SESSION['user'],
            'errors'     => $errors,
            'csrf_token' => csrf_token(),
        ]);
    }

    /**
     * Exclui usuário.
     */
    public function delete($id)
    {
        $this->requirePermission('user_delete');

        $userModel = new User();
        $usuario   = $userModel->findById($id);

        if (!$usuario) {
            set_flash('error', 'Usuário não encontrado!');
        } else {
            $userModel->delete($id);
            set_flash('success', 'Usuário excluído com sucesso!');
        }

        $this->redirect('/admin/users');
    }

    /**
     * Exibe detalhes de um usuário.
     */
    public function show($id)
    {
        $this->requirePermission('user_view');

        $userModel = new User();
        $usuario   = $userModel->findById($id);

        if (!$usuario) {
            set_flash('error', 'Usuário não encontrado!');
            $this->redirect('/admin/users');
        }

        $breadcrumb = [
            ['title' => 'Dashboard de usuários', 'url' => '/admin/users/dashboard'],
            ['title' => 'Usuários',  'url' => '/admin/users'],
            ['title' => 'Detalhes do usuário', 'url' => null],
        ];

        return $this->render('Admin/Users/show', [
            'breadcrumb' => $breadcrumb,
            'title'      => 'Detalhes do usuário',
            'usuario'    => $usuario,
            'user'       => $_SESSION['user'],
            'success'    => get_flash('success'),
            'error'      => get_flash('error'),
        ]);
    }

    /**
     * Protege operações checando permissão granular.
     */
    private function requirePermission($permission)
    {
        require_once __DIR__ . '/../../Helpers/permissions.php';
        if (!user_has_permission($permission)) {
            set_flash('error', 'Acesso negado. Permissão insuficiente!');
            header('Location: /admin/dashboard');
            exit;
        }
    }
}
