<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Tool;

/**
 * Controller responsável pelo CRUD de ferramentas no painel admin.
 * Protege todas as rotas para acesso apenas de administradores.
 *
 * Métodos:
 *  - dashboard(): KPIs/visão geral (cards) das ferramentas
 *  - index()    : Lista todas as ferramentas
 *  - create()   : Formulário + processamento de criação
 *  - edit($id)  : Formulário + processamento de edição
 *  - delete($id): Exclui uma ferramenta
 *  - show($id)  : Visualiza detalhes de uma ferramenta
 */
class ToolController extends Controller
{
    /**
     * Protege todos os métodos para acesso apenas de admins.
     * Caso o usuário não seja admin, redireciona para o dashboard e exibe mensagem.
     */
    private function authorize()
    {
        if (empty($_SESSION['user']) || ($_SESSION['user']['cargo'] ?? '') !== 'Admin') {
            set_flash('error', 'Acesso negado. Você precisa ser administrador!');
            header('Location: /admin/dashboard');
            exit;
        }
    }

/**
     * Dashboard de Ferramentas (cards/kpis + tabela recentes).
     */
    public function dashboard()
    {
        $this->authorize();

        $toolModel = new Tool();

        // Totais gerais
        $total_tools = $toolModel->countAll();

        // Por tipo
        $type_options = ['Nova', 'Usada', 'Restaurada', 'Danificada'];
        $type_counts  = [];
        foreach ($type_options as $t) {
            $type_counts[$t] = $toolModel->countByType($t);
        }

        // Por categoria
        $cat_options = [
            'Ferramentas elétricas',
            'Ferramentas hidráulicas',
            'Ferramentas de marcenaria',
            'Ferramentas de corte',
            'Ferramentas de medição',
            'Outras'
        ];
        $cat_counts = [];
        foreach ($cat_options as $c) {
            $cat_counts[$c] = $toolModel->countByCategory($c);
        }

        // KPIs adicionais
        $recent_30_days_count = $toolModel->countAcquiredLastDays(30);
        $damaged_count        = $toolModel->countByType('Danificada');

        // Ferramentas recentes (widget)
        $recent_tools = $toolModel->getRecentAcquired(5);

        $breadcrumb = [
            ['title' => 'Dashboard',   'url' => '/admin/dashboard'],
            ['title' => 'Ferramentas', 'url' => '/admin/tools'],
            ['title' => 'Visão geral', 'url' => null],
        ];

        return $this->render('Admin/Tools/dashboard', [
            'title'                => 'Dashboard de Ferramentas',
            'user'                 => $_SESSION['user'],
            'breadcrumb'           => $breadcrumb,
            'total_tools'          => $total_tools,
            'type_options'         => $type_options,
            'type_counts'          => $type_counts,
            'cat_options'          => $cat_options,
            'cat_counts'           => $cat_counts,
            'recent_30_days_count' => $recent_30_days_count,
            'damaged_count'        => $damaged_count,
            'recent_tools'         => $recent_tools, // <-- agora passando para a view
        ]);
    }

    /**
     * Lista todas as ferramentas cadastradas.
     * Exibe a tela index.twig.
     */
    public function index()
    {
        $this->authorize();

        $toolModel = new Tool();
        $tools = $toolModel->getAll();

            $type   = $_GET['type_tool']   ?? null;
    $cat    = $_GET['cat_tool']    ?? null;
    $recent = $_GET['recent_days'] ?? null;

    if ($type) {
        $tools = $toolModel->getByType($type);
    } elseif ($cat) {
        $tools = $toolModel->getByCategory($cat);
    } elseif ($recent) {
        $tools = $toolModel->getAcquiredLastDays((int) $recent);
    } else {
        $tools = $toolModel->getAll();
    }


        $breadcrumb = [
            ['title' => 'Dashboard',   'url' => '/admin/dashboard'],
            ['title' => 'Ferramentas', 'url' => null],
        ];

        return $this->render('Admin/Tools/index', [
            'title'      => 'Ferramentas',
            'tools'      => $tools,
            'user'       => $_SESSION['user'],
            'success'    => get_flash('success'),
            'error'      => get_flash('error'),
            'breadcrumb' => $breadcrumb
        ]);
    }

    /**
     * Exibe o formulário e processa a criação de uma nova ferramenta.
     */
    public function create()
    {
        $this->authorize();
        require_once __DIR__ . '/../../Helpers/csrf.php';
        require_once __DIR__ . '/../../Helpers/validate.php';

        $errors = [];
        $tool = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validação do token CSRF para evitar ataques
            if (!csrf_validate($_POST['csrf_token'] ?? '')) {
                $errors['csrf'] = 'Token CSRF inválido. Recarregue a página.';
            }

            $data = $_POST;

            // Validação e upload da imagem da ferramenta
            $imgResult = validate_img_upload($_FILES['img_tool'] ?? [], $errors);
            if ($imgResult && !isset($errors['img_tool'])) {
                $data['img_tool'] = $imgResult;
            } else {
                // Caso não seja feito upload, utiliza imagem padrão
                $data['img_tool'] = '/assets/adminlte/dist/img/default_tool.png';
            }

            $toolModel = new Tool();
            if (empty($errors) && $toolModel->create($data, $errors)) {
                set_flash('success', 'Ferramenta cadastrada com sucesso!');
                $this->redirect('/admin/tools');
            }
            // Mantém dados preenchidos no formulário se houver erro
            $tool = $data;
        }

        $breadcrumb = [
            ['title' => 'Dashboard',    'url' => '/admin/dashboard'],
            ['title' => 'Ferramentas',  'url' => '/admin/tools'],
            ['title' => 'Nova ferramenta', 'url' => null],
        ];

        return $this->render('Admin/Tools/create', [
            'title'      => 'Nova ferramenta',
            'tool'       => $tool,
            'errors'     => $errors,
            'user'       => $_SESSION['user'],
            'csrf_token' => csrf_token(),
            'breadcrumb' => $breadcrumb
        ]);
    }

    /**
     * Exibe o formulário e processa a edição de uma ferramenta existente.
     *
     * @param int $id ID da ferramenta a ser editada.
     */
    public function edit($id)
    {
        $this->authorize();
        require_once __DIR__ . '/../../Helpers/csrf.php';
        require_once __DIR__ . '/../../Helpers/validate.php';

        $toolModel = new Tool();
        $tool = $toolModel->findById($id);
        $errors = [];

        if (!$tool) {
            set_flash('error', 'Ferramenta não encontrada!');
            $this->redirect('/admin/tools');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Valida token CSRF
            if (!csrf_validate($_POST['csrf_token'] ?? '')) {
                $errors['csrf'] = 'Token CSRF inválido. Recarregue a página.';
            }

            $data = $_POST;

            // Validação e possível upload de nova imagem
            $imgResult = validate_img_upload($_FILES['img_tool'] ?? [], $errors);
            if ($imgResult && !isset($errors['img_tool'])) {
                // Remove imagem antiga do disco se não for a padrão
                if (!empty($tool['img_tool']) && $tool['img_tool'] !== '/assets/adminlte/dist/img/default_tool.png') {
                    $oldImgPath = $_SERVER['DOCUMENT_ROOT'] . $tool['img_tool'];
                    if (file_exists($oldImgPath)) {
                        @unlink($oldImgPath);
                    }
                }
                $data['img_tool'] = $imgResult;
            } else {
                // Mantém imagem antiga
                $data['img_tool'] = $tool['img_tool'];
            }

            if (empty($errors) && $toolModel->update($id, $data, $errors)) {
                set_flash('success', 'Ferramenta atualizada com sucesso!');
                $this->redirect('/admin/tools');
            }
            // Mantém dados do formulário em caso de erro
            $tool = array_merge($tool, $data);
        }

        $breadcrumb = [
            ['title' => 'Dashboard',    'url' => '/admin/dashboard'],
            ['title' => 'Ferramentas',  'url' => '/admin/tools'],
            ['title' => 'Editar ferramenta', 'url' => null],
        ];

        return $this->render('Admin/Tools/edit', [
            'title'      => 'Editar ferramenta',
            'tool'       => $tool,
            'errors'     => $errors,
            'user'       => $_SESSION['user'],
            'csrf_token' => csrf_token(),
            'breadcrumb' => $breadcrumb
        ]);
    }

    /**
     * Exclui uma ferramenta existente e remove sua imagem do disco, caso não seja a padrão.
     *
     * @param int $id ID da ferramenta a ser excluída.
     */
    public function delete($id)
    {
        $this->authorize();
        $toolModel = new Tool();
        $tool = $toolModel->findById($id);

        if (!$tool) {
            set_flash('error', 'Ferramenta não encontrada!');
        } else {
            // Remove imagem física se não for a padrão
            if (!empty($tool['img_tool']) && $tool['img_tool'] !== '/assets/adminlte/dist/img/default_tool.png') {
                $imgPath = $_SERVER['DOCUMENT_ROOT'] . $tool['img_tool'];
                if (file_exists($imgPath)) {
                    @unlink($imgPath);
                }
            }
            $toolModel->delete($id);
            set_flash('success', 'Ferramenta excluída com sucesso!');
        }
        $this->redirect('/admin/tools');
    }

    /**
     * Exibe os detalhes de uma ferramenta específica.
     *
     * @param int $id ID da ferramenta a ser exibida.
     */
    public function show($id)
    {
        $this->authorize();
        $toolModel = new Tool();
        $tool = $toolModel->findById($id);

        if (!$tool) {
            set_flash('error', 'Ferramenta não encontrada!');
            $this->redirect('/admin/tools');
        }

        $breadcrumb = [
            ['title' => 'Dashboard',   'url' => '/admin/dashboard'],
            ['title' => 'Ferramentas', 'url' => '/admin/tools'],
            ['title' => 'Detalhes',    'url' => null],
        ];

        return $this->render('Admin/Tools/show', [
            'title'      => 'Detalhes da Ferramenta',
            'tool'       => $tool,
            'user'       => $_SESSION['user'],
            'breadcrumb' => $breadcrumb
        ]);
    }
}
