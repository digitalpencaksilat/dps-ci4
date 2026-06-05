<?php

namespace Tests\Unit\Services;

use App\Services\ImageOptimizerService;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;

class ImageOptimizerServiceTest extends CIUnitTestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = WRITEPATH . 'tests/image-optimizer/';
        if (! is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        array_map('unlink', glob($this->tempDir . '*'));
        @rmdir($this->tempDir);
    }

    public function testOptimizeValidJpegSucceeds(): void
    {
        $tempFile = $this->createTempJpeg(200, 200);
        $uploaded = $this->mockUploaded($tempFile);

        $service = new ImageOptimizerService();
        $result = $service->optimizeAndStore($uploaded, $this->tempDir, 'test-image', 100, 80, 6);

        $this->assertStringEndsWith('.jpg', $result);
        $this->assertFileExists($this->tempDir . $result);
    }

    public function testFallbackDisabledThrowsOnCorruptImage(): void
    {
        $tempFile = $this->createCorruptFile();
        $uploaded = $this->mockUploaded($tempFile, 'image/jpeg');

        $service = new ImageOptimizerService();

        $this->expectException(\RuntimeException::class);
        $service->optimizeAndStore($uploaded, $this->tempDir, 'corrupt', 100, 80, 6, fallbackToRaw: false);
    }

    public function testFallbackEnabledSavesRawOnCorruptImage(): void
    {
        $tempFile = $this->createCorruptFile();
        $uploaded = $this->mockUploaded($tempFile, 'image/jpeg');

        $service = new ImageOptimizerService();
        $result = $service->optimizeAndStore($uploaded, $this->tempDir, 'corrupt-fallback', 100, 80, 6, fallbackToRaw: true);

        $this->assertStringEndsWith('.jpg', $result);
        $this->assertFileExists($this->tempDir . $result);
    }

    private function createTempJpeg(int $w, int $h): string
    {
        $img = imagecreatetruecolor($w, $h);
        $path = tempnam(sys_get_temp_dir(), 'img_') . '.jpg';
        imagejpeg($img, $path, 80);
        imagedestroy($img);
        return $path;
    }

    private function createCorruptFile(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'corrupt_') . '.jpg';
        file_put_contents($path, "\xFF\xD8\xFF\xE0" . str_repeat("\x00", 100));
        return $path;
    }

    private function mockUploaded(string $path, string $mime = 'image/jpeg'): UploadedFile
    {
        return new UploadedFile(
            $path,
            basename($path),
            $mime,
            filesize($path),
            UPLOAD_ERR_OK
        );
    }
}
