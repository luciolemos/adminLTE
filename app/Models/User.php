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

        if (!$dsn) {
            throw new Exception('DSN do banco de dados não encontrado.');
        }

        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    /* ================================
     * ======== CRUD BÁSICO ==========
     * ================================ */

    public function getAll(): array
    {
        return $this->pdo
            ->query("SELECT * FROM usuarios ORDER BY nome")
            ->fetchAll();
    }

    public function findById($id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data, array &$errors = []): bool
    {
        require_once __DIR__ . '/../Helpers/validate.php';

        // Validações básicas
        if (!$nome = validate_string($data['nome'] ?? '')) $errors['nome'] = 'Nome obrigatório.';
        if (!$email = validate_email($data['email'] ?? '')) $errors['email'] = 'E-mail inválido.';
        if ($this->findByEmail($email)) $errors['email'] = 'E-mail já cadastrado.';
        if (!$senha = $data['senha'] ?? null) $errors['senha'] = 'Senha obrigatória.';

        $cargo  = validate_enum($data['cargo'] ?? '', ['Admin','Editor','Moderador','Usuário']);
        $status = validate_enum($data['status'] ?? '', ['Ativo','Inativo','Pendente','Banido']);
        if (!$cargo)  $errors['cargo']  = 'Cargo inválido.';
        if (!$status) $errors['status'] = 'Status inválido.';

        // Dados pessoais obrigatórios
        $cpf             = trim($data['cpf'] ?? '');
        $data_nascimento = $data['data_nascimento'] ?? null;
        $cel             = trim($data['cel'] ?? '');
        if (!$cpf)             $errors['cpf'] = 'CPF obrigatório.';
        if (!$data_nascimento) $errors['data_nascimento'] = 'Data de nascimento obrigatória.';
        if (!$cel)             $errors['cel'] = 'Celular obrigatório.';

        if (!empty($errors)) return false;

        $hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("
            INSERT INTO usuarios (
                nome, email, senha, cargo, status, avatar,
                cpf, data_nascimento, cel, logradouro, cep, cidade, uf, data_cadastro
            )
            VALUES (
                :nome, :email, :senha, :cargo, :status, :avatar,
                :cpf, :data_nascimento, :cel, :logradouro, :cep, :cidade, :uf, NOW()
            )
        ");
        return $stmt->execute([
            'nome'            => $nome,
            'email'           => $email,
            'senha'           => $hash,
            'cargo'           => $cargo,
            'status'          => $status,
            'avatar'          => $data['avatar'] ?? null,
            'cpf'             => $cpf,
            'data_nascimento' => $data_nascimento,
            'cel'             => $cel,
            'logradouro'      => trim($data['logradouro'] ?? ''),
            'cep'             => trim($data['cep'] ?? ''),
            'cidade'          => trim($data['cidade'] ?? ''),
            'uf'              => strtoupper(trim($data['uf'] ?? '')),
        ]);
    }

    public function update(int $id, array $data, array &$errors = []): bool
    {
        require_once __DIR__ . '/../Helpers/validate.php';
        $usuario = $this->findById($id);
        if (!$usuario) {
            $errors['user'] = 'Usuário não encontrado.';
            return false;
        }

        // Validações básicas
        if (!$nome = validate_string($data['nome'] ?? '')) $errors['nome'] = 'Nome obrigatório.';
        if (!$email = validate_email($data['email'] ?? '')) $errors['email'] = 'E-mail inválido.';
        $exists = $this->findByEmail($email);
        if ($exists && $exists['id'] != $id) $errors['email'] = 'E-mail já cadastrado.';

        $cargo  = validate_enum($data['cargo'] ?? '', ['Admin','Editor','Moderador','Usuário']);
        $status = validate_enum($data['status'] ?? '', ['Ativo','Inativo','Pendente','Banido']);
        if (!$cargo)  $errors['cargo']  = 'Cargo inválido.';
        if (!$status) $errors['status'] = 'Status inválido.';

        // Dados pessoais obrigatórios
        $cpf             = trim($data['cpf'] ?? '');
        $data_nascimento = $data['data_nascimento'] ?? null;
        $cel             = trim($data['cel'] ?? '');
        if (!$cpf)             $errors['cpf'] = 'CPF obrigatório.';
        if (!$data_nascimento) $errors['data_nascimento'] = 'Data de nascimento obrigatória.';
        if (!$cel)             $errors['cel'] = 'Celular obrigatório.';

        if (!empty($errors)) return false;

        $fields = [
            'nome'            => $nome,
            'email'           => $email,
            'cargo'           => $cargo,
            'status'          => $status,
            'cpf'             => $cpf,
            'data_nascimento' => $data_nascimento,
            'cel'             => $cel,
            'logradouro'      => trim($data['logradouro'] ?? ''),
            'cep'             => trim($data['cep'] ?? ''),
            'cidade'          => trim($data['cidade'] ?? ''),
            'uf'              => strtoupper(trim($data['uf'] ?? '')),
            'id'              => $id,
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
                  uf = :uf";

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
        return $stmt->execute($fields);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    /* ================================
     * =========== KPIs ===============
     * ================================ */

    public function countAll(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE status = :status");
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function countByCargo(string $cargo): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE cargo = :cargo");
        $stmt->execute(['cargo' => $cargo]);
        return (int) $stmt->fetchColumn();
    }

    public function countCreatedLastDays(int $days): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
              FROM usuarios 
             WHERE DATE(data_cadastro) >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
        ");
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function usuariosPorMes(): array
    {
        $stmt = $this->pdo->query("
            SELECT DATE_FORMAT(data_cadastro, '%Y-%m') as mes, COUNT(*) as total
              FROM usuarios
          GROUP BY mes
          ORDER BY mes
             LIMIT 12
        ");
        return $stmt->fetchAll();
    }

    /* ================================
     * ========= FILTROS ==============
     * ================================ */

    public function findByStatus(string $status): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE status = :status ORDER BY nome");
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll();
    }

    public function findByCargo(string $cargo): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE cargo = :cargo ORDER BY nome");
        $stmt->execute(['cargo' => $cargo]);
        return $stmt->fetchAll();
    }

    public function getRecent(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * 
              FROM usuarios 
          ORDER BY data_cadastro DESC 
             LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lista usuários criados nos últimos N dias.
     */
    public function getCreatedLastDays(int $days): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * 
              FROM usuarios 
             WHERE DATE(data_cadastro) >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
          ORDER BY data_cadastro DESC
        ");
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /* ================================
     * ========== EXTRAS ==============
     * ================================ */

    public function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['senha']);
    }

    public function updateProfile(int $id, array $data): bool
    {
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
