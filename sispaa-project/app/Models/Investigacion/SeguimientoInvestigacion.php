<?php

namespace App\Models\Investigacion;

use Illuminate\Database\Eloquent\Model;

class SeguimientoInvestigacion extends Model
{
    protected $table = 'seguimiento_investigacion';

    protected $fillable = [
        'investigacion_id',
        'pregunta',
        'respuesta',
        'orden',
    ];

    public function investigacion()
    {
        return $this->belongsTo(Investigacion::class);
    }
}
