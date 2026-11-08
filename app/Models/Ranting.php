<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\ImageService;
use Database\Factories\RantingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ranting extends Model
{
    /** @use HasFactory<RantingFactory> */
    use HasFactory, HasUniqueSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'cover',
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
        static::saving(function (Ranting $ranting): void {
            $ranting->ensureUniqueSlug('name');

            if (! $ranting->exists && blank($ranting->sort_order)) {
                $ranting->sort_order = ((int) static::query()->max('sort_order')) + 1;
            }
        });

        static::saved(function (Ranting $ranting): void {
            if (($ranting->wasRecentlyCreated || $ranting->wasChanged('cover')) && filled($ranting->cover)) {
                app(ImageService::class)->generateWebpVariants($ranting->cover);
            }
        });
    }

    /**
     * @param  Builder<Ranting>  $query
     * @return Builder<Ranting>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  Builder<Ranting>  $query
     * @return Builder<Ranting>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $this->applyLikeSearch($query, $term, ['name', 'description']);
    }

    /** @return HasMany<RantingMember, Ranting> */
    public function members(): HasMany
    {
        return $this->hasMany(RantingMember::class)->orderBy('sort_order')->orderBy('id');
    }

    public function coverUrl(?int $width = null): ?string
    {
        return ImageService::url($this->cover, $width);
    }

    public function coverSrcset(): ?string
    {
        return ImageService::srcset($this->cover);
    }
}
