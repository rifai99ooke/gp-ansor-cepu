<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\ImageService;
use Database\Factories\RantingMemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RantingMember extends Model
{
    /** @use HasFactory<RantingMemberFactory> */
    use HasFactory;

    protected $fillable = [
        'ranting_id',
        'name',
        'photo',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'ranting_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (RantingMember $member): void {
            if (! $member->exists && blank($member->sort_order)) {
                $member->sort_order = ((int) static::query()
                    ->where('ranting_id', $member->ranting_id)
                    ->max('sort_order')) + 1;
            }
        });

        static::saved(function (RantingMember $member): void {
            if (($member->wasRecentlyCreated || $member->wasChanged('photo')) && filled($member->photo)) {
                app(ImageService::class)->generateWebpVariants($member->photo);
            }
        });
    }

    /** @return BelongsTo<Ranting, RantingMember> */
    public function ranting(): BelongsTo
    {
        return $this->belongsTo(Ranting::class);
    }

    /**
     * @param  Builder<RantingMember>  $query
     * @return Builder<RantingMember>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
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
