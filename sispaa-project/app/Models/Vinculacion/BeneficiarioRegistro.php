<?php

namespace App\Models\Vinculacion;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

/**
 * Una "tanda" de conteo de beneficiarios de una actividad (inicial o adicional).
 * El detalle género x edad vive en beneficiario_conteos.
 */
class BeneficiarioRegistro extends Model
{
    use HasAuditFields;

    protected $table = 'beneficiario_registros';

    protected $fillable = [
        'actividad_vinculacion_id',
        'tipo',
        'fecha',
        'observacion',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function actividad()
    {
        return $this->belongsTo(ActividadVinculacion::class, 'actividad_vinculacion_id');
    }

    public function conteos()
    {
        return $this->hasMany(BeneficiarioConteo::class, 'registro_id');
    }

    /** Total de personas de este registro (suma de todas las celdas). */
    public function getTotalAttribute(): int
    {
        return (int) $this->conteos->sum('cantidad');
    }
}
