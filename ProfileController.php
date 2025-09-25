<?php
namespace App\Controllers\Site;

use App\Core\Controller;

class ProfileController extends Controller
{
    public function index()
    {
        require_once __DIR__ . '/../../Helpers/flash.php';
        require_once __DIR__ . '/../../Models/User.php';

        if (empty($_SESSION['user'])) {
            set_flash('error', 'Você precisa estar logado para acessar o perfil.');
            header('Location: /login');
            exit;
        }

        // Busca dados atualizados do banco
        $userModel = new \App\Models\User();
        $user = $userModel->findById($_SESSION['user']['id']);

        return $this->render('Site/profile', [
            'title'         => 'Meu Perfil',
            'user'          => $user,
            'flash_success' => get_flash('success'),
            'flash_error'   => get_flash('error')
        ]);
    }

    public function edit()
{
    require_once __DIR__ . '/../../Helpers/flash.php';
    require_once __DIR__ . '/../../Helpers/validate.php';
    require_once __DIR__ . '/../../Models/User.php';

    if (empty($_SESSION['user'])) {
        set_flash('error', 'Você precisa estar logado para editar seu perfil.');
        header('Location: /login');
        exit;
    }

    $userModel = new \App\Models\User();
    $user = $userModel->findById($_SESSION['user']['id']);
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // PEGUE TODOS OS CAMPOS DO FORMULÁRIO
        $nome    = validate_string($_POST['nome'] ?? '', 3, 80);
        $cel     = validate_string($_POST['cel'] ?? '', 8, 20);
        $email   = validate_email($_POST['email'] ?? '');

        $cpf            = trim($_POST['cpf'] ?? '');
        $data_nascimento= $_POST['data_nascimento'] ?? null;
        $logradouro     = trim($_POST['logradouro'] ?? '');
        $cep            = trim($_POST['cep'] ?? '');
        $cidade         = trim($_POST['cidade'] ?? '');
        $uf             = strtoupper(trim($_POST['uf'] ?? ''));

        if (!$nome) $errors['nome'] = 'Nome obrigatório (mín. 3 letras).';
        if (!$cel) $errors['cel'] = 'Celular obrigatório.';
        if (!$email) $errors['email'] = 'Email inválido.';
        if (!$cpf) $errors['cpf'] = 'CPF obrigatório.';
        // Você pode adicionar mais validações...

        // Avatar upload (opcional)
        if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $avatar = validate_avatar_upload($_FILES['avatar']);
            if ($avatar) {
                $user['avatar'] = $avatar;
            } else {
                $errors['avatar'] = 'Imagem inválida ou falha no upload.';
            }
        }

        if (empty($errors)) {
            // Atualiza dados no banco - AGORA PASSANDO TODOS OS CAMPOS
            $ok = $userModel->updateProfile($user['id'], [
                'nome'            => $nome,
                'cel'             => $cel,
                'email'           => $email,
                'cpf'             => $cpf,
                'data_nascimento' => $data_nascimento,
                'logradouro'      => $logradouro,
                'cep'             => $cep,
                'cidade'          => $cidade,
                'uf'              => $uf,
                'avatar'          => $user['avatar'] ?? null,
            ]);
            if ($ok) {
                $_SESSION['user'] = $userModel->findById($user['id']);
                set_flash('success', 'Perfil atualizado com sucesso!');
                header('Location: /profile');
                exit;
            } else {
                $errors['geral'] = 'Erro ao atualizar perfil.';
            }
        }
    }

    return $this->render('Site/profile_edit', [
        'title'         => 'Editar Perfil',
        'user'          => $user,
        'errors'        => $errors,
        'flash_success' => get_flash('success'),
        'flash_error'   => get_flash('error')
    ]);
}

}
