<?php

namespace App\Models\Documentos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoEstudiante extends Model
{
    use SoftDeletes;

    protected $table = 'documentos_estudiante';

    protected $fillable = [
        'estudiante_id',
        'secretaria_id',
        'tipo_documento',
        'archivo_url',
        'estado',
        'observacion',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function estudiante()
    {
        return $this->belongsTo(\App\Models\User::class, 'estudiante_id');
    }

    public function secretaria()
    {
        return $this->belongsTo(\App\Models\User::class, 'secretaria_id');
    }
}
