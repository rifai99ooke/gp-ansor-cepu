<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\SettingsPage;
use App\Filament\Resources\Bodies\Pages\CreateBody;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BrandingUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('brandingFields')]
    public function test_rejects_svg_branding_uploads_before_public_storage(string $field, string $filename): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        Setting::clearCache();

        Livewire::test(SettingsPage::class)
            ->fillForm([$field => UploadedFile::fake()->createWithContent($filename, '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(document.domain)"><rect width="32" height="32"/></svg>')->mimeType('image/svg+xml')])
            ->call('save')
            ->assertHasFormErrors([$field]);

        $this->assertDatabaseCount('settings', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_rejects_svg_body_logos_before_public_storage(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateBody::class)
            ->fillForm([
                'name' => 'Banser',
                'logo' => UploadedFile::fake()->createWithContent('logo.png', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(document.domain)"><rect width="32" height="32"/></svg>')->mimeType('image/svg+xml'),
            ])
            ->call('create')
            ->assertHasFormErrors(['logo']);

        $this->assertDatabaseCount('bodies', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function brandingFields(): array
    {
        return [
            'organization logo' => ['site_logo', 'logo.svg'],
            'favicon' => ['site_favicon', 'logo.svg'],
            'SVG disguised as a PNG logo' => ['site_logo', 'logo.png'],
            'SVG disguised as a PNG favicon' => ['site_favicon', 'logo.png'],
        ];
    }
}
