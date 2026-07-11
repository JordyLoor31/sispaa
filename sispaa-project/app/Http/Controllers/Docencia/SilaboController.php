<?php

namespace App\Http\Controllers\Docencia;

use App\Http\Controllers\Controller;
use App\Models\Docencia\AsignacionDocente;
use App\Models\Docencia\Silabo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SilaboController extends Controller
{
    /**
     * Mis Sílabos: materias asignadas al docente en el período activo,
     * junto con el estado de su sílabo (si ya lo subió o no).
     */
    public function index(): Response
    {
        $docenteId = Auth::id();

        $asignaciones = AsignacionDocente::with(['materia.carrera', 'periodo'])
            ->where('docente_id', $docenteId)
            ->whereHas('periodo', fn ($q) => $q->where('activo', true))
            ->get();

        $silabos = Silabo::where('docente_id', $docenteId)
            ->whereIn('materia_id', $asignaciones->pluck('materia_id'))
            ->get()
            ->keyBy(fn ($s) => $s->materia_id . '-' . $s->periodo_id);

        $items = $asignaciones->map(function ($asig) use ($silabos) {
            $silabo = $silabos->get($asig->materia_id . '-' . $asig->periodo_id);

            return [
                'materia_id' => $asig->materia_id,
                'periodo_id' => $asig->periodo_id,
                'materia' => $asig->materia->nombre,
                'carrera' => $asig->materia->carrera?->nombre,
                'periodo' => $asig->periodo->nombre,
                'grupo' => $asig->grupo,
                'estado' => $silabo->estado ?? 'pendiente',
                'archivo_url' => $silabo->archivo_url ?? null,
                'observaciones' => $silabo->observaciones ?? null,
                'fecha_subida' => $silabo?->fecha_subida?->diffForHumans(),
            ];
        })->values();

        return Inertia::render('Docencia/MisSilabos', [
            'silabos' => $items,
        ]);
    }

    /**
     * Sube o reemplaza el sílabo de una materia/período asignados al docente.
     */
    public function store(Request $request)
    {
        $request->validate([
            'materia_id' => 'required|exists:materias,id',
            'periodo_id' => 'required|exists:periodos_academicos,id',
            'archivo' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $docenteId = Auth::id();

        $asignado = AsignacionDocente::where('docente_id', $docenteId)
            ->where('materia_id', $request->materia_id)
            ->where('periodo_id', $request->periodo_id)
            ->exists();

        if (!$asignado) {
            abort(403, 'No tienes asignada esta materia en este período.');
        }

        $path = $request->file('archivo')->store('silabos', 'public');

        Silabo::updateOrCreate(
            [
                'docente_id' => $docenteId,
                'materia_id' => $request->materia_id,
                'periodo_id' => $request->periodo_id,
            ],
            [
                'estado' => 'subido',
                'archivo_url' => '/storage/' . $path,
                'fecha_subida' => now(),
                'observaciones' => null,
            ]
        );

        return redirect()->back()->with('success', 'Sílabo subido correctamente.');
    }
}
