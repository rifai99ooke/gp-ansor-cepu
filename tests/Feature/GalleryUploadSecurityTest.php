<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Galleries\Pages\CreateGallery;
use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GalleryUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('svgFilenames')]
    public function test_rejects_svg_gallery_uploads_before_public_storage(string $filename): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateGallery::class)
            ->fillForm([
                'title' => 'Dokumentasi Kegiatan',
                'image_path' => UploadedFile::fake()->createWithContent($filename, '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(document.domain)"><rect width="32" height="32"/></svg>')->mimeType('image/svg+xml'),
            ])
            ->call('create')
            ->assertHasFormErrors(['image_path']);

        $this->assertDatabaseCount('galleries', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_stores_a_valid_raster_gallery_upload(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateGallery::class)
            ->fillForm([
                'title' => 'Dokumentasi Kegiatan',
                'image_path' => UploadedFile::fake()->createWithContent('kegiatan.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a5e8AAAAASUVORK5CYII=')),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('galleries', ['title' => 'Dokumentasi Kegiatan']);
        Storage::disk('public')->assertExists(Gallery::query()->sole()->image_path);
    }

    /** @return array<string, array{string}> */
    public static function svgFilenames(): array
    {
        return [
            'SVG' => ['kegiatan.svg'],
            'SVG disguised as PNG' => ['kegiatan.png'],
        ];
    }
}
