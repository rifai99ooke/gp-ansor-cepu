<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\HtmlSanitizerService;
use App\Services\ImageService;
use Database\Factories\BodyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Body extends Model
{
    /** @use HasFactory<BodyFactory> */
    use HasFactory, HasUniqueSlug;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Body $body): void {
            $body->ensureUniqueSlug('name');

            if (($body->isDirty('description') || ! $body->exists) && filled($body->description)) {
                $body->description = app(HtmlSanitizerService::class)->sanitize($body->description);
            }

            if (! $body->exists && blank($body->sort_order)) {
                $body->sort_order = ((int) static::query()->max('sort_order')) + 1;
            }
        });

        static::saved(function (Body $body): void {
            if (($body->wasRecentlyCreated || $body->wasChanged('logo')) && filled($body->logo)) {
                app(ImageService::class)->generateWebpVariants($body->logo);
            }
        });
    }

    /**
     * @param  Builder<Body>  $query
     * @return Builder<Body>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  Builder<Body>  $query
     * @return Builder<Body>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $this->applyLikeSearch($query, $term, ['name', 'description']);
    }

    public function logoUrl(?int $width = null): ?string
    {
        return ImageService::url($this->logo, $width);
    }

    public function initials(): string
    {
        $parts = array_values(array_filter(explode(' ', trim($this->name))));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1).substr($parts[1], 0, 1));
        }

        return strtoupper(substr($this->name, 0, 2));
    }
}
