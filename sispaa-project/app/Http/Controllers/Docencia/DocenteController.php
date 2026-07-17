<?php

namespace App\Http\Controllers\Docencia;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Docencia\AsignacionDocente;
use App\Models\Docencia\InformeDocente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
     *
     * El informe se comparte por materia+período: si otro docente que
     * dicta la misma materia (en otro paralelo) ya lo subió, se muestra
     * aquí también, aunque este docente no lo haya subido él mismo.
     */
    public function informes(): Response
    {
        $docenteId = Auth::id();

        $asignaciones = AsignacionDocente::with(['materia.carrera', 'periodo'])
            ->where('docente_id', $docenteId)
            ->whereHas('periodo', fn ($q) => $q->where('estado', 'activo'))
            ->get();

        $pares = $asignaciones
            ->map(fn ($a) => [$a->materia_id, $a->periodo_id])
            ->unique(fn ($p) => $p[0] . '-' . $p[1]);

        $informes = InformeDocente::with('docente:id,name')
            ->where('tipo', 'asignatura')
            ->where(function ($query) use ($pares) {
                foreach ($pares as $par) {
                    $query->orWhere(function ($q) use ($par) {
                        $q->where('materia_id', $par[0])->where('periodo_id', $par[1]);
                    });
                }
            })
            ->get()
            ->keyBy(fn ($i) => $i->materia_id . '-' . $i->periodo_id);

        $items = $asignaciones->map(function ($asig) use ($informes, $docenteId) {
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
                // Ver comentario en SilaboController::ver(): evita depender
                // del symlink public/storage (causa típica de 403 en XAMPP).
                'ver_url' => $informe ? route('docencia.mis-informes.ver', $informe->id) : null,
                'fecha_subida' => $informe?->fecha_subida?->diffForHumans(),
                // Nombre del docente que efectivamente subió el archivo,
                // solo cuando fue otro (mismo materia+período, otro paralelo).
                'subido_por' => ($informe && $informe->docente_id !== $docenteId) ? $informe->docente?->name : null,
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

        // Clave materia+período+tipo (sin docente_id): igual que en Sílabos,
        // cualquier docente del mismo paralelo/materia actualiza la misma
        // fila compartida. docente_id registra quién subió la versión vigente.
        InformeDocente::updateOrCreate(
            [
                'materia_id' => $request->materia_id,
                'periodo_id' => $request->periodo_id,
                'tipo' => 'asignatura',
            ],
            [
                'docente_id' => $docenteId,
                'estado' => 'subido',
                'archivo_url' => '/storage/' . $path,
                'fecha_subida' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Informe subido correctamente.');
    }

    /**
     * Sirve el archivo del informe a través de Laravel. Ver el comentario
     * en SilaboController::ver() para el detalle de por qué no se usa el
     * enlace directo /storage/... (403 típico en XAMPP por symlink).
     * El acceso se valida por materia+período (cualquier paralelo), igual
     * que en SilaboController::ver().
     */
    public function verInforme(InformeDocente $informe)
    {
        $tieneAcceso = AsignacionDocente::where('docente_id', Auth::id())
            ->where('materia_id', $informe->materia_id)
            ->where('periodo_id', $informe->periodo_id)
            ->exists();

        abort_unless(
            $tieneAcceso || Auth::user()->hasRole('SystemAdministrador'),
            403,
            'No tienes permiso para ver este documento.'
        );

        $ruta = $informe->archivo_url ? Str::after($informe->archivo_url, '/storage/') : null;

        abort_unless($ruta && Storage::disk('public')->exists($ruta), 404, 'El archivo ya no está disponible.');

        return Storage::disk('public')->response($ruta);
    }
}
