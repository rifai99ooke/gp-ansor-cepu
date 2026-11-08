<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(2, true);

        return [
            'slug' => Str::slug($title),
            'title' => ucfirst($title),
            'subtitle' => fake()->sentence(8),
            'content' => '<p>'.implode('</p><p>', fake()->paragraphs(3)).'</p>',
        ];
    }
}
