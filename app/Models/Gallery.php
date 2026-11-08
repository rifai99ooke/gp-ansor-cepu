<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\ImageService;
use Database\Factories\GalleryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    /** @use HasFactory<GalleryFactory> */
    use HasFactory, HasUniqueSlug;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'image_path',
        'event_date',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Gallery $gallery): void {
            $gallery->ensureUniqueSlug('title');

            if (! $gallery->exists && blank($gallery->sort_order)) {
                $gallery->sort_order = ((int) static::query()->max('sort_order')) + 1;
            }
        });

        static::saved(function (Gallery $gallery): void {
            if (($gallery->wasRecentlyCreated || $gallery->wasChanged('image_path')) && filled($gallery->image_path)) {
                app(ImageService::class)->generateWebpVariants($gallery->image_path);
            }
        });
    }

    /**
     * @param  Builder<Gallery>  $query
     * @return Builder<Gallery>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('event_date')->orderByDesc('id');
    }

    /**
     * @param  Builder<Gallery>  $query
     * @return Builder<Gallery>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $this->applyLikeSearch($query, $term, ['title', 'description']);
    }

    public function imageUrl(?int $width = null): string
    {
        return ImageService::url($this->image_path, $width) ?? '/storage/placeholders/news-default.webp';
    }

    public function imageSrcset(): ?string
    {
        return ImageService::srcset($this->image_path);
    }
}
