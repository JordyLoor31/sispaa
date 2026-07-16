<?php

namespace App\Http\Controllers\Estudiantes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Estudiantes\StoreEstudianteFamiliarRequest;
use App\Http\Requests\Estudiantes\UpdateEstudianteFamiliarRequest;
use App\Models\Estudiantes\EstudianteFamiliar;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * CRUD de familiares/representantes del estudiante (paso 4 del wizard de
 * perfil). A diferencia de perfil+datos adicionales, esto se persiste de
 * inmediato por fila (alta/edición/baja), no junto con el resto del wizard,
 * porque es una relación 1:N genuina (lista que crece/decrece).
 */
class EstudianteFamiliarController extends Controller
{
    public function index(Request $request, ?User $estudiante = null)
    {
        $estudiante = $estudiante ?? $request->user();

        abort_unless(
            $estudiante->id === $request->user()->id || $request->user()->hasRole('SystemAdministrador'),
            403
        );

        return response()->json(
            EstudianteFamiliar::porUsuario($estudiante->id)->orderBy('id')->get()
        );
    }

    public function store(StoreEstudianteFamiliarRequest $request, ?User $estudiante = null)
    {
        $estudiante = $estudiante ?? $request->user();

        EstudianteFamiliar::create([
            ...$request->validated(),
            'user_id' => $estudiante->id,
        ]);

        return redirect()->back()->with('success', 'Familiar agregado correctamente.');
    }

    public function update(UpdateEstudianteFamiliarRequest $request, EstudianteFamiliar $familiar)
    {
        $familiar->update($request->validated());

        return redirect()->back()->with('success', 'Familiar actualizado correctamente.');
    }

    public function destroy(Request $request, EstudianteFamiliar $familiar)
    {
        abort_unless(
            $familiar->user_id === $request->user()->id || $request->user()->hasRole('SystemAdministrador'),
            403
        );

        $familiar->delete();

        return redirect()->back()->with('success', 'Familiar eliminado correctamente.');
    }
}
