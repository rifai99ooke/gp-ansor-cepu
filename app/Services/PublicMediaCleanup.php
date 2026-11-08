<?php

namespace App\Services;

use App\Models\Body;
use App\Models\Gallery;
use App\Models\Officer;
use App\Models\Page;
use App\Models\Post;
use App\Models\Ranting;
use App\Models\RantingMember;
use App\Models\RegistrationForm;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
use WeakMap;

class PublicMediaCleanup
{
    /** @var array<class-string<Model>, string> */
    private const MEDIA_ATTRIBUTES = [
        Post::class => 'cover_image',
        Officer::class => 'photo',
        Body::class => 'logo',
        Gallery::class => 'image_path',
        RegistrationForm::class => 'cover_image',
        Ranting::class => 'cover',
        RantingMember::class => 'photo',
        Setting::class => 'value',
    ];

    /** @var array<class-string<Model>, string> */
    private const HTML_ATTRIBUTES = [
        Post::class => 'content',
        Officer::class => 'bio',
        Body::class => 'description',
        RegistrationForm::class => 'description',
        Page::class => 'content',
    ];

    /** @var WeakMap<Ranting, array<int, ?string>> */
    private WeakMap $memberPhotos;

    public function __construct()
    {
        $this->memberPhotos = new WeakMap;
    }

    public function registerModelEvents(): void
    {
        foreach (self::MEDIA_ATTRIBUTES as $modelClass => $attribute) {
            $modelClass::updated(function (Model $record) use ($attribute): void {
                if ($record->wasChanged($attribute) || ($record instanceof Setting && $record->wasChanged('key'))) {
                    $this->scheduleCleanup($record, [$this->originalMediaPath($record, $attribute)]);
                }
            });

            $modelClass::deleted(function (Model $record) use ($attribute): void {
                $paths = [$this->originalMediaPath($record, $attribute)];
                if ($record instanceof Ranting) {
                    $paths = [...$paths, ...($this->memberPhotos[$record] ?? [])];
                    unset($this->memberPhotos[$record]);
                }

                $this->scheduleCleanup($record, $paths);
            });
        }

        Ranting::deleting(function (Ranting $ranting): void {
            $this->memberPhotos[$ranting] = $ranting->members()->pluck('photo')->all();
        });
    }

    private function originalMediaPath(Model $record, string $attribute): ?string
    {
        if ($record instanceof Setting && ! in_array($record->getRawOriginal('key'), ['site_logo', 'site_favicon'], true)) {
            return null;
        }

        return $record->getRawOriginal($attribute);
    }

    /** @param array<int, ?string> $paths */
    private function scheduleCleanup(Model $record, array $paths): void
    {
        $paths = array_values(array_unique(array_filter($paths, fn (?string $path): bool => filled($path))));
        if ($paths === []) {
            return;
        }

        $record->getConnection()->afterCommit(function () use ($paths): void {
            foreach ($paths as $path) {
                try {
                    $this->deleteUnusedMedia($path);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        });
    }

    private function deleteUnusedMedia(string $path): void
    {
        if (str_starts_with($path, 'placeholders/') || strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'svg') {
            return;
        }

        if (! preg_match('~\A(?:branding|posts|officers|bodies|forms|galleries|rantings|ranting-members)/[a-z0-9][a-z0-9._-]*\.(?:jpe?g|png|webp|gif|ico)\z~i', $path)) {
            Log::warning('Refused public media cleanup for an unmanaged path.', ['path_hash' => hash('sha256', $path)]);

            return;
        }

        $stem = substr($path, 0, (int) strrpos($path, '.'));
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'webp') {
            $stem = preg_replace('/-(480|800|1280)w$/', '', $stem);
        }

        $variants = [$stem.'.webp'];
        foreach (ImageService::VARIANT_WIDTHS as $width) {
            $variants[] = $stem.'-'.$width.'w.webp';
        }

        $references = $variants;
        foreach (['jpg', 'jpeg', 'png', 'webp', 'gif', 'ico'] as $extension) {
            $references[] = $stem.'.'.$extension;
        }

        if ($this->hasReferences($references)) {
            return;
        }

        $disk = Storage::disk('public');
        $root = realpath($disk->path(''));
        if ($root === false) {
            return;
        }

        foreach (array_unique([$path, ...$variants]) as $candidate) {
            $absolutePath = $disk->path($candidate);
            $resolvedPath = realpath($absolutePath);
            if ($resolvedPath === false) {
                continue;
            }

            if (is_link($absolutePath) || is_link(dirname($absolutePath)) || ! is_file($resolvedPath) || ! str_starts_with($resolvedPath, $root.DIRECTORY_SEPARATOR)) {
                Log::warning('Refused public media cleanup outside its managed location.', ['path' => $candidate]);

                continue;
            }

            if (! $disk->delete($candidate)) {
                Log::warning('Could not remove unused public media.', ['path' => $candidate]);
            }
        }

        ImageService::flushCache();
    }

    /** @param array<int, string> $paths */
    private function hasReferences(array $paths): bool
    {
        foreach (self::MEDIA_ATTRIBUTES as $modelClass => $attribute) {
            $query = $modelClass::query()->whereNotNull($attribute);
            if ($modelClass === Setting::class) {
                $query->whereIn('key', ['site_logo', 'site_favicon']);
            }

            foreach ($query->select(['id', $attribute])->lazyById(100) as $record) {
                if (in_array($this->publicPath((string) $record->getRawOriginal($attribute)), $paths, true)) {
                    return true;
                }
            }
        }

        foreach (self::HTML_ATTRIBUTES as $modelClass => $attribute) {
            foreach ($modelClass::query()->select(['id', $attribute])->whereNotNull($attribute)->lazyById(100) as $record) {
                preg_match_all('/\b(?:src|href)\s*=\s*(["\'])(.*?)\1/is', (string) $record->getRawOriginal($attribute), $matches);
                foreach ($matches[2] as $reference) {
                    if (in_array($this->publicPath($reference), $paths, true)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function publicPath(string $reference): string
    {
        $reference = rawurldecode(html_entity_decode($reference, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $path = parse_url($reference, PHP_URL_PATH);
        $path = is_string($path) ? $path : $reference;
        $segments = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '..') {
                array_pop($segments);
            } elseif ($segment !== '' && $segment !== '.') {
                $segments[] = $segment;
            }
        }

        $path = implode('/', $segments);
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $extension !== '' ? substr($path, 0, -strlen($extension)).strtolower($extension) : $path;
    }
}
