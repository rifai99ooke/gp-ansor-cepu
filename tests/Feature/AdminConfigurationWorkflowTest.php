<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\RegistrationPage;
use App\Filament\Pages\SettingsPage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use App\Models\RegistrationForm;
use App\Models\Setting;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminConfigurationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('staticPages')]
    public function test_edits_static_content_without_changing_its_public_url(string $slug, string $path): void
    {
        $this->actingAs(User::factory()->create());
        $page = Page::factory()->create(['slug' => $slug]);

        Livewire::test(EditPage::class, ['record' => $page->id])
            ->fillForm(['title' => 'Halaman Diperbarui', 'subtitle' => 'Subjudul baru', 'content' => '<p style="text-align: center">Konten baru</p><script>alert(1)</script>'])
            ->call('save')->assertHasNoFormErrors()->assertNotified();

        $this->assertSame($slug, $page->fresh()->slug);
        $this->get($path)->assertOk()->assertSee('Halaman Diperbarui')->assertSee('Subjudul baru')
            ->assertSee('<p style="text-align: center">Konten baru</p>', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_lists_only_supported_static_pages_and_prevents_their_deletion(): void
    {
        $this->actingAs(User::factory()->create());
        $supported = Page::factory()->create(['slug' => 'program']);
        $unsupported = Page::factory()->create(['slug' => 'custom-page']);

        Livewire::test(ListPages::class)->assertCanSeeTableRecords([$supported])->assertCanNotSeeTableRecords([$unsupported]);
        Livewire::test(EditPage::class, ['record' => $supported->id])->assertActionDoesNotExist(DeleteAction::class);
        $this->get('/admin/pages/'.$unsupported->id.'/edit')->assertNotFound();
    }

    public function test_rejects_empty_static_page_content_without_overwriting_it(): void
    {
        $this->actingAs(User::factory()->create());
        $page = Page::factory()->create(['slug' => 'program', 'content' => '<p>Konten asli</p>']);

        Livewire::test(EditPage::class, ['record' => $page->id])->fillForm(['title' => '', 'content' => ''])
            ->call('save')->assertHasFormErrors(['title' => 'required', 'content']);

        $this->assertSame('<p>Konten asli</p>', $page->fresh()->content);
    }

    public function test_creates_registration_configuration_from_pasted_iframe_markup(): void
    {
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->create());
        $url = 'https://docs.google.com/forms/d/e/AdminConfiguration/viewform';
        $responsesUrl = 'https://docs.google.com/spreadsheets/d/AdminResponses/edit';

        $page = Livewire::test(RegistrationPage::class)->fillForm([
            'title' => 'Pendaftaran Cepu', 'description' => '<p>Ketentuan baru</p>',
            'form_url' => '<iframe src="'.$url.'?embedded=true"></iframe>',
            'responses_url' => $responsesUrl, 'is_active' => false,
        ])->call('save')->assertHasNoFormErrors()->assertNotified();

        $record = RegistrationForm::query()->sole();
        $this->assertSame($url, $record->form_url);
        $this->assertSame($url.'?embedded=true', $record->embed_url);
        $this->assertFalse($record->is_active);
        $page->assertActionHasUrl('view_public', route('pendaftaran.index'))
            ->assertActionVisible('open_responses')->assertActionHasUrl('open_responses', $responsesUrl);
        $this->get('/pendaftaran')->assertOk()->assertSee('Pendaftaran Cepu')->assertSee('Pendaftaran Telah Ditutup');
        Http::assertNothingSent();
    }

    public function test_updates_registration_schedule_and_active_status(): void
    {
        Http::preventStrayRequests();
        $this->freezeTime();
        $this->actingAs(User::factory()->create());
        $record = RegistrationForm::factory()->create();
        $opensAt = now()->addDay()->startOfMinute();
        $closesAt = now()->addDays(3)->startOfMinute();
        $page = Livewire::test(RegistrationPage::class);

        $page->fillForm(['title' => 'Jadwal Baru', 'opens_at' => $opensAt->toDateTimeString(), 'closes_at' => $closesAt->toDateTimeString()])
            ->call('save')->assertHasNoFormErrors();

        $record->refresh();
        $this->assertTrue($record->opens_at->equalTo($opensAt));
        $this->assertTrue($record->closes_at->equalTo($closesAt));
        $this->assertDatabaseCount('registration_forms', 1);
        $this->get('/pendaftaran')->assertOk()->assertSee('Pendaftaran Segera Dibuka');
        $page->fillForm(['is_active' => false])->call('save')->assertHasNoFormErrors();
        $this->assertFalse($record->fresh()->is_active);
        $this->get('/pendaftaran')->assertOk()->assertSee('Pendaftaran Telah Ditutup');
        Http::assertNothingSent();
    }

    #[DataProvider('invalidRegistrationValues')]
    public function test_rejects_invalid_registration_configuration_without_saving_it(string $field, string $value): void
    {
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->create());

        Livewire::test(RegistrationPage::class)->fillForm([
            'title' => 'Pendaftaran Cepu', 'form_url' => 'https://docs.google.com/forms/d/e/AdminConfiguration/viewform', $field => $value,
        ])->call('save')->assertHasFormErrors([$field]);

        $this->assertDatabaseCount('registration_forms', 0);
        Http::assertNothingSent();
    }

    public function test_resolves_a_short_form_link_when_saving_registration(): void
    {
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->create());
        $shortUrl = 'https://forms.gle/AdminConfiguration';
        $resolvedUrl = 'https://docs.google.com/forms/d/e/ResolvedAdminConfiguration/viewform';
        Http::fake([$shortUrl => Http::response('', 302, ['Location' => $resolvedUrl])]);

        Livewire::test(RegistrationPage::class)->fillForm(['title' => 'Pendaftaran Cepu', 'form_url' => $shortUrl, 'is_active' => false])
            ->call('save')->assertHasNoFormErrors();

        $this->assertSame($resolvedUrl, RegistrationForm::query()->sole()->form_url);
        Http::assertSent(fn ($request): bool => $request->url() === $shortUrl);
    }

    public function test_saves_site_identity_contact_social_links_and_map_for_public_pages(): void
    {
        $this->actingAs(User::factory()->create());
        Setting::clearCache();
        $state = [
            'site_name' => 'Ansor Cepu', 'site_full_name' => 'Gerakan Pemuda Ansor Cepu',
            'site_tagline' => 'Berkhidmat untuk masyarakat Cepu', 'footer_copyright' => 'Ansor Cepu',
            'contact_phone_1' => '081234567890', 'contact_phone_2' => '0296123456', 'contact_email' => 'sekretariat@example.test',
            'social_instagram_handle' => 'ansor_cepu', 'social_instagram_url' => 'https://www.instagram.com/ansor_cepu',
            'map_embed_url' => '<iframe src="https://www.google.com/maps/embed?pb=cepu"></iframe>',
        ];

        Livewire::test(SettingsPage::class)->fillForm($state)->call('save')->assertHasNoFormErrors()->assertNotified();

        $this->assertSame('https://www.google.com/maps/embed?pb=cepu', setting('map_embed_url'));
        $this->get('/')->assertOk()->assertSee('Ansor Cepu')->assertSee('Berkhidmat untuk masyarakat Cepu');
        $this->get('/hubungi-kami')->assertOk()->assertSee('sekretariat@example.test')
            ->assertSee('081234567890')->assertSee('https://www.instagram.com/ansor_cepu')->assertSee('https://www.google.com/maps/embed?pb=cepu');
    }

    public function test_rejects_an_unapproved_map_without_changing_existing_site_settings(): void
    {
        $this->actingAs(User::factory()->create());
        Setting::setValue('site_name', 'Nama Asli');

        Livewire::test(SettingsPage::class)->fillForm(['site_name' => 'Nama Ditolak', 'map_embed_url' => 'https://example.test/maps'])
            ->call('save')->assertHasFormErrors(['map_embed_url']);

        $this->assertSame('Nama Asli', setting('site_name'));
        $this->assertDatabaseMissing('settings', ['key' => 'map_embed_url']);
    }

    /** @return array<string, array{string, string}> */
    public static function staticPages(): array
    {
        return [
            'vision and mission' => ['visi-misi', '/profil/visi-misi'],
            'program' => ['program', '/program'],
            'documents' => ['dokumen', '/dokumen'],
        ];
    }

    /** @return array<string, array{string, string}> */
    public static function invalidRegistrationValues(): array
    {
        return [
            'missing title' => ['title', ''],
            'missing form URL' => ['form_url', ''],
            'unapproved host' => ['form_url', 'https://example.test/forms/example'],
            'non-form Google URL' => ['form_url', 'https://docs.google.com/spreadsheets/d/example/edit'],
            'invalid responses URL' => ['responses_url', 'not-a-url'],
        ];
    }
}
