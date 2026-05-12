<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'nombre',
        'ruc',
        'sector',
        'contacto',
    ];

    public function actividadesVinculacion()
    {
        return $this->hasMany(\App\Models\Vinculacion\ActividadVinculacion::class);
    }
}
