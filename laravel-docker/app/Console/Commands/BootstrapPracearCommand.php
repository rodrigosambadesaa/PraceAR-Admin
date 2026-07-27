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
        $this->seedAdminUser();

        return self::SUCCESS;
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
