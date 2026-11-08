<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'posts' => 'title',
            'officers' => 'name',
            'bodies' => 'name',
            'rantings' => 'name',
            'galleries' => 'title',
            'pages' => 'title',
            'registration_forms' => 'title',
        ] as $table => $sourceAttribute) {
            DB::table($table)
                ->select(['id', 'slug', $sourceAttribute])
                ->chunkById(100, function (Collection $records) use ($table, $sourceAttribute): void {
                    foreach ($records as $record) {
                        if (! blank($record->slug)) {
                            continue;
                        }

                        $baseSlug = Str::slug((string) $record->{$sourceAttribute});
                        if ($baseSlug === '') {
                            $baseSlug = 'item';
                        }
                        $slug = $baseSlug;
                        $counter = 2;

                        while (DB::table($table)->where('slug', $slug)->exists()) {
                            $slug = "{$baseSlug}-{$counter}";
                            $counter++;
                        }

                        DB::table($table)->where('id', $record->id)->update(['slug' => $slug]);
                    }
                });
        }
    }

    /**
     * Repaired URLs cannot safely be reverted to their original unusable slugs.
     */
    public function down(): void {}
};
