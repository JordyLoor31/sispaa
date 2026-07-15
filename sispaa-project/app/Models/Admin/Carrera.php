<?php

namespace App\Models\Admin;

use App\Models\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasAuditFields;

    protected $table = 'carreras';

    /**
     * Paleta de colores sugeridos para la etiqueta de la carrera (usada como
     * respaldo si no se especifica uno al crear, y expuesta al frontend como
     * opciones rápidas del selector de color).
     */
    public const PALETA_COLORES = [
        '#6366f1', '#f59e0b', '#10b981', '#ef4444', '#0ea5e9',
        '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#84cc16',
    ];

    protected $fillable = [
        'nombre',
        'codigo',
        'color',
        'activa',
        'coordinador_id',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Carrera $carrera) {
            if (empty($carrera->color)) {
                $indice = static::count() % count(self::PALETA_COLORES);
                $carrera->color = self::PALETA_COLORES[$indice];
            }
        });
    }

    public function coordinador()
    {
        return $this->belongsTo(\App\Models\User::class, 'coordinador_id');
    }

    public function materias()
    {
        return $this->hasMany(\App\Models\Docencia\Materia::class);
    }

    public function periodosAcademicos()
    {
        return $this->hasMany(PeriodoAcademico::class);
    }

    public function usuarios()
    {
        return $this->hasMany(\App\Models\User::class);
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
