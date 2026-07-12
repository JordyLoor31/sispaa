<?php

namespace App\Models\Documentos;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class RequisitoGrupo extends Model
{
    use HasAuditFields;

    protected $table = 'requisitos_grupo';

    protected $fillable = [
        'grupo_id',
        'nombre',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function grupo()
    {
        return $this->belongsTo(GrupoDocumento::class, 'grupo_id');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoEstudiante::class, 'requisito_id');
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
