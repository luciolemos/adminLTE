<?php
namespace App\Controllers\Site;

use App\Core\Controller;

class PageController extends Controller
{
    public function about()
    {
        return $this->render('Site/about', [
            'title'      => 'Sobre o Projeto',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/'],
                ['title' => 'Sobre', 'url' => null] // url null = página atual
            ]
        ]);
    }

    public function contact()
    {
        $user = $_SESSION['user'] ?? null;
        // Lógica de envio de contato (opcional)
        return $this->render('Site/contact', [
            'title' => 'Contato',
            'user'  => $user,
            'breadcrumb' => [
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'Contato', 'url' => null]
        ]
        ]);
    }

         public function readme()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/../README.md';
        $markdown = file_exists($path) ? file_get_contents($path) : 'README.md não encontrado!';
        $parsedown = new \Parsedown();
        // Renderiza o markdown em HTML
        $html = $parsedown->text($markdown);

        // Passa para a view
        return $this->render('Site/readme', [
            'title' => 'README',
            'readme_html' => $html
        ]);
    }

}