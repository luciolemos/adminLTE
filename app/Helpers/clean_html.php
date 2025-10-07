<?php
/**
 * ============================================================================
 * clean_html.php
 * ----------------------------------------------------------------------------
 * Sanitiza o conteúdo HTML de artigos e descrições.
 * Remove tags potencialmente perigosas, scripts e injeções de extensões.
 * Mantém apenas tags seguras e úteis (whitelist).
 * ============================================================================
 */

/**
 * Limpa e sanitiza o HTML com base em uma whitelist.
 *
 * @param string $html Conteúdo HTML bruto do editor.
 * @return string HTML seguro para armazenar e exibir.
 */
function clean_html_content(string $html): string
{
    if (trim($html) === '') {
        return '';
    }

    // 1️⃣ Normaliza entidades e remove caracteres invisíveis
    $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $html = preg_replace('/[\x00-\x1F\x7F]/u', '', $html);

    // 2️⃣ Remove injeções comuns de extensões e scripts
    $patterns = [
        '/<p><audio class="audio-for-speech"><\/audio><\/p>/i',
        '/<div class="translate-tooltip-mtz[\s\S]*?<\/div>/i',
        '/<grammarly-[^>]*>.*?<\/grammarly-[^>]*>/is',
        '/<span[^>]+data-grammarly[^>]*>.*?<\/span>/is',
        '/<script[^>]*>.*?<\/script>/is',
        '/<iframe[^>]*>.*?<\/iframe>/is',
        '/<object[^>]*>.*?<\/object>/is',
        '/<embed[^>]*>.*?<\/embed>/is',
        '/<style[^>]*>.*?<\/style>/is',
        '/<!--.*?-->/s',
    ];

    $html = preg_replace($patterns, '', $html);

    // 3️⃣ Define as tags permitidas (whitelist)
    $allowed_tags = [
        'p', 'br', 'hr', 'pre', 'code',
        'strong', 'em', 'b', 'i', 'u', 'mark', 'small',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'blockquote', 'q', 'cite',
        'table', 'thead', 'tbody', 'tr', 'td', 'th',
        'a', 'img', 'figure', 'figcaption',
        'section', 'article', 'header', 'footer', 'div', 'span'
    ];

    // Constrói a lista de tags para strip_tags
    $allowed_html = '';
    foreach ($allowed_tags as $tag) {
        $allowed_html .= "<$tag>";
    }

    // 4️⃣ Remove tudo que não estiver na lista permitida
    $html = strip_tags($html, $allowed_html);

    // 5️⃣ Remove atributos perigosos (on*, javascript:, etc.)
    $html = preg_replace('/\s*on\w+="[^"]*"/i', '', $html);
    $html = preg_replace('/\s*on\w+=\'[^\']*\'/i', '', $html);
    $html = preg_replace('/javascript:/i', '', $html);
    $html = preg_replace('/data:[^;]+;base64[^"]*/i', '', $html);

    // 6️⃣ Sanitiza atributos de <a> e <img>
    $html = preg_replace_callback(
        '/<(a|img)\s+([^>]+)>/i',
        function ($matches) {
            $tag = strtolower($matches[1]);
            $attrs = $matches[2];

            if ($tag === 'a') {
                // Permite apenas href, title, target e rel
                $attrs = preg_replace('/(?!href|title|target|rel)=("[^"]*"|\'[^\']*\')/i', '', $attrs);
                $attrs .= ' rel="noopener noreferrer"';
            } elseif ($tag === 'img') {
                // Permite apenas src, alt, title e class
                $attrs = preg_replace('/(?!src|alt|title|class)=("[^"]*"|\'[^\']*\')/i', '', $attrs);
            }

            return "<$tag $attrs>";
        },
        $html
    );

    // 7️⃣ Remove tags vazias redundantes
    $html = preg_replace('/<(\w+)(\s[^>]*)?>\s*<\/\1>/', '', $html);

    return trim($html);
}
