<?php

namespace App\Models\Docencia;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InformeDocente extends Model
{
    use SoftDeletes, HasAuditFields;

    protected $table = 'informes_docente';

    protected $fillable = [
        'docente_id',
        'materia_id',
        'periodo_id',
        'tipo',
        'estado',
        'archivo_url',
        'observaciones',
        'fecha_subida',
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

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
