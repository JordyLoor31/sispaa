<?php

namespace App\Models\Laboratorio;

use Illuminate\Database\Eloquent\Model;

class AsistenciaPractica extends Model
{
    protected $table = 'asistencias_practica';

    protected $fillable = [
        'practica_id',
        'estudiante_id',
        'asistio',
    ];

    protected $casts = [
        'asistio' => 'boolean',
    ];

    public function practica()
    {
        return $this->belongsTo(PracticaLaboratorio::class, 'practica_id');
    }

    public function estudiante()
    {
        return $this->belongsTo(\App\Models\User::class, 'estudiante_id');
    }
}
