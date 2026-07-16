<?php

namespace App\Http\Requests\Estudiantes;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida el guardado combinado del wizard "Completar mi perfil":
 * perfiles_estudiantes (pasos 1-2) + estudiante_datos_adicionales (paso 3)
 * en una sola petición. estudiante_familiares (paso 4) se gestiona aparte
 * vía EstudianteFamiliarController porque es 1:N (alta/baja inmediata).
 *
 * authorize() ya resuelve el "solo el propio estudiante, o SystemAdministrador
 * a futuro" para que el controlador no repita esa lógica.
 */
class UpdatePerfilEstudianteRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $estudiante */
        $estudiante = $this->route('estudiante') ?? $this->user();

        return $estudiante && (
            $estudiante->id === $this->user()->id
            || $this->user()->hasRole('SystemAdministrador')
        );
    }

    public function rules(): array
    {
        return [
            // --- Paso 1: Datos académicos ---
            'tipo_alumno' => ['nullable', 'string', 'max:100'],
            'nivel' => ['nullable', 'string', 'max:100'],
            'sede' => ['nullable', 'string', 'max:150'],
            // facultad_id ya no viene del formulario: todos los estudiantes
            // pertenecen hoy a una sola facultad, forzada en el controlador.
            'carrera_id' => ['nullable', 'integer', 'exists:carreras,id'],
            // itinerario/pensum: ocultos en el formulario por ahora, se
            // excluyen deliberadamente de las reglas para que validated()
            // no los incluya como null y no se sobrescriban datos existentes.
            'anio_ingreso' => ['nullable', 'integer', 'digits:4', 'min:1950', 'max:' . (date('Y') + 1)],
            'graduado_pregrado' => ['required', 'boolean'],
            'fecha_graduacion' => ['nullable', 'date', 'required_if:graduado_pregrado,1'],

            // --- Trayectoria educativa previa ---
            'colegio' => ['nullable', 'string', 'max:255'],
            'anio_graduacion_colegio' => ['nullable', 'integer', 'digits:4', 'min:1950', 'max:' . (date('Y') + 1)],
            'provincia_colegio' => ['nullable', 'string', 'max:100'],
            'universidad_procedencia' => ['nullable', 'string', 'max:255'],
            'provincia_universidad' => ['nullable', 'string', 'max:100'],

            // --- Paso 2: Residencia ---
            'residente' => ['required', 'boolean'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'provincia_residencia' => ['nullable', 'string', 'max:100'],
            'canton_residencia' => ['nullable', 'string', 'max:100'],
            'telefono_casa' => ['nullable', 'string', 'max:15'],

            // --- Paso 3: Datos personales y laborales (estudiante_datos_adicionales) ---
            'religion' => ['nullable', 'string', 'max:100'],
            'estado_civil' => ['nullable', 'string', Rule::in(['soltero', 'casado', 'divorciado', 'viudo', 'union_libre'])],
            'cantidad_hijos' => ['nullable', 'integer', 'min:0', 'max:30'],
            'etnia' => ['nullable', 'string', 'max:100'],
            'tipo_beca' => ['nullable', 'string', 'max:100'],
            'nacionalidad' => ['nullable', 'string', 'max:100'],
            'pais_nacimiento' => ['nullable', 'string', 'max:100'],
            'provincia_nacimiento' => ['nullable', 'string', 'max:100'],
            'canton_nacimiento' => ['nullable', 'string', 'max:100'],
            'empresa' => ['nullable', 'string', 'max:255'],
            'direccion_empresa' => ['nullable', 'string', 'max:255'],
            'telefono_empresa' => ['nullable', 'string', 'max:15'],
            'cargo' => ['nullable', 'string', 'max:150'],
            'ciudad_laboral' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function attributes(): array
    {
        return [
            'facultad_id' => 'facultad',
            'carrera_id' => 'carrera',
            'anio_ingreso' => 'año de ingreso',
            'anio_graduacion_colegio' => 'año de graduación del colegio',
            'graduado_pregrado' => 'graduado de pregrado',
            'fecha_graduacion' => 'fecha de graduación',
            'estado_civil' => 'estado civil',
            'cantidad_hijos' => 'cantidad de hijos',
        ];
    }
}
