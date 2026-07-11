<?php

namespace App\Models\Laboratorio;

use Illuminate\Database\Eloquent\Model;

class Reactivo extends Model
{
    protected $table = 'reactivos';

    protected $fillable = [
        'laboratorio_id',
        'nombre',
        'formula',
        'cantidad',
        'unidad',
        'estado',
    ];

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class);
    }

    public function practicas()
    {
        return $this->belongsToMany(PracticaLaboratorio::class, 'practica_reactivo', 'reactivo_id', 'practica_id')
            ->withPivot('cantidad_usada')
            ->withTimestamps();
    }
}
