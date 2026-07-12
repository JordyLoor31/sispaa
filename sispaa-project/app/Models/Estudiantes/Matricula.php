<?php

namespace App\Models\Estudiantes;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    use HasAuditFields;

    protected $table = 'matriculas';

    protected $fillable = [
        'estudiante_id',
        'periodo_id',
        'carrera_id',
        'estado',
        'fecha_matricula',
    ];

    protected $casts = [
        'fecha_matricula' => 'date',
    ];

    public function estudiante()
    {
        return $this->belongsTo(\App\Models\User::class, 'estudiante_id');
    }

    public function periodo()
    {
        return $this->belongsTo(\App\Models\Admin\PeriodoAcademico::class, 'periodo_id');
    }

    public function carrera()
    {
        return $this->belongsTo(\App\Models\Admin\Carrera::class);
    }

    public function faltas()
    {
        return $this->hasMany(Falta::class, 'estudiante_id', 'estudiante_id');
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
