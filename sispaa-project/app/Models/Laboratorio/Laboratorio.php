<?php

namespace App\Models\Laboratorio;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class Laboratorio extends Model
{
    use HasAuditFields;

    protected $table = 'laboratorios';

    protected $fillable = [
        'nombre',
        'ubicacion',
        'carrera_id',
        'capacidad',
        'responsable_id',
        'estado',
    ];

    public function carrera()
    {
        return $this->belongsTo(\App\Models\Admin\Carrera::class);
    }

    public function responsable()
    {
        return $this->belongsTo(\App\Models\User::class, 'responsable_id');
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class);
    }

    public function reactivos()
    {
        return $this->hasMany(Reactivo::class);
    }

    public function practicas()
    {
        return $this->hasMany(PracticaLaboratorio::class);
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
