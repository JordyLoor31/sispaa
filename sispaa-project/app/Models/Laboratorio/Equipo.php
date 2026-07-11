<?php

namespace App\Models\Laboratorio;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
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
}
