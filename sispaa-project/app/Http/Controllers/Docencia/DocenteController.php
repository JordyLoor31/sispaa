<?php

namespace App\Http\Controllers\Docencia;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Docencia\AsignacionDocente;
use App\Models\Docencia\InformeDocente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DocenteController extends Controller
{
    use HasBreadcrumbs;

    public function index()
    {
        // Listar docentes (vista de gestión, pendiente)
    }

    public function cumplimiento()
    {
        // Mostrar cumplimiento de docentes (vista de gestión, pendiente)
    }

    public function silabus()
    {
        // Gestión de sílabos a nivel de coordinación (pendiente; el
        // autoservicio del docente vive en SilaboController).
    }

    /**
     * Mis Informes de Asignatura: materias asignadas al docente en el
     * período activo, junto con el estado de su informe (si ya lo subió).
     */
    public function informes(): Response
    {
        $docenteId = Auth::id();

        $asignaciones = AsignacionDocente::with(['materia.carrera', 'periodo'])
            ->where('docente_id', $docenteId)
            ->whereHas('periodo', fn ($q) => $q->where('activo', true))
            ->get();

        $informes = InformeDocente::where('docente_id', $docenteId)
            ->where('tipo', 'asignatura')
            ->whereIn('materia_id', $asignaciones->pluck('materia_id'))
            ->get()
            ->keyBy(fn ($i) => $i->materia_id . '-' . $i->periodo_id);

        $items = $asignaciones->map(function ($asig) use ($informes) {
            $informe = $informes->get($asig->materia_id . '-' . $asig->periodo_id);

            return [
                'materia_id' => $asig->materia_id,
                'periodo_id' => $asig->periodo_id,
                'materia' => $asig->materia->nombre,
                'carrera' => $asig->materia->carrera?->nombre,
                'periodo' => $asig->periodo->nombre,
                'grupo' => $asig->grupo,
                'estado' => $informe->estado ?? 'pendiente',
                'archivo_url' => $informe->archivo_url ?? null,
                'fecha_subida' => $informe?->fecha_subida?->diffForHumans(),
            ];
        })->values();

        return Inertia::render('Docencia/MisInformes/Index', [
            'informes' => $items,
            'breadcrumbs' => $this->docenciaBreadcrumbs('Mis Informes de Asignatura', null, route('docencia.mis-informes')),
        ]);
    }

    /**
     * Sube o reemplaza el informe de asignatura de una materia/período
     * asignados al docente.
     */
    public function storeInforme(Request $request)
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

        $path = $request->file('archivo')->store('informes', 'public');

        InformeDocente::updateOrCreate(
            [
                'docente_id' => $docenteId,
                'materia_id' => $request->materia_id,
                'periodo_id' => $request->periodo_id,
                'tipo' => 'asignatura',
            ],
            [
                'estado' => 'subido',
                'archivo_url' => '/storage/' . $path,
                'fecha_subida' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Informe subido correctamente.');
    }
}
