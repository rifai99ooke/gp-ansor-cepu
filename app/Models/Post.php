<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Services\HtmlSanitizerService;
use App\Services\ImageService;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasUniqueSlug;

    /**
     * Columns required when rendering post cards/lists without loading the full longText content.
     *
     * @var array<int, string>
     */
    public const LIST_COLUMNS = [
        'id',
        'type',
        'title',
        'slug',
        'excerpt',
        'cover_image',
        'author_id',
        'author_name',
        'status',
        'is_featured',
        'views_count',
        'published_at',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'type',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'author_id',
        'author_name',
        'status',
        'is_featured',
        'views_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => PostType::class,
            'status' => PostStatus::class,
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            $post->ensureUniqueSlug('title');

            if (! $post->exists && $post->author_id === null && auth()->check()) {
                $post->author_id = (int) auth()->id();
            }

            if (! $post->exists && blank($post->author_name) && auth()->check()) {
                $post->author_name = (string) (auth()->user()?->name ?? 'Tim Redaksi GP Ansor');
            }

            if ($post->isDirty('content') || ! $post->exists) {
                $post->content = app(HtmlSanitizerService::class)->sanitize($post->content);
            }

            if ((blank($post->excerpt) || ($post->exists && $post->isDirty('content') && ! $post->isDirty('excerpt'))) && filled($post->content)) {
                $htmlWithSpaces = preg_replace('/<[^>]+>/', ' $0 ', (string) $post->content);
                $plainText = preg_replace('/\s+/', ' ', trim(strip_tags((string) $htmlWithSpaces)));
                $post->excerpt = Str::limit((string) $plainText, 160);
            }

            if ($post->status === PostStatus::Published && $post->published_at === null) {
                $post->published_at = now();
            }
        });

        static::saved(function (Post $post): void {
            if (($post->wasRecentlyCreated || $post->wasChanged('cover_image')) && filled($post->cover_image)) {
                app(ImageService::class)->generateWebpVariants($post->cover_image);
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope selecting only card-level columns and eager-loading lightweight author relation.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeForCard(Builder $query): Builder
    {
        return $query
            ->select(self::LIST_COLUMNS)
            ->with(['author:id,name']);
    }

    /**
     * Fetch featured hero carousel slides for Berita, falling back to latest published Berita when none are featured.
     *
     * @return Collection<int, Post>
     */
    public static function heroSlides(int $limit = 10): Collection
    {
        $slides = static::query()
            ->published()
            ->ofType(PostType::Berita)
            ->forCard()
            ->where('is_featured', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($slides->isNotEmpty()) {
            return $slides;
        }

        return static::query()
            ->published()
            ->ofType(PostType::Berita)
            ->forCard()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Scope for published posts.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', PostStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope for filtering by post type.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeOfType(Builder $query, PostType|string $type): Builder
    {
        $value = $type instanceof PostType ? $type->value : $type;

        return $query->where('type', $value);
    }

    /**
     * Scope for ordering by view count descending.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    /**
     * SQLite-friendly LIKE search scope with explicit ESCAPE clause.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $this->applyLikeSearch($query, $term, ['title', 'excerpt', 'content', 'author_name']);
    }

    public function coverUrl(?int $width = null): string
    {
        return ImageService::url($this->cover_image, $width) ?? '/storage/placeholders/news-default.webp';
    }

    public function coverSrcset(): ?string
    {
        return ImageService::srcset($this->cover_image);
    }

    public function displayAuthorName(): string
    {
        if (filled($this->author_name)) {
            return (string) $this->author_name;
        }

        return $this->author?->name ?? 'Redaksi GP Ansor';
    }

    public function readingTimeMinutes(): int
    {
        $words = str_word_count(strip_tags((string) $this->content));

        return max(1, (int) ceil($words / 180));
    }

    public function url(): string
    {
        return route('berita.show', $this->slug);
    }
}
