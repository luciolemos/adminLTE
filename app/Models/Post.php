<?php
namespace App\Models;

use PDO;
use Exception;

class Post
{
    protected PDO $pdo;

    public function __construct()
    {
        $dsn  = $_ENV['DB_DSN'] ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';

        if (!$dsn) throw new Exception('DSN do banco de dados não encontrado.');

        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    /* ============================
     * ====== CRUD BÁSICO ========
     * ============================ */

    public function getAllPublic(): array
    {
        return $this->pdo->query("
            SELECT p.*, u.nome AS autor_nome, u.avatar AS autor_avatar
              FROM posts p
              JOIN usuarios u ON p.autor_id = u.id
             WHERE p.status = 'publicado'
          ORDER BY p.criado_em DESC
        ")->fetchAll();
    }

    public function getAllAdmin(): array
    {
        return $this->pdo->query("
            SELECT p.*, u.nome AS autor_nome, u.avatar AS autor_avatar
              FROM posts p
              JOIN usuarios u ON p.autor_id = u.id
          ORDER BY p.criado_em DESC
        ")->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.nome AS autor_nome, u.avatar AS autor_avatar
              FROM posts p
              JOIN usuarios u ON p.autor_id = u.id
             WHERE p.slug = :slug AND p.status = 'publicado'
             LIMIT 1
        ");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.nome AS autor_nome, u.avatar AS autor_avatar
              FROM posts p
              JOIN usuarios u ON p.autor_id = u.id
             WHERE p.id = :id
             LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO posts (titulo, categoria, slug, conteudo, imagem, status, autor_id, criado_em)
            VALUES (:titulo, :categoria, :slug, :conteudo, :imagem, :status, :autor_id, NOW())
        ");
        return $stmt->execute([
            'titulo'    => $data['titulo'],
            'categoria' => $data['categoria'],
            'slug'      => $data['slug'],
            'conteudo'  => $data['conteudo'],
            'imagem'    => $data['imagem'] ?? null,
            'status'    => $data['status'],
            'autor_id'  => $data['autor_id'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE posts SET
                titulo = :titulo,
                categoria = :categoria,
                slug = :slug,
                conteudo = :conteudo,
                imagem = :imagem,
                status = :status
            WHERE id = :id
        ");
        return $stmt->execute([
            'titulo'    => $data['titulo'],
            'categoria' => $data['categoria'],
            'slug'      => $data['slug'],
            'conteudo'  => $data['conteudo'],
            'imagem'    => $data['imagem'] ?? null,
            'status'    => $data['status'],
            'id'        => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    /* ============================
     * ========= KPIs =============
     * ============================ */

    public function countAll(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM posts WHERE status = :status");
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function countByCategory(string $category): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM posts WHERE categoria = :categoria");
        $stmt->execute(['categoria' => $category]);
        return (int) $stmt->fetchColumn();
    }

    public function countCreatedLastDays(int $days): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM posts 
             WHERE DATE(criado_em) >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
        ");
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /* ============================
     * ========= FILTROS ==========
     * ============================ */

    public function getByStatus(string $status): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.nome AS autor_nome, u.avatar AS autor_avatar
              FROM posts p
              JOIN usuarios u ON p.autor_id = u.id
             WHERE p.status = :status
          ORDER BY p.criado_em DESC
        ");
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll();
    }

    public function getByCategory(string $category): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.nome AS autor_nome, u.avatar AS autor_avatar
              FROM posts p
              JOIN usuarios u ON p.autor_id = u.id
             WHERE p.categoria = :categoria
          ORDER BY p.criado_em DESC
        ");
        $stmt->execute(['categoria' => $category]);
        return $stmt->fetchAll();
    }

    public function getCreatedLastDays(int $days): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.nome AS autor_nome, u.avatar AS autor_avatar
              FROM posts p
              JOIN usuarios u ON p.autor_id = u.id
             WHERE DATE(p.criado_em) >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
          ORDER BY p.criado_em DESC
        ");
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecent(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.nome AS autor_nome, u.avatar AS autor_avatar
              FROM posts p
              JOIN usuarios u ON p.autor_id = u.id
             WHERE p.status = 'publicado'
          ORDER BY p.criado_em DESC
             LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

        /**
     * ==========================================================
     *  Busca com filtro e paginação (frontend)
     * ==========================================================
     */
    public function getFiltered($categoria = null, $query = '', $limit = 6, $offset = 0): array
    {
        $sql = "
            SELECT p.*, u.nome AS autor_nome, u.avatar AS autor_avatar
              FROM posts p
              JOIN usuarios u ON u.id = p.autor_id
             WHERE p.status = 'publicado'
        ";
        $params = [];

        if ($categoria) {
            $sql .= " AND p.categoria = :categoria";
            $params[':categoria'] = $categoria;
        }

        if ($query) {
            $sql .= " AND (p.titulo LIKE :q OR p.conteudo LIKE :q)";
            $params[':q'] = "%{$query}%";
        }

        $sql .= " ORDER BY p.criado_em DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Conta total de posts para paginação (frontend)
     */
    public function countFiltered($categoria = null, $query = ''): int
    {
        $sql = "SELECT COUNT(*) FROM posts WHERE status = 'publicado'";
        $params = [];

        if ($categoria) {
            $sql .= " AND categoria = :categoria";
            $params[':categoria'] = $categoria;
        }

        if ($query) {
            $sql .= " AND (titulo LIKE :q OR conteudo LIKE :q)";
            $params[':q'] = "%{$query}%";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Posts mais visualizados
     */
    public function getMostViewed($limit = 3): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM posts 
             WHERE status = 'publicado'
          ORDER BY views DESC
             LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Incrementa contador de visualizações
     */
    public function incrementViews(int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    /**
     * Posts relacionados (mesma categoria)
     */
    public function getRelated(string $categoria, int $excludeId, int $limit = 3): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM posts 
             WHERE categoria = :categoria AND id != :excludeId AND status = 'publicado'
          ORDER BY criado_em DESC
             LIMIT :limit
        ");
        $stmt->bindValue(':categoria', $categoria);
        $stmt->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

}
