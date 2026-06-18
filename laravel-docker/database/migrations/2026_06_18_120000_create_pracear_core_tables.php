<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('naves', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('tipo', 100);
        });

        Schema::create('usuarios', function (Blueprint $table): void {
            $table->id();
            $table->string('login', 50)->unique();
            $table->text('password');
        });

        Schema::create('puestos', function (Blueprint $table): void {
            $table->id();
            $table->string('caseta', 5)->unique();
            $table->string('imagen', 255)->nullable();
            $table->string('nombre', 50)->nullable();
            $table->string('contacto', 250)->nullable();
            $table->string('telefono', 15)->nullable();
            $table->unsignedTinyInteger('id_nave');
            $table->string('tipo_unity', 50)->default('default');
            $table->string('caseta_padre', 5)->nullable();
            $table->boolean('activo')->default(true);

            $table->foreign('id_nave')->references('id')->on('naves');
            $table->index('caseta_padre');
            $table->index(['id_nave', 'activo']);
        });

        Schema::create('puestos_traducciones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('puesto_id')->constrained('puestos')->cascadeOnDelete();
            $table->string('codigo_idioma', 5);
            $table->string('nombre_idioma', 50)->nullable();
            $table->string('tipo', 50)->nullable();
            $table->string('descripcion', 450)->nullable();

            $table->unique(['puesto_id', 'codigo_idioma']);
            $table->index('codigo_idioma');
        });

        Schema::create('old_passwords', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_usuario')->constrained('usuarios')->cascadeOnDelete();
            $table->text('password');
            $table->timestamp('date')->useCurrent();
        });

        Schema::create('accesos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_usuario')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('fecha')->useCurrent();
            $table->string('tipo', 20);

            $table->index(['id_usuario', 'fecha']);
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accesos');
        Schema::dropIfExists('old_passwords');
        Schema::dropIfExists('puestos_traducciones');
        Schema::dropIfExists('puestos');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('naves');
    }
};
