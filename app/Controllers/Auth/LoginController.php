<?php
namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\User;

class LoginController extends Controller
{
    public function index()
    {
        $user = $_SESSION['user'] ?? null;
        // Busca e limpa flash de erro se houver
        $error = get_flash('error');
        $success = get_flash('success');
        return $this->render('Auth/login', [
            'title'   => 'Login',
            'user'    => $user,
            'error'   => $error,
            'success' => $success,
            'breadcrumb' => [
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'Login', 'url' => null]
        ]
        ]);
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';

            $userModel = new User();
            $userData = $userModel->findByEmail($email);

            if ($userData && $userModel->verifyPassword($userData, $senha)) {
                if ($userData['status'] !== 'Ativo') {
                    set_flash('error', 'Sua conta está inativa, pendente ou banida. Aguarde aprovação do administrador.');
                    $this->redirect('/login');
                    exit;
                }

                unset($_SESSION['_permissoes']);
                $_SESSION['user'] = [
                    'id'     => $userData['id'],
                    'nome'   => $userData['nome'],
                    'cel'    => $userData['cel'],
                    'email'  => $userData['email'],
                    'cargo'  => $userData['cargo'],
                    'status' => $userData['status'],
                    'avatar' => $userData['avatar']
                ];

                // Direciona de acordo com o cargo
                if ($userData['cargo'] === 'Admin') {
                    set_flash('success', 'Bem-vindo, administrador!');
                  //$this->redirect('/admin/dashboard');
                    $this->redirect('/');
                } else {
                    set_flash('success', 'Login realizado com sucesso!');
                    $this->redirect('/'); // Ou para / se preferir
                    //$this->redirect('/user/home');
                }
                exit;
            } else {
                set_flash('error', 'Usuário ou senha inválidos.');
                $this->redirect('/login');
            }
        }
        $this->redirect('/login');
    }
}
