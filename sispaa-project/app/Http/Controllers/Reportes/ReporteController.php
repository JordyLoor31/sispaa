<?php

namespace App\Http\Controllers\Reportes;

use App\Exports\ReporteExport;
use App\Http\Controllers\Controller;
use App\Models\Admin\Carrera;
use App\Models\Admin\PeriodoAcademico;
use App\Models\Documentos\DocumentoEstudiante;
use App\Models\Documentos\GrupoDocumento;
use App\Models\Estudiantes\Falta;
use App\Models\Estudiantes\Matricula;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    /**
     * Solo estos 3 reportes: los que se pueden construir con datos que ya
     * existen en el sistema (matrículas, faltas/justificaciones, expediente
     * de documentos de Secretaría), en vez de inventar indicadores nuevos.
     */
    private function tiposDisponibles(): array
    {
        return [
            'matriculados' => 'Estudiantes Matriculados',
            'faltas' => 'Faltas y Justificaciones',
            'documentos' => 'Expediente de Documentos',
        ];
    }

    private function columnas(string $tipo): array
    {
        return match ($tipo) {
            'matriculados' => ['Cédula', 'Nombre', 'Email', 'Carrera', 'Período', 'Estado', 'Fecha Matrícula'],
            'faltas' => ['Estudiante', 'Materia', 'Período', 'Fecha', 'Justificada', 'Estado Justificación', 'Motivo'],
            'documentos' => ['Estudiante', 'Cédula', 'Grupo', 'Requisito', 'Tipo Documento', 'Estado', 'Fecha Revisión'],
            default => [],
        };
    }

    private function tituloReporte(string $tipo): string
    {
        return $this->tiposDisponibles()[$tipo] ?? 'Reporte';
    }

    /**
     * Query builder (sin ejecutar) según el tipo de reporte + filtros.
     * Separado del mapeo de filas para poder reutilizarlo tanto paginado
     * (vista previa) como completo (exportaciones).
     */
    private function query(string $tipo, Request $request): Builder
    {
        return match ($tipo) {
            'matriculados' => $this->queryMatriculados($request),
            'faltas' => $this->queryFaltas($request),
            'documentos' => $this->queryDocumentos($request),
            default => Matricula::query()->whereRaw('1 = 0'),
        };
    }

    private function queryMatriculados(Request $request): Builder
    {
        $query = Matricula::with(['estudiante', 'carrera', 'periodo']);

        if ($request->filled('periodo_id')) {
            $query->where('periodo_id', $request->periodo_id);
        }
        if ($request->filled('carrera_id')) {
            $query->where('carrera_id', $request->carrera_id);
        }

        return $query->orderBy('id');
    }

    private function queryFaltas(Request $request): Builder
    {
        $query = Falta::with(['estudiante', 'materia', 'periodo', 'justificacion']);

        if ($request->filled('periodo_id')) {
            $query->where('periodo_id', $request->periodo_id);
        }

        return $query->orderByDesc('fecha');
    }

    private function queryDocumentos(Request $request): Builder
    {
        $query = DocumentoEstudiante::with(['estudiante', 'grupo', 'requisito']);

        if ($request->filled('grupo_id')) {
            $query->where('grupo_id', $request->grupo_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        return $query->orderByDesc('created_at');
    }

    /**
     * Convierte un modelo ya cargado (con sus relaciones) en la fila de
     * texto plano que se muestra en la tabla y se exporta.
     */
    private function mapRow(string $tipo, $model): array
    {
        // Null-safe (?->) en todas las relaciones: un registro huérfano (p.ej.
        // un estudiante/materia/carrera eliminado) ya no rompe el reporte
        // completo con un error 500; la celda queda en '—'.
        return match ($tipo) {
            'matriculados' => [
                $model->estudiante?->cedula ?? '—',
                $model->estudiante?->name ?? '—',
                $model->estudiante?->email ?? '—',
                $model->carrera?->nombre ?? '—',
                $model->periodo?->nombre ?? '—',
                $model->estado,
                $model->fecha_matricula?->format('Y-m-d') ?? '—',
            ],
            'faltas' => [
                $model->estudiante?->name ?? '—',
                $model->materia?->nombre ?? '—',
                $model->periodo?->nombre ?? '—',
                $model->fecha?->format('Y-m-d') ?? '—',
                $model->justificada ? 'Sí' : 'No',
                $model->justificacion?->estado ?? '—',
                $model->motivo ?? '—',
            ],
            'documentos' => [
                $model->estudiante?->name ?? '—',
                $model->estudiante?->cedula ?? '—',
                $model->grupo?->nombre ?? '—',
                $model->requisito?->nombre ?? '—',
                $model->tipo_documento,
                $model->estado,
                $model->reviewed_at?->format('Y-m-d') ?? '—',
            ],
            default => [],
        };
    }

    /**
     * Todas las filas que cumplen los filtros, sin paginar (para exportar).
     */
    private function obtenerTodasLasFilas(string $tipo, Request $request): Collection
    {
        return $this->query($tipo, $request)->get()->map(fn ($m) => $this->mapRow($tipo, $m));
    }

    /**
     * Panel único: selector de tipo + filtros + vista previa paginada con
     * el mismo patrón de tabla (@tanstack/vue-table + shadcn Table) y
     * paginación server-side que el resto del sistema.
     */
    public function index(Request $request): Response
    {
        $tipo = $request->input('tipo', 'matriculados');
        if (!array_key_exists($tipo, $this->tiposDisponibles())) {
            $tipo = 'matriculados';
        }

        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));

        $paginado = $this->query($tipo, $request)
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($m) => $this->mapRow($tipo, $m));

        return Inertia::render('Reportes/Index', [
            'tipos' => $this->tiposDisponibles(),
            'tipoActual' => $tipo,
            'columnas' => $this->columnas($tipo),
            'filas' => $paginado,
            'periodos' => PeriodoAcademico::orderByDesc('fecha_inicio')->get(['id', 'nombre']),
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'grupos' => GrupoDocumento::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => $request->only(['periodo_id', 'carrera_id', 'grupo_id', 'estado', 'per_page']),
        ]);
    }

    public function exportCsv(Request $request)
    {
        $tipo = $request->input('tipo', 'matriculados');
        $columnas = $this->columnas($tipo);
        $filas = $this->obtenerTodasLasFilas($tipo, $request);
        $filename = 'reporte_' . $tipo . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($columnas, $filas) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para que Excel abra tildes/eñes correctamente.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $columnas);
            foreach ($filas as $fila) {
                fputcsv($handle, $fila);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportXlsx(Request $request)
    {
        $tipo = $request->input('tipo', 'matriculados');
        $columnas = $this->columnas($tipo);
        $filas = $this->obtenerTodasLasFilas($tipo, $request);
        $filename = 'reporte_' . $tipo . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ReporteExport($columnas, $filas), $filename);
    }

    public function exportPdf(Request $request)
    {
        $tipo = $request->input('tipo', 'matriculados');
        $titulo = $this->tituloReporte($tipo);
        $columnas = $this->columnas($tipo);
        $filas = $this->obtenerTodasLasFilas($tipo, $request);
        $filename = 'reporte_' . $tipo . '_' . now()->format('Ymd_His') . '.pdf';

        $pdf = Pdf::loadView('reportes.pdf', compact('titulo', 'columnas', 'filas'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
