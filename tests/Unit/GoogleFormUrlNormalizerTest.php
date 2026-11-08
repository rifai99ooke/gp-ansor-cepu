<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\GoogleFormUrlNormalizer;
use PHPUnit\Framework\TestCase;

class GoogleFormUrlNormalizerTest extends TestCase
{
    public function test_rewrites_edit_path_and_strips_publish_editor_query_param(): void
    {
        $normalizer = new GoogleFormUrlNormalizer;

        $result = $normalizer->normalize('https://docs.google.com/forms/d/e/1FAIpQLSdTest/edit?usp=publish-editor');

        $this->assertSame(
            'https://docs.google.com/forms/d/e/1FAIpQLSdTest/viewform',
            $result['form_url']
        );
        $this->assertSame(
            'https://docs.google.com/forms/d/e/1FAIpQLSdTest/viewform?embedded=true',
            $result['embed_url']
        );
    }

    public function test_validates_allowed_embed_and_form_urls(): void
    {
        $this->assertTrue(GoogleFormUrlNormalizer::isAllowedEmbedUrl('https://docs.google.com/forms/d/e/123/viewform?embedded=true'));
        $this->assertFalse(GoogleFormUrlNormalizer::isAllowedEmbedUrl('https://forms.gle/short123'));
        $this->assertFalse(GoogleFormUrlNormalizer::isAllowedEmbedUrl('https://evil.example.com/forms/d/e/123/viewform'));

        $this->assertTrue(GoogleFormUrlNormalizer::isAllowedFormUrl('https://docs.google.com/forms/d/e/123/viewform'));
        $this->assertTrue(GoogleFormUrlNormalizer::isAllowedFormUrl('https://forms.gle/short123'));
        $this->assertFalse(GoogleFormUrlNormalizer::isAllowedFormUrl('https://forms.gle/'));
        $this->assertFalse(GoogleFormUrlNormalizer::isAllowedFormUrl('http://docs.google.com/forms/d/e/123/viewform'));
    }
}
