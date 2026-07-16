<?php

namespace App\Models\Estudiantes;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Datos personales/laborales complementarios del estudiante (1:1 con
 * users). Paso 3 del wizard "Completar mi perfil".
 */
class EstudianteDatosAdicionales extends Model
{
    protected $table = 'estudiante_datos_adicionales';

    protected $fillable = [
        'user_id',
        'religion',
        'estado_civil',
        'cantidad_hijos',
        'etnia',
        'tipo_beca',
        'nacionalidad',
        'pais_nacimiento',
        'provincia_nacimiento',
        'canton_nacimiento',
        'empresa',
        'direccion_empresa',
        'telefono_empresa',
        'cargo',
        'ciudad_laboral',
    ];

    protected $casts = [
        'cantidad_hijos' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ---------------------------------------------------------------
     | Scopes
     |----------------------------------------------------------------*/

    /** Estudiantes con algún tipo de beca registrada (no nulo ni "ninguna"). */
    public function scopeConBeca($query)
    {
        return $query->whereNotNull('tipo_beca')->where('tipo_beca', '!=', 'ninguna');
    }

    /** Estudiantes que reportan estar laborando actualmente (tienen empresa). */
    public function scopeEmpleados($query)
    {
        return $query->whereNotNull('empresa')->where('empresa', '!=', '');
    }

    public function scopePorNacionalidad($query, string $nacionalidad)
    {
        return $query->where('nacionalidad', $nacionalidad);
    }

    public function scopeConHijos($query)
    {
        return $query->where('cantidad_hijos', '>', 0);
    }
}
