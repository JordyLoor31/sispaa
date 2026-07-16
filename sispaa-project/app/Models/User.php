<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cedula',
        'telefono',
        'is_active',
        'email_verified_at',
        'carrera_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function carrera()
    {
        return $this->belongsTo(\App\Models\Admin\Carrera::class, 'carrera_id');
    }

    /**
     * Módulo de perfil de estudiante (perfiles_estudiantes,
     * estudiante_datos_adicionales, estudiante_familiares). Cualquier
     * usuario puede tener estas relaciones, aunque hoy solo se usan para
     * quienes tienen el rol "estudiante".
     */
    public function perfilEstudiante()
    {
        return $this->hasOne(\App\Models\Estudiantes\PerfilEstudiante::class);
    }

    public function datosAdicionales()
    {
        return $this->hasOne(\App\Models\Estudiantes\EstudianteDatosAdicionales::class);
    }

    public function familiares()
    {
        return $this->hasMany(\App\Models\Estudiantes\EstudianteFamiliar::class);
    }
}
