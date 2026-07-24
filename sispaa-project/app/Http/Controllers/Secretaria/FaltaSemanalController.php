<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Estudiantes\FaltaSemanal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD de Faltas Semanales por Carrera (tabla faltas_semanales_carrera):
 * reemplaza el antiguo flujo de faltas individuales por estudiante +
 * solicitud de justificación. Ahora Secretaría solo ingresa cuántas faltas
 * hubo en total, por carrera, en una semana determinada de un período
 * académico; ese número alimenta el gráfico "Faltas por carrera" en
 * Reportes.
 */
class FaltaSemanalController extends Controller
{
    use HasBreadcrumbs;

    private function carrerasDisponibles()
    {
        return Carrera::where('activa', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    private function periodosDisponibles()
    {
        return PeriodoAcademico::orderByDesc('id')->get(['id', 'nombre', 'estado']);
    }

    public function index(Request $request): Response
    {
        $carreraId = $request->input('carrera_id', 'all');
        $periodoId = $request->input('periodo_id', 'all');
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));

        $query = FaltaSemanal::with(['carrera:id,nombre', 'periodo:id,nombre,estado']);

        if ($carreraId !== 'all') {
            $query->where('carrera_id', $carreraId);
        }
        if ($periodoId !== 'all') {
            $query->where('periodo_id', $periodoId);
        }

        $faltas = $query->orderByDesc('periodo_id')->orderByDesc('semana')->paginate($perPage)->withQueryString();

        return Inertia::render('Secretaria/FaltasSemanales/Index', [
            'faltas' => $faltas,
            'carreras' => $this->carrerasDisponibles(),
            'periodos' => $this->periodosDisponibles(),
            'filters' => ['carrera_id' => $carreraId, 'periodo_id' => $periodoId],
            'breadcrumbs' => $this->secretariaBreadcrumbs('Faltas Semanales'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Secretaria/FaltasSemanales/Create', [
            'carreras' => $this->carrerasDisponibles(),
            'periodos' => $this->periodosDisponibles(),
            'breadcrumbs' => $this->secretariaBreadcrumbs('Faltas Semanales', 'Nuevo Registro', route('secretaria.faltas-semanales.index')),
        ]);
    }

    private function reglas($faltaId = null): array
    {
        return [
            'carrera_id' => ['required', Rule::exists('carreras', 'id')],
            'periodo_id' => ['required', Rule::exists('periodos_academicos', 'id')],
            'semana' => [
                'required', 'integer', 'min:1', 'max:52',
                Rule::unique('faltas_semanales_carrera', 'semana')
                    ->where(fn ($q) => $q->where('carrera_id', request('carrera_id'))->where('periodo_id', request('periodo_id')))
                    ->ignore($faltaId),
            ],
            'cantidad_faltas' => ['required', 'integer', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->reglas(), [
            'semana.unique' => 'Ya existe un registro para esa carrera, período y semana.',
        ]);

        FaltaSemanal::create($validated);

        return redirect()->route('secretaria.faltas-semanales.index')->with('success', 'Registro de faltas creado correctamente.');
    }

    public function edit(FaltaSemanal $faltaSemanal): Response
    {
        return Inertia::render('Secretaria/FaltasSemanales/Edit', [
            'falta' => $faltaSemanal->load(['carrera:id,nombre', 'periodo:id,nombre,estado']),
            'carreras' => $this->carrerasDisponibles(),
            'periodos' => $this->periodosDisponibles(),
            'breadcrumbs' => $this->secretariaBreadcrumbs('Faltas Semanales', 'Editar Registro', route('secretaria.faltas-semanales.index')),
        ]);
    }

    public function update(Request $request, FaltaSemanal $faltaSemanal)
    {
        $validated = $request->validate($this->reglas($faltaSemanal->id), [
            'semana.unique' => 'Ya existe un registro para esa carrera, período y semana.',
        ]);

        $faltaSemanal->update($validated);

        return redirect()->route('secretaria.faltas-semanales.index')->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(FaltaSemanal $faltaSemanal)
    {
        $faltaSemanal->delete();

        return redirect()->route('secretaria.faltas-semanales.index')->with('success', 'Registro eliminado correctamente.');
    }
}
