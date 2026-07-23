<?php

namespace App\Models\Vinculacion;

use Illuminate\Database\Eloquent\Model;

/**
 * Celda de la matriz género x edad de un registro de conteo.
 * genero: hombre | mujer
 * rango_edad: ninos | jovenes | adultos | adultos_mayores
 */
class BeneficiarioConteo extends Model
{
    protected $table = 'beneficiario_conteos';

    protected $fillable = [
        'registro_id',
        'genero',
        'rango_edad',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    public function registro()
    {
        return $this->belongsTo(BeneficiarioRegistro::class, 'registro_id');
    }
}
