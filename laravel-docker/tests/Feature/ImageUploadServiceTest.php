<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ImageUploadService;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

final class ImageUploadServiceTest extends TestCase
{
    public function test_a_verified_jpeg_is_stored_with_public_read_permissions(): void
    {
        config()->set('services.virustotal.required', false);
        config()->set('services.virustotal.key', '');

        $directory = $this->temporaryDirectory();
        $target = $directory . DIRECTORY_SEPARATOR . 'CE001.jpg';

        try {
            $image = UploadedFile::fake()->image('puesto.jpg', 640, 480);
            app(ImageUploadService::class)->storeJpeg('CE001', $image, $target);

            $this->assertFileExists($target);
            $this->assertSame('image/jpeg', mime_content_type($target));
            $this->assertSame('0644', substr(sprintf('%o', (int) fileperms($target)), -4));
            $this->assertSame([], glob($directory . DIRECTORY_SEPARATOR . '.upload-*'));
        } finally {
            $this->removeTemporaryDirectory($directory);
        }
    }

    public function test_changing_the_original_upload_during_antivirus_scan_does_not_change_the_published_image(): void
    {
        config()->set('services.virustotal.required', true);
        config()->set('services.virustotal.key', 'test-api-key');

        $directory = $this->temporaryDirectory();
        $target = $directory . DIRECTORY_SEPARATOR . 'CE001.jpg';
        $original = UploadedFile::fake()->image('original.jpg', 640, 480);
        $sourcePath = (string) $original->getRealPath();
        $replacement = UploadedFile::fake()->image('replacement.jpg', 32, 32);
        $replacementBytes = file_get_contents((string) $replacement->getRealPath());

        Http::fake(function (HttpRequest $request) use ($sourcePath, $replacementBytes) {
            if (str_ends_with($request->url(), '/files')) {
                // Simulate a concurrent write to the untrusted upload after validation.
                file_put_contents($sourcePath, $replacementBytes);

                return Http::response(['data' => ['id' => 'test-analysis']], 200);
            }

            return $this->cleanAnalysis();
        });

        try {
            app(ImageUploadService::class)->storeJpeg('CE001', $original, $target);
            $dimensions = getimagesize($target);

            $this->assertSame(640, $dimensions[0]);
            $this->assertSame(480, $dimensions[1]);
            $this->assertSame([], glob($directory . DIRECTORY_SEPARATOR . '.upload-*'));
            Http::assertSentCount(2);
        } finally {
            $this->removeTemporaryDirectory($directory);
        }
    }

    public function test_modification_of_staged_image_during_scan_is_detected_and_previous_image_is_preserved(): void
    {
        config()->set('services.virustotal.required', true);
        config()->set('services.virustotal.key', 'test-api-key');

        $directory = $this->temporaryDirectory();
        $target = $directory . DIRECTORY_SEPARATOR . 'CE001.jpg';
        file_put_contents($target, 'previous-image');
        $modified = false;

        Http::fake(function (HttpRequest $request) use ($directory, &$modified) {
            if (str_ends_with($request->url(), '/files')) {
                $staged = glob($directory . DIRECTORY_SEPARATOR . '.upload-*');
                if (count($staged) === 1) {
                    file_put_contents($staged[0], 'tampered');
                    $modified = true;
                }

                return Http::response(['data' => ['id' => 'test-analysis']], 200);
            }

            return $this->cleanAnalysis();
        });

        try {
            app(ImageUploadService::class)->storeJpeg(
                'CE001',
                UploadedFile::fake()->image('original.jpg', 640, 480),
                $target,
            );
            $this->fail('A modified staged image must not be published.');
        } catch (RuntimeException $exception) {
            $this->assertTrue($modified);
            $this->assertStringContainsString('cambió durante el análisis', $exception->getMessage());
            $this->assertSame('previous-image', file_get_contents($target));
            $this->assertSame([], glob($directory . DIRECTORY_SEPARATOR . '.upload-*'));
        } finally {
            $this->removeTemporaryDirectory($directory);
        }
    }

    public function test_malicious_scan_result_cannot_replace_an_existing_image(): void
    {
        config()->set('services.virustotal.required', true);
        config()->set('services.virustotal.key', 'test-api-key');

        $directory = $this->temporaryDirectory();
        $target = $directory . DIRECTORY_SEPARATOR . 'CE001.jpg';
        file_put_contents($target, 'previous-image');

        Http::fake(function (HttpRequest $request) {
            if (str_ends_with($request->url(), '/files')) {
                return Http::response(['data' => ['id' => 'test-analysis']], 200);
            }

            return Http::response([
                'data' => [
                    'attributes' => [
                        'status' => 'completed',
                        'stats' => ['malicious' => 1, 'suspicious' => 0],
                    ],
                ],
            ], 200);
        });

        try {
            app(ImageUploadService::class)->storeJpeg(
                'CE001',
                UploadedFile::fake()->image('original.jpg', 640, 480),
                $target,
            );
            $this->fail('A rejected image must not be published.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('rechazada', $exception->getMessage());
            $this->assertSame('previous-image', file_get_contents($target));
            $this->assertSame([], glob($directory . DIRECTORY_SEPARATOR . '.upload-*'));
        } finally {
            $this->removeTemporaryDirectory($directory);
        }
    }

    private function cleanAnalysis(): mixed
    {
        return Http::response([
            'data' => [
                'attributes' => [
                    'status' => 'completed',
                    'stats' => ['malicious' => 0, 'suspicious' => 0],
                ],
            ],
        ], 200);
    }

    private function temporaryDirectory(): string
    {
        $directory = storage_path('framework/testing/image-upload-' . bin2hex(random_bytes(8)));
        $this->assertTrue(mkdir($directory, 0700, true));

        return $directory;
    }

    private function removeTemporaryDirectory(string $directory): void
    {
        foreach (glob($directory . DIRECTORY_SEPARATOR . '*') ?: [] as $path) {
            @unlink($path);
        }
        foreach (glob($directory . DIRECTORY_SEPARATOR . '.upload-*') ?: [] as $path) {
            @unlink($path);
        }
        @rmdir($directory);
    }
}
