<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\RegistrationForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationSchemaCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('unsupportedFormConfigurations')]
    public function test_unsupported_inputs_use_the_original_form_and_block_native_submission(array $question, mixed $settings): void
    {
        Http::preventStrayRequests();
        $url = 'https://docs.google.com/forms/d/e/CompatibilityTest/viewform';
        RegistrationForm::factory()->create(['form_url' => $url]);
        Http::fake([$url => Http::response($this->formHtml($question, $settings))]);

        $this->get('/pendaftaran')
            ->assertOk()
            ->assertViewHas('schema', fn (?array $schema): bool => $schema === null)
            ->assertSee('Buka Google Form')
            ->assertSee('href="'.$url.'"', false)
            ->assertDontSee('name="entry_', false);

        $this->post('/pendaftaran', ['entry_456' => 'Ahmad'])
            ->assertRedirect(route('pendaftaran.index'))
            ->assertSessionHasErrors('form')
            ->assertSessionMissing('registration_submitted');

        Http::assertNotSent(fn (Request $request): bool => $request->method() === 'POST');
    }

    /**
     * @return array<string, array{array<int, mixed>, mixed}>
     */
    public static function unsupportedFormConfigurations(): array
    {
        $textQuestion = [123, 'Nama', null, 0, [[456, null, 1]]];

        return [
            'verified email collection' => [$textQuestion, [null, null, null, 2, null, null, 2]],
            'responder email collection' => [$textQuestion, [1, null, null, 3, null, null, 3]],
            'legacy email collection' => [$textQuestion, [null, null, null, null, 1]],
            'unknown email collection mode' => [$textQuestion, [null, null, null, null, null, null, 4]],
            'email collection mode with unexpected type' => [$textQuestion, [null, null, null, null, null, null, '1']],
            'malformed form settings' => [$textQuestion, 'unexpected settings'],
            'radio other option' => [[123, 'Pekerjaan', null, 2, [[456, [['Pelajar'], ['', null, null, null, 1]], 1]]], null],
            'checkbox other option' => [[123, 'Minat', null, 4, [[456, [['PKD'], ['', null, null, null, true]], 1]]], null],
        ];
    }

    public function test_disabled_email_collection_and_an_ordinary_other_label_remain_supported(): void
    {
        Http::preventStrayRequests();
        $url = 'https://docs.google.com/forms/d/e/CompatibilityTest/viewform';
        RegistrationForm::factory()->create(['form_url' => $url]);
        $question = [123, 'Minat', null, 2, [[456, [['PKD', null, null, null, 0], ['Lainnya', null, null, null, false]], 1]]];
        Http::fake([$url => Http::response($this->formHtml($question, [1, null, null, 2, null, null, 1]))]);

        $this->get('/pendaftaran')
            ->assertOk()
            ->assertSee('name="entry_456"', false)
            ->assertSee('value="Lainnya"', false)
            ->assertDontSee('Buka Google Form');
    }

    #[DataProvider('labelsWithoutEmailValidation')]
    public function test_labels_do_not_add_email_validation(string $label, ?array $validation): void
    {
        Http::preventStrayRequests();
        $url = 'https://docs.google.com/forms/d/e/CompatibilityTest/viewform';
        RegistrationForm::factory()->create(['form_url' => $url]);
        $question = [123, $label, null, 0, [[456, null, 1, null, $validation]]];
        Http::fake([
            $url => Http::response($this->formHtml($question)),
            'https://docs.google.com/forms/d/e/CompatibilityTest/formResponse' => Http::response('<div class="vHW8K">Terkirim.</div>'),
        ]);

        $this->post('/pendaftaran', ['entry_456' => 'Ahmad'])
            ->assertRedirect(route('pendaftaran.index'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('registration_submitted', true);

        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->body() === 'entry.456=Ahmad');
    }

    /**
     * @return array<string, array{string, ?array<int, mixed>}>
     */
    public static function labelsWithoutEmailValidation(): array
    {
        return [
            'email in a name question' => ['Nama pemilik email', null],
            'pos-el in a name question' => ['Nama pemilik pos-el', null],
            'surel in a name question' => ['Nama pemilik surel', null],
            'email with malformed validation codes' => ['Email', [['2', '102']]],
        ];
    }

    public function test_explicit_email_metadata_is_validated_even_without_an_email_label(): void
    {
        Http::preventStrayRequests();
        $url = 'https://docs.google.com/forms/d/e/CompatibilityTest/viewform';
        RegistrationForm::factory()->create(['form_url' => $url]);
        $question = [123, 'Kontak', null, 0, [[456, null, 1, null, [[2, 102, null, 'Alamat tidak valid']]]]];
        Http::fake([
            $url => Http::response($this->formHtml($question)),
            'https://docs.google.com/forms/d/e/CompatibilityTest/formResponse' => Http::response('<div class="vHW8K">Terkirim.</div>'),
        ]);

        $this->from('/pendaftaran')->post('/pendaftaran', ['entry_456' => 'Ahmad'])
            ->assertSessionHasErrors(['entry_456' => 'Format Kontak harus berupa alamat email yang valid.']);
        Http::assertNotSent(fn (Request $request): bool => $request->method() === 'POST');

        $this->post('/pendaftaran', ['entry_456' => 'ahmad@example.test'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('registration_submitted', true);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->body() === 'entry.456=ahmad%40example.test');
    }

    /**
     * @param  array<int, mixed>  $question
     */
    private function formHtml(array $question, mixed $settings = null): string
    {
        $formMeta = ['Deskripsi', [$question], null, null, null, null, null, null, 'Pendaftaran', null, $settings];

        return '<script>var FB_PUBLIC_LOAD_DATA_ = '.json_encode([null, $formMeta], JSON_THROW_ON_ERROR).';</script>';
    }
}
