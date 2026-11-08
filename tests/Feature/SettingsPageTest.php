<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\SettingsPage;
use App\Models\Setting;
use App\Models\User;
use App\Services\ImageService;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_cache_is_invalidated_after_transaction_commit(): void
    {
        Setting::setValue('site_name', 'Nama Lama');

        DB::transaction(function (): void {
            Setting::setValue('site_name', 'Nama Baru');
            Cache::put(Setting::CACHE_KEY, ['site_name' => 'Nama Lama'], 600);
        });

        $this->assertSame('Nama Baru', setting('site_name'));
    }

    public function test_admin_can_save_structured_address_used_by_public_metadata(): void
    {
        $this->actingAs(User::factory()->create());
        Setting::clearCache();
        $address = [
            'contact_address' => 'Jl. Merdeka No. 12',
            'address_locality' => 'Surabaya',
            'address_region' => 'Jawa Timur',
            'address_postal_code' => '01234',
        ];

        Livewire::test(SettingsPage::class)
            ->fillForm($address)
            ->call('save')
            ->assertHasNoFormErrors();

        foreach ($address as $key => $value) {
            $this->assertDatabaseHas('settings', ['key' => $key, 'value' => $value]);
        }

        $response = $this->get('/hubungi-kami')->assertOk();
        $document = new DOMDocument;
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $script = (new DOMXPath($document))->query('//script[@type="application/ld+json"]')->item(0);
        $schema = json_decode($script->textContent, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame([
            '@type' => 'PostalAddress',
            'streetAddress' => 'Jl. Merdeka No. 12',
            'addressLocality' => 'Surabaya',
            'addressRegion' => 'Jawa Timur',
            'postalCode' => '01234',
            'addressCountry' => 'ID',
        ], $schema['address']);
    }

    #[DataProvider('invalidAddressValues')]
    public function test_invalid_address_does_not_save_any_changes(string $field, string $value, string $rule): void
    {
        $this->actingAs(User::factory()->create());
        Setting::clearCache();

        Livewire::test(SettingsPage::class)
            ->fillForm(['site_name' => 'Perubahan belum sah', $field => $value])
            ->call('save')
            ->assertHasFormErrors([$field => $rule]);

        $this->assertDatabaseCount('settings', 0);
    }

    public function test_unsaved_changes_baseline_only_updates_after_successful_save(): void
    {
        $this->actingAs(User::factory()->create());
        Setting::clearCache();
        $page = Livewire::test(SettingsPage::class);
        $initialData = $page->get('data');

        $page->assertSet('savedData', $initialData)
            ->fillForm(['site_name' => 'Ansor Baru', 'contact_email' => 'bukan-email'])
            ->call('save')
            ->assertHasFormErrors(['contact_email' => 'email'])
            ->assertSet('savedData', $initialData)
            ->fillForm(['contact_email' => 'sekretariat@example.test'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($page->get('data'), $page->get('savedData'));
        $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'Ansor Baru']);
    }

    public function test_branding_uploads_are_saved_with_the_updated_form(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        Setting::clearCache();

        $page = Livewire::test(SettingsPage::class)
            ->fillForm([
                'site_logo' => UploadedFile::fake()->createWithContent('logo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a5e8AAAAASUVORK5CYII=')),
                'site_favicon' => UploadedFile::fake()->createWithContent('favicon.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a5e8AAAAASUVORK5CYII=')),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        Storage::disk('public')->assertExists([
            Setting::getValue('site_logo'),
            Setting::getValue('site_favicon'),
        ]);
        $this->assertSame($page->get('data'), $page->get('savedData'));
    }

    public function test_swapping_logo_and_favicon_preserves_both_files(): void
    {
        Storage::fake('public');
        $this->mock(ImageService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('generateWebpVariants')->andReturnUsing(fn (string $path): string => $path);
        });
        $this->actingAs(User::factory()->create());
        Storage::disk('public')->put('branding/logo.png', 'logo');
        Storage::disk('public')->put('branding/favicon.png', 'favicon');
        Setting::setValue('site_logo', 'branding/logo.png');
        Setting::setValue('site_favicon', 'branding/favicon.png');

        Livewire::test(SettingsPage::class)
            ->fillForm(['site_logo' => ['branding/favicon.png'], 'site_favicon' => ['branding/logo.png']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('settings', ['key' => 'site_logo', 'value' => 'branding/favicon.png']);
        $this->assertDatabaseHas('settings', ['key' => 'site_favicon', 'value' => 'branding/logo.png']);
        Storage::disk('public')->assertExists(['branding/logo.png', 'branding/favicon.png']);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function invalidAddressValues(): array
    {
        return [
            'empty postal code' => ['address_postal_code', '', 'required'],
            'postal code too short' => ['address_postal_code', '1234', 'regex'],
            'postal code too long' => ['address_postal_code', '123456', 'regex'],
            'postal code letters' => ['address_postal_code', 'ABCDE', 'regex'],
            'empty city' => ['address_locality', '', 'required'],
            'city too long' => ['address_locality', str_repeat('a', 101), 'max'],
            'empty province' => ['address_region', '', 'required'],
            'province too long' => ['address_region', str_repeat('a', 101), 'max'],
        ];
    }
}
