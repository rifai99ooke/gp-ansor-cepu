<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RegistrationStatus;
use App\Services\GoogleFormSchemaService;
use App\Services\GoogleFormUrlNormalizer;
use App\Services\HtmlSanitizerService;
use App\Services\ImageService;
use Database\Factories\RegistrationFormFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationForm extends Model
{
    /** @use HasFactory<RegistrationFormFactory> */
    use HasFactory, HasUniqueSlug;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'form_url',
        'embed_url',
        'responses_url',
        'opens_at',
        'closes_at',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (RegistrationForm $form): void {
            $form->ensureUniqueSlug('title');

            if ($form->isDirty('description') && filled($form->description)) {
                $form->description = app(HtmlSanitizerService::class)->sanitize($form->description);
            }

            if ($form->isDirty('form_url') || (! $form->exists && filled($form->form_url))) {
                $normalized = app(GoogleFormUrlNormalizer::class)->normalize((string) $form->form_url);
                $form->form_url = $normalized['form_url'];
                $form->embed_url = $normalized['embed_url'];
            }
        });

        static::saved(function (RegistrationForm $form): void {
            app(GoogleFormSchemaService::class)->forgetCache($form->form_url);

            if (($form->wasRecentlyCreated || $form->wasChanged('cover_image')) && filled($form->cover_image)) {
                app(ImageService::class)->generateWebpVariants($form->cover_image);
            }
        });
    }

    /**
     * @param  Builder<RegistrationForm>  $query
     * @return Builder<RegistrationForm>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<RegistrationForm>  $query
     * @return Builder<RegistrationForm>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function status(): RegistrationStatus
    {
        if (! $this->is_active) {
            return RegistrationStatus::Closed;
        }

        $now = now();

        if ($this->opens_at !== null && $now->lt($this->opens_at)) {
            return RegistrationStatus::Upcoming;
        }

        if ($this->closes_at !== null && $now->gt($this->closes_at)) {
            return RegistrationStatus::Closed;
        }

        return RegistrationStatus::Open;
    }

    public function isOpen(): bool
    {
        return $this->status() === RegistrationStatus::Open;
    }

    public function canEmbed(): bool
    {
        return $this->isOpen() && GoogleFormUrlNormalizer::isAllowedEmbedUrl($this->embed_url);
    }

    public function coverUrl(?int $width = null): ?string
    {
        return ImageService::url($this->cover_image, $width);
    }

    public function coverSrcset(): ?string
    {
        return ImageService::srcset($this->cover_image);
    }
}
