<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\HtmlSanitizerService;
use App\Services\ImageService;
use Database\Factories\OfficerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Officer extends Model
{
    /** @use HasFactory<OfficerFactory> */
    use HasFactory, HasUniqueSlug;

    protected $fillable = [
        'name',
        'slug',
        'position',
        'period',
        'photo',
        'bio',
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
        static::saving(function (Officer $officer): void {
            $officer->ensureUniqueSlug('name');

            if (($officer->isDirty('bio') || ! $officer->exists) && filled($officer->bio)) {
                $officer->bio = app(HtmlSanitizerService::class)->sanitize($officer->bio);
            }

            if (! $officer->exists && blank($officer->sort_order)) {
                $officer->sort_order = ((int) static::query()->max('sort_order')) + 1;
            }
        });

        static::saved(function (Officer $officer): void {
            if (($officer->wasRecentlyCreated || $officer->wasChanged('photo')) && filled($officer->photo)) {
                app(ImageService::class)->generateWebpVariants($officer->photo);
            }
        });
    }

    /**
     * @param  Builder<Officer>  $query
     * @return Builder<Officer>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  Builder<Officer>  $query
     * @return Builder<Officer>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $this->applyLikeSearch($query, $term, ['name', 'position']);
    }

    public function photoUrl(?int $width = null): ?string
    {
        return ImageService::url($this->photo, $width);
    }

    public function photoSrcset(): ?string
    {
        return ImageService::srcset($this->photo);
    }

    public function initials(): string
    {
        $clean = preg_replace('/^(H\.|Dr\.|KH\.|Prof\.|Ir\.|A\.)\s*/i', '', $this->name) ?: $this->name;
        $parts = array_values(array_filter(explode(' ', trim($clean))));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1).substr($parts[1], 0, 1));
        }

        return strtoupper(substr($clean, 0, 2));
    }
}
