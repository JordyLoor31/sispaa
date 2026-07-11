<?php

namespace App\Models\Documentos;

use Illuminate\Database\Eloquent\Model;

class RequisitoGrupo extends Model
{
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
}
