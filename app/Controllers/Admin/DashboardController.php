<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;
use App\Models\Tool;
use App\Models\Post;

/**
 * Controller responsável pelo Dashboard (Visão Geral) do painel administrativo.
 * Exibe KPIs, gráficos e listas recentes de usuários, ferramentas e posts.
 */
class DashboardController extends Controller
{
    /**
     * Garante que apenas usuários administradores tenham acesso ao dashboard.
     */
    private function authorize(): void
    {
        if (empty($_SESSION['user']) || ($_SESSION['user']['cargo'] ?? '') !== 'Admin') {
            set_flash('error', 'Acesso negado. Você precisa ser administrador!');
            header('Location: /');
            exit;
        }
    }

    /**
     * Exibe a visão geral do dashboard.
     * Rota: GET /admin/dashboard
     */
    public function index()
    {
        $this->authorize();

        // Instanciação dos models
        $userModel = new User();
        $toolModel = new Tool();
        $postModel = class_exists(Post::class) ? new Post() : null; // opcional, só se existir

        /* ============================
         * ===== KPIs Gerais ==========
         * ============================ */
        $total_users = method_exists($userModel, 'countAll') ? $userModel->countAll() : 0;
        $total_tools = method_exists($toolModel, 'countAll') ? $toolModel->countAll() : 0;
        $total_posts = ($postModel && method_exists($postModel, 'countAll')) ? $postModel->countAll() : 0;

        /* ============================
         * ===== KPIs Específicos =====
         * ============================ */
        $users_last30  = method_exists($userModel, 'countCreatedLastDays') ? $userModel->countCreatedLastDays(30) : 0;
        $tools_last30  = method_exists($toolModel, 'countAcquiredLastDays') ? $toolModel->countAcquiredLastDays(30) : 0;
        $posts_last30  = ($postModel && method_exists($postModel, 'countCreatedLastDays')) ? $postModel->countCreatedLastDays(30) : 0;
        $tools_damaged = method_exists($toolModel, 'countByType') ? $toolModel->countByType('Danificada') : 0;

        /* ============================
         * ===== Listas Recentes ======
         * ============================ */
        $recent_users = method_exists($userModel, 'getRecent') ? $userModel->getRecent(5) : [];
        $recent_tools = method_exists($toolModel, 'getRecentAcquired') ? $toolModel->getRecentAcquired(5) : [];
        $recent_posts = ($postModel && method_exists($postModel, 'getRecent')) ? $postModel->getRecent(5) : [];

        /* ============================
         * ===== Navegação (breadcrumb)
         * ============================ */
        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => '/admin/dashboard'],
            ['title' => 'Visão geral', 'url' => null],
        ];

        /* ============================
         * ===== Renderização =========
         * ============================ */
        return $this->render('Admin/dashboard', [
            'title'         => 'Visão Geral',
            'user'          => $_SESSION['user'],
            'breadcrumb'    => $breadcrumb,

            // KPIs gerais
            'total_users'   => $total_users,
            'total_tools'   => $total_tools,
            'total_posts'   => $total_posts,

            // KPIs últimos 30 dias
            'users_last30'  => $users_last30,
            'tools_last30'  => $tools_last30,
            'posts_last30'  => $posts_last30,
            'tools_damaged' => $tools_damaged,

            // Listas recentes
            'recent_users'  => $recent_users,
            'recent_tools'  => $recent_tools,
            'recent_posts'  => $recent_posts,
        ]);
    }
}
