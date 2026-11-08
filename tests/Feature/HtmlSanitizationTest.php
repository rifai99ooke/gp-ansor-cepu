<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use App\Services\HtmlSanitizerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HtmlSanitizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_saved_article_keeps_alignment_after_editing_and_renders_it_publicly(): void
    {
        Http::preventStrayRequests();
        $post = Post::factory()->create([
            'content' => '<h2 style="text-align: center">Judul bagian</h2><p style="text-align: end">Isi artikel</p>',
        ]);
        $post = $post->fresh();
        $post->update(['content' => $post->content.'<p>Paragraf tambahan</p>']);

        $response = $this->get('/berita/'.$post->slug)->assertOk();

        $this->assertSame(
            '<h2 style="text-align: center">Judul bagian</h2><p style="text-align: end">Isi artikel</p><p>Paragraf tambahan</p>',
            $post->fresh()->content,
        );
        $response->assertSee('<h2 style="text-align: center">Judul bagian</h2>', false)
            ->assertSee('<p style="text-align: end">Isi artikel</p>', false);
        Http::assertNothingSent();
    }

    #[DataProvider('richTextStyles')]
    public function test_preserves_only_safe_text_alignment(string $html, string $expected): void
    {
        $sanitized = app(HtmlSanitizerService::class)->sanitize($html);

        $this->assertSame($expected, $sanitized);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function richTextStyles(): array
    {
        return [
            'paragraph start' => ['<p style="text-align: start">Isi</p>', '<p style="text-align: start">Isi</p>'],
            'heading one center' => ['<h1 style="text-align: center">Isi</h1>', '<h1 style="text-align: center">Isi</h1>'],
            'heading two end' => ['<h2 style="text-align: end">Isi</h2>', '<h2 style="text-align: end">Isi</h2>'],
            'heading three left' => ['<h3 style="text-align: left">Isi</h3>', '<h3 style="text-align: left">Isi</h3>'],
            'heading four right' => ['<h4 style="text-align: right">Isi</h4>', '<h4 style="text-align: right">Isi</h4>'],
            'heading five justify' => ['<h5 style="text-align: justify">Isi</h5>', '<h5 style="text-align: justify">Isi</h5>'],
            'heading six center' => ['<h6 style="text-align: center">Isi</h6>', '<h6 style="text-align: center">Isi</h6>'],
            'case and whitespace normalization' => ['<p style=" TEXT-ALIGN : CENTER ; ">Isi</p>', '<p style="text-align: center">Isi</p>'],
            'other CSS is stripped' => ['<p style="position: fixed; text-align: center; background: url(https://example.test/track)">Isi</p>', '<p style="text-align: center">Isi</p>'],
            'last valid alignment wins' => ['<p style="text-align: left; text-align: right">Isi</p>', '<p style="text-align: right">Isi</p>'],
            'invalid declaration does not erase valid alignment' => ['<p style="text-align: center; text-align: invalid">Isi</p>', '<p style="text-align: center">Isi</p>'],
            'unapproved alignment is removed' => ['<p style="text-align: inherit">Isi</p>', '<p>Isi</p>'],
            'malicious alignment is removed' => ['<p style="text-align: expression(alert(1))">Isi</p>', '<p>Isi</p>'],
            'CSS escape is removed' => ['<p style="text-align: \\63 enter">Isi</p>', '<p>Isi</p>'],
            'styles on inline elements are removed' => ['<span style="text-align: center; position: fixed">Isi</span>', '<span>Isi</span>'],
            'existing XSS protection remains active' => ['<p style="text-align: center" onclick="alert(1)">Isi<script>alert(1)</script><a href="javascript:alert(1)">tautan</a></p>', '<p style="text-align: center">Isi<a>tautan</a></p>'],
        ];
    }
}
