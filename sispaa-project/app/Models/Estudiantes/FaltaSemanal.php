<?php

namespace App\Models\Estudiantes;

use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

/**
 * Reemplaza el antiguo registro de faltas individuales por
 * estudiante+materia+fecha (con justificación aprobada/rechazada por
 * Secretaría). Ahora Secretaría solo ingresa un número agregado de faltas
 * por carrera, por semana, dentro de un período académico.
 */
class FaltaSemanal extends Model
{
    use HasAuditFields;

    protected $table = 'faltas_semanales_carrera';

    protected $fillable = [
        'carrera_id',
        'periodo_id',
        'semana',
        'cantidad_faltas',
        'observaciones',
    ];

    protected $casts = [
        'semana' => 'integer',
        'cantidad_faltas' => 'integer',
    ];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function periodo()
    {
        return $this->belongsTo(PeriodoAcademico::class, 'periodo_id');
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
