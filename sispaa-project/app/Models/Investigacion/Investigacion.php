<?php

namespace App\Models\Investigacion;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investigacion extends Model
{
    use SoftDeletes, HasAuditFields;

    protected $table = 'investigaciones';

    protected $fillable = [
        'docente_id',
        'coordinador_id',
        'periodo_id',
        'titulo',
        'objetivo',
        'estado',
    ];

    public function docente()
    {
        return $this->belongsTo(\App\Models\User::class, 'docente_id');
    }

    public function coordinador()
    {
        return $this->belongsTo(\App\Models\User::class, 'coordinador_id');
    }

    public function periodo()
    {
        return $this->belongsTo(\App\Models\Admin\PeriodoAcademico::class, 'periodo_id');
    }

    public function hitos()
    {
        return $this->hasMany(HitoInvestigacion::class);
    }

    public function seguimientos()
    {
        return $this->hasMany(SeguimientoInvestigacion::class);
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
