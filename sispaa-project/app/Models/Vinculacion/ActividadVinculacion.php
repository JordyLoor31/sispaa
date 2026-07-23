<?php

namespace App\Models\Vinculacion;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActividadVinculacion extends Model
{
    use SoftDeletes, HasAuditFields;

    protected $table = 'actividades_vinculacion';

    protected $fillable = [
        'docente_lider_id',
        'supervisor_id',
        'carrera_id',
        'periodo_id',
        'beneficiario_id',
        'representante_id',
        'nombre',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'fecha_cierre',
        'motivo_cancelacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_cierre' => 'date',
    ];

    public function docenteLider()
    {
        return $this->belongsTo(\App\Models\User::class, 'docente_lider_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(\App\Models\User::class, 'supervisor_id');
    }

    public function carrera()
    {
        return $this->belongsTo(\App\Models\Admin\Carrera::class);
    }

    public function periodo()
    {
        return $this->belongsTo(\App\Models\Admin\PeriodoAcademico::class, 'periodo_id');
    }

    public function beneficiario()
    {
        return $this->belongsTo(Beneficiario::class);
    }

    public function representante()
    {
        return $this->belongsTo(Representante::class);
    }

    public function registros()
    {
        return $this->hasMany(BeneficiarioRegistro::class, 'actividad_vinculacion_id');
    }

    /** Registro inicial (conteo capturado al crear la actividad). */
    public function registroInicial()
    {
        return $this->hasOne(BeneficiarioRegistro::class, 'actividad_vinculacion_id')
            ->where('tipo', 'inicial');
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
