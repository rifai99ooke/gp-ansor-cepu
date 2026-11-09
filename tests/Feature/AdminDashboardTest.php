<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\DashboardBannerWidget;
use App\Filament\Widgets\LatestPostsWidget;
use App\Models\Body;
use App\Models\Gallery;
use App\Models\Officer;
use App\Models\Post;
use App\Models\RegistrationForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_counts_published_content_and_links_to_management_pages(): void
    {
        Storage::fake('public');
        $this->freezeTime();
        $this->actingAs(User::factory()->create(['name' => 'Admin Cepu']));
        Post::factory()->create(['cover_image' => null]);
        Post::factory()->draft()->create(['cover_image' => null]);
        Post::factory()->create(['cover_image' => null, 'published_at' => now()->addDay()]);
        Officer::factory()->count(2)->create();
        Body::factory()->count(3)->create();
        Gallery::factory()->count(4)->create();
        RegistrationForm::factory()->create(['title' => 'Pendaftaran Aktif']);

        Livewire::test(DashboardBannerWidget::class)
            ->assertViewHas('beritaCount', 1)->assertViewHas('draftCount', 1)
            ->assertViewHas('officersCount', 2)->assertViewHas('bodiesCount', 3)->assertViewHas('galleriesCount', 4)
            ->assertViewHas('formOpen', true)->assertSee('Admin Cepu')->assertSee('Pendaftaran Aktif')
            ->assertSee(url('/admin/posts/create'))->assertSee(url('/admin/galleries/create'));
    }

    public function test_dashboard_latest_articles_are_limited_and_link_to_edit_pages(): void
    {
        Storage::fake('public');
        $this->freezeTime();
        $this->actingAs(User::factory()->create());
        $posts = Post::factory()->count(7)->sequence(fn ($sequence): array => [
            'title' => 'Berita nomor '.$sequence->index, 'published_at' => now()->subDays($sequence->index), 'cover_image' => null,
        ])->create();

        Livewire::test(LatestPostsWidget::class)->assertCanSeeTableRecords($posts->take(6), inOrder: true)
            ->assertCanNotSeeTableRecords([$posts->last()])->assertSee(url('/admin/posts/'.$posts->first()->id.'/edit'));
    }

    public function test_ranting_and_create_pages_require_authentication(): void
    {
        foreach (['/admin/rantings', '/admin/posts/create', '/admin/officers/create', '/admin/bodies/create', '/admin/rantings/create', '/admin/galleries/create', '/admin/users/create'] as $path) {
            $this->get($path)->assertRedirect('/admin/login');
        }
    }
}
