<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Filament\Resources\Bodies\Pages\CreateBody;
use App\Filament\Resources\Galleries\Pages\CreateGallery;
use App\Filament\Resources\Officers\Pages\CreateOfficer;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_require_authentication(): void
    {
        $protectedRoutes = [
            '/admin',
            '/admin/posts',
            '/admin/officers',
            '/admin/bodies',
            '/admin/galleries',
            '/admin/pages',
            '/admin/pendaftaran',
            '/admin/users',
            '/admin/pengaturan',
        ];

        foreach ($protectedRoutes as $uri) {
            $this->get($uri)->assertRedirect('/admin/login');
        }

        $this->get('/admin/login')->assertStatus(200);
    }

    public function test_authenticated_admin_can_access_all_filament_resources_and_settings(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->firstOrFail();

        $adminRoutes = [
            '/admin',
            '/admin/posts',
            '/admin/officers',
            '/admin/bodies',
            '/admin/galleries',
            '/admin/pages',
            '/admin/pendaftaran',
            '/admin/users',
            '/admin/pengaturan',
        ];

        foreach ($adminRoutes as $uri) {
            $this->actingAs($admin)
                ->get($uri)
                ->assertStatus(200, "Failed asserting that authenticated admin can GET {$uri}.");
        }
    }

    public function test_creating_post_when_authenticated_populates_author_id(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin);

        $post = Post::create([
            'type' => PostType::Berita,
            'title' => 'Berita Oleh Admin Terautentikasi',
            'content' => '<p>Isi berita resmi.</p>',
            'status' => PostStatus::Published,
        ]);

        $this->assertSame($admin->id, $post->fresh()->author_id);
    }

    public function test_setting_helper_caches_and_clears_cache_on_update(): void
    {
        Setting::clearCache();

        $this->assertSame('GP Ansor', setting('site_name'));

        Setting::setValue('site_name', 'GP Ansor Pusat');
        $this->assertSame('GP Ansor Pusat', setting('site_name'));
    }

    public function test_empty_settings_do_not_revert_to_default_values_and_hide_social_links(): void
    {
        Setting::clearCache();

        $this->assertSame('https://www.tiktok.com/@gpansor1934', setting('social_tiktok_url'));

        Setting::setValue('social_tiktok_url', '');
        Setting::setValue('social_tiktok_handle', '');
        Setting::setValue('social_facebook_url', '');
        Setting::setValue('social_facebook_handle', '');
        Setting::setValue('social_instagram_url', '');
        Setting::setValue('social_instagram_handle', '');
        Setting::setValue('social_youtube_url', '');
        Setting::setValue('social_youtube_handle', '');

        $this->assertSame('', setting('social_tiktok_url'));
        $this->assertSame('', setting('social_tiktok_handle'));

        $this->get('/hubungi-kami')
            ->assertStatus(200)
            ->assertDontSee('https://www.tiktok.com/@gpansor1934')
            ->assertSee('Belum ada akun media sosial yang ditambahkan.');
    }

    public function test_map_embed_url_validates_allowed_google_maps_hosts_only(): void
    {
        $this->assertTrue(Setting::isAllowedMapEmbedUrl('https://maps.google.com/maps?q=Jakarta&output=embed'));
        $this->assertTrue(Setting::isAllowedMapEmbedUrl('<iframe src="https://www.google.com/maps/embed?pb=123" width="600"></iframe>'));
        $this->assertFalse(Setting::isAllowedMapEmbedUrl('https://evil.example.com/maps?output=embed'));
        $this->assertFalse(Setting::isAllowedMapEmbedUrl('http://maps.google.com/maps?q=Jakarta'));

        Setting::setValue('map_embed_url', 'https://evil.example.com/maps');

        $this->get('/hubungi-kami')
            ->assertStatus(200)
            ->assertDontSee('evil.example.com');
    }

    public function test_create_another_feature_is_disabled_across_filament_create_pages(): void
    {
        $this->assertFalse((new CreatePost)->canCreateAnother());
        $this->assertFalse((new CreateOfficer)->canCreateAnother());
        $this->assertFalse((new CreateBody)->canCreateAnother());
        $this->assertFalse((new CreateGallery)->canCreateAnother());
        $this->assertFalse((new CreateUser)->canCreateAnother());
        $this->assertFalse(CreateAction::make()->canCreateAnother());
    }

    public function test_code_block_toolbar_button_is_disabled_on_rich_editor(): void
    {
        $post = new CreatePost;
        $schema = PostForm::configure(Schema::make($post));
        $editor = $schema->getComponent(fn ($c) => $c instanceof RichEditor);

        $this->assertNotNull($editor);
        $flatToolbar = array_merge(...$editor->getToolbarButtons());
        $this->assertNotContains('codeBlock', $flatToolbar);
    }
}
