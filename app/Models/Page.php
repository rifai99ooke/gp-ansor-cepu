<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\HtmlSanitizerService;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, HasUniqueSlug;

    protected $fillable = [
        'slug',
        'title',
        'subtitle',
        'content',
    ];

    protected static function booted(): void
    {
        static::saving(function (Page $page): void {
            $page->ensureUniqueSlug('title');

            if ($page->isDirty('content') || ! $page->exists) {
                $page->content = app(HtmlSanitizerService::class)->sanitize($page->content);
            }
        });
    }
}
