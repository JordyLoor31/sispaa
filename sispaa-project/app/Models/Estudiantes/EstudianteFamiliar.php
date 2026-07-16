<?php

namespace App\Models\Estudiantes;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Familiar/representante de un estudiante (1:N con users). Paso 4 del
 * wizard "Completar mi perfil". El listado de valores válidos de
 * "parentesco" vive aquí (const PARENTESCOS) para reutilizarse tanto en el
 * Form Request de validación como en el frontend (columns/Select).
 */
class EstudianteFamiliar extends Model
{
    protected $table = 'estudiante_familiares';

    public const PARENTESCO_PADRE = 'padre';
    public const PARENTESCO_MADRE = 'madre';
    public const PARENTESCO_CONYUGE = 'conyuge';
    public const PARENTESCO_HIJO = 'hijo';
    public const PARENTESCO_OTRO = 'otro';

    // 'representante' ya no es una opción seleccionable (se quitó del
    // formulario); filas existentes con ese valor no se tocan, pero no
    // vuelve a ofrecerse como opción.
    public const PARENTESCOS = [
        self::PARENTESCO_PADRE,
        self::PARENTESCO_MADRE,
        self::PARENTESCO_CONYUGE,
        self::PARENTESCO_HIJO,
        self::PARENTESCO_OTRO,
    ];

    protected $fillable = [
        'user_id',
        'parentesco',
        'nombres',
        'telefono',
        'correo',
        'ocupacion',
        'empresa',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ---------------------------------------------------------------
     | Scopes
     |----------------------------------------------------------------*/

    public function scopePorParentesco($query, string $parentesco)
    {
        return $query->where('parentesco', $parentesco);
    }

    public function scopePorUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
