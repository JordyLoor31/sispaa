<?php

namespace App\Http\Requests\Estudiantes;

use App\Models\Estudiantes\EstudianteFamiliar;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEstudianteFamiliarRequest extends FormRequest
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
            'parentesco' => ['required', 'string', Rule::in(EstudianteFamiliar::PARENTESCOS)],
            'nombres' => ['required', 'string', 'max:255'],
            'cedula' => ['nullable', 'digits:10'],
            'telefono' => ['nullable', 'string', 'max:15'],
            'correo' => ['nullable', 'email', 'max:255'],
            'ocupacion' => ['nullable', 'string', 'max:150'],
            'empresa' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'parentesco' => 'parentesco',
            'nombres' => 'nombres',
        ];
    }
}
