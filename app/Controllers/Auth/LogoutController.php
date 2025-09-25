<?php
namespace App\Controllers\Auth;

use App\Core\Controller;

class LogoutController extends Controller
{
    public function index()
    {
        // Inicia a sessão só se necessário (opcional: depende do seu Core\Controller)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Limpa os dados da sessão
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        // Mensagem flash para a próxima requisição
        if (!function_exists('set_flash')) {
            function set_flash($type, $msg) {
                if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
            }
        }
        set_flash('success', 'Logout realizado com sucesso!');

        // Redireciona para a home
        header('Location: /');
        exit;
    }
}
