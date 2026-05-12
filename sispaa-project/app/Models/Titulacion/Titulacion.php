<?php

namespace App\Models\Titulacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Titulacion extends Model
{
    use SoftDeletes;

    protected $table = 'titulaciones';

    protected $fillable = [
        'estudiante_id',
        'tutor_id',
        'tema',
        'estado',
        'fecha_inicio',
        'fecha_graduacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_graduacion' => 'date',
    ];

    public function estudiante()
    {
        return $this->belongsTo(\App\Models\User::class, 'estudiante_id');
    }

    public function tutor()
    {
        return $this->belongsTo(\App\Models\User::class, 'tutor_id');
    }
}
