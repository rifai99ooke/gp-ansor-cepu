<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Officer;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_index_renders_all_officers_in_sort_order(): void
    {
        $second = Officer::factory()->create(['name' => 'Budi Santoso', 'position' => 'Sekretaris', 'sort_order' => 2]);
        $first = Officer::factory()->create(['name' => 'Ahmad Fauzi', 'position' => 'Ketua Umum', 'sort_order' => 1]);

        $response = $this->get('/profil/struktur-organisasi');

        $response->assertStatus(200)
            ->assertSee('Ahmad Fauzi')
            ->assertSee('Budi Santoso')
            ->assertSee('Ketua Umum');
    }

    public function test_officer_index_filters_by_search_query(): void
    {
        Officer::factory()->create(['name' => 'Ahmad Fauzi', 'position' => 'Ketua Umum']);
        Officer::factory()->create(['name' => 'Budi Santoso', 'position' => 'Sekretaris']);

        $response = $this->get('/profil/struktur-organisasi?q=Budi');

        $response->assertStatus(200)
            ->assertSee('Budi Santoso')
            ->assertDontSee('Ahmad Fauzi');
    }

    public function test_officer_index_filters_by_position(): void
    {
        Officer::factory()->create(['name' => 'Ahmad Fauzi', 'position' => 'Ketua Umum']);
        Officer::factory()->create(['name' => 'Budi Santoso', 'position' => 'Sekretaris Jenderal']);

        $response = $this->get('/profil/struktur-organisasi?q=Sekretaris');

        $response->assertStatus(200)
            ->assertSee('Budi Santoso')
            ->assertDontSee('Ahmad Fauzi');
    }

    public function test_officer_show_displays_detail_and_other_officers(): void
    {
        $main = Officer::factory()->create(['name' => 'Ahmad Fauzi', 'position' => 'Ketua Umum']);
        $other = Officer::factory()->create(['name' => 'Budi Santoso', 'position' => 'Sekretaris']);

        $response = $this->get('/profil/struktur-organisasi/'.$main->slug);

        $response->assertStatus(200)
            ->assertSee('Ahmad Fauzi')
            ->assertSee('Ketua Umum')
            ->assertSee('Budi Santoso');
    }

    public function test_officer_show_returns_404_for_nonexistent_slug(): void
    {
        $this->get('/profil/struktur-organisasi/slug-tidak-ada')
            ->assertStatus(404);
    }

    public function test_officer_empty_search_returns_all(): void
    {
        Officer::factory()->count(3)->create();

        $this->get('/profil/struktur-organisasi?q=')
            ->assertStatus(200);
    }

    public function test_empty_search_results_have_a_reset_link_to_the_complete_directory(): void
    {
        Officer::factory()->create(['name' => 'Ahmad Fauzi']);
        Officer::factory()->create(['name' => 'Budi Santoso']);
        $response = $this->get('/profil/struktur-organisasi?q=TidakAdaHasil')->assertOk();
        $document = new DOMDocument;
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);

        $links = (new DOMXPath($document))->query('//a[@aria-label="Hapus filter pencarian"]');

        $this->assertSame(1, $links->length);
        $this->assertSame(route('profil.struktur'), $links->item(0)->getAttribute('href'));
        $this->get($links->item(0)->getAttribute('href'))
            ->assertOk()->assertSee('Ahmad Fauzi')->assertSee('Budi Santoso');
    }
}
