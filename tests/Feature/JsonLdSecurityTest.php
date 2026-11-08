<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Setting;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JsonLdSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_json_ld_preserves_site_names_without_injecting_scripts(): void
    {
        Http::preventStrayRequests();
        $siteName = 'Ansor "Muda" & Putra </script><script>window.jsonLdInjected=true</script> — Indonesia';
        Setting::setValue('site_name', $siteName);
        Setting::setValue('site_full_name', $siteName);

        $response = $this->get('/hubungi-kami')->assertOk();

        $schemas = $this->jsonLdSchemas($response->getContent());
        $this->assertSame(['Organization'], array_keys($schemas));
        $this->assertSame($siteName, $schemas['Organization']['name']);
        $this->assertSame($siteName, $schemas['Organization']['alternateName']);
    }

    public function test_news_article_json_ld_preserves_post_metadata_without_injecting_scripts(): void
    {
        Http::preventStrayRequests();
        $title = 'Berita </script><script>window.jsonLdInjected=true</script> "Kader" & Indonesia';
        $excerpt = 'Ringkasan </script><script>window.jsonLdInjected=true</script>';
        $authorName = 'Redaksi </script><script>window.jsonLdInjected=true</script>';
        $post = Post::factory()->create([
            'title' => $title,
            'excerpt' => $excerpt,
            'author_name' => $authorName,
        ]);

        $response = $this->get('/berita/'.$post->slug)->assertOk();

        $schemas = $this->jsonLdSchemas($response->getContent());
        $this->assertSame(['Organization', 'NewsArticle'], array_keys($schemas));
        $this->assertSame($title, $schemas['NewsArticle']['headline']);
        $this->assertSame($excerpt, $schemas['NewsArticle']['description']);
        $this->assertSame($authorName, $schemas['NewsArticle']['author']['name']);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function jsonLdSchemas(string $html): array
    {
        $document = new DOMDocument;
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($document);
        $this->assertSame(0, $xpath->query('//script[normalize-space(.)="window.jsonLdInjected=true"]')->length);

        $schemas = [];
        foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
            $schema = json_decode($script->textContent, true, 512, JSON_THROW_ON_ERROR);
            $schemas[$schema['@type']] = $schema;
        }

        return $schemas;
    }
}
