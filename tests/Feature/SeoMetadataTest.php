<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\Post;
use App\Models\Ranting;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('paginatedListings')]
    public function test_paginated_listings_use_a_canonical_for_the_current_page(string $route, string $model, int $recordCount): void
    {
        Http::preventStrayRequests();
        $model::factory()->count($recordCount)->create();

        $response = $this->get(route($route, ['page' => 2, 'utm_source' => 'newsletter']))->assertOk();
        $document = new DOMDocument;
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($document);

        $this->assertSame(route($route).'?page=2', $xpath->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertSame(route($route).'?page=2', $xpath->evaluate('string(//meta[@property="og:url"]/@content)'));
        Http::assertNothingSent();
    }

    public function test_first_page_canonical_omits_pagination_search_and_tracking_parameters(): void
    {
        Http::preventStrayRequests();

        $response = $this->get('/berita?page=1&q=Ansor&utm_source=newsletter')->assertOk();
        $document = new DOMDocument;
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);

        $this->assertSame(route('berita.index'), (new DOMXPath($document))->evaluate('string(//link[@rel="canonical"]/@href)'));
        Http::assertNothingSent();
    }

    public function test_sitemap_includes_the_ranting_directory_and_details(): void
    {
        Http::preventStrayRequests();
        $ranting = Ranting::factory()->create(['name' => 'Ranting Cepu']);

        $response = $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $document = new DOMDocument;
        $this->assertTrue($document->loadXML($response->getContent()));
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $locations = array_map(fn ($node): string => $node->textContent, iterator_to_array($xpath->query('//s:url/s:loc')));

        $this->assertContains(route('ranting.index'), $locations);
        $this->assertContains(route('ranting.show', $ranting->slug), $locations);
        $this->assertSame($ranting->updated_at->toAtomString(), $xpath->evaluate('string(//s:url[s:loc="'.route('ranting.show', $ranting->slug).'"]/s:lastmod)'));
        Http::assertNothingSent();
    }

    /** @return array<string, array{string, class-string, int}> */
    public static function paginatedListings(): array
    {
        return [
            'home' => ['home', Post::class, 7],
            'news' => ['berita.index', Post::class, 10],
            'gallery' => ['galeri.index', Gallery::class, 10],
        ];
    }
}
