<?php

namespace App\Models\Laboratorio;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class Reactivo extends Model
{
    use HasAuditFields;

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

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
