<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Body;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BodyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_body_index_renders_all_bodies_in_sort_order(): void
    {
        Body::factory()->create(['name' => 'Banser', 'sort_order' => 2]);
        Body::factory()->create(['name' => 'Rijalul Ansor', 'sort_order' => 1]);

        $response = $this->get('/badan-lembaga');

        $response->assertStatus(200)
            ->assertSee('Banser')
            ->assertSee('Rijalul Ansor');
    }

    public function test_body_index_filters_by_search_query(): void
    {
        Body::factory()->create(['name' => 'Banser', 'description' => '<p>Barisan Ansor Serbaguna</p>']);
        Body::factory()->create(['name' => 'Rijalul Ansor', 'description' => '<p>Majelis Dzikir dan Shalawat</p>']);

        $response = $this->get('/badan-lembaga?q=Banser');

        $response->assertStatus(200)
            ->assertSee('Banser')
            ->assertDontSee('Rijalul Ansor');
    }

    public function test_body_show_displays_detail_and_other_bodies(): void
    {
        $main = Body::factory()->create(['name' => 'Banser']);
        $other = Body::factory()->create(['name' => 'Rijalul Ansor']);

        $response = $this->get('/badan-lembaga/'.$main->slug);

        $response->assertStatus(200)
            ->assertSee('Banser')
            ->assertSee('Rijalul Ansor');
    }

    public function test_body_show_returns_404_for_nonexistent_slug(): void
    {
        $this->get('/badan-lembaga/slug-tidak-ada')
            ->assertStatus(404);
    }

    public function test_search_reset_link_loads_the_complete_directory(): void
    {
        Body::factory()->create(['name' => 'Banser']);
        Body::factory()->create(['name' => 'Rijalul Ansor']);
        $response = $this->get('/badan-lembaga?q=Banser')->assertOk();
        $document = new DOMDocument;
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);

        $links = (new DOMXPath($document))->query('//a[@aria-label="Hapus filter pencarian"]');

        $this->assertSame(1, $links->length);
        $this->assertSame(route('badan-lembaga.index'), $links->item(0)->getAttribute('href'));
        $this->get($links->item(0)->getAttribute('href'))
            ->assertOk()->assertSee('Banser')->assertSee('Rijalul Ansor');
    }
}
