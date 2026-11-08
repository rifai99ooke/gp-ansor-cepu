<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Throwable;

class GoogleFormUrlNormalizer
{
    /**
     * Allowed hosts for Google Form input.
     *
     * @var array<int, string>
     */
    public const ALLOWED_HOSTS = [
        'docs.google.com',
        'forms.gle',
    ];

    /**
     * Normalize user input (docs.google.com URL, forms.gle short URL, or pasted <iframe> HTML)
     * into ['form_url' => string, 'embed_url' => ?string].
     *
     * @return array{form_url: string, embed_url: ?string}
     *
     * @throws ValidationException
     */
    public function normalize(string $rawInput): array
    {
        $input = trim($rawInput);

        if ($input === '') {
            throw ValidationException::withMessages([
                'form_url' => 'URL Google Form wajib diisi.',
            ]);
        }

        // 1. Extract src attribute if input is an <iframe> embed code
        if (stripos($input, '<iframe') !== false) {
            if (preg_match('/\bsrc\s*=\s*(["\'])(.*?)\1/i', $input, $matches)) {
                $input = html_entity_decode(trim($matches[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            } else {
                throw ValidationException::withMessages([
                    'form_url' => 'Kode sematan iframe tidak memiliki atribut src yang valid.',
                ]);
            }
        }

        // Upgrade http:// to https:// if needed
        if (str_starts_with(strtolower($input), 'http://')) {
            $input = 'https://'.substr($input, 7);
        }

        $parsed = parse_url($input);
        $scheme = strtolower($parsed['scheme'] ?? '');
        $host = strtolower($parsed['host'] ?? '');

        if ($scheme !== 'https' || ! in_array($host, self::ALLOWED_HOSTS, true)) {
            throw ValidationException::withMessages([
                'form_url' => 'Host URL tidak diizinkan. Gunakan tautan resmi dari docs.google.com atau forms.gle.',
            ]);
        }

        // 2. If host is forms.gle, resolve redirect once (5s timeout)
        if ($host === 'forms.gle') {
            $resolvedUrl = $this->resolveShortUrl($input);
            if ($resolvedUrl !== null && $this->isDocsGoogleFormsUrl($resolvedUrl)) {
                return [
                    'form_url' => $this->stripEmbeddedParam($resolvedUrl),
                    'embed_url' => $this->ensureEmbeddedParam($resolvedUrl),
                ];
            }

            // Fallback to button-only if resolution fails
            return [
                'form_url' => $input,
                'embed_url' => null,
            ];
        }

        // 3. Host is docs.google.com — verify path starts with /forms/
        if (! $this->isDocsGoogleFormsUrl($input)) {
            throw ValidationException::withMessages([
                'form_url' => 'URL docs.google.com harus merupakan tautan Google Forms (/forms/...).',
            ]);
        }

        return [
            'form_url' => $this->stripEmbeddedParam($input),
            'embed_url' => $this->ensureEmbeddedParam($input),
        ];
    }

    /**
     * Check whether an embed URL is strictly safe to render inside an <iframe>.
     */
    public static function isAllowedEmbedUrl(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return false;
        }

        $parsed = parse_url(trim($url));
        if (! is_array($parsed)) {
            return false;
        }

        $scheme = strtolower($parsed['scheme'] ?? '');
        $host = strtolower($parsed['host'] ?? '');
        $path = $parsed['path'] ?? '';

        return $scheme === 'https'
            && $host === 'docs.google.com'
            && str_starts_with($path, '/forms/');
    }

    /**
     * Check whether a form URL is from an allowed Google Form host.
     */
    public static function isAllowedFormUrl(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return false;
        }

        $parsed = parse_url(trim($url));
        if (! is_array($parsed)) {
            return false;
        }

        $scheme = strtolower($parsed['scheme'] ?? '');
        $host = strtolower($parsed['host'] ?? '');
        $path = $parsed['path'] ?? '';

        if ($scheme !== 'https' || ! in_array($host, self::ALLOWED_HOSTS, true)) {
            return false;
        }

        if ($host === 'docs.google.com') {
            return str_starts_with($path, '/forms/');
        }

        return $path !== '' && $path !== '/';
    }

    private function isDocsGoogleFormsUrl(string $url): bool
    {
        $parsed = parse_url($url);
        if (! is_array($parsed)) {
            return false;
        }

        $scheme = strtolower($parsed['scheme'] ?? '');
        $host = strtolower($parsed['host'] ?? '');
        $path = $parsed['path'] ?? '';

        return $scheme === 'https'
            && $host === 'docs.google.com'
            && str_starts_with($path, '/forms/');
    }

    private function resolveShortUrl(string $shortUrl): ?string
    {
        try {
            $currentUrl = $shortUrl;

            for ($i = 0; $i < 5; $i++) {
                $response = Http::timeout(5)
                    ->withoutRedirecting()
                    ->withHeaders([
                        'User-Agent' => 'GPAnsorPortal/1.0',
                    ])
                    ->get($currentUrl);

                if ($response->redirect()) {
                    $location = $response->header('Location');
                    if ($location === '') {
                        return null;
                    }

                    if ($this->isDocsGoogleFormsUrl($location)) {
                        return $location;
                    }

                    $locHost = strtolower((string) parse_url($location, PHP_URL_HOST));
                    if (in_array($locHost, self::ALLOWED_HOSTS, true)) {
                        $currentUrl = $location;

                        continue;
                    }

                    return null;
                }

                break;
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function ensureEmbeddedParam(string $url): string
    {
        return $this->buildNormalizedDocsUrl($url, true);
    }

    private function stripEmbeddedParam(string $url): string
    {
        return $this->buildNormalizedDocsUrl($url, false);
    }

    private function buildNormalizedDocsUrl(string $url, bool $embedded): string
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';

        if (preg_match('#^(/forms/d/e/[^/]+|/forms/d/[^/]+)(?:/(edit|formResponse))?/?$#', $path, $matches)) {
            $path = $matches[1].'/viewform';
        } elseif (! str_ends_with($path, '/viewform') && ! str_contains($path, '/viewform')) {
            $path = rtrim($path, '/').'/viewform';
        }

        $queryParams = [];
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
        }

        if (($queryParams['usp'] ?? null) === 'publish-editor') {
            unset($queryParams['usp']);
        }

        if ($embedded) {
            $queryParams['embedded'] = 'true';
        } else {
            unset($queryParams['embedded']);
        }

        $queryString = http_build_query($queryParams);

        return 'https://docs.google.com'.$path.($queryString !== '' ? '?'.$queryString : '');
    }
}
