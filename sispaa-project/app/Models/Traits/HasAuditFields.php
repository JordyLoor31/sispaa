<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Asigna automáticamente created_by/updated_by con el usuario autenticado al
 * crear/actualizar el modelo, sin tener que repetirlo en cada controlador.
 *
 * El modelo que use este trait debe tener las columnas created_by/updated_by
 * (nullable, FK a users) en su tabla. Para exponerlas cómodamente en el
 * frontend, agrega también las relaciones:
 *
 *   public function creator() { return $this->belongsTo(User::class, 'created_by'); }
 *   public function updater() { return $this->belongsTo(User::class, 'updated_by'); }
 */
trait HasAuditFields
{
    protected static function bootHasAuditFields()
    {
        // Asigna created_by y updated_by si hay un usuario autenticado
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        // Asigna updated_by si hay un usuario autenticado
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
