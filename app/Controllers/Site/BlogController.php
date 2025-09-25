<?php
namespace App\Controllers\Site;

use App\Core\Controller;
use App\Models\Post;
use App\Helpers\Breadcrumb;

class BlogController extends Controller
{
    public function index()
    {
        $postModel = new Post();
        $posts = $postModel->getAllPublic();

        // 🔹 Usando helper em vez de array manual
        $breadcrumb = (new Breadcrumb())
            ->add('Home', '/')
            ->add('Blog') // url = null → item ativo
            ->get();

        return $this->render('Site/blog_index', [
            'title'      => 'Blog',
            'posts'      => $posts,
            'breadcrumb' => $breadcrumb
        ]);
    }

    public function view($slug)
    {
        $postModel = new Post();
        $post = $postModel->findBySlug($slug);

        if (!$post) {
            $this->redirect('/blog');
        }

        // 🔹 Breadcrumb dinâmico para página de post
        $breadcrumb = (new Breadcrumb())
            ->add('Home', '/')
            ->add('Blog', '/blog')
            ->add($post['titulo']) // último sem URL
            ->get();

        return $this->render('Site/blog_post', [
            'title'      => $post['titulo'],
            'post'       => $post,
            'breadcrumb' => $breadcrumb
        ]);
    }
}
