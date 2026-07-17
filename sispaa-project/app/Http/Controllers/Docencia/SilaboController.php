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
     *
     * El sílabo se comparte por materia+período: si otro docente que
     * dicta la misma materia (en otro paralelo) ya lo subió, se muestra
     * aquí también, aunque este docente no lo haya subido él mismo.
     */
    public function index(): Response
    {
        $docenteId = Auth::id();

        $asignaciones = AsignacionDocente::with(['materia.carrera', 'periodo'])
            ->where('docente_id', $docenteId)
            ->whereHas('periodo', fn ($q) => $q->where('estado', 'activo'))
            ->get();

        $pares = $asignaciones
            ->map(fn ($a) => [$a->materia_id, $a->periodo_id])
            ->unique(fn ($p) => $p[0] . '-' . $p[1]);

        $silabos = Silabo::with('docente:id,name')
            ->where(function ($query) use ($pares) {
                foreach ($pares as $par) {
                    $query->orWhere(function ($q) use ($par) {
                        $q->where('materia_id', $par[0])->where('periodo_id', $par[1]);
                    });
                }
            })
            ->get()
            ->keyBy(fn ($s) => $s->materia_id . '-' . $s->periodo_id);

        $items = $asignaciones->map(function ($asig) use ($silabos, $docenteId) {
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
                // Nombre del docente que efectivamente subió el archivo,
                // solo cuando fue otro (mismo materia+período, otro paralelo).
                'subido_por' => ($silabo && $silabo->docente_id !== $docenteId) ? $silabo->docente?->name : null,
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

        // La clave es materia+período (sin docente_id): si otro docente del
        // mismo paralelo/materia ya tenía una fila, se actualiza esa misma
        // fila en vez de crear una nueva. docente_id queda registrado como
        // "quién subió la versión vigente".
        Silabo::updateOrCreate(
            [
                'materia_id' => $request->materia_id,
                'periodo_id' => $request->periodo_id,
            ],
            [
                'docente_id' => $docenteId,
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
     * además permite controlar el acceso: solo un docente asignado a la
     * misma materia+período (cualquier paralelo) o SystemAdministrador
     * pueden abrirlo.
     */
    public function ver(Silabo $silabo)
    {
        $tieneAcceso = AsignacionDocente::where('docente_id', Auth::id())
            ->where('materia_id', $silabo->materia_id)
            ->where('periodo_id', $silabo->periodo_id)
            ->exists();

        abort_unless(
            $tieneAcceso || Auth::user()->hasRole('SystemAdministrador'),
            403,
            'No tienes permiso para ver este documento.'
        );

        $ruta = $silabo->archivo_url ? Str::after($silabo->archivo_url, '/storage/') : null;

        abort_unless($ruta && Storage::disk('public')->exists($ruta), 404, 'El archivo ya no está disponible.');

        return Storage::disk('public')->response($ruta);
    }
}
