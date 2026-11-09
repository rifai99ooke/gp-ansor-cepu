<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Bodies\Pages\CreateBody;
use App\Filament\Resources\Bodies\Pages\EditBody;
use App\Filament\Resources\Bodies\Pages\ListBodies;
use App\Filament\Resources\Galleries\Pages\CreateGallery;
use App\Filament\Resources\Galleries\Pages\EditGallery;
use App\Filament\Resources\Galleries\Pages\ListGalleries;
use App\Filament\Resources\Officers\Pages\CreateOfficer;
use App\Filament\Resources\Officers\Pages\EditOfficer;
use App\Filament\Resources\Officers\Pages\ListOfficers;
use App\Filament\Resources\Rantings\Pages\CreateRanting;
use App\Filament\Resources\Rantings\Pages\EditRanting;
use App\Filament\Resources\Rantings\Pages\ListRantings;
use App\Models\Body;
use App\Models\Gallery;
use App\Models\Officer;
use App\Models\Ranting;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminDirectoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a5e8AAAAASUVORK5CYII=';

    #[DataProvider('resources')]
    public function test_creates_a_directory_record_with_a_public_image(array $resource): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test($resource['create'])->fillForm([
            ...$resource['fields'],
            $resource['name'] => 'Data Baru Cepu',
            $resource['media'] => UploadedFile::fake()->createWithContent('foto.png', base64_decode(self::PNG)),
        ])->call('create')->assertHasNoFormErrors()->assertNotified();

        $record = $resource['model']::query()->sole();
        $this->assertSame('Data Baru Cepu', $record->getAttribute($resource['name']));
        $this->assertNotEmpty($record->slug);
        Storage::disk('public')->assertExists($record->getAttribute($resource['media']));
        $this->get($resource['public_path'])->assertOk()->assertSee('Data Baru Cepu');
    }

    #[DataProvider('resources')]
    public function test_edits_a_directory_record_and_replaces_its_image(array $resource): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $oldPath = $resource['directory'].'/old.png';
        Storage::disk('public')->put($oldPath, base64_decode(self::PNG));
        $record = $resource['model']::factory()->create([$resource['media'] => $oldPath]);
        $originalSlug = $record->slug;

        Livewire::test($resource['edit'], ['record' => $record->id])->set('data.'.$resource['media'], [])->fillForm([
            $resource['name'] => 'Data Diperbarui Cepu',
            $resource['media'] => UploadedFile::fake()->createWithContent('baru.png', base64_decode(self::PNG)),
        ])->call('save')->assertHasNoFormErrors()->assertNotified();

        $record->refresh();
        $this->assertSame('Data Diperbarui Cepu', $record->getAttribute($resource['name']));
        $this->assertSame($originalSlug, $record->slug);
        $this->assertNotSame($oldPath, $record->getAttribute($resource['media']));
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($record->getAttribute($resource['media']));
        $this->get($resource['public_path'])->assertOk()->assertSee('Data Diperbarui Cepu');
    }

    #[DataProvider('resources')]
    public function test_rejects_missing_required_directory_fields(array $resource): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test($resource['create'])->fillForm(array_fill_keys($resource['required'], null))
            ->call('create')->assertHasFormErrors(array_fill_keys($resource['required'], 'required'));

        $this->assertDatabaseCount((new $resource['model'])->getTable(), 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    #[DataProvider('resources')]
    public function test_deletes_a_directory_record_from_its_edit_page(array $resource): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $record = $resource['model']::factory()->create();

        Livewire::test($resource['edit'], ['record' => $record->id])->callAction(DeleteAction::class)->assertNotified();

        $this->assertModelMissing($record);
    }

    #[DataProvider('resources')]
    public function test_deletes_a_directory_record_from_the_list(array $resource): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $record = $resource['model']::factory()->create();

        Livewire::test($resource['list'])->callAction(TestAction::make(DeleteAction::class)->table($record))->assertNotified();

        $this->assertModelMissing($record);
    }

    #[DataProvider('reorderableResources')]
    public function test_reorders_directory_records_and_the_public_listing(array $resource): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $first = $resource['model']::factory()->create([$resource['name'] => 'Urutan Pertama', 'sort_order' => 1]);
        $second = $resource['model']::factory()->create([$resource['name'] => 'Urutan Kedua', 'sort_order' => 2]);

        Livewire::test($resource['list'])->call('reorderTable', [(string) $second->id, (string) $first->id]);

        $this->assertSame(1, $second->fresh()->sort_order);
        $this->assertSame(2, $first->fresh()->sort_order);
        $this->get($resource['public_path'])->assertOk()->assertSeeInOrder(['Urutan Kedua', 'Urutan Pertama']);
    }

    public function test_searches_gallery_titles_in_the_admin_list(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $matching = Gallery::factory()->create(['title' => 'Dokumentasi PKD']);
        $other = Gallery::factory()->create(['title' => 'Bakti Sosial']);

        Livewire::test(ListGalleries::class)->searchTable('PKD')
            ->assertCanSeeTableRecords([$matching])->assertCanNotSeeTableRecords([$other]);
    }

    /**
     * @return array<string, array{array{
     *     model: class-string<Model>,
     *     create: class-string<CreateRecord>,
     *     edit: class-string<EditRecord>,
     *     list: class-string<ListRecords>,
     *     name: string, media: string, directory: string, public_path: string,
     *     fields: array<string, mixed>, required: array<int, string>
     * }}>
     */
    public static function resources(): array
    {
        return [
            'officers' => [[
                'model' => Officer::class, 'create' => CreateOfficer::class, 'edit' => EditOfficer::class, 'list' => ListOfficers::class,
                'name' => 'name', 'media' => 'photo', 'directory' => 'officers', 'public_path' => '/profil/struktur-organisasi',
                'fields' => ['position' => 'Ketua', 'period' => '2026 - 2030', 'bio' => '<p>Biografi pengurus</p>'],
                'required' => ['name', 'position'],
            ]],
            'bodies' => [[
                'model' => Body::class, 'create' => CreateBody::class, 'edit' => EditBody::class, 'list' => ListBodies::class,
                'name' => 'name', 'media' => 'logo', 'directory' => 'bodies', 'public_path' => '/badan-lembaga',
                'fields' => ['description' => '<p>Deskripsi lembaga</p>'], 'required' => ['name'],
            ]],
            'galleries' => [[
                'model' => Gallery::class, 'create' => CreateGallery::class, 'edit' => EditGallery::class, 'list' => ListGalleries::class,
                'name' => 'title', 'media' => 'image_path', 'directory' => 'galleries', 'public_path' => '/galeri',
                'fields' => ['description' => 'Dokumentasi kegiatan', 'event_date' => '2026-11-01'], 'required' => ['title', 'image_path'],
            ]],
            'rantings' => [[
                'model' => Ranting::class, 'create' => CreateRanting::class, 'edit' => EditRanting::class, 'list' => ListRantings::class,
                'name' => 'name', 'media' => 'cover', 'directory' => 'rantings', 'public_path' => '/ranting',
                'fields' => ['description' => 'Desa di Kecamatan Cepu', 'members' => []], 'required' => ['name'],
            ]],
        ];
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function reorderableResources(): array
    {
        return array_intersect_key(self::resources(), array_flip(['officers', 'bodies', 'galleries']));
    }
}
