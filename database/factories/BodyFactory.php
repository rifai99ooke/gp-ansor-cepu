<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Body;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Body>
 */
class BodyFactory extends Factory
{
    protected $model = Body::class;

    public function definition(): array
    {
        $name = 'Lembaga '.ucfirst(fake()->unique()->word());

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'logo' => null,
            'description' => '<p>'.fake()->paragraph(3).'</p>',
            'sort_order' => 0,
        ];
    }
}
