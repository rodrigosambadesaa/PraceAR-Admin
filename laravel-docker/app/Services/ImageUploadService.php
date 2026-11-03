<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

final class ImageUploadService
{
    private const MAX_BYTES = 4_194_304;
    private const MAX_PIXELS = 40_000_000;
    private const MAX_DIMENSION = 12_000;

    public function __construct(
        private readonly VirusTotalScanner $scanner,
        private readonly LocalAntivirusScanner $localScanner,
    )
    {
    }

    public function storeJpeg(string $caseta, UploadedFile $image, string $targetPath): void
    {
        if (preg_match('/^(?:CE|CO|MC|NA|NC)[0-9]{3}$/', $caseta) !== 1) {
            throw new InvalidArgumentException('El identificador de la caseta no es válido.');
        }

        if (basename($targetPath) !== $caseta . '.jpg') {
            throw new InvalidArgumentException('La ruta de destino no corresponde a la caseta.');
        }

        if (!$image->isValid() || $image->getSize() === false || $image->getSize() > self::MAX_BYTES) {
            throw new InvalidArgumentException('La imagen no es válida o supera el límite de 4 MB.');
        }

        // Snapshot the upload ONCE. A later change to PHP's upload temporary file
        // must not change the bytes we validate, decode, scan or publish.
        $source = $image->getRealPath();
        $bytes = $source !== false
            ? @file_get_contents($source, false, null, 0, self::MAX_BYTES + 1)
            : false;

        if (!is_string($bytes) || $bytes === '' || strlen($bytes) > self::MAX_BYTES) {
            throw new InvalidArgumentException('La imagen no es válida o supera el límite de 4 MB.');
        }

        // Scan a private copy of the original bytes BEFORE invoking image decoders.
        // This is necessary because malformed images could target vulnerabilities in GD.
        $this->scanOriginalSnapshot($bytes);

        $info = @getimagesizefromstring($bytes);
        if (
            !is_array($info)
            || ($info[2] ?? null) !== IMAGETYPE_JPEG
            || ($info['mime'] ?? '') !== 'image/jpeg'
        ) {
            throw new InvalidArgumentException('Solo se permiten imágenes JPEG auténticas.');
        }

        $width = (int) ($info[0] ?? 0);
        $height = (int) ($info[1] ?? 0);
        if (
            $width <= 0
            || $height <= 0
            || $width > self::MAX_DIMENSION
            || $height > self::MAX_DIMENSION
            || ($width * $height) > self::MAX_PIXELS
        ) {
            throw new InvalidArgumentException('Las dimensiones de la imagen no son seguras.');
        }

        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            throw new RuntimeException('El servidor no dispone del procesador seguro de imágenes.');
        }

        $decoded = @imagecreatefromstring($bytes);
        unset($bytes);
        if ($decoded === false) {
            throw new InvalidArgumentException('La imagen JPEG está dañada.');
        }

        $temporaryPath = null;
        $stream = null;
        $published = false;

        try {
            $targetDirectory = dirname($targetPath);
            if (!is_dir($targetDirectory) && !@mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
                throw new RuntimeException('No se pudo preparar el almacenamiento de imágenes.');
            }

            // Exclusive creation prevents a pre-existing file or symlink from being
            // followed. Keep the handle open throughout normalization and scanning.
            $temporaryPath = $targetDirectory . DIRECTORY_SEPARATOR . '.upload-' . bin2hex(random_bytes(16));
            $stream = @fopen($temporaryPath, 'x+b');
            if ($stream === false) {
                throw new RuntimeException('No se pudo preparar el archivo temporal de imagen.');
            }

            if (!@chmod($temporaryPath, 0600)) {
                throw new RuntimeException('No se pudieron aplicar permisos seguros a la imagen temporal.');
            }

            // The scanner sees the exact sanitized JPEG that we intend to publish,
            // never the original upload (which might have changed after validation).
            if (!@imagejpeg($decoded, $stream, 88) || !@fflush($stream)) {
                throw new RuntimeException('No se pudo normalizar la imagen.');
            }

            $metadata = @fstat($stream);
            $size = is_array($metadata) ? (int) ($metadata['size'] ?? 0) : 0;
            if ($size <= 0 || $size > self::MAX_BYTES || @fseek($stream, 0) !== 0) {
                throw new RuntimeException('La imagen normalizada no tiene un tamaño seguro.');
            }

            $hash = hash_init('sha256');
            if (hash_update_stream($hash, $stream) !== $size) {
                throw new RuntimeException('No se pudo verificar la integridad de la imagen.');
            }
            $expectedHash = hash_final($hash);

            // Scan the exact normalized artifact as well; neither the original
            // upload pathname nor the staged pathname is trusted after scanning.
            $this->localScanner->scan($temporaryPath);
            $scan = $this->scanner->scan($temporaryPath);
            if (($scan['success'] ?? false) !== true || ($scan['is_malicious'] ?? true) !== false) {
                throw new InvalidArgumentException((string) ($scan['message'] ?? 'La imagen no ha superado el análisis.'));
            }

            // Fail closed if the staged file changed while the external scanner was
            // running, or if its pathname was replaced with a different inode.
            clearstatcache(true, $temporaryPath);
            $pathStat = @stat($temporaryPath);
            $handleStat = @fstat($stream);
            if (
                !is_array($pathStat)
                || !is_array($handleStat)
                || $pathStat['dev'] !== $handleStat['dev']
                || $pathStat['ino'] !== $handleStat['ino']
                || (int) $handleStat['size'] !== $size
                || @fseek($stream, 0) !== 0
            ) {
                throw new RuntimeException('La imagen cambió durante el análisis de seguridad.');
            }

            $verifiedHash = hash_init('sha256');
            if (
                hash_update_stream($verifiedHash, $stream) !== $size
                || !hash_equals($expectedHash, hash_final($verifiedHash))
            ) {
                throw new RuntimeException('La imagen cambió durante el análisis de seguridad.');
            }

            if (!@chmod($temporaryPath, 0644) || !@rename($temporaryPath, $targetPath)) {
                throw new RuntimeException('No se pudo guardar la imagen.');
            }

            $published = true;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
            if (!$published && $temporaryPath !== null) {
                @unlink($temporaryPath);
            }
            imagedestroy($decoded);
        }
    }

    /**
     * Scan the uploaded byte snapshot (not PHP's mutable temporary upload).
     * Use a private, exclusively created tempfile and verify it wasn't modified
     * during the local AV scan; the original bytes stay in memory for decoding.
     */
    private function scanOriginalSnapshot(string $bytes): void
    {
        $path = @tempnam(sys_get_temp_dir(), 'pracear-av-');
        if ($path === false) {
            throw new RuntimeException('No se pudo preparar el análisis antivirus local.');
        }

        try {
            if (!@chmod($path, 0600) || @file_put_contents($path, $bytes, LOCK_EX) !== strlen($bytes)) {
                throw new RuntimeException('No se pudo preparar la copia privada de la imagen.');
            }

            $this->localScanner->scan($path);
            $checked = @file_get_contents($path);
            if (!is_string($checked) || !hash_equals(hash('sha256', $bytes), hash('sha256', $checked))) {
                throw new RuntimeException('La imagen cambió durante el análisis antivirus local.');
            }
        } finally {
            @unlink($path);
        }
    }
}
