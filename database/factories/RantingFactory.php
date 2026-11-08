<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ranting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Ranting>
 */
class RantingFactory extends Factory
{
    protected $model = Ranting::class;

    public function definition(): array
    {
        $name = 'Ranting '.fake()->unique()->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'cover' => null,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
