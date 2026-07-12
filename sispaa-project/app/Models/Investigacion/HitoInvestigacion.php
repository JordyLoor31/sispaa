<?php

namespace App\Models\Investigacion;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class HitoInvestigacion extends Model
{
    use HasAuditFields;

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

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
