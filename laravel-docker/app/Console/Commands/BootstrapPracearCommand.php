<?php

namespace App\Console\Commands;

use App\Security\PasswordPolicy;
use App\Support\PracearSupport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class BootstrapPracearCommand extends Command
{
    protected $signature = 'pracear:bootstrap {--force : Reescribe el usuario admin si ya existe}';

    protected $description = 'Inicializa datos mínimos de PraceAR para despliegues limpios.';

    public function handle(): int
    {
        $this->seedNaves();
        $this->seedCatalog();
        $this->seedAdminUser();

        return self::SUCCESS;
    }

    private function seedCatalog(): void
    {
        if (DB::table('puestos')->exists()) {
            $this->line('El catálogo de puestos ya existe y se conserva sin cambios.');

            return;
        }

        $catalogPath = database_path('data/catalog.json');
        if (!is_file($catalogPath)) {
            throw new \RuntimeException('No existe el catálogo base saneado.');
        }

        $catalog = json_decode(
            (string) file_get_contents($catalogPath),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $stalls = $catalog['stalls'] ?? null;
        $translations = $catalog['translations'] ?? null;

        if (!is_array($stalls) || $stalls === [] || !is_array($translations) || $translations === []) {
            throw new \RuntimeException('El catálogo base está vacío o tiene un formato inválido.');
        }

        $allowedStallKeys = ['id', 'caseta', 'nombre', 'id_nave', 'activo', 'tipo_unity', 'caseta_padre'];
        $allowedTranslationKeys = ['id', 'puesto_id', 'codigo_idioma', 'nombre_idioma', 'tipo', 'descripcion'];

        foreach ($stalls as $stall) {
            if (!is_array($stall) || array_diff(array_keys($stall), $allowedStallKeys) !== []) {
                throw new \RuntimeException('El catálogo contiene columnas de puesto no permitidas.');
            }
        }

        foreach ($translations as $translation) {
            if (!is_array($translation) || array_diff(array_keys($translation), $allowedTranslationKeys) !== []) {
                throw new \RuntimeException('El catálogo contiene columnas de traducción no permitidas.');
            }
        }

        DB::transaction(function () use ($stalls, $translations): void {
            foreach (array_chunk($stalls, 100) as $chunk) {
                DB::table('puestos')->insert($chunk);
            }

            foreach (array_chunk($translations, 250) as $chunk) {
                DB::table('puestos_traducciones')->insert($chunk);
            }
        });

        $this->info(sprintf(
            'Catálogo base restaurado: %d puestos y %d traducciones.',
            count($stalls),
            count($translations),
        ));
    }

    private function seedNaves(): void
    {
        $naves = [
            1 => 'Ameas 1',
            2 => 'Ameas 2',
            3 => 'Nave 1',
            4 => 'Nave 2',
            5 => 'Nave 3',
            6 => 'Nave 4',
            7 => 'Nave 5',
            8 => 'Nave 6',
            9 => 'Nave 7',
            10 => 'Nave 8',
            11 => 'Murallón 1',
            12 => 'Murallón 2',
        ];

        foreach ($naves as $id => $tipo) {
            DB::table('naves')->updateOrInsert(
                ['id' => $id],
                ['tipo' => $tipo],
            );
        }

        $this->info('Catálogo base de naves sincronizado.');
    }

    private function seedAdminUser(): void
    {
        $login = trim((string) env('PRACEAR_ADMIN_LOGIN', ''));
        $password = (string) env('PRACEAR_ADMIN_PASSWORD', '');
        $force = (bool) $this->option('force');

        if ($login === '' || $password === '') {
            $this->warn('No se creó usuario admin porque faltan PRACEAR_ADMIN_LOGIN o PRACEAR_ADMIN_PASSWORD.');

            return;
        }

        try {
            $login = PracearSupport::validateLoginName($login);
            PasswordPolicy::validate($password, $login);
        } catch (\InvalidArgumentException $exception) {
            $this->warn('No se creó el usuario admin: ' . $exception->getMessage());

            return;
        }

        $existingUser = DB::table('usuarios')->where('login', $login)->first();

        if ($existingUser && !$force) {
            $this->line('El usuario admin ya existe y se conserva sin cambios.');

            return;
        }

        try {
            $hash = PracearSupport::hashPassword($password);
        } catch (Throwable $throwable) {
            $this->warn('No se creó usuario admin porque no hay un pepper válido configurado: ' . $throwable->getMessage());

            return;
        }

        DB::table('usuarios')->updateOrInsert(
            ['login' => $login],
            ['password' => $hash],
        );

        $this->info('Usuario admin sincronizado.');
    }
}
