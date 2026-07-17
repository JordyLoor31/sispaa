<?php

namespace App\Http\Controllers\Docencia;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Docencia\AsignacionDocente;
use App\Models\Docencia\Silabo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SilaboController extends Controller
{
    use HasBreadcrumbs;

    /**
     * Mis Sílabos: materias asignadas al docente en el período activo,
     * junto con el estado de su sílabo (si ya lo subió o no).
     */
    public function index(): Response
    {
        $docenteId = Auth::id();

        $asignaciones = AsignacionDocente::with(['materia.carrera', 'periodo'])
            ->where('docente_id', $docenteId)
            ->whereHas('periodo', fn ($q) => $q->where('estado', 'activo'))
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
                // URL de visualización servida por Laravel (no depende del
                // symlink public/storage ni de que Apache siga symlinks):
                // ver comentario en el método ver() más abajo.
                'ver_url' => $silabo ? route('docencia.mis-silabos.ver', $silabo->id) : null,
                'observaciones' => $silabo->observaciones ?? null,
                'fecha_subida' => $silabo?->fecha_subida?->diffForHumans(),
            ];
        })->values();

        return Inertia::render('Docencia/MisSilabos/Index', [
            'silabos' => $items,
            'breadcrumbs' => $this->docenciaBreadcrumbs('Mis Sílabos', null, route('docencia.mis-silabos')),
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

    /**
     * Sirve el archivo del sílabo a través de Laravel (streaming desde el
     * disco 'public'), en vez de depender del enlace estático
     * public/storage -> storage/app/public que crea `storage:link`. Ese
     * enlace es un symlink: si Apache no tiene "Options FollowSymLinks"
     * habilitado para la carpeta public/ (algo común en instalaciones
     * XAMPP por defecto) o el symlink no se creó correctamente, el
     * navegador recibe un 403 al abrir /storage/silabos/archivo.pdf aunque
     * el archivo exista y el usuario sea el dueño. Sirviéndolo desde aquí,
     * PHP lee el archivo directo del disco (sin pasar por ese symlink) y
     * además permite controlar el acceso: solo el docente dueño del
     * sílabo o SystemAdministrador pueden abrirlo.
     */
    public function ver(Silabo $silabo)
    {
        abort_unless(
            $silabo->docente_id === Auth::id() || Auth::user()->hasRole('SystemAdministrador'),
            403,
            'No tienes permiso para ver este documento.'
        );

        $ruta = $silabo->archivo_url ? Str::after($silabo->archivo_url, '/storage/') : null;

        abort_unless($ruta && Storage::disk('public')->exists($ruta), 404, 'El archivo ya no está disponible.');

        return Storage::disk('public')->response($ruta);
    }
}
