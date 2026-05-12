<?php

namespace App\Models\Docencia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Silabo extends Model
{
    use SoftDeletes;

    protected $table = 'silabos';

    protected $fillable = [
        'docente_id',
        'materia_id',
        'periodo_id',
        'estado',
        'archivo_url',
        'fecha_subida',
        'observaciones',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
    ];

    public function docente()
    {
        return $this->belongsTo(\App\Models\User::class, 'docente_id');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function periodo()
    {
        return $this->belongsTo(\App\Models\Admin\PeriodoAcademico::class, 'periodo_id');
    }
}
