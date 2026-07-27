<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Security\PepperedPasswordHasher;
use App\Support\PracearSupport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

final class SecurityCheckCommand extends Command
{
    protected $signature = 'pracear:security-check {--production : Aplica requisitos estrictos de producción}';

    protected $description = 'Valida que el despliegue no arranque con secretos o controles de seguridad incompletos.';

    public function handle(): int
    {
        $production = (bool) $this->option('production') || app()->environment('production');
        $errors = [];

        if (trim((string) config('app.key')) === '') {
            $errors[] = 'APP_KEY no está configurada.';
        }

        try {
            PepperedPasswordHasher::currentPepper();
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }

        $appUrl = (string) config('app.url');
        if ($production && (!str_starts_with($appUrl, 'https://') || (bool) config('app.debug'))) {
            $errors[] = 'Producción requiere APP_URL con HTTPS y APP_DEBUG=false.';
        }

        if ($production && trim((string) env('APP_ALLOWED_HOSTS', '')) === '') {
            $errors[] = 'APP_ALLOWED_HOSTS debe declarar los hosts públicos permitidos.';
        }

        if ($production && trim((string) env('TRUSTED_PROXIES', '')) === '') {
            $errors[] = 'TRUSTED_PROXIES debe configurarse explícitamente para el proxy del despliegue.';
        }

        if ($production && config('session.secure') !== true) {
            $errors[] = 'SESSION_SECURE_COOKIE debe estar activo en producción.';
        }

        if (
            $production
            && (bool) config('services.virustotal.required', true)
            && trim((string) config('services.virustotal.key', '')) === ''
        ) {
            $errors[] = 'VIRUSTOTAL_API_KEY es obligatoria cuando VIRUSTOTAL_REQUIRED=true.';
        }

        try {
            if (!DB::table('usuarios')->exists()) {
                $errors[] = 'No existe ninguna cuenta administradora.';
            }

            if (!DB::table('puestos')->exists() || !DB::table('puestos_traducciones')->exists()) {
                $errors[] = 'El catálogo de puestos no está inicializado.';
            }
        } catch (Throwable) {
            $errors[] = 'No se pudo validar la base de datos.';
        }

        $assetsDirectory = PracearSupport::imageStorageDirectory();
        if (!is_dir($assetsDirectory) && !mkdir($assetsDirectory, 0750, true) && !is_dir($assetsDirectory)) {
            $errors[] = 'No se pudo crear el almacenamiento persistente de imágenes.';
        } elseif (!is_writable($assetsDirectory)) {
            $errors[] = 'El almacenamiento de imágenes no tiene permisos de escritura.';
        }

        foreach ($errors as $error) {
            $this->error($error);
        }

        if ($errors !== []) {
            return self::FAILURE;
        }

        $this->info('Configuración de seguridad validada.');

        return self::SUCCESS;
    }
}
