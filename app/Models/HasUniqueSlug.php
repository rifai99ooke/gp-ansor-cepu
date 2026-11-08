<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasUniqueSlug
{
    /**
     * Populate the model's slug attribute with a unique slug if currently blank.
     */
    protected function ensureUniqueSlug(string $sourceAttribute = 'title'): void
    {
        $sourceValue = (string) ($this->getAttribute($sourceAttribute) ?? '');

        if (blank($this->getAttribute('slug')) && filled($sourceValue)) {
            $ignoreId = $this->exists ? (int) $this->getKey() : null;
            $this->setAttribute('slug', static::generateUniqueSlug($sourceValue, $ignoreId));
        }
    }

    /**
     * Generate a unique slug for the model table.
     */
    public static function generateUniqueSlug(string $source, ?int $ignoreId = null, string $column = 'slug'): string
    {
        $baseSlug = Str::slug($source);
        $slug = $baseSlug;
        $counter = 2;

        while (
            static::query()
                ->where($column, $slug)
                ->when($ignoreId !== null, fn (Builder $q): Builder => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Apply a wildcard-escaped LIKE search across the given columns with an explicit ESCAPE clause.
     *
     * @param  Builder<static>  $query
     * @param  array<int, string>  $columns
     * @return Builder<static>
     */
    protected function applyLikeSearch(Builder $query, ?string $term, array $columns): Builder
    {
        $term = trim((string) $term);
        if ($term === '' || $columns === []) {
            return $query;
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term).'%';
        $grammar = $query->getQuery()->getGrammar();

        return $query->where(function (Builder $q) use ($columns, $like, $grammar): void {
            foreach ($columns as $index => $column) {
                $wrapped = $grammar->wrap($column);
                $sql = "{$wrapped} LIKE ? ESCAPE '\\'";

                if ($index === 0) {
                    $q->whereRaw($sql, [$like]);
                } else {
                    $q->orWhereRaw($sql, [$like]);
                }
            }
        });
    }
}
