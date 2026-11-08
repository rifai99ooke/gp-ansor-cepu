<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlSanitizerService
{
    private HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowElement('p', ['class'])
            ->allowElement('br')
            ->allowElement('hr', ['class'])
            ->allowElement('div', ['class'])
            ->allowElement('span', ['class'])
            ->allowElement('h1', ['class', 'id'])
            ->allowElement('h2', ['class', 'id'])
            ->allowElement('h3', ['class', 'id'])
            ->allowElement('h4', ['class', 'id'])
            ->allowElement('h5', ['class', 'id'])
            ->allowElement('h6', ['class', 'id'])
            ->allowElement('strong', ['class'])
            ->allowElement('b', ['class'])
            ->allowElement('em', ['class'])
            ->allowElement('i', ['class'])
            ->allowElement('u', ['class'])
            ->allowElement('s', ['class'])
            ->allowElement('del', ['class'])
            ->allowElement('sub')
            ->allowElement('sup')
            ->allowElement('blockquote', ['class'])
            ->allowElement('pre', ['class'])
            ->allowElement('code', ['class'])
            ->allowElement('ul', ['class'])
            ->allowElement('ol', ['class', 'start', 'type'])
            ->allowElement('li', ['class'])
            ->allowElement('figure', ['class'])
            ->allowElement('figcaption', ['class'])
            ->allowElement('a', ['href', 'title', 'target', 'rel', 'class'])
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height', 'class', 'loading'])
            ->allowElement('table', ['class'])
            ->allowElement('thead', ['class'])
            ->allowElement('tbody', ['class'])
            ->allowElement('tr', ['class'])
            ->allowElement('th', ['class', 'colspan', 'rowspan', 'scope'])
            ->allowElement('td', ['class', 'colspan', 'rowspan'])
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowMediaSchemes(['http', 'https', 'data'])
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->withMaxInputLength(500000);

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function sanitize(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        return $this->sanitizer->sanitize($html);
    }
}
