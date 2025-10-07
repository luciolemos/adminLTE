<?php
namespace App\Models;

use PDO;
use Exception;
use DateTime;

class Tool
{
    /** @var PDO */
    protected $pdo;

    public function __construct()
    {
        $dsn  = $_ENV['DB_DSN']  ?? '';
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

    /* ============================
     * ====== CRUD BÁSICO ========
     * ============================ */

    /** Lista todas as ferramentas. */
    public function getAll(): array
    {
        $sql = "SELECT * 
                  FROM tools 
              ORDER BY COALESCE(created_at, '1970-01-01') DESC, id DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    /** Busca ferramenta por ID. */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tools WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Cria nova ferramenta. */
    public function create(array $data, array &$errors = []): bool
    {
        require_once __DIR__ . '/../Helpers/validate.php';

        $desc_tool           = validate_string($data['desc_tool'] ?? '', 3, 255);
        $caracteristica_tool = validate_string($data['caracteristica_tool'] ?? '', 0, 5000);
        $cat_tool            = validate_tool_categoria($data['cat_tool'] ?? '');
        $type_tool           = validate_tool_estado($data['type_tool'] ?? '');
        $data_aquisicao      = validate_date($data['data_aquisicao'] ?? '');
        $img_tool            = $data['img_tool'] ?? '/assets/adminlte/dist/img/default_tool.png';

        if (!$desc_tool) $errors['desc_tool'] = 'Descrição obrigatória.';
        if (!$cat_tool)  $errors['cat_tool']  = 'Selecione categoria válida.';
        if (!$type_tool) $errors['type_tool'] = 'Selecione estado válido.';

        if (!empty($errors)) return false;

        $stmt = $this->pdo->prepare("
            INSERT INTO tools (
                img_tool, desc_tool, caracteristica_tool,
                cat_tool, type_tool, data_aquisicao,
                created_at, updated_at
            ) VALUES (
                :img_tool, :desc_tool, :caracteristica_tool,
                :cat_tool, :type_tool, :data_aquisicao,
                NOW(), NOW()
            )
        ");

        return $stmt->execute([
            'img_tool'            => $img_tool,
            'desc_tool'           => $desc_tool,
            'caracteristica_tool' => $caracteristica_tool,
            'cat_tool'            => $cat_tool,
            'type_tool'           => $type_tool,
            'data_aquisicao'      => $data_aquisicao ?: null,
        ]);
    }

    /** Atualiza ferramenta existente. */
    public function update(int $id, array $data, array &$errors = []): bool
    {
        require_once __DIR__ . '/../Helpers/validate.php';

        $desc_tool           = validate_string($data['desc_tool'] ?? '', 3, 255);
        $caracteristica_tool = validate_string($data['caracteristica_tool'] ?? '', 0, 5000);
        $cat_tool            = validate_tool_categoria($data['cat_tool'] ?? '');
        $type_tool           = validate_tool_estado($data['type_tool'] ?? '');
        $data_aquisicao      = validate_date($data['data_aquisicao'] ?? '');
        $img_tool            = $data['img_tool'] ?? '/assets/adminlte/dist/img/default_tool.png';

        if (!$desc_tool) $errors['desc_tool'] = 'Descrição obrigatória.';
        if (!$cat_tool)  $errors['cat_tool']  = 'Selecione categoria válida.';
        if (!$type_tool) $errors['type_tool'] = 'Selecione estado válido.';

        if (!empty($errors)) return false;

        $stmt = $this->pdo->prepare("
            UPDATE tools
               SET img_tool = :img_tool,
                   desc_tool = :desc_tool,
                   caracteristica_tool = :caracteristica_tool,
                   cat_tool = :cat_tool,
                   type_tool = :type_tool,
                   data_aquisicao = :data_aquisicao,
                   updated_at = NOW()
             WHERE id = :id
        ");

        return $stmt->execute([
            'img_tool'            => $img_tool,
            'desc_tool'           => $desc_tool,
            'caracteristica_tool' => $caracteristica_tool,
            'cat_tool'            => $cat_tool,
            'type_tool'           => $type_tool,
            'data_aquisicao'      => $data_aquisicao ?: null,
            'id'                  => $id,
        ]);
    }

    /** Remove ferramenta. */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM tools WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    /* ============================
     * ========= KPIs =============
     * ============================ */

    /** Total geral de ferramentas. */
    public function countAll(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM tools")->fetchColumn();
    }

    /** Total por tipo (Nova, Usada etc.). */
    public function countByType(string $type): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tools WHERE type_tool = :t");
        $stmt->execute(['t' => $type]);
        return (int) $stmt->fetchColumn();
    }

    /** Total por categoria. */
    public function countByCategory(string $category): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tools WHERE cat_tool = :c");
        $stmt->execute(['c' => $category]);
        return (int) $stmt->fetchColumn();
    }

    /** Total adquiridas nos últimos N dias. */
    public function countAcquiredLastDays(int $days, string $column = 'data_aquisicao'): int
    {
        $allowed = ['data_aquisicao', 'created_at', 'updated_at'];
        if (!in_array($column, $allowed, true)) {
            $column = 'data_aquisicao';
        }

        $dateLimit = (new DateTime("-{$days} days"))->format('Y-m-d');

        $sql = "SELECT COUNT(*) 
                  FROM tools 
                 WHERE {$column} IS NOT NULL 
                   AND {$column} >= :date";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['date' => $dateLimit]);
        return (int) $stmt->fetchColumn();
    }

    /* ============================
     * ======== FILTROS ===========
     * ============================ */

    /** Lista por tipo. */
    public function getByType(string $type): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM tools
             WHERE type_tool = :type
          ORDER BY COALESCE(created_at, '1970-01-01') DESC, id DESC
        ");
        $stmt->execute(['type' => $type]);
        return $stmt->fetchAll();
    }

    /** Lista por categoria. */
    public function getByCategory(string $category): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM tools
             WHERE cat_tool = :cat
          ORDER BY COALESCE(created_at, '1970-01-01') DESC, id DESC
        ");
        $stmt->execute(['cat' => $category]);
        return $stmt->fetchAll();
    }

    /** Lista adquiridas nos últimos N dias. */
    public function getAcquiredLastDays(int $days, string $column = 'data_aquisicao'): array
    {
        $allowed = ['data_aquisicao', 'created_at', 'updated_at'];
        if (!in_array($column, $allowed, true)) {
            $column = 'data_aquisicao';
        }

        $dateLimit = (new DateTime("-{$days} days"))->format('Y-m-d');

        $sql = "SELECT *
                  FROM tools
                 WHERE {$column} IS NOT NULL
                   AND {$column} >= :date
              ORDER BY {$column} DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['date' => $dateLimit]);
        return $stmt->fetchAll();
    }

    /** Lista últimas adquiridas. */
    public function getRecentAcquired(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM tools
          ORDER BY data_aquisicao DESC 
             LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
