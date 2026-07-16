<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

/**
 * Catálogo mínimo de facultades, requerido como padre de
 * PerfilEstudiante::facultad_id. No existía en el esquema original del
 * proyecto (las carreras no cuelgan hoy de una facultad).
 */
class Facultad extends Model
{
    protected $table = 'facultades';

    protected $fillable = [
        'nombre',
        'codigo',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function perfilesEstudiantes()
    {
        return $this->hasMany(\App\Models\Estudiantes\PerfilEstudiante::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
