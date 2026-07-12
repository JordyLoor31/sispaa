<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tablas gestionadas por CRUD en el sistema (catálogos administrativos +
     * módulos de Investigación/Titulación/Vinculación/Laboratorio/Docencia/
     * Secretaría) a las que se les agrega auditoría de creación/edición vía
     * App\Models\Traits\HasAuditFields.
     *
     * Se excluyen deliberadamente: faltas, asistencias_practica y
     * notificaciones (registros generados en lote/por el sistema, sin un
     * formulario de creación manual) y asignaciones_docente (hoy solo se
     * siembra, no tiene UI de CRUD propia).
     */
    private array $tablas = [
        'carreras',
        'materias',
        'periodos_academicos',
        'convocatorias',
        'grupos_documentos',
        'requisitos_grupo',
        'documentos_estudiante',
        'justificaciones_solicitudes',
        'matriculas',
        'actividades_vinculacion',
        'empresas',
        'investigaciones',
        'hitos_investigacion',
        'seguimiento_investigacion',
        'titulaciones',
        'silabos',
        'informes_docente',
        'laboratorios',
        'equipos',
        'reactivos',
        'practicas_laboratorio',
    ];

    public function up(): void
    {
        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                if (!Schema::hasColumn($tabla, 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn($tabla, 'updated_by')) {
                    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                if (Schema::hasColumn($tabla, 'updated_by')) {
                    $table->dropConstrainedForeignId('updated_by');
                }
                if (Schema::hasColumn($tabla, 'created_by')) {
                    $table->dropConstrainedForeignId('created_by');
                }
            });
        }
    }
};
