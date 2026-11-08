<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\RegistrationForm;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationRenderingTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('textQuestionTypes')]
    public function test_invalid_array_input_returns_a_usable_form(int $typeCode): void
    {
        Http::preventStrayRequests();
        $url = 'https://docs.google.com/forms/d/e/RenderingTest/viewform';
        Http::fake([$url => Http::response('<script>var FB_PUBLIC_LOAD_DATA_ = [null,["Deskripsi",[[123,"Nama",null,'.$typeCode.',[[456,null,1]]]],null,null,null,null,null,null,"Pendaftaran"]];</script>')]);
        RegistrationForm::factory()->create(['form_url' => $url]);

        $this->post('/pendaftaran', ['entry_456' => ['tipe tidak valid']])
            ->assertSessionHasErrors('entry_456');

        $this->get('/pendaftaran')->assertOk()->assertSee('Kirim Pendaftaran');
        Http::assertSentCount(1);
    }

    public function test_invalid_checkbox_option_displays_its_validation_message(): void
    {
        Http::preventStrayRequests();
        $url = 'https://docs.google.com/forms/d/e/RenderingTest/viewform';
        Http::fake([$url => Http::response('<script>var FB_PUBLIC_LOAD_DATA_ = [null,["Deskripsi",[[123,"Minat",null,4,[[456,[["PKD"]],1]]]],null,null,null,null,null,null,"Pendaftaran"]];</script>')]);
        RegistrationForm::factory()->create(['form_url' => $url]);

        $this->from('/pendaftaran')->followingRedirects()
            ->post('/pendaftaran', ['entry_456' => ['Pilihan lama']])
            ->assertOk()
            ->assertSee('Pilihan Minat tidak valid.');

        Http::assertSentCount(1);
    }

    #[DataProvider('textQuestionTypes')]
    public function test_valid_old_text_is_preserved_and_escaped(int $typeCode): void
    {
        Http::preventStrayRequests();
        $url = 'https://docs.google.com/forms/d/e/RenderingTest/viewform';
        Http::fake([$url => Http::response('<script>var FB_PUBLIC_LOAD_DATA_ = [null,["Deskripsi",[[123,"Nama",null,'.$typeCode.',[[456,null,1]]]],null,null,null,null,null,null,"Pendaftaran"]];</script>')]);
        RegistrationForm::factory()->create(['form_url' => $url]);
        $input = '<script>alert("nama")</script>';

        $this->withSession(['_old_input' => ['entry_456' => $input]])
            ->get('/pendaftaran')
            ->assertOk()
            ->assertSee($input)
            ->assertDontSee($input, false);

        Http::assertSentCount(1);
    }

    public function test_open_graph_type_is_independent_of_the_last_form_field(): void
    {
        Http::preventStrayRequests();
        $url = 'https://docs.google.com/forms/d/e/RenderingTest/viewform';
        Http::fake([$url => Http::response('<script>var FB_PUBLIC_LOAD_DATA_ = [null,["Deskripsi",[[123,"Minat",null,4,[[456,[["PKD"]],1]]]],null,null,null,null,null,null,"Pendaftaran"]];</script>')]);
        RegistrationForm::factory()->create(['form_url' => $url]);

        $response = $this->get('/pendaftaran')->assertOk();

        $document = new DOMDocument;
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $meta = (new DOMXPath($document))->query('//meta[@property="og:type"]')->item(0);
        $this->assertSame('website', $meta->getAttribute('content'));
        Http::assertSentCount(1);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function textQuestionTypes(): array
    {
        return [
            'short answer' => [0],
            'paragraph' => [1],
        ];
    }
}
