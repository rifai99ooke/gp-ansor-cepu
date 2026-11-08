<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Officer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Officer>
 */
class OfficerFactory extends Factory
{
    protected $model = Officer::class;

    public function definition(): array
    {
        $name = 'H. '.fake()->unique()->name('male');

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'position' => fake()->randomElement(['Ketua Umum', 'Sekretaris Jenderal', 'Bendahara Umum', 'Wakil Ketua Umum']),
            'period' => '2024 - 2029',
            'photo' => null,
            'bio' => '<p>'.fake()->paragraph(3).'</p>',
            'sort_order' => 0,
        ];
    }
}
