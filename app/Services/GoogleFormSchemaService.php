<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RegistrationForm;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class GoogleFormSchemaService
{
    private const SCHEMA_CACHE_PREFIX = 'google_form_schema_v2_';

    /**
     * Extract form schema (title, description, fields) from a RegistrationForm's Google Form URL.
     *
     * @return array{
     *     title: ?string,
     *     description: ?string,
     *     action_url: string,
     *     fields: array<int, array{
     *         id: string,
     *         input_name: string,
     *         entry_key: string,
     *         label: string,
     *         description: ?string,
     *         type: string,
     *         required: bool,
     *         options: array<int, string>,
     *         placeholder: string
     *     }>
     * }|null
     */
    public function getSchema(RegistrationForm $form): ?array
    {
        $url = trim((string) ($form->form_url ?: $form->embed_url));
        if (! GoogleFormUrlNormalizer::isAllowedFormUrl($url)) {
            return null;
        }

        $cacheKey = self::SCHEMA_CACHE_PREFIX.md5($url);

        return Cache::remember($cacheKey, now()->addHour(), function () use ($url): ?array {
            $sourceUrl = $url;
            if (strtolower((string) parse_url($url, PHP_URL_HOST)) === 'forms.gle') {
                $sourceUrl = app(GoogleFormUrlNormalizer::class)->normalize($url)['form_url'];
                if (! GoogleFormUrlNormalizer::isAllowedEmbedUrl($sourceUrl)) {
                    return null;
                }
            }

            $html = $this->fetchFormHtml($sourceUrl);
            if ($html === null) {
                return null;
            }

            $schema = $this->parseHtml($html, $sourceUrl);

            return $schema !== null && $schema['action_url'] !== null ? $schema : null;
        });
    }

    /**
     * Clear cached schema for a given Google Form URL.
     */
    public function forgetCache(?string $url): void
    {
        if (filled($url)) {
            Cache::forget(self::SCHEMA_CACHE_PREFIX.md5(trim((string) $url)));
        }
    }

    /**
     * Parse raw Google Form HTML containing FB_PUBLIC_LOAD_DATA_ into our normalized schema array.
     *
     * @return array{
     *     title: ?string,
     *     description: ?string,
     *     action_url: ?string,
     *     fields: array<int, array{
     *         id: string,
     *         input_name: string,
     *         entry_key: string,
     *         label: string,
     *         description: ?string,
     *         type: string,
     *         required: bool,
     *         options: array<int, string>,
     *         placeholder: string
     *     }>
     * }|null
     */
    public function parseHtml(string $html, ?string $sourceUrl = null): ?array
    {
        if (! preg_match('/var\s+FB_PUBLIC_LOAD_DATA_\s*=\s*(\[.*?\]);\s*<\/script>/s', $html, $matches)) {
            return null;
        }

        $data = json_decode($matches[1], true);
        if (! is_array($data) || ! isset($data[1]) || ! is_array($data[1])) {
            return null;
        }

        $formMeta = $data[1];
        $title = isset($formMeta[8]) && is_string($formMeta[8]) ? trim($formMeta[8]) : null;
        $description = isset($formMeta[0]) && is_string($formMeta[0]) ? trim($formMeta[0]) : null;
        $rawItems = is_array($formMeta[1] ?? null) ? $formMeta[1] : [];

        $fields = [];

        foreach ($rawItems as $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = isset($item[1]) && is_string($item[1]) ? trim($item[1]) : '';
            $helpText = isset($item[2]) && is_string($item[2]) && trim($item[2]) !== '' ? trim($item[2]) : null;
            $typeCode = isset($item[3]) && is_int($item[3]) ? $item[3] : null;
            $inputs = $item[4] ?? null;

            if ($label === '' || ! is_array($inputs) || ! isset($inputs[0]) || ! is_array($inputs[0])) {
                continue;
            }

            $firstInput = $inputs[0];
            $entryId = $firstInput[0] ?? null;
            if ($entryId === null || (! is_int($entryId) && ! is_string($entryId))) {
                continue;
            }

            $entryIdStr = (string) $entryId;
            $required = ((int) ($firstInput[2] ?? 0)) === 1;

            $options = [];
            if (isset($firstInput[1]) && is_array($firstInput[1])) {
                foreach ($firstInput[1] as $opt) {
                    if (is_array($opt) && isset($opt[0]) && is_string($opt[0]) && trim($opt[0]) !== '') {
                        $options[] = trim($opt[0]);
                    }
                }
            }

            $fieldType = $this->resolveFieldType($typeCode, $label, $firstInput);

            $fields[] = [
                'id' => $entryIdStr,
                'input_name' => 'entry_'.$entryIdStr,
                'entry_key' => 'entry.'.$entryIdStr,
                'label' => $label,
                'description' => $helpText,
                'type' => $fieldType,
                'required' => $required,
                'options' => $options,
                'placeholder' => $this->resolvePlaceholder($label, $fieldType),
            ];
        }

        if ($fields === []) {
            return null;
        }

        return [
            'title' => $title,
            'description' => $description,
            'action_url' => $sourceUrl ? $this->buildFormResponseUrl($sourceUrl) : null,
            'fields' => $fields,
        ];
    }

    /**
     * Submit validated form data to Google Forms /formResponse endpoint.
     *
     * @param  array<string, mixed>  $validatedInput
     */
    public function submit(RegistrationForm $form, array $validatedInput): bool
    {
        $schema = $this->getSchema($form);
        if ($schema === null) {
            return false;
        }

        $actionUrl = $schema['action_url'];
        $queryParts = [];
        foreach ($schema['fields'] as $field) {
            $inputName = $field['input_name'];
            $entryKey = $field['entry_key'];

            if (! array_key_exists($inputName, $validatedInput) || $validatedInput[$inputName] === null) {
                continue;
            }

            $value = $validatedInput[$inputName];
            if (is_array($value)) {
                foreach ($value as $item) {
                    if ($item !== null && $item !== '') {
                        $queryParts[] = rawurlencode($entryKey).'='.rawurlencode((string) $item);
                    }
                }
            } else {
                $queryParts[] = rawurlencode($entryKey).'='.rawurlencode((string) $value);
            }
        }

        try {
            $response = Http::connectTimeout(4)
                ->timeout(8)
                ->withoutRedirecting()
                ->withHeaders([
                    'User-Agent' => 'GPAnsorPortal/1.0',
                    'Referer' => (string) $form->form_url,
                ])
                ->withBody(implode('&', $queryParts), 'application/x-www-form-urlencoded')
                ->post($actionUrl);

            return $response->ok() && $this->hasSubmissionConfirmation($response->body());
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Google's viewer HTML is not a documented response API. Recognize its confirmation elements
     * independently of localized or custom message text, treating unknown markup as unconfirmed.
     */
    private function hasSubmissionConfirmation(string $html): bool
    {
        if (trim($html) === '') {
            return false;
        }

        $document = new DOMDocument;
        if (! $document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            return false;
        }

        return (bool) (new DOMXPath($document))->evaluate(
            "boolean(//div[contains(concat(' ', normalize-space(@class), ' '), ' vHW8K ')"
            ." or contains(concat(' ', normalize-space(@class), ' '), ' freebirdFormviewerViewResponseConfirmationMessage ')])"
        );
    }

    /**
     * Convert a Google Form /viewform URL into its /formResponse POST endpoint.
     */
    public function buildFormResponseUrl(string $url): ?string
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }

        $parsed = parse_url($trimmed);
        if (! is_array($parsed) || strtolower($parsed['host'] ?? '') !== 'docs.google.com') {
            return null;
        }

        $path = $parsed['path'] ?? '';
        if (preg_match('#^(/forms/d/e/[^/]+|/forms/d/[^/]+)#', $path, $matches)) {
            return 'https://docs.google.com'.$matches[1].'/formResponse';
        }

        return null;
    }

    private function fetchFormHtml(string $url): ?string
    {
        if (! GoogleFormUrlNormalizer::isAllowedFormUrl($url)) {
            return null;
        }

        try {
            $response = Http::timeout(6)
                ->withHeaders([
                    'User-Agent' => 'GPAnsorPortal/1.0',
                    'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                ])
                ->get($url);

            if ($response->successful()) {
                return $response->body();
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $firstInput
     */
    private function resolveFieldType(?int $typeCode, string $label, array $firstInput): string
    {
        return match ($typeCode) {
            1 => 'textarea',
            2 => 'radio',
            3 => 'select',
            4 => 'checkbox',
            9 => 'date',
            10 => 'time',
            default => $this->resolveTextSubtype($label, $firstInput),
        };
    }

    /**
     * @param  array<int, mixed>  $firstInput
     */
    private function resolveTextSubtype(string $label, array $firstInput): string
    {
        $lowerLabel = mb_strtolower($label);

        if (str_contains($lowerLabel, 'email') || str_contains($lowerLabel, 'pos-el') || str_contains($lowerLabel, 'surel')) {
            return 'email';
        }

        // Check Google Form validation rule [2, 102] (Email validation)
        $validations = $firstInput[4] ?? null;
        if (is_array($validations)) {
            foreach ($validations as $rule) {
                if (is_array($rule) && ($rule[0] ?? null) === 2 && ($rule[1] ?? null) === 102) {
                    return 'email';
                }
            }
        }

        if (
            str_contains($lowerLabel, 'telepon')
            || str_contains($lowerLabel, 'whatsapp')
            || str_contains($lowerLabel, 'no. hp')
            || str_contains($lowerLabel, 'nomor hp')
            || str_contains($lowerLabel, 'handphone')
        ) {
            return 'tel';
        }

        return 'text';
    }

    private function resolvePlaceholder(string $label, string $fieldType): string
    {
        return match ($fieldType) {
            'email' => 'nama@email.com',
            'tel' => 'Contoh: 081234567890',
            'textarea' => 'Tuliskan '.mb_strtolower($label).' lengkap Anda...',
            default => 'Masukkan '.mb_strtolower($label).' Anda',
        };
    }
}
