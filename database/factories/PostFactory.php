<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(7);

        return [
            'type' => PostType::Berita,
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(2),
            'content' => '<p>'.implode('</p><p>', fake()->paragraphs(4)).'</p>',
            'cover_image' => 'placeholders/news-default.webp',
            'author_id' => null,
            'author_name' => 'Tim Redaksi GP Ansor',
            'status' => PostStatus::Published,
            'is_featured' => false,
            'views_count' => fake()->numberBetween(10, 1500),
            'published_at' => now()->subDays(fake()->numberBetween(1, 180)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => PostStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn () => [
            'is_featured' => true,
        ]);
    }
}
