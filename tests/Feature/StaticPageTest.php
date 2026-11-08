<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaticPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_visi_misi_page_renders_content(): void
    {
        Page::factory()->create([
            'slug' => 'visi-misi',
            'title' => 'Visi & Misi',
            'content' => '<p>Mewujudkan generasi muda.</p>',
        ]);

        $this->get('/profil/visi-misi')
            ->assertStatus(200)
            ->assertSee('Visi')
            ->assertSee('Mewujudkan generasi muda.');
    }

    public function test_program_page_renders_content(): void
    {
        Page::factory()->create([
            'slug' => 'program',
            'title' => 'Program Kerja',
            'content' => '<p>Program kaderisasi nasional.</p>',
        ]);

        $this->get('/program')
            ->assertStatus(200)
            ->assertSee('Program Kerja')
            ->assertSee('Program kaderisasi nasional.');
    }

    public function test_dokumen_page_renders_content(): void
    {
        Page::factory()->create([
            'slug' => 'dokumen',
            'title' => 'Dokumen Organisasi',
            'content' => '<p>Peraturan Dasar dan Peraturan Rumah Tangga.</p>',
        ]);

        $this->get('/dokumen')
            ->assertStatus(200)
            ->assertSee('Dokumen Organisasi')
            ->assertSee('Peraturan Dasar dan Peraturan Rumah Tangga.');
    }

    public function test_static_page_returns_404_when_slug_missing_from_database(): void
    {
        // No pages seeded — controller calls firstOrFail()
        $this->get('/profil/visi-misi')->assertStatus(404);
        $this->get('/program')->assertStatus(404);
        $this->get('/dokumen')->assertStatus(404);
    }

    public function test_contact_page_renders_successfully(): void
    {
        $this->get('/hubungi-kami')
            ->assertStatus(200);
    }
}
