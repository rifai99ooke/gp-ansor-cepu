<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Body;
use App\Models\Gallery;
use App\Models\Officer;
use App\Models\Post;
use App\Models\RegistrationForm;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_public_routes_render_successfully_with_seed_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $berita = Post::query()->published()->ofType(PostType::Berita)->firstOrFail();
        $officer = Officer::query()->firstOrFail();
        $body = Body::query()->firstOrFail();
        $form = RegistrationForm::query()->active()->firstOrFail();

        $routes = [
            '/',
            '/berita',
            '/berita/'.$berita->slug,
            '/profil/visi-misi',
            '/profil/struktur-organisasi',
            '/profil/struktur-organisasi/'.$officer->slug,
            '/badan-lembaga',
            '/badan-lembaga/'.$body->slug,
            '/galeri',
            '/hubungi-kami',
            '/pendaftaran',
            '/program',
            '/dokumen',
            '/sitemap.xml',
        ];

        foreach ($routes as $uri) {
            $response = $this->get($uri);
            $response->assertStatus(200, "Failed asserting that GET {$uri} returned 200.");
        }

        $this->get('/pendaftaran')
            ->assertStatus(200)
            ->assertSee($form->title);
    }

    public function test_search_and_pagination_work_on_berita_and_beranda(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Search on Beranda
        $this->get('/?q=Ramadhan')
            ->assertStatus(200)
            ->assertSee('Posko Ramadhan');

        // Pagination on Beranda (6 per page, 30 total)
        $this->get('/?page=2')
            ->assertStatus(200);

        // Search on Berita
        $this->get('/berita?q=Banser')
            ->assertStatus(200)
            ->assertSee('Banser');

        // Pagination on Berita (9 per page, 30 total)
        $this->get('/berita?page=2')
            ->assertStatus(200);

        // Empty search state on Berita
        $this->get('/berita?q=KataKunciYangTidakAdaSamaSekali999')
            ->assertStatus(200)
            ->assertSee('Belum ada berita');

        // Pagination on Galeri (max 9 per page)
        Gallery::factory()->count(3)->create();
        $this->get('/galeri')
            ->assertStatus(200)
            ->assertSee('?page=2');
        $this->get('/galeri?page=2')
            ->assertStatus(200);
    }

    public function test_post_views_count_increments_once_per_session(): void
    {
        $post = Post::factory()->create(['views_count' => 10]);

        $this->get('/berita/'.$post->slug)->assertStatus(200);
        $this->assertSame(11, $post->fresh()->views_count);

        // Second visit in the same session does not increment again
        $this->get('/berita/'.$post->slug)->assertStatus(200);
        $this->assertSame(11, $post->fresh()->views_count);
    }

    public function test_html_sanitization_and_auto_excerpt_on_post_save(): void
    {
        $post = Post::create([
            'type' => PostType::Berita,
            'title' => 'Uji Sanitasi HTML dan Excerpt Otomatis',
            'content' => '<p>Paragraf pembuka yang aman untuk diuji.</p><script>alert("xss")</script>',
            'status' => PostStatus::Published,
        ]);

        $this->assertStringNotContainsString('<script>', $post->content);
        $this->assertStringContainsString('Paragraf pembuka yang aman', (string) $post->excerpt);
    }

    public function test_scheduled_posts_are_excluded_until_published_at_is_reached(): void
    {
        $futurePost = Post::factory()->create([
            'title' => 'Berita Terjadwal Masa Depan',
            'status' => PostStatus::Published,
            'published_at' => now()->addDay(),
        ]);

        $this->assertFalse(Post::query()->published()->whereKey($futurePost->id)->exists());
        $this->get('/berita/'.$futurePost->slug)->assertNotFound();
    }

    public function test_custom_404_page_is_rendered(): void
    {
        $this->get('/halaman-tidak-ditemukan-404')
            ->assertStatus(404)
            ->assertSee('Halaman Tidak Ditemukan');
    }

    public function test_repeated_requests_with_database_cache_store_succeed(): void
    {
        config(['cache.default' => 'database']);

        Post::factory()->featured()->create();

        // First request populates cache; second request reads from serialized database cache
        $this->get('/')->assertStatus(200);
        $this->get('/')->assertStatus(200);
        $this->get('/berita')->assertStatus(200);
        $this->get('/berita')->assertStatus(200);
    }

    public function test_models_auto_assign_sort_order_and_deduplicate_slugs(): void
    {
        $firstOfficer = Officer::create([
            'name' => 'H. Tokoh Duplikat',
            'position' => 'Ketua Bidang',
        ]);

        $secondOfficer = Officer::create([
            'name' => 'H. Tokoh Duplikat',
            'position' => 'Wakil Ketua',
        ]);

        $this->assertSame(1, $firstOfficer->fresh()->sort_order);
        $this->assertSame(2, $secondOfficer->fresh()->sort_order);
        $this->assertSame('h-tokoh-duplikat', $firstOfficer->slug);
        $this->assertSame('h-tokoh-duplikat-2', $secondOfficer->slug);
    }

    public function test_home_registration_banner_respects_open_and_close_dates(): void
    {
        $form = RegistrationForm::create([
            'title' => 'Pendaftaran Kader',
            'form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSdBannerTest/viewform',
            'opens_at' => now()->subDays(10),
            'closes_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertStatus(200)
            ->assertDontSee('Bergabung Bersama Kami');

        $form->update([
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addDays(5),
        ]);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Bergabung Bersama Kami');
    }

    public function test_search_escapes_sqlite_like_wildcards(): void
    {
        Post::factory()->create([
            'title' => 'Berita Reguler Tanpa Simbol Persen',
            'excerpt' => 'Ringkasan biasa',
            'content' => '<p>Isi biasa</p>',
        ]);

        Post::factory()->create([
            'title' => 'Capaian 100% Kaderisasi Nasional',
            'excerpt' => 'Ringkasan persen',
            'content' => '<p>Isi persen</p>',
        ]);

        $results = Post::query()->search('100%')->pluck('title')->all();

        $this->assertSame(['Capaian 100% Kaderisasi Nasional'], $results);
    }
}
