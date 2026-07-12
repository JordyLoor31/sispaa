<?php

namespace App\Models\Laboratorio;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class PracticaLaboratorio extends Model
{
    use HasAuditFields;

    protected $table = 'practicas_laboratorio';

    protected $fillable = [
        'materia_id',
        'docente_id',
        'periodo_id',
        'laboratorio_id',
        'ubicacion',
        'numero_practica',
        'tema',
        'subtema',
        'logro_aprendizaje',
        'semestre',
        'numero_estudiantes',
        'horario',
        'objetivo',
        'metodologia',
        'resultados',
        'conclusiones',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function materia()
    {
        return $this->belongsTo(\App\Models\Docencia\Materia::class);
    }

    public function docente()
    {
        return $this->belongsTo(\App\Models\User::class, 'docente_id');
    }

    public function periodo()
    {
        return $this->belongsTo(\App\Models\Admin\PeriodoAcademico::class, 'periodo_id');
    }

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class);
    }

    public function equipos()
    {
        return $this->belongsToMany(Equipo::class, 'practica_equipo', 'practica_id', 'equipo_id')
            ->withPivot('cantidad_usada')
            ->withTimestamps();
    }

    public function reactivos()
    {
        return $this->belongsToMany(Reactivo::class, 'practica_reactivo', 'practica_id', 'reactivo_id')
            ->withPivot('cantidad_usada')
            ->withTimestamps();
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaPractica::class, 'practica_id');
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
