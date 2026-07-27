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

    public function __construct(private readonly VirusTotalScanner $scanner)
    {
    }

    public function storeJpeg(string $caseta, UploadedFile $image, string $targetPath): void
    {
        if (preg_match('/^(?:CE|CO|MC|NA|NC)[0-9]{3}$/', $caseta) !== 1) {
            throw new InvalidArgumentException('El identificador de la caseta no es válido.');
        }

        if (!$image->isValid() || $image->getSize() === false || $image->getSize() > self::MAX_BYTES) {
            throw new InvalidArgumentException('La imagen no es válida o supera el límite de 4 MB.');
        }

        $source = $image->getRealPath();
        $info = $source !== false ? @getimagesize($source) : false;

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

        $scan = $this->scanner->scan($source);
        if (($scan['is_malicious'] ?? true) === true) {
            throw new InvalidArgumentException((string) ($scan['message'] ?? 'La imagen no ha superado el análisis.'));
        }

        if (!function_exists('imagecreatefromjpeg')) {
            throw new RuntimeException('El servidor no dispone del procesador seguro de imágenes.');
        }

        $decoded = @imagecreatefromjpeg($source);
        if ($decoded === false) {
            throw new InvalidArgumentException('La imagen JPEG está dañada.');
        }

        $targetDirectory = dirname($targetPath);
        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0750, true) && !is_dir($targetDirectory)) {
            imagedestroy($decoded);
            throw new RuntimeException('No se pudo preparar el almacenamiento de imágenes.');
        }

        $temporaryPath = $targetDirectory . DIRECTORY_SEPARATOR . '.upload-' . bin2hex(random_bytes(12)) . '.jpg';

        try {
            if (!imagejpeg($decoded, $temporaryPath, 88)) {
                throw new RuntimeException('No se pudo normalizar la imagen.');
            }
        } finally {
            imagedestroy($decoded);
        }

        @chmod($temporaryPath, 0640);

        if (!rename($temporaryPath, $targetPath)) {
            @unlink($temporaryPath);
            throw new RuntimeException('No se pudo guardar la imagen.');
        }
    }
}
