<?php
namespace App\Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFunction;

abstract class Controller
{
    protected Environment $twig;

    public function __construct()
{
    require_once dirname(__DIR__) . '/Helpers/permissions.php';

    $viewsPath = dirname(__DIR__) . '/Views';
    $loader = new FilesystemLoader($viewsPath);
    $this->twig = new Environment($loader, [
        'debug' => getenv('APP_DEBUG') === 'true',
        'cache' => false
    ]);

    // ADICIONE ESTA LINHA:
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());

    // REGISTRA COMO FUNÇÃO TWIG!
    $this->twig->addFunction(
        new TwigFunction('user_has_permission', function($permission) {
            return user_has_permission($permission);
        })
    );

    // --- ADICIONE ESTE FILTRO AQUI ---
    $this->twig->addFilter(new \Twig\TwigFilter('cpf_format', function ($cpf) {
    // Garante que não seja null, e faz trim
    $cpf = trim((string) $cpf);
    if (!$cpf || strlen(preg_replace('/\D/', '', $cpf)) !== 11) return $cpf;
    $cpf = preg_replace('/\D/', '', $cpf);
    return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
}));

    // CELULAR
$this->twig->addFilter(new \Twig\TwigFilter('cel_format', function ($cel) {
    $cel = trim((string) $cel);
    $cel = preg_replace('/\D/', '', $cel);
    if (strlen($cel) === 11) {
        // Exemplo: 84999999999 => (84) 99999-9999
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $cel);
    }
    if (strlen($cel) === 10) {
        // Exemplo: 8433339999 => (84) 3333-9999
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $cel);
    }
    return $cel; // Se não bater, retorna como está
}));

// CEP
$this->twig->addFilter(new \Twig\TwigFilter('cep_format', function ($cep) {
    $cep = trim((string) $cep);
    $cep = preg_replace('/\D/', '', $cep);
    if (strlen($cep) === 8) {
        // Exemplo: 12345678 => 12345-678
        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
    }
    return $cep;
}));
}


    protected function render(string $view, array $params = [])
{
    $params['current_path'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Só injeta se NÃO tiver sido passado no $params!
    if (!isset($params['user'])) {
        $params['user'] = $_SESSION['user'] ?? null;
    }

    echo $this->twig->render($view . '.twig', $params);
}


    protected function redirect(string $url)
    {
        header('Location: ' . $url);
        exit;
    }

    
}
