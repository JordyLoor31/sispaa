<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vinculación: "Empresa" pasa a ser "Beneficiario".
 *
 * Se renombra la tabla `empresas` -> `beneficiarios` y se agregan:
 *  - tipo: persona_natural | persona_juridica | comunidad_organizacion
 *  - cedula: identificación para persona natural (opcional)
 *
 * El RUC (ya existente y nullable) sigue siendo opcional a nivel de BD; su
 * obligatoriedad cuando tipo = persona_juridica se valida en la Request.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('empresas', 'beneficiarios');

        Schema::table('beneficiarios', function (Blueprint $table) {
            $table->enum('tipo', ['persona_natural', 'persona_juridica', 'comunidad_organizacion'])
                  ->default('persona_juridica');
            $table->string('cedula', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('beneficiarios', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'cedula']);
        });

        Schema::rename('beneficiarios', 'empresas');
    }
};
