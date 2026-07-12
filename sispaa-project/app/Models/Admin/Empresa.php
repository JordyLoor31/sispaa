<?php

namespace App\Models\Admin;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasAuditFields;

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

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
