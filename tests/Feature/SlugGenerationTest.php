<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Body;
use App\Models\Gallery;
use App\Models\Officer;
use App\Models\Page;
use App\Models\Post;
use App\Models\Ranting;
use App\Models\RegistrationForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SlugGenerationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('titlesWithoutAnAsciiSlug')]
    public function test_posts_with_nontransliterable_titles_have_unique_reachable_urls(string $title): void
    {
        $first = Post::factory()->create(['title' => $title, 'slug' => null, 'cover_image' => null]);
        $second = Post::factory()->create(['title' => $title, 'slug' => null, 'cover_image' => null]);

        $this->assertNotSame('', $first->slug);
        $this->assertNotSame($first->slug, $second->slug);
        $this->get('/')->assertOk();
        $this->get('/berita')->assertOk();
        $this->get('/sitemap.xml')->assertOk()->assertSee($first->url());
        $this->get($first->url())->assertOk()->assertViewHas('post', fn (Post $post): bool => $post->is($first));
        $this->get($second->url())->assertOk()->assertViewHas('post', fn (Post $post): bool => $post->is($second));
    }

    /** @return array<string, array{string}> */
    public static function titlesWithoutAnAsciiSlug(): array
    {
        return [
            'punctuation' => ['---'],
            'emoji' => ['🕌'],
            'nontransliterable text' => ['你好'],
        ];
    }

    public function test_fallback_slugs_do_not_collide_with_existing_titles(): void
    {
        $existing = Post::factory()->create(['title' => 'Item', 'slug' => 'item', 'cover_image' => null]);

        $post = Post::factory()->create(['title' => '---', 'slug' => null, 'cover_image' => null]);

        $this->assertNotSame('', $post->slug);
        $this->assertNotSame($existing->slug, $post->slug);
        $this->get($post->url())->assertOk()->assertViewHas('post', fn (Post $record): bool => $record->is($post));
    }

    public function test_zero_remains_a_valid_slug(): void
    {
        $post = Post::factory()->create(['title' => '0', 'slug' => null, 'cover_image' => null]);

        $this->assertSame('0', $post->slug);
        $this->get($post->url())->assertOk()->assertViewHas('post', fn (Post $record): bool => $record->is($post));
    }

    #[DataProvider('modelsWithSlugs')]
    public function test_migration_repairs_legacy_blank_slugs_without_changing_valid_urls(string $modelClass, string $sourceAttribute): void
    {
        $existing = $modelClass::factory()->create(['slug' => 'item']);
        $existingSuffix = $modelClass::factory()->create(['slug' => 'item-2']);
        $empty = $modelClass::factory()->create([$sourceAttribute => '---']);
        $whitespace = $modelClass::factory()->create([$sourceAttribute => '你好']);
        $empty->newQuery()->whereKey($empty->id)->update(['slug' => '']);
        $whitespace->newQuery()->whereKey($whitespace->id)->update(['slug' => " \t\n"]);
        $migration = require database_path('migrations/2026_11_08_162825_repair_empty_content_slugs.php');

        $migration->up();

        $expectedSlugs = ['item', 'item-2', 'item-3', 'item-4'];
        $this->assertSame($expectedSlugs, $modelClass::query()->orderBy('id')->pluck('slug')->all());
        $this->assertSame('item', $existing->fresh()->slug);
        $this->assertSame('item-2', $existingSuffix->fresh()->slug);

        $migration->up();

        $this->assertSame($expectedSlugs, $modelClass::query()->orderBy('id')->pluck('slug')->all());
    }

    /** @return array<string, array{class-string, string}> */
    public static function modelsWithSlugs(): array
    {
        return [
            'posts' => [Post::class, 'title'],
            'officers' => [Officer::class, 'name'],
            'bodies' => [Body::class, 'name'],
            'rantings' => [Ranting::class, 'name'],
            'galleries' => [Gallery::class, 'title'],
            'pages' => [Page::class, 'title'],
            'registration forms' => [RegistrationForm::class, 'title'],
        ];
    }
}
