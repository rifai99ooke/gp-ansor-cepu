<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RantingMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RantingMember>
 */
class RantingMemberFactory extends Factory
{
    protected $model = RantingMember::class;

    public function definition(): array
    {
        return [
            'ranting_id' => null,
            'name' => fake()->name('male'),
            'photo' => null,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
