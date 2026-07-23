<?php

namespace App\Models\Investigacion;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

/**
 * Informe trimestral de avance, subido como archivo por el líder de
 * proyecto (único rol autorizado por el momento). Se conserva un historial
 * completo (no se reemplaza el anterior), igual que Silabo/InformeDocente:
 * archivo_url guarda "/storage/..." y se sirve por una ruta autenticada.
 */
class InformeTrimestralInvestigacion extends Model
{
    use HasAuditFields;

    protected $table = 'informes_trimestrales_investigacion';

    protected $fillable = [
        'investigacion_id',
        'archivo_url',
        'nombre_original',
        'observaciones',
        'fecha_subida',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
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
