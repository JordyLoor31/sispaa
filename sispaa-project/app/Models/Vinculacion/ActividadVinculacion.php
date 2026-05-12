<?php

namespace App\Models\Vinculacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActividadVinculacion extends Model
{
    use SoftDeletes;

    protected $table = 'actividades_vinculacion';

    protected $fillable = [
        'docente_lider_id',
        'carrera_id',
        'periodo_id',
        'empresa_id',
        'nombre',
        'estado',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function docenteLider()
    {
        return $this->belongsTo(\App\Models\User::class, 'docente_lider_id');
    }

    public function carrera()
    {
        return $this->belongsTo(\App\Models\Admin\Carrera::class);
    }

    public function periodo()
    {
        return $this->belongsTo(\App\Models\Admin\PeriodoAcademico::class, 'periodo_id');
    }

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Admin\Empresa::class);
    }
}
