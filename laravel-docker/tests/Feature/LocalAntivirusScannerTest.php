<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\LocalAntivirusScanner;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

final class LocalAntivirusScannerTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = storage_path('framework/testing/local-av-' . bin2hex(random_bytes(8)));
        $this->assertTrue(mkdir($this->directory, 0700, true));
        config()->set('services.local_antivirus.driver', 'clamav');
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->directory);
        parent::tearDown();
    }

    public function test_clamav_clean_exit_allows_an_image_and_passes_a_literal_path(): void
    {
        $binary = $this->fakeScanner(
            'test "$1" = "--no-summary" && test "$2" = "--stdout" && test -f "$3"',
        );
        config()->set('services.local_antivirus.binary', $binary);

        // This name would be dangerous if the application used shell interpolation.
        $image = $this->directory . '/safe; touch INJECTION_MARKER.jpg';
        file_put_contents($image, 'jpeg bytes for scanner protocol test');

        app(LocalAntivirusScanner::class)->scan($image);

        $this->assertFileDoesNotExist($this->directory . '/INJECTION_MARKER.jpg');
    }

    public function test_clamav_detection_blocks_upload(): void
    {
        config()->set('services.local_antivirus.binary', $this->fakeScanner('exit 1'));
        $image = $this->imageFixture();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('detectado una amenaza');
        app(LocalAntivirusScanner::class)->scan($image);
    }

    public function test_clamav_engine_error_blocks_upload(): void
    {
        config()->set('services.local_antivirus.binary', $this->fakeScanner('exit 2'));
        $image = $this->imageFixture();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no ha confirmado');
        app(LocalAntivirusScanner::class)->scan($image);
    }

    public function test_missing_antivirus_fails_closed(): void
    {
        config()->set('services.local_antivirus.binary', $this->directory . '/missing-scanner');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no está instalado');
        app(LocalAntivirusScanner::class)->scan($this->imageFixture());
    }

    public function test_defender_custom_scan_passes_file_without_a_shell(): void
    {
        config()->set('services.local_antivirus.driver', 'defender');
        config()->set('services.local_antivirus.binary', $this->fakeScanner(
            'test "$1" = "-Scan" && test "$2" = "-ScanType" && test "$3" = "3" && test "$4" = "-DisableRemediation" && test "$5" = "-File" && test -f "$6"',
        ));

        app(LocalAntivirusScanner::class)->scan($this->imageFixture());
    }

    public function test_defender_scan_error_or_detection_fails_closed(): void
    {
        config()->set('services.local_antivirus.driver', 'defender');
        config()->set('services.local_antivirus.binary', $this->fakeScanner('exit 2'));

        $this->expectException(RuntimeException::class);
        app(LocalAntivirusScanner::class)->scan($this->imageFixture());
    }

    public function test_unsupported_engine_is_rejected(): void
    {
        config()->set('services.local_antivirus.driver', 'none');

        $this->expectException(RuntimeException::class);
        app(LocalAntivirusScanner::class)->assertAvailable();
    }

    private function imageFixture(): string
    {
        $image = $this->directory . '/sample.jpg';
        file_put_contents($image, 'sample bytes');

        return $image;
    }

    private function fakeScanner(string $script): string
    {
        $binary = $this->directory . '/fake-antivirus';
        file_put_contents($binary, "#!/bin/sh\n" . $script . "\n");
        chmod($binary, 0700);

        return $binary;
    }
}
