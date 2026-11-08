<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\RegistrationForm;
use App\Services\GoogleFormSchemaService;
use App\Services\GoogleFormUrlNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalizes_docs_google_com_viewform_link(): void
    {
        $normalizer = app(GoogleFormUrlNormalizer::class);

        $result = $normalizer->normalize('https://docs.google.com/forms/d/e/1FAIpQLSdTestFormId123/viewform?usp=sf_link');

        $this->assertSame(
            'https://docs.google.com/forms/d/e/1FAIpQLSdTestFormId123/viewform?usp=sf_link',
            $result['form_url']
        );
        $this->assertSame(
            'https://docs.google.com/forms/d/e/1FAIpQLSdTestFormId123/viewform?usp=sf_link&embedded=true',
            $result['embed_url']
        );
    }

    public function test_extracts_src_from_pasted_iframe_embed_code(): void
    {
        $normalizer = app(GoogleFormUrlNormalizer::class);

        $iframeCode = '<iframe src="https://docs.google.com/forms/d/e/1FAIpQLSdTestEmbed999/viewform?embedded=true" width="640" height="800" frameborder="0">Memuat…</iframe>';

        $result = $normalizer->normalize($iframeCode);

        $this->assertSame(
            'https://docs.google.com/forms/d/e/1FAIpQLSdTestEmbed999/viewform',
            $result['form_url']
        );
        $this->assertSame(
            'https://docs.google.com/forms/d/e/1FAIpQLSdTestEmbed999/viewform?embedded=true',
            $result['embed_url']
        );
    }

    public function test_resolves_forms_gle_short_link_and_falls_back_gracefully_on_failure(): void
    {
        Http::fake([
            'https://forms.gle/ResolvedShort123' => Http::response('', 302, [
                'Location' => 'https://docs.google.com/forms/d/e/1FAIpQLSdResolvedForm/viewform',
            ]),
            'https://forms.gle/UnresolvedShort999' => Http::response('Server Error', 500),
        ]);

        $normalizer = app(GoogleFormUrlNormalizer::class);

        $resolved = $normalizer->normalize('https://forms.gle/ResolvedShort123');
        $this->assertSame('https://docs.google.com/forms/d/e/1FAIpQLSdResolvedForm/viewform', $resolved['form_url']);
        $this->assertSame('https://docs.google.com/forms/d/e/1FAIpQLSdResolvedForm/viewform?embedded=true', $resolved['embed_url']);

        // Fallback to button-only (embed_url = null) when resolution fails
        $fallback = $normalizer->normalize('https://forms.gle/UnresolvedShort999');
        $this->assertSame('https://forms.gle/UnresolvedShort999', $fallback['form_url']);
        $this->assertNull($fallback['embed_url']);
    }

    public function test_rejects_non_whitelisted_hosts(): void
    {
        $normalizer = app(GoogleFormUrlNormalizer::class);

        $this->expectException(ValidationException::class);
        $normalizer->normalize('https://evil-phishing.example.com/forms/d/e/123/viewform');
    }

    public function test_closed_and_upcoming_forms_show_notice_instead_of_iframe(): void
    {
        RegistrationForm::query()->delete();

        RegistrationForm::create([
            'title' => 'Pendaftaran Kader Lama',
            'slug' => 'pendaftaran-kader-lama',
            'form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSdClosed123/viewform',
            'opens_at' => now()->subDays(30),
            'closes_at' => now()->subDays(2),
            'is_active' => true,
        ]);

        $response = $this->get('/pendaftaran');
        $response->assertStatus(200)
            ->assertSee('Pendaftaran Telah Ditutup')
            ->assertDontSee('<iframe', false);

        RegistrationForm::query()->delete();

        RegistrationForm::create([
            'title' => 'Pendaftaran Kader Mendatang',
            'slug' => 'pendaftaran-kader-mendatang',
            'form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSdUpcoming123/viewform',
            'opens_at' => now()->addDays(5),
            'closes_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        $this->get('/pendaftaran')
            ->assertStatus(200)
            ->assertSee('Pendaftaran Segera Dibuka')
            ->assertDontSee('<iframe', false);
    }

    public function test_renders_native_form_without_google_branding_and_submits_response(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://docs.google.com/forms/d/e/1FAIpQLScFU7mJqqpLni5rcIPz39mIDirHNImy9ZCXA8qjsEJ2gbyAhg/viewform*' => Http::response(
                '<script>var FB_PUBLIC_LOAD_DATA_ = [null,["Deskripsi",[ [1633920210,"Nama",null,0,[[2005620554,null,1]]], [227649005,"Email",null,0,[[1045781291,null,1,null,[[2,102,null,"Please enter a valid email address"]]]]], [790080973,"Alamat",null,1,[[1065046570,null,1]]] ],null,null,null,null,null,null,"Informasi kontak"]];</script>',
                200
            ),
            'https://docs.google.com/forms/d/e/1FAIpQLScFU7mJqqpLni5rcIPz39mIDirHNImy9ZCXA8qjsEJ2gbyAhg/formResponse' => Http::response('<div class="vHW8K">Jawaban Anda telah direkam.</div>', 200),
        ]);

        RegistrationForm::query()->delete();
        RegistrationForm::create([
            'title' => 'Pendaftaran Kader GP Ansor',
            'slug' => 'pendaftaran-kader-gp-ansor',
            'form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLScFU7mJqqpLni5rcIPz39mIDirHNImy9ZCXA8qjsEJ2gbyAhg/viewform',
            'is_active' => true,
        ]);

        $this->get('/pendaftaran')
            ->assertStatus(200)
            ->assertSee('Formulir Isian Pendaftaran')
            ->assertSee('Nama')
            ->assertSee('Email')
            ->assertSee('Alamat')
            ->assertSee('Kirim Pendaftaran')
            ->assertDontSee('<iframe', false)
            ->assertDontSee('Jangan pernah mengirimkan sandi');

        $this->followingRedirects()
            ->post('/pendaftaran', [
                'entry_2005620554' => 'Ahmad Ansor',
                'entry_1045781291' => 'ahmad@gpansor.or.id',
                'entry_1065046570' => 'Jl. Kramat Raya No. 65A, Jakarta Pusat',
            ])
            ->assertStatus(200)
            ->assertSee('Pendaftaran Berhasil Dikirim!');
    }

    public function test_submits_checkbox_arrays_as_repeated_entry_keys_and_handles_upstream_failure(): void
    {
        Http::preventStrayRequests();
        $formHtml = '<script>var FB_PUBLIC_LOAD_DATA_ = [null,["Deskripsi",[ [1633920210,"Nama",null,0,[[2005620554,null,1]]], [999111222,"Minat Kaderisasi",null,4,[[333444555,[["PKD"],["Diklatsar Banser"]],1]]] ],null,null,null,null,null,null,"Formulir Kader"]];</script>';

        Http::fake([
            'https://docs.google.com/forms/d/e/1FAIpQLSdCheckboxTest/viewform*' => Http::response($formHtml, 200),
            'https://docs.google.com/forms/d/e/1FAIpQLSdCheckboxTest/formResponse' => Http::sequence()
                ->push('<div class="vHW8K">Jawaban Anda telah direkam.</div>', 200)
                ->push('Server Error', 500),
        ]);

        RegistrationForm::query()->delete();
        RegistrationForm::create([
            'title' => 'Pendaftaran Kaderisasi',
            'slug' => 'pendaftaran-kaderisasi',
            'form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSdCheckboxTest/viewform',
            'is_active' => true,
        ]);

        // 1. Successful checkbox submission sends repeated entry.333444555 keys
        $this->post('/pendaftaran', [
            'entry_2005620554' => 'Budi Santoso',
            'entry_333444555' => ['PKD', 'Diklatsar Banser'],
        ])->assertRedirect(route('pendaftaran.index'));

        Http::assertSent(function (Request $request): bool {
            return str_contains($request->url(), '/formResponse')
                && str_contains($request->body(), 'entry.333444555=PKD&entry.333444555=Diklatsar%20Banser');
        });

        // 2. Invalid checkbox option is rejected by validation
        $this->post('/pendaftaran', [
            'entry_2005620554' => 'Budi Santoso',
            'entry_333444555' => ['Opsi Tidak Valid'],
        ])
            ->assertSessionHasErrors([
                'entry_333444555.0' => 'Pilihan Minat Kaderisasi tidak valid.',
            ]);

        // 3. Failed upstream response redirects back with form error instead of false success
        $this->post('/pendaftaran', [
            'entry_2005620554' => 'Budi Santoso',
            'entry_333444555' => ['PKD'],
        ])
            ->assertRedirect(route('pendaftaran.index'))
            ->assertSessionHasErrors(['form']);
    }

    #[DataProvider('loginRedirectStatuses')]
    public function test_login_redirects_are_not_followed_or_reported_as_success(int $status): void
    {
        Http::preventStrayRequests();
        $this->createSubmissionForm();
        Http::fake([
            'https://docs.google.com/forms/d/e/SubmissionTest/viewform' => Http::response($this->submissionFormHtml()),
            'https://docs.google.com/forms/d/e/SubmissionTest/formResponse' => Http::response('', $status, [
                'Location' => 'https://accounts.google.com/ServiceLogin',
            ]),
            'https://accounts.google.com/ServiceLogin' => Http::response('<html><body>Sign in</body></html>'),
        ]);

        $this->post('/pendaftaran', ['entry_456' => 'Peserta Uji'])
            ->assertRedirect(route('pendaftaran.index'))
            ->assertSessionHasErrors(['form' => 'Gagal mengirim data pendaftaran ke server. Silakan coba beberapa saat lagi.'])
            ->assertSessionHasInput('entry_456', 'Peserta Uji')
            ->assertSessionMissing('registration_submitted');

        Http::assertNotSent(fn (Request $request): bool => str_starts_with($request->url(), 'https://accounts.google.com/'));
    }

    /**
     * @return array<string, array{0: int}>
     */
    public static function loginRedirectStatuses(): array
    {
        return [
            '302 login redirect' => [302],
            '307 redirect preserving POST data' => [307],
        ];
    }

    #[DataProvider('unconfirmedSubmissionBodies')]
    public function test_http_200_without_confirmation_does_not_report_success(string $body): void
    {
        Http::preventStrayRequests();
        $this->createSubmissionForm();
        Http::fake([
            'https://docs.google.com/forms/d/e/SubmissionTest/viewform' => Http::response($this->submissionFormHtml()),
            'https://docs.google.com/forms/d/e/SubmissionTest/formResponse' => Http::response($body),
        ]);

        $this->post('/pendaftaran', ['entry_456' => 'Peserta Uji'])
            ->assertRedirect(route('pendaftaran.index'))
            ->assertSessionHasErrors(['form'])
            ->assertSessionHasInput('entry_456', 'Peserta Uji')
            ->assertSessionMissing('registration_submitted');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function unconfirmedSubmissionBodies(): array
    {
        return [
            'login page' => ['<html><body><form action="https://accounts.google.com/ServiceLogin">Sign in</form></body></html>'],
            'validation errors' => ['<html><body><form><div role="alert">This is a required question</div></form></body></html>'],
            'closed form' => ['<html><body>This form is no longer accepting responses.</body></html>'],
            'empty response' => [''],
            'generic success status' => ['OK'],
            'confirmation markup inside a comment' => ['<!-- <div class="vHW8K">Saved</div> -->'],
            'confirmation class mentioned in a stylesheet' => ['<style>.vHW8K { color: green; }</style>'],
            'similar class name' => ['<div class="vHW8K-error">Not saved</div>'],
        ];
    }

    #[DataProvider('confirmedSubmissionBodies')]
    public function test_confirmed_submissions_report_success_with_custom_messages(string $body): void
    {
        Http::preventStrayRequests();
        $this->createSubmissionForm();
        Http::fake([
            'https://docs.google.com/forms/d/e/SubmissionTest/viewform' => Http::response($this->submissionFormHtml()),
            'https://docs.google.com/forms/d/e/SubmissionTest/formResponse' => Http::response($body),
        ]);

        $this->post('/pendaftaran', ['entry_456' => 'Peserta Uji'])
            ->assertRedirect(route('pendaftaran.index'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('registration_submitted', true);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function confirmedSubmissionBodies(): array
    {
        return [
            'current confirmation markup' => ['<html><body><div class="other-class vHW8K">Terima kasih, panitia akan menghubungi Anda.</div></body></html>'],
            'legacy confirmation markup' => ['<html><body><div class="freebirdFormviewerViewResponseConfirmationMessage">Your response has been recorded.</div></body></html>'],
        ];
    }

    #[DataProvider('unavailableSchemaResponses')]
    public function test_unavailable_schema_shows_original_form_and_blocks_portal_submission(?int $status, string $body): void
    {
        Http::preventStrayRequests();
        $this->createSubmissionForm();
        Http::fake([
            'https://docs.google.com/forms/d/e/SubmissionTest/viewform' => $status === null
                ? Http::failedConnection()
                : Http::response($body, $status),
            'https://docs.google.com/forms/d/e/SubmissionTest/formResponse' => Http::response('<div class="vHW8K">Jawaban Anda telah direkam.</div>'),
        ]);

        $this->get('/pendaftaran')
            ->assertOk()
            ->assertSee('Formulir Belum Dapat Dimuat')
            ->assertSee('Buka Google Form')
            ->assertSee('href="https://docs.google.com/forms/d/e/SubmissionTest/viewform"', false)
            ->assertDontSee('name="entry_', false)
            ->assertDontSee('Kirim Pendaftaran');

        $this->post('/pendaftaran', [
            'entry_456' => 'Peserta Uji',
            'entry_2005620554' => 'Peserta Uji',
            'entry_1045781291' => 'peserta@example.test',
            'entry_1065046570' => 'Alamat Uji',
        ])
            ->assertRedirect(route('pendaftaran.index'))
            ->assertSessionHasErrors(['form' => 'Formulir pendaftaran belum dapat dimuat. Silakan coba lagi atau isi melalui Google Form.'])
            ->assertSessionHasInput('entry_456', 'Peserta Uji')
            ->assertSessionMissing('registration_submitted');

        Http::assertNotSent(fn (Request $request): bool => $request->method() === 'POST');
    }

    /**
     * @return array<string, array{0: ?int, 1: string}>
     */
    public static function unavailableSchemaResponses(): array
    {
        return [
            'server error' => [503, 'Unavailable'],
            'missing form' => [404, 'Not found'],
            'connection failure' => [null, ''],
            'empty response' => [200, ''],
            'login page' => [200, '<html><body>Sign in to Google</body></html>'],
            'invalid JSON' => [200, '<script>var FB_PUBLIC_LOAD_DATA_ = [invalid];</script>'],
            'missing fields' => [200, '<script>var FB_PUBLIC_LOAD_DATA_ = [null,["Deskripsi",[]]];</script>'],
            'grid with multiple rows' => [200, '<script>var FB_PUBLIC_LOAD_DATA_ = [null,["Deskripsi",[[123,"Ketersediaan",null,7,[[456,[["Ya"],["Tidak"]],1,["Senin"]],[789,[["Ya"],["Tidak"]],1,["Selasa"]]]]],null,null,null,null,null,null,"Pendaftaran"]];</script>'],
            'unsupported question alongside a supported field' => [200, '<script>var FB_PUBLIC_LOAD_DATA_ = [null,["Deskripsi",[[123,"Nama",null,0,[[456,null,1]]],[789,"Berkas",null,13,[[999,null,1]]]],null,null,null,null,null,null,"Pendaftaran"]];</script>'],
            'date or datetime question alongside a supported field' => [200, '<script>var FB_PUBLIC_LOAD_DATA_ = [null,["Deskripsi",[[123,"Nama",null,0,[[456,null,1]]],[789,"Tanggal Kegiatan",null,9,[[999,null,1]]]],null,null,null,null,null,null,"Pendaftaran"]];</script>'],
            'time or duration question alongside a supported field' => [200, '<script>var FB_PUBLIC_LOAD_DATA_ = [null,["Deskripsi",[[123,"Nama",null,0,[[456,null,1]]],[789,"Waktu Kegiatan",null,10,[[999,null,1]]]],null,null,null,null,null,null,"Pendaftaran"]];</script>'],
        ];
    }

    public function test_service_does_not_submit_when_schema_is_unavailable(): void
    {
        Http::preventStrayRequests();
        $this->createSubmissionForm();
        Http::fake([
            'https://docs.google.com/forms/d/e/SubmissionTest/viewform' => Http::response('Unavailable', 503),
            'https://docs.google.com/forms/d/e/SubmissionTest/formResponse' => Http::response('<div class="vHW8K">Jawaban Anda telah direkam.</div>'),
        ]);

        $submitted = app(GoogleFormSchemaService::class)->submit(RegistrationForm::query()->firstOrFail(), [
            'entry_2005620554' => 'Peserta Uji',
        ]);

        $this->assertFalse($submitted);
        Http::assertNotSent(fn (Request $request): bool => $request->method() === 'POST');
    }

    #[DataProvider('schemaCacheStores')]
    public function test_schema_fetch_recovers_immediately_after_failure(string $cacheStore): void
    {
        config(['cache.default' => $cacheStore]);
        Http::preventStrayRequests();
        $this->createSubmissionForm();
        Http::fake([
            'https://docs.google.com/forms/d/e/SubmissionTest/viewform' => Http::sequence()
                ->push('Unavailable', 503)
                ->push($this->submissionFormHtml()),
        ]);

        $this->get('/pendaftaran')->assertOk()->assertSee('Formulir Belum Dapat Dimuat');
        $this->get('/pendaftaran')->assertOk()->assertSee('name="entry_456"', false)->assertDontSee('Formulir Belum Dapat Dimuat');
        $this->get('/pendaftaran')->assertOk()->assertSee('name="entry_456"', false);

        Http::assertSentCount(2);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function schemaCacheStores(): array
    {
        return [
            'array cache' => ['array'],
            'database cache' => ['database'],
        ];
    }

    #[DataProvider('legacySchemaCachePrefixes')]
    public function test_legacy_cached_fallback_fields_are_not_reused(string $prefix): void
    {
        config(['cache.default' => 'database']);
        Http::preventStrayRequests();
        $this->createSubmissionForm();
        Cache::put($prefix.md5('https://docs.google.com/forms/d/e/SubmissionTest/viewform'), [
            'title' => 'Informasi Kontak',
            'description' => null,
            'action_url' => 'https://docs.google.com/forms/d/e/SubmissionTest/formResponse',
            'fields' => [[
                'id' => '2005620554',
                'input_name' => 'entry_2005620554',
                'entry_key' => 'entry.2005620554',
                'label' => 'Nama Rekaan',
                'description' => null,
                'type' => 'text',
                'required' => true,
                'options' => [],
                'placeholder' => 'Masukkan nama',
            ]],
        ], 3600);
        Http::fake([
            'https://docs.google.com/forms/d/e/SubmissionTest/viewform' => Http::response('Unavailable', 503),
        ]);

        $this->get('/pendaftaran')
            ->assertOk()
            ->assertSee('Formulir Belum Dapat Dimuat')
            ->assertDontSee('Nama Rekaan')
            ->assertDontSee('name="entry_', false);

        Http::assertSentCount(1);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function legacySchemaCachePrefixes(): array
    {
        return [
            'fallback schema' => ['google_form_schema_'],
            'schema without question compatibility checks' => ['google_form_schema_v2_'],
            'schema without date and time compatibility checks' => ['google_form_schema_v3_'],
            'schema without email collection and other option compatibility checks' => ['google_form_schema_v4_'],
        ];
    }

    public function test_saving_registration_invalidates_the_cached_schema(): void
    {
        Http::preventStrayRequests();
        $this->createSubmissionForm();
        Http::fake([
            'https://docs.google.com/forms/d/e/SubmissionTest/viewform' => Http::sequence()
                ->push($this->submissionFormHtml())
                ->push('<script>var FB_PUBLIC_LOAD_DATA_ = [null,["Deskripsi",[[123,"Organisasi",null,0,[[789,null,1]]]],null,null,null,null,null,null,"Pendaftaran"]];</script>'),
        ]);
        $this->get('/pendaftaran')->assertOk()->assertSee('name="entry_456"', false);
        RegistrationForm::query()->firstOrFail()->update(['title' => 'Pendaftaran Diperbarui']);

        $this->get('/pendaftaran')
            ->assertOk()
            ->assertSee('name="entry_789"', false)
            ->assertDontSee('name="entry_456"', false);

        Http::assertSentCount(2);
    }

    public function test_missing_form_url_has_no_schema_and_makes_no_http_request(): void
    {
        Http::preventStrayRequests();

        $schema = app(GoogleFormSchemaService::class)->getSchema(new RegistrationForm);

        $this->assertNull($schema);
        Http::assertNothingSent();
    }

    public function test_short_link_recovers_and_submits_after_initial_resolution_failure(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://forms.gle/RecoverableShortLink' => Http::sequence()
                ->push('Unavailable', 503)
                ->push('', 302, ['Location' => 'https://docs.google.com/forms/d/e/SubmissionTest/viewform']),
            'https://docs.google.com/forms/d/e/SubmissionTest/viewform' => Http::response($this->submissionFormHtml()),
            'https://docs.google.com/forms/d/e/SubmissionTest/formResponse' => Http::response('<div class="vHW8K">Jawaban Anda telah direkam.</div>'),
        ]);
        RegistrationForm::factory()->create(['form_url' => 'https://forms.gle/RecoverableShortLink']);

        $this->get('/pendaftaran')->assertOk()->assertSee('name="entry_456"', false);
        $this->post('/pendaftaran', ['entry_456' => 'Peserta Uji'])
            ->assertRedirect(route('pendaftaran.index'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('registration_submitted', true);

        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->url() === 'https://docs.google.com/forms/d/e/SubmissionTest/formResponse');
        Http::assertSentCount(4);
    }

    public function test_short_link_retry_does_not_follow_redirects_to_unapproved_hosts(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://forms.gle/UnsafeRedirect' => Http::sequence()
                ->push('Unavailable', 503)
                ->push('', 302, ['Location' => 'https://unapproved.example.test/form']),
            'https://unapproved.example.test/form' => Http::response($this->submissionFormHtml()),
        ]);
        $form = RegistrationForm::factory()->create(['form_url' => 'https://forms.gle/UnsafeRedirect']);

        $this->assertNull(app(GoogleFormSchemaService::class)->getSchema($form));

        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'unapproved.example.test'));
        Http::assertSentCount(2);
    }

    private function createSubmissionForm(): void
    {
        RegistrationForm::factory()->create([
            'form_url' => 'https://docs.google.com/forms/d/e/SubmissionTest/viewform',
        ]);
    }

    private function submissionFormHtml(): string
    {
        return '<script>var FB_PUBLIC_LOAD_DATA_ = [null,["Deskripsi",[[123,"Nama",null,0,[[456,null,1]]]],null,null,null,null,null,null,"Pendaftaran"]];</script>';
    }
}
