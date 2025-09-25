<?php
namespace App\Models;

use PDO;
use Exception;

class User
{
    protected $pdo;

    public function __construct()
    {
        $dsn  = $_ENV['DB_DSN'] ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';
        if (!$dsn) throw new Exception('DSN do banco de dados não encontrado.');
        $this->pdo = new PDO($dsn, $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAll()
    {
        return $this->pdo->query("SELECT * FROM usuarios ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByStatus($status)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE status = :status ORDER BY nome");
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByCargo($cargo)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE cargo = :cargo ORDER BY nome");
        $stmt->execute(['cargo' => $cargo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll()
    {
        return $this->pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    }

    public function countByStatus($status)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE status = :status");
        $stmt->execute(['status' => $status]);
        return $stmt->fetchColumn();
    }

    public function countByCargo($cargo)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE cargo = :cargo");
        $stmt->execute(['cargo' => $cargo]);
        return $stmt->fetchColumn();
    }

    public function verifyPassword($user, $password)
    {
        return password_verify($password, $user['senha']);
    }

    public function create($data, &$errors = [])
    {
        require_once __DIR__ . '/../Helpers/validate.php';

        if (!$nome = validate_string($data['nome'] ?? '')) $errors['nome'] = 'Nome obrigatório.';
        if (!$email = validate_email($data['email'] ?? '')) $errors['email'] = 'E-mail inválido.';
        if ($this->findByEmail($email)) $errors['email'] = 'E-mail já cadastrado.';
        if (!$senha = $data['senha'] ?? null) $errors['senha'] = 'Senha obrigatória.';

        $cargo = validate_enum($data['cargo'] ?? '', ['Admin','Editor','Moderador','Usuário']);
        $status = validate_enum($data['status'] ?? '', ['Ativo','Inativo','Pendente','Banido']);
        if (!$cargo) $errors['cargo'] = 'Cargo inválido.';
        if (!$status) $errors['status'] = 'Status inválido.';

        // Novos campos pessoais e endereço
        $cpf            = trim($data['cpf'] ?? '');
        $data_nascimento= $data['data_nascimento'] ?? null;
        $cel            = trim($data['cel'] ?? '');
        $logradouro     = trim($data['logradouro'] ?? '');
        $cep            = trim($data['cep'] ?? '');
        $cidade         = trim($data['cidade'] ?? '');
        $uf             = strtoupper(trim($data['uf'] ?? ''));

        // Validações mínimas para campos obrigatórios
        if (!$cpf) $errors['cpf'] = 'CPF obrigatório.';
        if (!$data_nascimento) $errors['data_nascimento'] = 'Data de nascimento obrigatória.';
        if (!$cel) $errors['cel'] = 'Celular obrigatório.';

        if (!empty($errors)) return false;

        $hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("
            INSERT INTO usuarios (
                nome, email, senha, cargo, status, avatar,
                cpf, data_nascimento, cel, logradouro, cep, cidade, uf
            )
            VALUES (
                :nome, :email, :senha, :cargo, :status, :avatar,
                :cpf, :data_nascimento, :cel, :logradouro, :cep, :cidade, :uf
            )
        ");
        $stmt->execute([
            'nome'   => $nome,
            'email'  => $email,
            'senha'  => $hash,
            'cargo'  => $cargo,
            'status' => $status,
            'avatar' => $data['avatar'] ?? null,
            'cpf'    => $cpf,
            'data_nascimento' => $data_nascimento,
            'cel'    => $cel,
            'logradouro' => $logradouro,
            'cep'    => $cep,
            'cidade' => $cidade,
            'uf'     => $uf,
        ]);
        return true;
    }

    public function update($id, $data, &$errors = [])
    {
        require_once __DIR__ . '/../Helpers/validate.php';
        $usuario = $this->findById($id);
        if (!$usuario) { $errors['user'] = 'Usuário não encontrado.'; return false; }

        if (!$nome = validate_string($data['nome'] ?? '')) $errors['nome'] = 'Nome obrigatório.';
        if (!$email = validate_email($data['email'] ?? '')) $errors['email'] = 'E-mail inválido.';
        $exists = $this->findByEmail($email);
        if ($exists && $exists['id'] != $id) $errors['email'] = 'E-mail já cadastrado.';

        $cargo = validate_enum($data['cargo'] ?? '', ['Admin','Editor','Moderador','Usuário']);
        $status = validate_enum($data['status'] ?? '', ['Ativo','Inativo','Pendente','Banido']);
        if (!$cargo) $errors['cargo'] = 'Cargo inválido.';
        if (!$status) $errors['status'] = 'Status inválido.';

        // Novos campos pessoais e endereço
        $cpf            = trim($data['cpf'] ?? '');
        $data_nascimento= $data['data_nascimento'] ?? null;
        $cel            = trim($data['cel'] ?? '');
        $logradouro     = trim($data['logradouro'] ?? '');
        $cep            = trim($data['cep'] ?? '');
        $cidade         = trim($data['cidade'] ?? '');
        $uf             = strtoupper(trim($data['uf'] ?? ''));

        // Validação mínima
        if (!$cpf) $errors['cpf'] = 'CPF obrigatório.';
        if (!$data_nascimento) $errors['data_nascimento'] = 'Data de nascimento obrigatória.';
        if (!$cel) $errors['cel'] = 'Celular obrigatório.';

        if (!empty($errors)) return false;

        $fields = [
            'nome'   => $nome,
            'email'  => $email,
            'cargo'  => $cargo,
            'status' => $status,
            'cpf'    => $cpf,
            'data_nascimento' => $data_nascimento,
            'cel'    => $cel,
            'logradouro' => $logradouro,
            'cep'    => $cep,
            'cidade' => $cidade,
            'uf'     => $uf,
            'id'     => $id,
        ];

        $sql = "UPDATE usuarios SET
            nome = :nome,
            email = :email,
            cargo = :cargo,
            status = :status,
            cpf = :cpf,
            data_nascimento = :data_nascimento,
            cel = :cel,
            logradouro = :logradouro,
            cep = :cep,
            cidade = :cidade,
            uf = :uf
        ";

        if (!empty($data['senha'])) {
            $sql .= ", senha = :senha";
            $fields['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }
        if (!empty($data['avatar'])) {
            $sql .= ", avatar = :avatar";
            $fields['avatar'] = $data['avatar'];
        }
        $sql .= " WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($fields);
        return true;
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function usuariosPorMes()
    {
        $stmt = $this->pdo->query("
            SELECT DATE_FORMAT(data_cadastro, '%Y-%m') as mes, COUNT(*) as total
            FROM usuarios
            GROUP BY mes
            ORDER BY mes
            LIMIT 12
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $data) {
    $fields = [];
    $params = [];
    foreach ($data as $key => $value) {
        $fields[] = "$key = ?";
        $params[] = $value;
    }
    $params[] = $id;
    $sql = "UPDATE usuarios SET " . implode(',', $fields) . " WHERE id = ?";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute($params);
}

}
