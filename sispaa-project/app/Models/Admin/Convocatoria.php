<?php

namespace App\Models\Admin;

use App\Models\Traits\HasAuditFields;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Convocatoria extends Model
{
    use HasAuditFields;

    protected $table = 'convocatorias';

    protected $fillable = [
        'titulo',
        'descripcion',
        'modulo',
        'tipo_documento',
        'fecha_inicio',
        'fecha_fin',
        'creado_por',
        'activa',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activa' => 'boolean',
    ];

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
