<?php

namespace App\Models\Vinculacion;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

/**
 * Representante de los beneficiarios (p. ej. "Presidente del barrio").
 * Entidad distinta del líder/supervisor interno del equipo. Reutilizable entre
 * actividades.
 */
class Representante extends Model
{
    use HasAuditFields;

    protected $table = 'representantes';

    protected $fillable = [
        'beneficiario_id',
        'nombre',
        'cedula',
        'cargo',
        'telefono',
    ];

    public function beneficiario()
    {
        return $this->belongsTo(Beneficiario::class);
    }

    public function actividadesVinculacion()
    {
        return $this->hasMany(ActividadVinculacion::class);
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
