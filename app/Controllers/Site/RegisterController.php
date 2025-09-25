<?php
namespace App\Controllers\Site;

use App\Core\Controller;
use App\Models\User;

class RegisterController extends Controller
{
    public function index()
    {
        require_once __DIR__ . '/../../Helpers/csrf.php';

        // Lê e limpa flash se existir
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return $this->render('Site/register', [
            'title'      => 'Registrar-se',
            'errors'     => [],
            'usuario'    => [],
            'success'    => null,
            'flash'      => $flash,
            'csrf_token' => csrf_token(),
             'breadcrumb' => [
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'Registro', 'url' => null]
        ]
        ]);
    }

    public function register()
    {
        require_once __DIR__ . '/../../Helpers/csrf.php';
        require_once __DIR__ . '/../../Helpers/validate.php';

        $errors  = [];
        $usuario = $_POST;

        // CSRF check
        if (!csrf_validate($_POST['csrf_token'] ?? '')) {
            $errors['csrf'] = 'Token CSRF inválido. Recarregue a página!';
        }

        // Senha e confirmação
        $senha  = $_POST['senha']  ?? '';
        $senha2 = $_POST['senha2'] ?? '';
        if (!$senha) {
            $errors['senha'] = 'Senha obrigatória.';
        } elseif ($senha !== $senha2) {
            $errors['senha2'] = 'As senhas não coincidem.';
        }

        // Validação dos campos obrigatórios
        $data = [];
        $data['nome'] = validate_string($_POST['nome'] ?? '', 3, 80);
        if (!$data['nome']) $errors['nome'] = 'Nome obrigatório (mínimo 3 letras).';

        $data['email'] = validate_email($_POST['email'] ?? '');
        if (!$data['email']) $errors['email'] = 'E-mail inválido.';

        $data['cpf'] = function_exists('validate_cpf') ? validate_cpf($_POST['cpf'] ?? '') : validate_string($_POST['cpf'] ?? '', 11, 14);
        if (!$data['cpf']) $errors['cpf'] = 'CPF inválido.';

        $data['data_nascimento'] = $_POST['data_nascimento'] ?? null;
        if (!$data['data_nascimento']) $errors['data_nascimento'] = 'Data de nascimento obrigatória.';

        $data['cel'] = validate_string($_POST['cel'] ?? '', 8, 20);
        if (!$data['cel']) $errors['cel'] = 'Celular obrigatório.';

        $data['logradouro'] = trim($_POST['logradouro'] ?? '');
        $data['cidade']     = trim($_POST['cidade'] ?? '');
        $data['uf']         = trim($_POST['uf'] ?? '');
        $data['cep']        = trim($_POST['cep'] ?? '');
        if (!empty($data['cep']) && !preg_match('/^\d{5}-\d{3}$/', $data['cep'])) {
            $errors['cep'] = 'CEP inválido.';
        }

        // Só salva senha se validada
        $data['senha']  = $senha;

        // Set cargo/status fixos
        $data['cargo']  = 'Usuário';
        $data['status'] = 'Pendente';

        // Avatar upload
        if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $avatar = validate_avatar_upload($_FILES['avatar']);
            if ($avatar) {
                $data['avatar'] = $avatar;
            } else {
                $errors['avatar'] = 'Imagem inválida ou falha no upload.';
            }
        } else {
            $data['avatar'] = null;
        }

        // Se não houve erros, cria usuário e redireciona para GET com flash
        $userModel = new User();
        if (empty($errors) && $userModel->create($data, $errors)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg'  => 'Registro do usuário realizado com sucesso! Aguarde aprovação do administrador.'
            ];
            // Redireciona para evitar POST duplo
            header("Location: /register");
            exit;
        } else {
            return $this->render('Site/register', [
                'title'      => 'Registrar-se',
                'errors'     => $errors,
                'usuario'    => $usuario,
                'success'    => null,
                'flash'      => null,
                'csrf_token' => csrf_token(),
                'breadcrumb' => [
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'Registro', 'url' => null]
        ]
            ]);
        }
    }
}
