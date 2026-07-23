<?php

namespace App\Models\Vinculacion;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

/**
 * Beneficiario de las actividades de vinculación (antes "Empresa").
 *
 * tipo:
 *  - persona_natural       (cédula opcional)
 *  - persona_juridica      (RUC obligatorio, validado en la Request)
 *  - comunidad_organizacion
 */
class Beneficiario extends Model
{
    use HasAuditFields;

    protected $table = 'beneficiarios';

    protected $fillable = [
        'tipo',
        'nombre',
        'ruc',
        'cedula',
        'sector',
        'contacto',
    ];

    public function actividadesVinculacion()
    {
        return $this->hasMany(ActividadVinculacion::class);
    }

    public function representantes()
    {
        return $this->hasMany(Representante::class);
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
