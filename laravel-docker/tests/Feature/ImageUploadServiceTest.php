<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class ImageUploadServiceTest extends TestCase
{
    public function test_a_verified_jpeg_is_stored_with_public_read_permissions(): void
    {
        config()->set('services.virustotal.required', false);
        config()->set('services.virustotal.key', '');

        $directory = storage_path('framework/testing/image-upload-' . bin2hex(random_bytes(8)));
        $this->assertTrue(mkdir($directory, 0700, true));
        $target = $directory . DIRECTORY_SEPARATOR . 'CE001.jpg';

        try {
            $image = UploadedFile::fake()->image('puesto.jpg', 640, 480);
            app(ImageUploadService::class)->storeJpeg('CE001', $image, $target);

            $this->assertFileExists($target);
            $this->assertSame('image/jpeg', mime_content_type($target));
            $this->assertSame('0644', substr(sprintf('%o', (int) fileperms($target)), -4));
            $this->assertSame([], glob($directory . DIRECTORY_SEPARATOR . '.upload-*.jpg'));
        } finally {
            @unlink($target);
            @rmdir($directory);
        }
    }
}
