<?php
namespace App\Controllers\Site;

use App\Core\Controller;
use App\Models\Post;
use App\Helpers\Breadcrumb;

/**
 * ==============================================================
 *  Controller: BlogController
 *  --------------------------------------------------------------
 *  Responsável por exibir o blog no site público.
 *  - Lista todos os posts publicados (index)
 *  - Exibe um artigo individual (view)
 *  - Cria breadcrumbs dinâmicos com o helper Breadcrumb
 * ==============================================================
 */
class BlogController extends Controller
{
    /**
     * ==========================================================
     *  Página principal do Blog (/blog)
     *  ----------------------------------------------------------
     *  Lista todos os posts com status "publicado", ordenados
     *  do mais recente para o mais antigo.
     * ==========================================================
     */
    public function index(): void
    {
        // 🧩 Instancia o model de posts
        $postModel = new Post();

        // 🔹 Busca todos os posts públicos
        $posts = $postModel->getAllPublic();

        // 🧭 Cria o breadcrumb dinâmico
        $breadcrumb = (new Breadcrumb())
            ->add('Home', '/')
            ->add('Blog') // (último item → sem URL)
            ->get();

        // 🎨 Renderiza o template da listagem de posts
        $this->render('Site/blog_index', [
            'title'      => 'Blog',
            'posts'      => $posts,
            'breadcrumb' => $breadcrumb
        ]);
    }

    /**
     * ==========================================================
     *  Página de visualização de um post (/blog/{slug})
     *  ----------------------------------------------------------
     *  Exibe o conteúdo completo de um artigo, identificado
     *  pelo seu "slug" (ex: /blog/o-que-e-o-bootstrap).
     * ==========================================================
     */
    public function view(string $slug): void
    {
        // 🧩 Instancia o model
        $postModel = new Post();

        // 🔍 Busca o post pelo slug
        $post = $postModel->findBySlug($slug);

        // 🚨 Caso o post não exista, redireciona de volta ao blog
        if (!$post) {
            set_flash('error', 'Artigo não encontrado!');
            $this->redirect('/blog');
        }

        // 🧭 Cria breadcrumb dinâmico
        $breadcrumb = (new Breadcrumb())
            ->add('Home', '/')
            ->add('Blog', '/blog')
            ->add($post['titulo']) // último sem link
            ->get();

        // 📈 (Opcional) Poderia incrementar visualizações aqui
        // $postModel->incrementViews($post['id']);

        // 🎨 Renderiza o template de exibição do artigo
        $this->render('Site/blog_post', [
            'title'      => $post['titulo'],
            'post'       => $post,
            'breadcrumb' => $breadcrumb
        ]);
    }
}
