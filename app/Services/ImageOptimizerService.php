<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

class ImageOptimizerService
{
    public function optimizeAndStore(
        UploadedFile $uploaded,
        string $targetDir,
        string $baseName,
        int $maxSide = 1600,
        int $jpgQuality = 82,
        int $pngCompression = 6
    ): string {
        $meta = $this->detectImageMeta($uploaded);
        $this->ensureDirectory($targetDir);

        $source = $this->createSourceImage($uploaded->getTempName(), $meta['type']);
        if ($source === false) {
            throw new \RuntimeException('Gagal memproses file gambar yang diunggah.');
        }

        $targetWidth = $meta['width'];
        $targetHeight = $meta['height'];

        $longestSide = max($meta['width'], $meta['height']);
        if ($maxSide > 0 && $longestSide > $maxSide) {
            $ratio = $maxSide / $longestSide;
            $targetWidth = max(1, (int) round($meta['width'] * $ratio));
            $targetHeight = max(1, (int) round($meta['height'] * $ratio));
        }

        $canvas = $this->createCanvas($meta['type'], $targetWidth, $targetHeight);
        if ($canvas === false) {
            imagedestroy($source);
            throw new \RuntimeException('Gagal menyiapkan kanvas gambar.');
        }

        if (! imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $meta['width'], $meta['height'])) {
            imagedestroy($source);
            imagedestroy($canvas);
            throw new \RuntimeException('Gagal melakukan optimasi ukuran gambar.');
        }

        $fileName = $baseName . '.' . $meta['extension'];
        $targetPath = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

        $saved = $meta['type'] === IMAGETYPE_PNG
            ? imagepng($canvas, $targetPath, max(0, min(9, $pngCompression)))
            : imagejpeg($canvas, $targetPath, max(10, min(100, $jpgQuality)));

        imagedestroy($source);
        imagedestroy($canvas);

        if (! $saved) {
            throw new \RuntimeException('Gagal menyimpan file gambar hasil optimasi.');
        }

        return $fileName;
    }

    public function detectImageMeta(UploadedFile $uploaded): array
    {
        $tempPath = $uploaded->getTempName();
        if (! is_file($tempPath)) {
            throw new \RuntimeException('File upload sementara tidak ditemukan.');
        }

        $info = @getimagesize($tempPath);
        if ($info === false || ! isset($info[0], $info[1], $info[2], $info['mime'])) {
            throw new \RuntimeException('File yang diunggah harus berupa gambar yang valid.');
        }

        $supportedTypes = [
            IMAGETYPE_JPEG => ['extension' => 'jpg', 'mime' => 'image/jpeg'],
            IMAGETYPE_PNG => ['extension' => 'png', 'mime' => 'image/png'],
        ];

        if (! isset($supportedTypes[$info[2]])) {
            throw new \RuntimeException('Format gambar yang didukung hanya JPG, JPEG, dan PNG.');
        }

        return [
            'width' => (int) $info[0],
            'height' => (int) $info[1],
            'type' => (int) $info[2],
            'mime' => strtolower((string) $supportedTypes[$info[2]]['mime']),
            'extension' => (string) $supportedTypes[$info[2]]['extension'],
        ];
    }

    private function ensureDirectory(string $targetDir): void
    {
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $targetIndex = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'index.html';
        if (! is_file($targetIndex)) {
            file_put_contents($targetIndex, '');
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

    private function createCanvas(int $type, int $width, int $height)
    {
        $canvas = imagecreatetruecolor($width, $height);
        if ($canvas === false) {
            return false;
        }

        if ($type === IMAGETYPE_PNG) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
        }

        return $canvas;
    }
}
