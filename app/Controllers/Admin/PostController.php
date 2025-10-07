<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Post;

/**
 * ==============================================================
 *  Controller: PostController
 *  Responsável pelo CRUD de posts no painel administrativo.
 *  Protege todas as rotas via permissões granulares:
 *  (post_view, post_create, post_edit, post_delete)
 * ==============================================================
 */
class PostController extends Controller
{
    /**
     * ==========================================================
     *  Exibe a lista de posts no painel admin.
     *  Permite filtros por status, categoria ou posts recentes.
     * ==========================================================
     */
    public function index()
    {
        $this->requirePermission('post_view');

        $postModel = new Post();

        $status    = $_GET['status']      ?? null;
        $categoria = $_GET['categoria']   ?? null;
        $recent    = $_GET['recent_days'] ?? null;

        if ($status) {
            $posts = $postModel->getByStatus($status);
        } elseif ($categoria) {
            $posts = $postModel->getByCategory($categoria);
        } elseif ($recent) {
            $posts = $postModel->getCreatedLastDays((int)$recent);
        } else {
            $posts = $postModel->getAllAdmin();
        }

        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => '/admin/dashboard'],
            ['title' => 'Posts', 'url' => null],
        ];

        return $this->render('Admin/Posts/index', [
            'title'      => 'Posts',
            'posts'      => $posts,
            'user'       => $_SESSION['user'],
            'success'    => get_flash('success'),
            'error'      => get_flash('error'),
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * ==========================================================
     *  Dashboard de Posts — visão geral com estatísticas.
     * ==========================================================
     */
    public function dashboard()
    {
        $this->requirePermission('post_view');

        $postModel = new Post();

        // Totais gerais
        $total_posts = $postModel->countAll();

        // Por status (publicado, rascunho)
        $status_options = ['publicado', 'rascunho'];
        $status_counts  = [];
        foreach ($status_options as $s) {
            $status_counts[$s] = $postModel->countByStatus($s);
        }

        // Por categoria (estática por enquanto)
        $cat_options = ['Apache', 'PHP', 'Java', 'Python', 'Javascript', 'MySQL', 'Twig', 'Bootstrap', 'Outros'];
        $cat_counts = [];
        foreach ($cat_options as $c) {
            $cat_counts[$c] = $postModel->countByCategory($c);
        }

        // Últimos 30 dias
        $recent_30_days_count = $postModel->countCreatedLastDays(30);

        // Posts recentes (widget)
        $recent_posts = $postModel->getRecent(5);

        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => '/admin/dashboard'],
            ['title' => 'Posts', 'url' => '/admin/posts'],
            ['title' => 'Visão geral', 'url' => null],
        ];

        return $this->render('Admin/Posts/dashboard', [
            'title'                => 'Dashboard de Posts',
            'user'                 => $_SESSION['user'],
            'breadcrumb'           => $breadcrumb,
            'total_posts'          => $total_posts,
            'status_options'       => $status_options,
            'status_counts'        => $status_counts,
            'cat_options'          => $cat_options,
            'cat_counts'           => $cat_counts,
            'recent_30_days_count' => $recent_30_days_count,
            'recent_posts'         => $recent_posts,
        ]);
    }

    /**
     * ==========================================================
     *  Criação de novo post.
     *  Exibe formulário e processa envio (POST).
     * ==========================================================
     */
    public function create()
    {
        $this->requirePermission('post_create');
        require_once __DIR__ . '/../../Helpers/clean_html.php';

        $errors = [];
        $data   = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data             = $_POST;
            $data['autor_id'] = $_SESSION['user']['id'];
            $data['slug']     = $this->slugify($data['titulo']);
            $data['imagem']   = $this->handleImageUpload($_FILES['imagem'] ?? null);
            $data['status']   = $data['status'] ?? 'rascunho';

            // 🔍 Limpa HTML injetado (extensões, etc.)
            $data['conteudo'] = clean_html_content($data['conteudo'] ?? '');

            $postModel = new Post();
            if ($postModel->create($data)) {
                set_flash('success', 'Post criado com sucesso!');
                $this->redirect('/admin/posts');
            } else {
                $errors['geral'] = 'Erro ao criar post.';
            }
        }

        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => '/admin/dashboard'],
            ['title' => 'Posts', 'url' => '/admin/posts'],
            ['title' => 'Novo post', 'url' => null],
        ];

        return $this->render('Admin/Posts/create', [
            'title'      => 'Novo Post',
            'data'       => $data,
            'errors'     => $errors,
            'user'       => $_SESSION['user'],
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * ==========================================================
     *  Edição de post existente.
     * ==========================================================
     */
    public function edit($id)
    {
        $this->requirePermission('post_edit');
        require_once __DIR__ . '/../../Helpers/clean_html.php';

        $postModel = new Post();
        $post = $postModel->findById($id);

        if (!$post) {
            set_flash('error', 'Post não encontrado!');
            $this->redirect('/admin/posts');
        }

        $errors = [];
        $data   = $post;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data             = $_POST;
            $data['slug']     = $this->slugify($data['titulo']);
            $data['imagem']   = $this->handleImageUpload($_FILES['imagem'] ?? null, $post['imagem']);
            $data['status']   = $data['status'] ?? 'rascunho';
            $data['conteudo'] = clean_html_content($data['conteudo'] ?? '');

            if ($postModel->update($id, $data)) {
                set_flash('success', 'Post atualizado com sucesso!');
                $this->redirect('/admin/posts');
            } else {
                $errors['geral'] = 'Erro ao atualizar post.';
            }
        }

        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => '/admin/dashboard'],
            ['title' => 'Posts', 'url' => '/admin/posts'],
            ['title' => 'Editar post', 'url' => null],
        ];

        return $this->render('Admin/Posts/edit', [
            'title'      => 'Editar Post',
            'post'       => $post,
            'data'       => $data,
            'errors'     => $errors,
            'user'       => $_SESSION['user'],
            'breadcrumb' => $breadcrumb,
        ]);
    }

    /**
     * ==========================================================
     *  Exclui um post e sua imagem associada (se existir).
     * ==========================================================
     */
    public function delete($id)
    {
        $this->requirePermission('post_delete');

        $postModel = new Post();
        $post = $postModel->findById($id);

        if (!$post) {
            set_flash('error', 'Post não encontrado!');
        } else {
            // Exclui imagem física se existir
            if (!empty($post['imagem'])) {
                $imgPath = $_SERVER['DOCUMENT_ROOT'] . $post['imagem'];
                if (file_exists($imgPath)) {
                    @unlink($imgPath);
                }
            }

            $postModel->delete($id);
            set_flash('success', 'Post excluído com sucesso!');
        }

        $this->redirect('/admin/posts');
    }

    /**
     * ==========================================================
     *  Exibe os detalhes de um post.
     * ==========================================================
     */
    public function show($id)
    {
        $this->requirePermission('post_view');

        $postModel = new Post();
        $post = $postModel->findById($id);

        if (!$post) {
            set_flash('error', 'Post não encontrado!');
            $this->redirect('/admin/posts');
        }

        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => '/admin/dashboard'],
            ['title' => 'Posts', 'url' => '/admin/posts'],
            ['title' => 'Detalhes', 'url' => null],
        ];

        return $this->render('Admin/Posts/show', [
            'title'      => 'Detalhes do Post',
            'post'       => $post,
            'user'       => $_SESSION['user'],
            'breadcrumb' => $breadcrumb,
            'success'    => get_flash('success'),
            'error'      => get_flash('error'),
        ]);
    }

    /**
     * ==========================================================
     *  Gera um slug limpo e único a partir do título.
     * ==========================================================
     */
    private function slugify($string): string
    {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $string));
        return trim($slug, '-');
    }

    /**
     * ==========================================================
     *  Lida com upload de imagem do post.
     * ==========================================================
     */
    private function handleImageUpload($file, $currentImage = null)
    {
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $name = uniqid('img_') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $dest = '/uploads/posts/' . $name;
            move_uploaded_file($file['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $dest);
            return $dest;
        }

        return $currentImage;
    }

    /**
     * ==========================================================
     *  Verifica se o usuário tem permissão para executar a ação.
     * ==========================================================
     */
    private function requirePermission(string $permission): void
    {
        require_once __DIR__ . '/../../Helpers/permissions.php';

        if (!user_has_permission($permission)) {
            set_flash('error', 'Acesso negado. Permissão insuficiente!');
            header('Location: /admin/dashboard');
            exit;
        }
    }
}
