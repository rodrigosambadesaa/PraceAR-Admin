<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table): void {
            $table->boolean('es_superadmin')->default(false);
        });

        // Preserve access of pre-existing administrator accounts during upgrade.
        // New accounts default to least privilege and require explicit grants.
        DB::table('usuarios')->update(['es_superadmin' => true]);

        Schema::create('usuario_naves', function (Blueprint $table): void {
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->unsignedTinyInteger('nave_id');
            $table->foreign('nave_id')->references('id')->on('naves')->cascadeOnDelete();
            $table->primary(['usuario_id', 'nave_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_naves');

        Schema::table('usuarios', function (Blueprint $table): void {
            $table->dropColumn('es_superadmin');
        });
    }
};
