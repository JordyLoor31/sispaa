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

    private function obtenerFilas(string $tipo, Request $request): Collection
    {
        return match ($tipo) {
            'matriculados' => $this->filasMatriculados($request),
            'faltas' => $this->filasFaltas($request),
            'documentos' => $this->filasDocumentos($request),
            default => collect(),
        };
    }

    private function filasMatriculados(Request $request): Collection
    {
        $query = Matricula::with(['estudiante', 'carrera', 'periodo']);

        if ($request->filled('periodo_id')) {
            $query->where('periodo_id', $request->periodo_id);
        }
        if ($request->filled('carrera_id')) {
            $query->where('carrera_id', $request->carrera_id);
        }

        return $query->orderBy('id')->get()->map(fn ($m) => [
            $m->estudiante->cedula,
            $m->estudiante->name,
            $m->estudiante->email,
            $m->carrera->nombre,
            $m->periodo->nombre,
            $m->estado,
            $m->fecha_matricula?->format('Y-m-d'),
        ]);
    }

    private function filasFaltas(Request $request): Collection
    {
        $query = Falta::with(['estudiante', 'materia', 'periodo', 'justificacion']);

        if ($request->filled('periodo_id')) {
            $query->where('periodo_id', $request->periodo_id);
        }

        return $query->orderByDesc('fecha')->get()->map(fn ($f) => [
            $f->estudiante->name,
            $f->materia->nombre,
            $f->periodo->nombre,
            $f->fecha?->format('Y-m-d'),
            $f->justificada ? 'Sí' : 'No',
            $f->justificacion->estado ?? '—',
            $f->motivo ?? '—',
        ]);
    }

    private function filasDocumentos(Request $request): Collection
    {
        $query = DocumentoEstudiante::with(['estudiante', 'grupo', 'requisito']);

        if ($request->filled('grupo_id')) {
            $query->where('grupo_id', $request->grupo_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        return $query->orderByDesc('created_at')->get()->map(fn ($d) => [
            $d->estudiante->name,
            $d->estudiante->cedula,
            $d->grupo->nombre ?? '—',
            $d->requisito->nombre ?? '—',
            $d->tipo_documento,
            $d->estado,
            $d->reviewed_at?->format('Y-m-d') ?? '—',
        ]);
    }

    private function tituloReporte(string $tipo): string
    {
        return $this->tiposDisponibles()[$tipo] ?? 'Reporte';
    }

    /**
     * Panel único: selector de tipo + filtros + vista previa en tabla.
     * La vista previa ES el "preview"; no hay una pantalla separada para eso.
     */
    public function index(Request $request): Response
    {
        $tipo = $request->input('tipo', 'matriculados');
        if (!array_key_exists($tipo, $this->tiposDisponibles())) {
            $tipo = 'matriculados';
        }

        return Inertia::render('Reportes/Index', [
            'tipos' => $this->tiposDisponibles(),
            'tipoActual' => $tipo,
            'columnas' => $this->columnas($tipo),
            'filas' => $this->obtenerFilas($tipo, $request),
            'periodos' => PeriodoAcademico::orderByDesc('fecha_inicio')->get(['id', 'nombre']),
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'grupos' => GrupoDocumento::orderBy('nombre')->get(['id', 'nombre']),
            'filters' => $request->only(['periodo_id', 'carrera_id', 'grupo_id', 'estado']),
        ]);
    }

    public function exportCsv(Request $request)
    {
        $tipo = $request->input('tipo', 'matriculados');
        $columnas = $this->columnas($tipo);
        $filas = $this->obtenerFilas($tipo, $request);
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
        $filas = $this->obtenerFilas($tipo, $request);
        $filename = 'reporte_' . $tipo . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ReporteExport($columnas, $filas), $filename);
    }

    public function exportPdf(Request $request)
    {
        $tipo = $request->input('tipo', 'matriculados');
        $titulo = $this->tituloReporte($tipo);
        $columnas = $this->columnas($tipo);
        $filas = $this->obtenerFilas($tipo, $request);
        $filename = 'reporte_' . $tipo . '_' . now()->format('Ymd_His') . '.pdf';

        $pdf = Pdf::loadView('reportes.pdf', compact('titulo', 'columnas', 'filas'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
