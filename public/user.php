<?php
$target = __DIR__ . '/uploads/tools/teste.txt';
file_put_contents($target, "Testando escrita\n");
echo "Arquivo criado? " . (file_exists($target) ? "Sim" : "Não");



namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\User;

class LoginController extends Controller
{
    public function index()
    {
        $user = $_SESSION['user'] ?? null;
        return $this->render('Auth/login', [
            'title' => 'Login',
            'user'  => $user
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
            // Agora salva todos os campos relevantes, incluindo avatar!
            $_SESSION['user'] = [
                'id' => $userData['id'],
                'nome' => $userData['nome'],
                'email' => $userData['email'],
                'cargo' => $userData['cargo'],
                'status' => $userData['status'],
                'avatar' => $userData['avatar'] // ESSENCIAL!
            ];
            $this->redirect('/admin/dashboard');
        } else {
            return $this->render('Auth/login', [
                'title' => 'Login',
                'error' => 'Usuário ou senha inválidos.',
                'user'  => null
            ]);
        }
    }
    $this->redirect('/login');
}

}
