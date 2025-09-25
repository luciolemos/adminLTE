<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Post;

/**
 * Controller responsável pelo CRUD de posts no painel admin.
 * Protege todas as rotas via permissões granulares (post_view, post_create, etc).
 */
class PostController extends Controller
{
    /**
     * Lista todos os posts.
     */
    public function index()
    {
        $this->requirePermission('post_view');

        $postModel = new Post();
        $posts = $postModel->getAllAdmin();

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
     * Criação de um novo post (form + processamento).
     */
    public function create()
    {
        $this->requirePermission('post_create');

        $errors = [];
        $data = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['autor_id'] = $_SESSION['user']['id'];
            $data['slug'] = $this->slugify($data['titulo']);
            $data['imagem'] = $this->handleImageUpload($_FILES['imagem'] ?? null);
            $data['status'] = $data['status'] ?? 'rascunho';

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
     * Edição de post existente.
     */
    public function edit($id)
    {
        $this->requirePermission('post_edit');

        $postModel = new Post();
        $post = $postModel->findById($id);
        if (!$post) {
            set_flash('error', 'Post não encontrado!');
            $this->redirect('/admin/posts');
        }

        $errors = [];
        $data = $post;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['slug'] = $this->slugify($data['titulo']);
            $data['imagem'] = $this->handleImageUpload($_FILES['imagem'] ?? null, $post['imagem']);
            $data['status'] = $data['status'] ?? 'rascunho';

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
     * Exclui um post (e imagem, se houver).
     */
    public function delete($id)
    {
        $this->requirePermission('post_delete');

        $postModel = new Post();
        $post = $postModel->findById($id);
        if (!$post) {
            set_flash('error', 'Post não encontrado!');
        } else {
            // Exclui a imagem física se houver e não for vazia
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
     * Visualização detalhada do post.
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
              ['title' => 'Posts', 'url' => '/site/posts'],
            ['title' => 'Detalhes', 'url' => null],
        ];

        return $this->render('Admin/Posts/show', [
            'title'      => 'Detalhes do Post',
            'post'       => $post,
            'user'       => $_SESSION['user'],
            'breadcrumb' => $breadcrumb,
            'success'    => get_flash('success'),
            'error'      => get_flash('error')
        ]);
    }

    /**
     * Gera um slug a partir do título.
     */
    private function slugify($string)
    {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $string));
        return trim($slug, '-');
    }

    /**
     * Lida com upload de imagem do post.
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
     * Checa permissão granular para cada ação.
     */
    private function requirePermission($permission)
    {
        require_once __DIR__ . '/../../Helpers/permissions.php';
        if (!user_has_permission($permission)) {
            set_flash('error', 'Acesso negado. Permissão insuficiente!');
            header('Location: /admin/dashboard');
            exit;
        }
    }
}
