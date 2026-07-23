<?php

namespace App\Http\Controllers\Vinculacion;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\User;
use App\Models\Vinculacion\ActividadVinculacion;
use App\Models\Vinculacion\Beneficiario;
use App\Models\Vinculacion\BeneficiarioRegistro;
use App\Models\Vinculacion\Representante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD de Actividades de Vinculación.
 *
 * Flujo de estados:
 *  - Al crear nace en "en_ejecucion" con fecha_inicio y un registro de
 *    beneficiarios "inicial" (matriz género x edad).
 *  - Mientras esté "en_ejecucion" se agregan registros "adicional"
 *    (agregarBeneficiarios), que se suman al total (no se sobrescriben).
 *  - Al pasar a "ejecutado" se exige fecha_fin y el conteo queda consolidado.
 *  - Al "cancelado" se pueden capturar fecha_cierre y motivo (opcionales).
 */
class ActividadVinculacionController extends Controller
{
    use HasBreadcrumbs;

    private const GENEROS = ['hombre', 'mujer'];
    private const RANGOS = ['ninos', 'jovenes', 'adultos', 'adultos_mayores'];

    private function catalogos(): array
    {
        return [
            'docentes' => User::role('docente')->orderBy('name')->get(['id', 'name']),
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'periodos' => PeriodoAcademico::where('estado', 1)->get(['id', 'nombre']),
            'beneficiarios' => Beneficiario::orderBy('nombre')->get(['id', 'nombre', 'tipo', 'ruc', 'cedula']),
            'representantes' => Representante::orderBy('nombre')->get(['id', 'nombre', 'cargo', 'cedula', 'telefono', 'beneficiario_id']),
        ];
    }

    public function index(Request $request): Response
    {
        $estado = $request->input('estado', 'all');

        $query = ActividadVinculacion::with(['docenteLider', 'supervisor', 'carrera', 'periodo', 'beneficiario', 'registros.conteos']);
        if ($estado !== 'all') {
            $query->where('estado', $estado);
        }

        $actividades = $query->orderByDesc('created_at')->get()->map(fn ($a) => [
            'id' => $a->id,
            'nombre' => $a->nombre,
            'estado' => $a->estado,
            'fecha_inicio' => $a->fecha_inicio?->format('Y-m-d'),
            'fecha_fin' => $a->fecha_fin?->format('Y-m-d'),
            'docente_lider' => $a->docenteLider ? ['id' => $a->docenteLider->id, 'name' => $a->docenteLider->name] : null,
            'supervisor' => $a->supervisor ? ['id' => $a->supervisor->id, 'name' => $a->supervisor->name] : null,
            'carrera_id' => $a->carrera_id,
            'carrera' => $a->carrera?->nombre,
            'periodo_id' => $a->periodo_id,
            'periodo' => $a->periodo?->nombre,
            'beneficiario_id' => $a->beneficiario_id,
            'beneficiario' => $a->beneficiario?->nombre,
            'total_beneficiarios' => $this->totalBeneficiarios($a),
        ]);

        return Inertia::render('Vinculacion/Actividades/Index', [
            'actividades' => $actividades,
            'filters' => ['estado' => $estado],
            'stats' => [
                'en_ejecucion' => ActividadVinculacion::where('estado', 'en_ejecucion')->count(),
                'ejecutadas' => ActividadVinculacion::where('estado', 'ejecutado')->count(),
                'canceladas' => ActividadVinculacion::where('estado', 'cancelado')->count(),
                'total' => ActividadVinculacion::count(),
            ],
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Actividades'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Vinculacion/Actividades/Create', [
            ...$this->catalogos(),
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Actividades', 'Nueva Actividad', route('vinculacion.actividades')),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->reglasDatos() + [
            'fecha_registro_inicial' => 'nullable|date',
        ] + $this->reglasConteos());

        DB::transaction(function () use ($request, $data) {
            $representanteId = $this->resolverRepresentante($data);

            $actividad = ActividadVinculacion::create([
                'nombre' => $data['nombre'],
                'docente_lider_id' => $data['docente_lider_id'],
                'supervisor_id' => $data['supervisor_id'],
                'carrera_id' => $data['carrera_id'],
                'periodo_id' => $data['periodo_id'],
                'beneficiario_id' => $data['beneficiario_id'],
                'representante_id' => $representanteId,
                'fecha_inicio' => $data['fecha_inicio'],
                'estado' => 'en_ejecucion',
            ]);

            $this->guardarConteos(
                $actividad,
                'inicial',
                $data['fecha_registro_inicial'] ?? $data['fecha_inicio'],
                null,
                $request->input('conteos', [])
            );
        });

        return redirect()->route('vinculacion.actividades')->with('success', 'Actividad de vinculación registrada.');
    }

    public function show(ActividadVinculacion $actividad): Response
    {
        $actividad->load([
            'docenteLider', 'supervisor', 'carrera', 'periodo', 'beneficiario',
            'representante', 'registros.conteos', 'creator', 'updater',
        ]);

        return Inertia::render('Vinculacion/Actividades/Show', [
            'actividad' => [
                'id' => $actividad->id,
                'nombre' => $actividad->nombre,
                'estado' => $actividad->estado,
                'fecha_inicio' => $actividad->fecha_inicio?->format('Y-m-d'),
                'fecha_fin' => $actividad->fecha_fin?->format('Y-m-d'),
                'fecha_cierre' => $actividad->fecha_cierre?->format('Y-m-d'),
                'motivo_cancelacion' => $actividad->motivo_cancelacion,
                'docente_lider' => $actividad->docenteLider ? ['id' => $actividad->docenteLider->id, 'name' => $actividad->docenteLider->name] : null,
                'supervisor' => $actividad->supervisor ? ['id' => $actividad->supervisor->id, 'name' => $actividad->supervisor->name] : null,
                'carrera' => $actividad->carrera?->nombre,
                'periodo' => $actividad->periodo?->nombre,
                'beneficiario' => $actividad->beneficiario ? [
                    'id' => $actividad->beneficiario->id,
                    'nombre' => $actividad->beneficiario->nombre,
                    'tipo' => $actividad->beneficiario->tipo,
                    'ruc' => $actividad->beneficiario->ruc,
                    'cedula' => $actividad->beneficiario->cedula,
                ] : null,
                'representante' => $actividad->representante ? [
                    'id' => $actividad->representante->id,
                    'nombre' => $actividad->representante->nombre,
                    'cedula' => $actividad->representante->cedula,
                    'cargo' => $actividad->representante->cargo,
                    'telefono' => $actividad->representante->telefono,
                ] : null,
                'registros' => $actividad->registros->map(fn ($r) => [
                    'id' => $r->id,
                    'tipo' => $r->tipo,
                    'fecha' => $r->fecha?->format('Y-m-d'),
                    'observacion' => $r->observacion,
                    'total' => $r->total,
                    'conteos' => $r->conteos->map(fn ($c) => [
                        'genero' => $c->genero,
                        'rango_edad' => $c->rango_edad,
                        'cantidad' => $c->cantidad,
                    ]),
                ]),
                'resumen' => $this->resumenBeneficiarios($actividad),
                'creator' => $actividad->creator ? ['id' => $actividad->creator->id, 'name' => $actividad->creator->name] : null,
                'updater' => $actividad->updater ? ['id' => $actividad->updater->id, 'name' => $actividad->updater->name] : null,
                'created_at' => $actividad->created_at,
            ],
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Actividades', 'Ver Actividad', route('vinculacion.actividades'), $actividad->nombre),
        ]);
    }

    public function edit(ActividadVinculacion $actividad): Response
    {
        $actividad->load(['docenteLider', 'supervisor', 'carrera', 'periodo', 'beneficiario', 'representante', 'registroInicial.conteos']);

        return Inertia::render('Vinculacion/Actividades/Edit', [
            'actividad' => [
                'id' => $actividad->id,
                'nombre' => $actividad->nombre,
                'estado' => $actividad->estado,
                'fecha_inicio' => $actividad->fecha_inicio?->format('Y-m-d'),
                'fecha_fin' => $actividad->fecha_fin?->format('Y-m-d'),
                'docente_lider' => $actividad->docenteLider ? ['id' => $actividad->docenteLider->id, 'name' => $actividad->docenteLider->name] : null,
                'supervisor' => $actividad->supervisor ? ['id' => $actividad->supervisor->id, 'name' => $actividad->supervisor->name] : null,
                'carrera_id' => $actividad->carrera_id,
                'periodo_id' => $actividad->periodo_id,
                'beneficiario_id' => $actividad->beneficiario_id,
                'representante_id' => $actividad->representante_id,
                'representante' => $actividad->representante ? [
                    'id' => $actividad->representante->id,
                    'nombre' => $actividad->representante->nombre,
                    'cedula' => $actividad->representante->cedula,
                    'cargo' => $actividad->representante->cargo,
                    'telefono' => $actividad->representante->telefono,
                ] : null,
                'conteos_iniciales' => $actividad->registroInicial
                    ? $actividad->registroInicial->conteos->map(fn ($c) => [
                        'genero' => $c->genero,
                        'rango_edad' => $c->rango_edad,
                        'cantidad' => $c->cantidad,
                    ])
                    : [],
            ],
            ...$this->catalogos(),
            'breadcrumbs' => $this->vinculacionBreadcrumbs('Actividades', 'Editar Actividad', route('vinculacion.actividades'), $actividad->nombre),
        ]);
    }

    public function update(Request $request, ActividadVinculacion $actividad)
    {
        // 1) Edición de datos generales (+ conteo inicial mientras esté en ejecución).
        if ($request->filled('nombre')) {
            $data = $request->validate($this->reglasDatos() + $this->reglasConteos());

            $actividad->update([
                'nombre' => $data['nombre'],
                'docente_lider_id' => $data['docente_lider_id'],
                'supervisor_id' => $data['supervisor_id'],
                'carrera_id' => $data['carrera_id'],
                'periodo_id' => $data['periodo_id'],
                'beneficiario_id' => $data['beneficiario_id'],
                'representante_id' => $this->resolverRepresentante($data),
                'fecha_inicio' => $data['fecha_inicio'],
            ]);

            // El conteo inicial solo se puede corregir mientras la actividad
            // siga "en ejecución"; una vez ejecutada queda consolidada.
            if ($actividad->estado === 'en_ejecucion' && $request->has('conteos')) {
                DB::transaction(function () use ($actividad, $request) {
                    $actividad->registros()->where('tipo', 'inicial')->each(fn ($r) => $r->delete());
                    $this->guardarConteos($actividad, 'inicial', $actividad->fecha_inicio?->format('Y-m-d'), null, $request->input('conteos', []));
                });
            }
        }

        // 2) Cambio de estado (con la lógica de fechas de fin/cierre).
        if ($request->filled('estado')) {
            $this->cambiarEstado($request, $actividad);
        }

        return redirect()->route('vinculacion.actividades')->with('success', 'Actividad actualizada.');
    }

    /**
     * Registra una tanda "adicional" de beneficiarios mientras la actividad
     * siga en ejecución. Se suma al total, nunca sobrescribe.
     */
    public function agregarBeneficiarios(Request $request, ActividadVinculacion $actividad)
    {
        if ($actividad->estado !== 'en_ejecucion') {
            return back()->with('error', 'Solo se pueden agregar beneficiarios mientras la actividad está en ejecución.');
        }

        $data = $request->validate([
            'fecha' => 'nullable|date',
            'observacion' => 'nullable|string|max:255',
        ] + $this->reglasConteos(true));

        $this->guardarConteos($actividad, 'adicional', $data['fecha'] ?? now()->toDateString(), $data['observacion'] ?? null, $request->input('conteos', []));

        return back()->with('success', 'Beneficiarios adicionales registrados.');
    }

    public function destroy(ActividadVinculacion $actividad)
    {
        $actividad->delete();

        return redirect()->route('vinculacion.actividades')->with('success', 'Actividad eliminada.');
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function reglasDatos(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'docente_lider_id' => 'required|exists:users,id',
            'supervisor_id' => 'required|exists:users,id',
            'carrera_id' => 'required|exists:carreras,id',
            'periodo_id' => 'required|exists:periodos_academicos,id',
            'beneficiario_id' => 'required|exists:beneficiarios,id',
            // Representante: se reusa uno existente (representante_id) o se
            // registra uno nuevo con estos campos.
            'representante_id' => 'nullable|exists:representantes,id',
            'representante_nombre' => 'nullable|string|max:255',
            'representante_cedula' => 'nullable|digits:10',
            'representante_cargo' => 'nullable|string|max:255',
            'representante_telefono' => 'nullable|digits:10',
            'fecha_inicio' => 'required|date',
        ];
    }

    /**
     * Devuelve el id del representante a asociar: reusa el existente o crea uno
     * nuevo (ligado al beneficiario) si se ingresaron sus datos.
     */
    private function resolverRepresentante(array $data): ?int
    {
        if (!empty($data['representante_id'])) {
            return (int) $data['representante_id'];
        }

        if (!empty($data['representante_nombre'])) {
            $rep = Representante::create([
                'beneficiario_id' => $data['beneficiario_id'] ?? null,
                'nombre' => $data['representante_nombre'],
                'cedula' => $data['representante_cedula'] ?? null,
                'cargo' => $data['representante_cargo'] ?? null,
                'telefono' => $data['representante_telefono'] ?? null,
            ]);

            return $rep->id;
        }

        return null;
    }

    /**
     * Reglas para la matriz género x edad. En un registro adicional se exige al
     * menos una celda con cantidad > 0; en el inicial puede quedar en 0.
     */
    private function reglasConteos(bool $requerido = false): array
    {
        return [
            'conteos' => ($requerido ? 'required|' : 'nullable|') . 'array',
            'conteos.*.genero' => 'required|in:' . implode(',', self::GENEROS),
            'conteos.*.rango_edad' => 'required|in:' . implode(',', self::RANGOS),
            'conteos.*.cantidad' => 'required|integer|min:0',
        ];
    }

    private function guardarConteos(ActividadVinculacion $actividad, string $tipo, ?string $fecha, ?string $observacion, array $conteos): void
    {
        $registro = $actividad->registros()->create([
            'tipo' => $tipo,
            'fecha' => $fecha,
            'observacion' => $observacion,
        ]);

        foreach ($conteos as $c) {
            $cantidad = (int) ($c['cantidad'] ?? 0);
            if ($cantidad <= 0) {
                continue; // solo persistimos celdas con valor
            }
            $registro->conteos()->create([
                'genero' => $c['genero'],
                'rango_edad' => $c['rango_edad'],
                'cantidad' => $cantidad,
            ]);
        }
    }

    private function cambiarEstado(Request $request, ActividadVinculacion $actividad): void
    {
        $request->validate(['estado' => 'required|in:en_ejecucion,ejecutado,cancelado']);
        $estado = $request->input('estado');

        if ($estado === 'ejecutado') {
            $request->validate([
                'fecha_fin' => 'required|date|after_or_equal:' . $actividad->fecha_inicio?->format('Y-m-d'),
            ], [], ['fecha_fin' => 'fecha de fin']);

            $actividad->update([
                'estado' => 'ejecutado',
                'fecha_fin' => $request->input('fecha_fin'),
            ]);

            return;
        }

        if ($estado === 'cancelado') {
            $request->validate([
                'fecha_cierre' => 'nullable|date|after_or_equal:' . $actividad->fecha_inicio?->format('Y-m-d'),
                'motivo_cancelacion' => 'nullable|string|max:500',
            ]);

            $actividad->update([
                'estado' => 'cancelado',
                'fecha_cierre' => $request->input('fecha_cierre'),
                'motivo_cancelacion' => $request->input('motivo_cancelacion'),
            ]);

            return;
        }

        // Volver a en_ejecucion: se limpian las fechas de cierre.
        $actividad->update([
            'estado' => 'en_ejecucion',
            'fecha_fin' => null,
            'fecha_cierre' => null,
            'motivo_cancelacion' => null,
        ]);
    }

    private function totalBeneficiarios(ActividadVinculacion $actividad): int
    {
        return (int) $actividad->registros->sum(fn ($r) => $r->conteos->sum('cantidad'));
    }

    /**
     * Consolida todos los registros (iniciales + adicionales) en la matriz
     * género x edad, con totales por género, por edad y total general.
     */
    private function resumenBeneficiarios(ActividadVinculacion $actividad): array
    {
        $matriz = [];
        foreach (self::GENEROS as $g) {
            foreach (self::RANGOS as $r) {
                $matriz[$g][$r] = 0;
            }
        }
        $porGenero = array_fill_keys(self::GENEROS, 0);
        $porEdad = array_fill_keys(self::RANGOS, 0);
        $total = 0;

        foreach ($actividad->registros as $registro) {
            foreach ($registro->conteos as $c) {
                if (!isset($matriz[$c->genero][$c->rango_edad])) {
                    continue;
                }
                $matriz[$c->genero][$c->rango_edad] += $c->cantidad;
                $porGenero[$c->genero] += $c->cantidad;
                $porEdad[$c->rango_edad] += $c->cantidad;
                $total += $c->cantidad;
            }
        }

        return [
            'matriz' => $matriz,
            'por_genero' => $porGenero,
            'por_edad' => $porEdad,
            'total' => $total,
        ];
    }
}
