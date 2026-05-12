<?php

namespace App\Models\Investigacion;

use Illuminate\Database\Eloquent\Model;

class HitoInvestigacion extends Model
{
    protected $table = 'hitos_investigacion';

    protected $fillable = [
        'investigacion_id',
        'nombre',
        'descripcion',
        'fecha_planificada',
        'fecha_cumplida',
        'porcentaje_avance',
    ];

    protected $casts = [
        'fecha_planificada' => 'date',
        'fecha_cumplida' => 'date',
    ];

    public function investigacion()
    {
        return $this->belongsTo(Investigacion::class);
    }
}
