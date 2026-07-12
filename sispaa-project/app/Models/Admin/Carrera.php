<?php

namespace App\Models\Admin;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasAuditFields;

    protected $table = 'carreras';

    protected $fillable = [
        'nombre',
        'codigo',
        'activa',
        'coordinador_id',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function coordinador()
    {
        return $this->belongsTo(\App\Models\User::class, 'coordinador_id');
    }

    public function materias()
    {
        return $this->hasMany(\App\Models\Docencia\Materia::class);
    }

    public function periodosAcademicos()
    {
        return $this->hasMany(PeriodoAcademico::class);
    }

    public function usuarios()
    {
        return $this->hasMany(\App\Models\User::class);
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
