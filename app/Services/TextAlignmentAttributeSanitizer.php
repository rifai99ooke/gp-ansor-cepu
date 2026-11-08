<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

class TextAlignmentAttributeSanitizer implements AttributeSanitizerInterface
{
    public function getSupportedElements(): ?array
    {
        return null;
    }

    public function getSupportedAttributes(): ?array
    {
        return ['style'];
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        $alignment = null;

        foreach (explode(';', $value) as $declaration) {
            if (preg_match('/\A\s*text-align\s*:\s*(start|end|left|center|right|justify)\s*\z/i', $declaration, $matches) === 1) {
                $alignment = strtolower($matches[1]);
            }
        }

        return $alignment === null ? null : 'text-align: '.$alignment;
    }
}
