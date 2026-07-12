<?php

namespace App\Models\Laboratorio;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasAuditFields;

    protected $table = 'equipos';

    protected $fillable = [
        'laboratorio_id',
        'nombre',
        'codigo',
        'cantidad',
        'estado',
    ];

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class);
    }

    public function practicas()
    {
        return $this->belongsToMany(PracticaLaboratorio::class, 'practica_equipo', 'equipo_id', 'practica_id')
            ->withPivot('cantidad_usada')
            ->withTimestamps();
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
