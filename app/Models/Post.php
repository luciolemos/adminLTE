<?php
namespace App\Models;

use PDO;
use Exception;

class Post
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

    public function getAllPublic()
    {
        return $this->pdo->query("
            SELECT p.*, u.nome AS autor_nome, u.avatar AS autor_avatar
            FROM posts p
            JOIN usuarios u ON p.autor_id = u.id
            WHERE p.status = 'publicado'
            ORDER BY p.criado_em DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllAdmin()
    {
        return $this->pdo->query("
            SELECT p.*, u.nome AS autor_nome, u.avatar AS autor_avatar
            FROM posts p
            JOIN usuarios u ON p.autor_id = u.id
            ORDER BY p.criado_em DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findBySlug($slug)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.nome AS autor_nome, u.avatar AS autor_avatar
            FROM posts p
            JOIN usuarios u ON p.autor_id = u.id
            WHERE p.slug = ? AND p.status = 'publicado'
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.nome AS autor_nome, u.avatar AS autor_avatar
            FROM posts p
            JOIN usuarios u ON p.autor_id = u.id
            WHERE p.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO posts (titulo, categoria, slug, conteudo, imagem, status, autor_id)
            VALUES (:titulo, :categoria, :slug, :conteudo, :imagem, :status, :autor_id)
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

    public function update($id, $data)
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

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
