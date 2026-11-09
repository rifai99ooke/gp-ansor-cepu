<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Galleries\Pages\CreateGallery;
use App\Filament\Resources\Galleries\Pages\ListGalleries;
use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class GalleryOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_upload_is_first_in_admin_and_on_the_first_public_page(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $existingPhotos = Gallery::factory()->count(10)->sequence(fn ($sequence): array => [
            'sort_order' => ($sequence->index + 1) * 10,
            'event_date' => '2026-11-09',
        ])->create();

        Livewire::test(CreateGallery::class)->fillForm([
            'title' => 'Foto Unggahan Terbaru',
            'event_date' => '2000-01-01',
            'image_path' => UploadedFile::fake()->createWithContent('foto.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a5e8AAAAASUVORK5CYII=')),
        ])->call('create')->assertHasNoFormErrors();

        $newPhoto = Gallery::query()->where('title', 'Foto Unggahan Terbaru')->sole();
        $expectedFirstPage = [$newPhoto->id, ...$existingPhotos->take(8)->modelKeys()];
        $this->get('/galeri')->assertOk()
            ->assertViewHas('galleries', fn (LengthAwarePaginator $galleries): bool => $galleries->getCollection()->modelKeys() === $expectedFirstPage);
        Livewire::test(ListGalleries::class)
            ->assertCanSeeTableRecords([$newPhoto, ...$existingPhotos], inOrder: true);
        $this->assertSame(range(10, 100, 10), $existingPhotos->map(fn (Gallery $photo): int => $photo->fresh()->sort_order)->all());
    }

    public function test_consecutive_insertions_place_the_latest_photo_first_in_an_initially_empty_gallery(): void
    {
        Storage::fake('public');
        $firstPhoto = Gallery::factory()->create(['sort_order' => null, 'event_date' => '2026-11-09']);

        $secondPhoto = Gallery::factory()->create(['sort_order' => null, 'event_date' => '2000-01-01']);

        $this->assertSame([$secondPhoto->id, $firstPhoto->id], Gallery::query()->ordered()->pluck('id')->all());
    }

    public function test_new_uploads_preserve_dragged_order_and_edits_do_not_move_photos(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $firstPhoto = Gallery::factory()->create(['sort_order' => null]);
        $secondPhoto = Gallery::factory()->create(['sort_order' => null]);
        Livewire::test(ListGalleries::class)->call('reorderTable', [(string) $firstPhoto->id, (string) $secondPhoto->id]);

        $newPhoto = Gallery::factory()->create(['sort_order' => null]);
        $firstPhoto->update(['title' => 'Judul Diperbarui']);

        $this->assertSame([$newPhoto->id, $firstPhoto->id, $secondPhoto->id], Gallery::query()->ordered()->pluck('id')->all());
        $this->assertSame(1, $firstPhoto->fresh()->sort_order);
        $this->assertSame(2, $secondPhoto->fresh()->sort_order);
    }
}
