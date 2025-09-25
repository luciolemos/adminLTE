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

    public function index()
    {
        $this->requirePermission('user_view');

        $status    = $_GET['status'] ?? null;
        $cargo     = $_GET['cargo']  ?? null;
        $userModel = new User();

        if ($status && in_array($status, $this->statusOptions)) {
            $usuarios = $userModel->findByStatus($status);
        } elseif ($cargo && in_array($cargo, $this->cargoOptions)) {
            $usuarios = $userModel->findByCargo($cargo);
        } else {
            $usuarios = $userModel->getAll();
        }

        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => '/admin/dashboard'],
            ['title' => 'Usuários cadastrados', 'url' => null],
        ];

        return $this->render('Admin/Users/index', [
            'breadcrumb' => $breadcrumb,
            'title'      => 'Usuários',
            'usuarios'   => $usuarios,
            'user'       => $_SESSION['user'],
            'success'    => get_flash('success'),
            'error'      => get_flash('error')
        ]);
    }

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
            if ($avatar) $data['avatar'] = $avatar;

            $userModel = new User();
            if ($userModel->create($data, $errors)) {
                set_flash('success', 'Usuário criado com sucesso!');
                $this->redirect('/admin/users');
            }
            $usuario = $data;
        }

        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => '/admin/dashboard'],
            ['title' => 'Usuários', 'url' => '/admin/users'],
            ['title' => 'Novo usuário', 'url' => null],
        ];

        return $this->render('Admin/Users/create', [
            'breadcrumb' => $breadcrumb,
            'title'      => 'Novo usuário',
            'user'       => $_SESSION['user'],
            'usuario'    => $usuario,
            'errors'     => $errors,
            'csrf_token' => csrf_token()
        ]);
    }

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

            // Pegue o parâmetro return_to da URL (list ou show)
            $return_to = $_POST['return_to'] ?? ($_GET['return_to'] ?? 'show');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate($_POST['csrf_token'] ?? '')) {
                $errors['csrf'] = 'Token CSRF inválido. Recarregue a página.';
            }

            $data = $_POST;
            $avatar = validate_avatar_upload($_FILES['avatar'] ?? []);
            if ($avatar) $data['avatar'] = $avatar;

            if ($userModel->update($id, $data, $errors)) {
                set_flash('success', 'Usuário atualizado com sucesso!');
                if ($_SESSION['user']['id'] == $id) {
                    $_SESSION['user'] = $userModel->findById($id);
                }
               // Redirecionamento flexível
                if ($return_to === 'list') {
                $this->redirect('/admin/users');
                } else {
                $this->redirect("/admin/users/show/{$id}");
                }
            }
            $usuario = array_merge($usuario, $data);
        }

        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => '/admin/dashboard'],
            ['title' => 'Usuários', 'url' => '/admin/users'],
            ['title' => 'Edição do usuário', 'url' => null],
        ];

        return $this->render('Admin/Users/edit', [
            'breadcrumb' => $breadcrumb,
            'title'      => 'Atualizar usuário',
            'usuario'    => $usuario,
            'user'       => $_SESSION['user'],
            'errors'     => $errors,
            'csrf_token' => csrf_token(),
            'return_to'  => $return_to
        ]);
    }

    public function delete($id)
    {
        $this->requirePermission('user_delete');

        $userModel = new User();
        $usuario   = $userModel->findById($id);

        if (!$usuario) {
            set_flash('error', 'Usuário não encontrado!');
            $this->redirect('/admin/users');
        }

        $userModel->delete($id);
        set_flash('success', 'Usuário excluído com sucesso!');
        $this->redirect('/admin/users');
    }

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
            ['title' => 'Dashboard', 'url' => '/admin/dashboard'],
            ['title' => 'Usuários', 'url' => '/admin/users'],
            ['title' => 'Detalhes do usuário', 'url' => null],
        ];

        return $this->render('Admin/Users/show', [
            'breadcrumb' => $breadcrumb,
            'title'      => 'Visualizar usuário',
            'usuario'    => $usuario,
            'user'       => $_SESSION['user'],
            'success'    => get_flash('success'),  // <<< ADICIONE ESTA LINHA
            'error'      => get_flash('error')     // <<< E ESTA, se quiser erros também
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
