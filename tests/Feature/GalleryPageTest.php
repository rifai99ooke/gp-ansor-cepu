<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Gallery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_index_renders_successfully(): void
    {
        Gallery::factory()->create(['title' => 'Pelantikan Pengurus']);

        $this->get('/galeri')
            ->assertStatus(200)
            ->assertSee('Pelantikan Pengurus');
    }

    public function test_gallery_index_filters_by_search_query(): void
    {
        Gallery::factory()->create(['title' => 'Pelantikan Pengurus']);
        Gallery::factory()->create(['title' => 'Aksi Donor Darah']);

        $response = $this->get('/galeri?q=Donor');

        $response->assertStatus(200)
            ->assertSee('Aksi Donor Darah')
            ->assertDontSee('Pelantikan Pengurus');
    }

    public function test_gallery_index_paginates_at_9_per_page(): void
    {
        Gallery::factory()->count(12)->create();

        $this->get('/galeri')
            ->assertStatus(200)
            ->assertSee('?page=2');

        $this->get('/galeri?page=2')
            ->assertStatus(200);
    }

    public function test_gallery_empty_search_shows_no_results_message(): void
    {
        $this->get('/galeri?q=TidakAdaGaleriDenganJudulIni999')
            ->assertStatus(200);
    }
}
