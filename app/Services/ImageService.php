<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Throwable;

class ImageService
{
    /**
     * Standard responsive WebP widths generated for uploaded raster images.
     *
     * @var array<int, int>
     */
    public const VARIANT_WIDTHS = [480, 800, 1280];

    /**
     * In-memory cache of public disk file existence checks to avoid repeated filesystem I/O when rendering lists.
     *
     * @var array<string, bool>
     */
    private static array $existsCache = [];

    public static function flushCache(): void
    {
        self::$existsCache = [];
    }

    private static function publicFileExists(string $relativePath): bool
    {
        $root = (string) config('filesystems.disks.public.root', '');
        $key = $root.'|'.$relativePath;

        if (! array_key_exists($key, self::$existsCache)) {
            self::$existsCache[$key] = Storage::disk('public')->exists($relativePath);
        }

        return self::$existsCache[$key];
    }

    /**
     * Generate resized WebP variants for an image stored on the public disk.
     * Returns the primary relative path (converted to .webp if raster and convertPrimary is true).
     */
    public function generateWebpVariants(string $relativePath, bool $convertPrimary = false): string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($relativePath)) {
            return $relativePath;
        }

        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        if ($extension === 'svg') {
            return $relativePath;
        }

        $fullPath = $disk->path($relativePath);
        $dir = pathinfo($relativePath, PATHINFO_DIRNAME);
        $filename = pathinfo($relativePath, PATHINFO_FILENAME);
        $prefix = ($dir !== '.' && $dir !== '') ? $dir.'/'.$filename : $filename;

        $sourceMtime = (int) @filemtime($fullPath);
        $targets = [];
        foreach (self::VARIANT_WIDTHS as $width) {
            $variantRel = "{$prefix}-{$width}w.webp";
            $variantFull = $disk->path($variantRel);
            if (! file_exists($variantFull) || (int) @filemtime($variantFull) < $sourceMtime) {
                $targets[(string) $width] = $variantFull;
            }
        }

        $primaryWebpRel = "{$prefix}.webp";
        $primaryWebpFull = $disk->path($primaryWebpRel);
        if ($extension !== 'webp' && (! file_exists($primaryWebpFull) || (int) @filemtime($primaryWebpFull) < $sourceMtime)) {
            $targets['1280_primary'] = $primaryWebpFull;
        }

        if ($targets === []) {
            if ($convertPrimary && $extension !== 'webp' && $disk->exists($primaryWebpRel)) {
                return $primaryWebpRel;
            }

            return $relativePath;
        }

        if ($this->resizeWithGd($fullPath, $targets) || $this->resizeWithPythonPillow($fullPath, $targets)) {
            self::flushCache();
            if ($convertPrimary && $extension !== 'webp' && $disk->exists($primaryWebpRel)) {
                return $primaryWebpRel;
            }
        }

        return $relativePath;
    }

    /**
     * Resolve the public URL for an image, optionally preferring a specific WebP variant width.
     */
    public static function url(?string $relativePath, ?int $preferredWidth = null): ?string
    {
        if ($relativePath === null || trim($relativePath) === '') {
            return null;
        }

        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://') || str_starts_with($relativePath, '/')) {
            return $relativePath;
        }

        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        if ($preferredWidth !== null && $extension !== 'svg') {
            $dir = pathinfo($relativePath, PATHINFO_DIRNAME);
            $filename = pathinfo($relativePath, PATHINFO_FILENAME);
            $prefix = ($dir !== '.' && $dir !== '') ? $dir.'/'.$filename : $filename;
            $variantRel = "{$prefix}-{$preferredWidth}w.webp";

            if (self::publicFileExists($variantRel)) {
                return '/storage/'.ltrim($variantRel, '/');
            }
        }

        if ($extension !== 'svg' && $extension !== 'webp') {
            $dir = pathinfo($relativePath, PATHINFO_DIRNAME);
            $filename = pathinfo($relativePath, PATHINFO_FILENAME);
            $prefix = ($dir !== '.' && $dir !== '') ? $dir.'/'.$filename : $filename;
            $webpRel = "{$prefix}.webp";

            if (self::publicFileExists($webpRel)) {
                return '/storage/'.ltrim($webpRel, '/');
            }
        }

        return '/storage/'.ltrim($relativePath, '/');
    }

    /**
     * Build a responsive srcset attribute string if WebP variants exist on the public disk.
     */
    public static function srcset(?string $relativePath): ?string
    {
        if ($relativePath === null || trim($relativePath) === '') {
            return null;
        }

        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return null;
        }

        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        if ($extension === 'svg') {
            return null;
        }

        $dir = pathinfo($relativePath, PATHINFO_DIRNAME);
        $filename = pathinfo($relativePath, PATHINFO_FILENAME);
        $prefix = ($dir !== '.' && $dir !== '') ? $dir.'/'.$filename : $filename;

        $entries = [];
        foreach (self::VARIANT_WIDTHS as $width) {
            $variantRel = "{$prefix}-{$width}w.webp";
            if (self::publicFileExists($variantRel)) {
                $entries[] = '/storage/'.ltrim($variantRel, '/')." {$width}w";
            }
        }

        return $entries !== [] ? implode(', ', $entries) : null;
    }

    /**
     * Resize and convert using PHP GD if available.
     *
     * @param  array<string, string>  $targets
     */
    private function resizeWithGd(string $sourcePath, array $targets): bool
    {
        if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
            return false;
        }

        try {
            $info = @getimagesize($sourcePath);
            if ($info === false) {
                return false;
            }

            [$origW, $origH, $type] = $info;
            $srcImg = match ($type) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
                IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
                IMAGETYPE_WEBP => @imagecreatefromwebp($sourcePath),
                default => false,
            };

            if (! $srcImg) {
                return false;
            }

            foreach ($targets as $key => $targetPath) {
                $maxWidth = (int) filter_var($key, FILTER_SANITIZE_NUMBER_INT);
                if ($maxWidth <= 0) {
                    $maxWidth = 1280;
                }

                $targetW = min($origW, $maxWidth);
                $targetH = (int) max(1, round($origH * ($targetW / max(1, $origW))));

                $dstImg = imagecreatetruecolor($targetW, $targetH);
                imagealphablending($dstImg, false);
                imagesavealpha($dstImg, true);
                imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $targetW, $targetH, $origW, $origH);

                @mkdir(dirname($targetPath), 0775, true);
                imagewebp($dstImg, $targetPath, 82);
                imagedestroy($dstImg);
            }

            imagedestroy($srcImg);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Resize and convert using Python 3 Pillow (PIL) when PHP GD is unavailable.
     *
     * @param  array<string, string>  $targets
     */
    private function resizeWithPythonPillow(string $sourcePath, array $targets): bool
    {
        try {
            $payload = json_encode([
                'source' => $sourcePath,
                'targets' => $targets,
            ], JSON_THROW_ON_ERROR);

            $pythonScript = <<<'PY'
import sys, json, os, re
from PIL import Image

data = json.loads(sys.argv[1])
src = data['source']
targets = data['targets']

with Image.open(src) as im:
    if im.mode not in ('RGB', 'RGBA'):
        im = im.convert('RGBA' if 'A' in im.getbands() else 'RGB')
    orig_w, orig_h = im.size
    for key, out_path in targets.items():
        m = re.search(r'(\d+)', str(key))
        max_w = int(m.group(1)) if m else 1280
        target_w = min(orig_w, max_w)
        target_h = max(1, int(round(orig_h * (target_w / max(1, orig_w)))))
        resized = im.resize((target_w, target_h), Image.Resampling.LANCZOS)
        os.makedirs(os.path.dirname(out_path), exist_ok=True)
        resized.save(out_path, format='WEBP', quality=82, method=4)
PY;

            $process = new Process(['python3', '-c', $pythonScript, $payload]);
            $process->setTimeout(15);
            $process->run();

            return $process->isSuccessful();
        } catch (Throwable) {
            return false;
        }
    }
}
