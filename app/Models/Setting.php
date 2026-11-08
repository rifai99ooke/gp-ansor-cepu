<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\ImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

class Setting extends Model
{
    public const CACHE_KEY = 'site_settings_map';

    /**
     * Allowed hosts for Google Maps iframe embeds.
     *
     * @var array<int, string>
     */
    public const ALLOWED_MAP_HOSTS = [
        'maps.google.com',
        'www.google.com',
        'google.com',
    ];

    protected $fillable = [
        'key',
        'value',
    ];

    protected static function booted(): void
    {
        static::saved(function (Setting $setting): void {
            if (in_array($setting->key, ['site_logo', 'site_favicon'], true) && filled($setting->value)) {
                app(ImageService::class)->generateWebpVariants((string) $setting->value);
            }

            static::clearCache();
            $setting->getConnection()->afterCommit(static function (): void {
                static::clearCache();
            });
        });

        static::deleted(function (Setting $setting): void {
            static::clearCache();
            $setting->getConnection()->afterCommit(static function (): void {
                static::clearCache();
            });
        });
    }

    /**
     * Default setting values matching the GP Ansor reference screenshots.
     *
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'site_name' => 'GP Ansor',
            'site_full_name' => 'Gerakan Pemuda Ansor',
            'site_tagline' => 'Membentuk generasi muda yang berakhlak mulia, nasionalis, dan siap menghadapi tantangan masa depan.',
            'site_logo' => 'branding/logo-ansor.svg',
            'site_favicon' => 'branding/logo-ansor.svg',
            'contact_address' => 'Jl. Kramat Raya No.65 1, RT.1/RW.8, Kramat, Kec. Senen, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10450',
            'map_embed_url' => 'https://maps.google.com/maps?q=GERAKAN%20PEMUDA%20ANSOR%20Jl.%20Kramat%20Raya%20No.65%20Jakarta%20Pusat&t=k&z=16&ie=UTF8&iwloc=&output=embed',
            'contact_phone_1' => '08111021934',
            'contact_phone_2' => '0213162929',
            'contact_email' => 'sekretariat@ansor.id',
            'social_tiktok_handle' => '@gpansor1934',
            'social_tiktok_url' => 'https://www.tiktok.com/@gpansor1934',
            'social_facebook_handle' => 'gerakanpemudaansor',
            'social_facebook_url' => 'https://www.facebook.com/gerakanpemudaansor',
            'social_instagram_handle' => 'gp.ansor',
            'social_instagram_url' => 'https://www.instagram.com/gp.ansor',
            'social_youtube_handle' => '@GerakanPemudaAnsor',
            'social_youtube_url' => 'https://www.youtube.com/@GerakanPemudaAnsor',
            'footer_copyright' => 'GP Ansor. All rights reserved.',
            'address_locality' => 'Jakarta Pusat',
            'address_region' => 'DKI Jakarta',
            'address_postal_code' => '10450',
        ];
    }

    /**
     * Extract and normalize a Google Maps embed URL from raw URL or <iframe> markup.
     */
    public static function normalizeMapEmbedUrl(?string $raw): string
    {
        $trimmed = trim((string) $raw);
        if ($trimmed === '') {
            return '';
        }

        if (stripos($trimmed, '<iframe') !== false && preg_match('/\bsrc\s*=\s*(["\'])(.*?)\1/i', $trimmed, $matches)) {
            $trimmed = html_entity_decode(trim($matches[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $trimmed;
    }

    /**
     * Check whether a map embed URL uses HTTPS and an allowed Google Maps host.
     */
    public static function isAllowedMapEmbedUrl(?string $url): bool
    {
        $normalized = static::normalizeMapEmbedUrl($url);
        if ($normalized === '') {
            return false;
        }

        $parsed = parse_url($normalized);
        if (! is_array($parsed)) {
            return false;
        }

        $scheme = strtolower($parsed['scheme'] ?? '');
        $host = strtolower($parsed['host'] ?? '');
        $path = $parsed['path'] ?? '';

        return $scheme === 'https'
            && in_array($host, self::ALLOWED_MAP_HOSTS, true)
            && str_starts_with($path, '/maps');
    }

    /**
     * Get a setting value with caching (10+ minutes) and fallback defaults.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $all = static::allCached();
        if (array_key_exists($key, $all)) {
            return $all[$key] ?? $default;
        }

        $defaults = static::defaults();

        return $default ?? ($defaults[$key] ?? null);
    }

    /**
     * Set or update a setting value and clear cache.
     */
    public static function setValue(string $key, mixed $value): self
    {
        return static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_scalar($value) || $value === null ? (string) $value : json_encode($value)]
        );
    }

    /**
     * Retrieve all settings as an associative array, cached for 10 minutes (600 seconds).
     *
     * @return array<string, ?string>
     */
    public static function allCached(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, 600, function (): array {
                $stored = static::query()->pluck('value', 'key')->all();

                return array_merge(static::defaults(), $stored);
            });
        } catch (Throwable) {
            return static::defaults();
        }
    }

    public static function clearCache(): void
    {
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (Throwable) {
            // Ignore cache errors during early bootstrap
        }
    }
}
