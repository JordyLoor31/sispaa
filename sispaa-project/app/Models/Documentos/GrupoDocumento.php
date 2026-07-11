<?php

namespace App\Models\Documentos;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GrupoDocumento extends Model
{
    protected $table = 'grupos_documentos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'creado_por',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function requisitos()
    {
        return $this->hasMany(RequisitoGrupo::class, 'grupo_id')->orderBy('orden');
    }

    public function requisitosActivos()
    {
        return $this->requisitos()->where('activo', true);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
