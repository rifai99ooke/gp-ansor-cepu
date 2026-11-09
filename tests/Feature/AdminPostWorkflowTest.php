<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPostWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_published_article_with_cover_and_author(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['name' => 'Redaksi Cepu']);
        $this->actingAs($admin);

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Kegiatan Kader Cepu',
                'content' => '<p style="text-align: center"><strong>Berita resmi</strong></p><p></p><p></p><p>Paragraf berikutnya</p><script>alert(1)</script>',
                'cover_image' => UploadedFile::fake()->createWithContent('sampul.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAABAAAAAJCAIAAAC0SDtlAAAAFklEQVR4nGNkaGAgCTCRpnxUA400AADsugCSkBK5aAAAAABJRU5ErkJggg==')),
                'status' => 'published',
                'is_featured' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $post = Post::query()->sole();
        $this->assertSame($admin->id, $post->author_id);
        $this->assertSame('Redaksi Cepu', $post->author_name);
        $this->assertSame(PostStatus::Published, $post->status);
        $this->assertTrue($post->is_featured);
        $this->assertNotNull($post->published_at);
        Storage::disk('public')->assertExists($post->cover_image);
        $this->get($post->url())->assertOk()
            ->assertSee('<p style="text-align: center"><strong>Berita resmi</strong></p><p></p><p></p><p>Paragraf berikutnya</p>', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_saves_a_draft_without_publishing_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreatePost::class)
            ->fillForm(['title' => 'Draf Rahasia', 'content' => '<p>Belum siap terbit</p>', 'status' => 'draft'])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::query()->sole();
        $this->assertSame(PostStatus::Draft, $post->status);
        $this->assertNull($post->published_at);
        $this->get($post->url())->assertNotFound();
        $this->get('/berita')->assertOk()->assertDontSee('Draf Rahasia');
    }

    public function test_edits_and_publishes_a_draft_without_changing_its_url(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $post = Post::factory()->draft()->create(['cover_image' => null]);
        $originalSlug = $post->slug;

        Livewire::test(EditPost::class, ['record' => $post->id])
            ->fillForm(['title' => 'Judul Diperbarui', 'content' => '<p>Isi baru</p>', 'status' => 'published', 'is_featured' => true])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $post->refresh();
        $this->assertSame($originalSlug, $post->slug);
        $this->assertSame(PostStatus::Published, $post->status);
        $this->assertTrue($post->is_featured);
        $this->get($post->url())->assertOk()->assertSee('Judul Diperbarui')->assertSee('Isi baru');
    }

    public function test_requires_a_title_and_article_content(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreatePost::class)
            ->fillForm(['title' => '', 'content' => null])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required', 'content'])
            ->assertNotNotified();

        $this->assertDatabaseCount('posts', 0);
    }

    #[DataProvider('invalidPostValues')]
    public function test_rejects_invalid_article_values(string $field, string $value, string $rule): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreatePost::class)
            ->fillForm(['title' => 'Berita Baru', 'content' => '<p>Isi</p>', $field => $value])
            ->call('create')
            ->assertHasFormErrors([$field => $rule]);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_searches_article_titles(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $matching = Post::factory()->create(['title' => 'Pelatihan Kader Cepu', 'cover_image' => null]);
        $other = Post::factory()->create(['title' => 'Bakti Sosial', 'cover_image' => null]);

        Livewire::test(ListPosts::class)->searchTable('Pelatihan')
            ->assertCanSeeTableRecords([$matching])->assertCanNotSeeTableRecords([$other]);
    }

    public function test_filters_article_status_and_featured_flag(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $featured = Post::factory()->featured()->create(['cover_image' => null]);
        $ordinary = Post::factory()->create(['cover_image' => null]);
        $draft = Post::factory()->draft()->create(['cover_image' => null]);

        Livewire::test(ListPosts::class)
            ->filterTable('status', 'draft')->assertCanSeeTableRecords([$draft])->assertCanNotSeeTableRecords([$featured, $ordinary])
            ->removeTableFilters()
            ->filterTable('is_featured', true)->assertCanSeeTableRecords([$featured])->assertCanNotSeeTableRecords([$ordinary, $draft])
            ->removeTableFilters()->assertCanSeeTableRecords([$featured, $ordinary, $draft]);
    }

    public function test_sorts_articles_by_views_and_paginates_the_list(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $posts = Post::factory()->count(11)->sequence(fn ($sequence): array => ['views_count' => $sequence->index, 'cover_image' => null])->create();

        Livewire::test(ListPosts::class)->set('tableRecordsPerPage', 5)->sortTable('views_count', 'desc')
            ->assertCanSeeTableRecords($posts->reverse()->take(5), inOrder: true)
            ->assertCanNotSeeTableRecords($posts->take(6))
            ->call('gotoPage', 3)->assertCanSeeTableRecords([$posts->first()]);
    }

    public function test_deletes_an_article_from_its_edit_page(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $post = Post::factory()->create(['cover_image' => null]);

        Livewire::test(EditPost::class, ['record' => $post->id])->callAction(DeleteAction::class)->assertNotified();

        $this->assertModelMissing($post);
        $this->get($post->url())->assertNotFound();
    }

    public function test_deletes_an_article_from_the_list(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $post = Post::factory()->create(['cover_image' => null]);

        Livewire::test(ListPosts::class)->callAction(TestAction::make(DeleteAction::class)->table($post))->assertNotified();

        $this->assertModelMissing($post);
    }

    public function test_bulk_deletion_removes_only_selected_articles(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $selected = Post::factory()->count(2)->create(['cover_image' => null]);
        $remaining = Post::factory()->create(['cover_image' => null]);

        Livewire::test(ListPosts::class)->selectTableRecords($selected->modelKeys())
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())->assertNotified();

        foreach ($selected as $post) {
            $this->assertModelMissing($post);
        }
        $this->assertModelExists($remaining);
    }

    /** @return array<string, array{string, string, string}> */
    public static function invalidPostValues(): array
    {
        return [
            'title exceeds maximum length' => ['title', str_repeat('a', 256), 'max'],
            'unknown publication status' => ['status', 'archived', 'in'],
        ];
    }
}
