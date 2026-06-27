<?php

namespace App\Services;

class IdCardPhotoService
{
    private const SOURCE_DIR = 'uploads/peserta/foto/';
    private const TARGET_DIR = 'uploads/peserta/foto/id-card/';
    private const MAX_WIDTH = 720;
    private const MAX_HEIGHT = 900;
    private const JPG_QUALITY = 88;

    /**
     * Return optimized participant photo URL for ID card.
     * Falls back to original photo when GD cannot process the file.
     */
    public function photoUrl(?string $filename): string
    {
        $filename = $this->sanitizeFilename((string) $filename);
        if ($filename === '') {
            return '';
        }

        $sourcePath = FCPATH . self::SOURCE_DIR . $filename;
        if (! is_file($sourcePath)) {
            return '';
        }

        $optimized = $this->ensureOptimizedPhoto($sourcePath, $filename);
        if ($optimized !== '') {
            return base_url(self::TARGET_DIR . $optimized) . '?v=' . (@filemtime(FCPATH . self::TARGET_DIR . $optimized) ?: time());
        }

        return base_url(self::SOURCE_DIR . $filename) . '?v=' . (@filemtime($sourcePath) ?: time());
    }

    /**
     * Pre-generate optimized participant photos before batch rendering starts.
     *
     * @param list<string|null> $filenames
     */
    public function prewarm(array $filenames): int
    {
        $created = 0;

        foreach (array_unique(array_filter(array_map('strval', $filenames))) as $filename) {
            $filename = $this->sanitizeFilename($filename);
            if ($filename === '') {
                continue;
            }

            $sourcePath = FCPATH . self::SOURCE_DIR . $filename;
            if (! is_file($sourcePath)) {
                continue;
            }

            if ($this->ensureOptimizedPhoto($sourcePath, $filename) !== '') {
                $created++;
            }
        }

        return $created;
    }

    public function ensureOptimizedPhoto(string $sourcePath, string $filename): string
    {
        if (! extension_loaded('gd')) {
            return '';
        }

        $meta = @getimagesize($sourcePath);
        if ($meta === false || ! isset($meta[0], $meta[1], $meta[2])) {
            return '';
        }

        $type = (int) $meta[2];
        if (! in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            return '';
        }

        $targetDir = FCPATH . self::TARGET_DIR;
        $this->ensureDirectory($targetDir);

        $targetFilename = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
        $targetFilename = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $targetFilename) ?: ('foto_' . md5($filename) . '.jpg');
        $targetPath = $targetDir . $targetFilename;

        $sourceMtime = @filemtime($sourcePath) ?: time();
        if (is_file($targetPath) && ((@filemtime($targetPath) ?: 0) >= $sourceMtime)) {
            return $targetFilename;
        }

        $source = $this->createSourceImage($sourcePath, $type);
        if ($source === false) {
            return '';
        }

        [$targetWidth, $targetHeight] = $this->targetSize((int) $meta[0], (int) $meta[1]);
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($canvas === false) {
            imagedestroy($source);
            return '';
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $white);

        $ok = imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            (int) $meta[0],
            (int) $meta[1]
        );

        imagedestroy($source);

        if (! $ok) {
            imagedestroy($canvas);
            return '';
        }

        $saved = imagejpeg($canvas, $targetPath, self::JPG_QUALITY);
        imagedestroy($canvas);

        return $saved ? $targetFilename : '';
    }

    /**
     * Keep participant photo sharp for ID card while capping oversized phone photos.
     *
     * @return array{0:int,1:int}
     */
    private function targetSize(int $width, int $height): array
    {
        if ($width <= 0 || $height <= 0) {
            return [self::MAX_WIDTH, self::MAX_HEIGHT];
        }

        $ratio = min(self::MAX_WIDTH / $width, self::MAX_HEIGHT / $height, 1);

        return [
            max(1, (int) round($width * $ratio)),
            max(1, (int) round($height * $ratio)),
        ];
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = basename($filename);

        return preg_match('/\.(jpe?g|png)$/i', $filename) === 1 ? $filename : '';
    }

    private function ensureDirectory(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $index = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'index.html';
        if (! is_file($index)) {
            file_put_contents($index, '');
        }
    }

    private function createSourceImage(string $path, int $type)
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            default => false,
        };
    }
}

