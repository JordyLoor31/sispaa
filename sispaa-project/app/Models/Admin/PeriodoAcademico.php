<?php

namespace App\Models\Admin;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class PeriodoAcademico extends Model
{
    use HasAuditFields;

    protected $table = 'periodos_academicos';

    /** Ciclo de vida de un período académico: se planifica, se activa y finalmente se finaliza. */
    public const ESTADO_PLANIFICADO = 'planificado';
    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_FINALIZADO = 'finalizado';
    public const ESTADOS = [self::ESTADO_PLANIFICADO, self::ESTADO_ACTIVO, self::ESTADO_FINALIZADO];

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'tipo',
        'estado',
        'fecha_limite_silabo',
        'fecha_limite_informe',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_limite_silabo' => 'date',
        'fecha_limite_informe' => 'date',
    ];

    public function scopeActivo($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    /**
     * Período "vigente": activo y dentro de su rango de fechas (fecha_fin aún
     * no vencida). Es el que consumen los demás roles (docente, secretaría,
     * coordinación): un período que ya venció deja de aparecer aquí aunque el
     * estado en BD no se haya flipado aún a finalizado.
     */
    public function scopeVigente($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO)
            ->whereDate('fecha_fin', '>=', now()->toDateString());
    }

    /**
     * Desactiva automáticamente los períodos activos cuya fecha_fin ya pasó
     * (los marca como finalizado). Lo llama el scheduler y también el listado
     * del admin, para que un período vencido no siga apareciendo como activo.
     */
    public static function finalizarVencidos(): int
    {
        return self::query()
            ->where('estado', self::ESTADO_ACTIVO)
            ->whereDate('fecha_fin', '<', now()->toDateString())
            ->update(['estado' => self::ESTADO_FINALIZADO]);
    }

    /**
     * Marca como "necesita actualizar perfil" a todos los estudiantes de las
     * carreras que tuvieron asignaciones de docentes en el periodo indicado.
     * Se llama cuando un periodo se finaliza (cron, admin o al activar otro).
     */
    public static function marcarEstudiantesPeriodoVencido(int $periodoId): int
    {
        $carrerasIds = \App\Models\Docencia\Materia::query()
            ->whereHas('asignacionesDocente', fn ($q) => $q->where('periodo_id', $periodoId))
            ->pluck('carrera_id')
            ->unique();

        if ($carrerasIds->isEmpty()) {
            return 0;
        }

        return \App\Models\User::role('estudiante')
            ->whereIn('carrera_id', $carrerasIds)
            ->update(['needs_profile_update' => true]);
    }

    /**
     * Un periodo académico es una entidad global compartida por todas las
     * carreras (ej. "2026-1"), no un registro por carrera. El alcance por
     * carrera se resuelve en cada tabla dependiente que ya tiene su propio
     * carrera_id (actividades_vinculacion) o vía materia->carrera
     * (silabos, informes_docente, asignaciones_docente, practicas_laboratorio).
     */
    public function asignacionesDocente()
    {
        return $this->hasMany(\App\Models\Docencia\AsignacionDocente::class);
    }

    public function silabos()
    {
        return $this->hasMany(\App\Models\Docencia\Silabo::class);
    }

    public function informesDocente()
    {
        return $this->hasMany(\App\Models\Docencia\InformeDocente::class);
    }

    public function faltasSemanales()
    {
        return $this->hasMany(\App\Models\Estudiantes\FaltaSemanal::class, 'periodo_id');
    }

    public function investigaciones()
    {
        return $this->hasMany(\App\Models\Investigacion\Investigacion::class);
    }

    public function actividadesVinculacion()
    {
        return $this->hasMany(\App\Models\Vinculacion\ActividadVinculacion::class);
    }

    public function practicasLaboratorio()
    {
        return $this->hasMany(\App\Models\Laboratorio\PracticaLaboratorio::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
