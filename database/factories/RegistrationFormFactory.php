<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RegistrationForm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RegistrationForm>
 */
class RegistrationFormFactory extends Factory
{
    protected $model = RegistrationForm::class;

    public function definition(): array
    {
        $title = 'Pendaftaran '.ucfirst(fake()->unique()->words(2, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(2),
            'cover_image' => null,
            'form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSdSamplePlaceholderFormId1934/viewform',
            'embed_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSdSamplePlaceholderFormId1934/viewform?embedded=true',
            'responses_url' => 'https://docs.google.com/spreadsheets/d/1SampleResponsesSheetId1934/edit',
            'opens_at' => now()->subDays(7),
            'closes_at' => now()->addDays(60),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
