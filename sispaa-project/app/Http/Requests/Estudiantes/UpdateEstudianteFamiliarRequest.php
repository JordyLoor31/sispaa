<?php

namespace App\Http\Requests\Estudiantes;

use App\Models\Estudiantes\EstudianteFamiliar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstudianteFamiliarRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var EstudianteFamiliar $familiar */
        $familiar = $this->route('familiar');

        return $familiar && (
            $familiar->user_id === $this->user()->id
            || $this->user()->hasRole('SystemAdministrador')
        );
    }

    public function rules(): array
    {
        return [
            'parentesco' => ['required', 'string', Rule::in(EstudianteFamiliar::PARENTESCOS)],
            'nombres' => ['required', 'string', 'max:255'],
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
