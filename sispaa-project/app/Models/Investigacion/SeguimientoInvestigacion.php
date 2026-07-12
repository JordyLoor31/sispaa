<?php

namespace App\Models\Investigacion;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class SeguimientoInvestigacion extends Model
{
    use HasAuditFields;

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

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
