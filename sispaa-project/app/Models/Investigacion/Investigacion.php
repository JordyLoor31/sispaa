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
        'lider_id',
        'colider_id',
        'periodo_id',
        'titulo',
        'objetivo',
        'estado',
    ];

    public function docente()
    {
        return $this->belongsTo(\App\Models\User::class, 'docente_id');
    }

    public function lider()
    {
        return $this->belongsTo(\App\Models\User::class, 'lider_id');
    }

    public function colider()
    {
        return $this->belongsTo(\App\Models\User::class, 'colider_id');
    }

    /**
     * Miembros del equipo (aparte de líder y colíder). Primera relación
     * belongsToMany hacia User en el proyecto: usa la tabla pivote
     * investigacion_miembros (sin columnas extra).
     */
    public function miembros()
    {
        return $this->belongsToMany(\App\Models\User::class, 'investigacion_miembros')->withTimestamps();
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

    public function informesTrimestrales()
    {
        return $this->hasMany(InformeTrimestralInvestigacion::class);
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
