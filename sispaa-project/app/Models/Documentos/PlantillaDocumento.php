<?php

namespace App\Models\Documentos;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Plantillas de documentos (formatos institucionales: solicitudes,
 * certificados, etc.) que Secretaría sube para que cualquier estudiante
 * pueda descargarlas desde su portal (menú "Plantillas", ver
 * StudentPortalController::plantillas()).
 */
class PlantillaDocumento extends Model
{
    use SoftDeletes, HasAuditFields;

    protected $table = 'plantillas_documentos';

    protected $fillable = [
        'nombre_doc',
        'url_doc',
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
