<?php

namespace App\Http\Controllers\Estudiantes;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Http\Requests\Estudiantes\UpdatePerfilEstudianteRequest;
use App\Models\Admin\Carrera;
use App\Models\Admin\Facultad;
use App\Models\Estudiantes\EstudianteDatosAdicionales;
use App\Models\Estudiantes\PerfilEstudiante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Wizard "Completar mi perfil": une perfiles_estudiantes (pasos 1-2) y
 * estudiante_datos_adicionales (paso 3) en una sola pantalla multi-step.
 * estudiante_familiares (paso 4) se gestiona en EstudianteFamiliarController.
 *
 * Los métodos reciben un ?User $estudiante opcional resuelto por route model
 * binding: hoy solo se enruta desde el portal del estudiante (sin {estudiante}
 * en la URL, así que cae al usuario autenticado), pero cuando Secretaría/
 * SystemAdministrador necesiten ver/editar el perfil de un estudiante
 * puntual, basta con agregar una ruta tipo
 * `/admin/estudiantes/{estudiante}/perfil` apuntando a estos mismos métodos:
 * no hace falta tocar el controlador.
 */
class PerfilEstudianteController extends Controller
{
    use HasBreadcrumbs;

    /**
     * Hoy todos los estudiantes pertenecen a una sola facultad
     * institucional: no se elige en el formulario, se fuerza aquí (ver
     * migración seed_default_facultad). Si en el futuro se necesitan más
     * facultades seleccionables, basta con quitar este forzado y volver a
     * mostrar el combobox en el wizard.
     */
    private const FACULTAD_DEFAULT_CODIGO = 'CVT';

    private function facultadPorDefecto(): ?Facultad
    {
        return Facultad::where('codigo', self::FACULTAD_DEFAULT_CODIGO)->first();
    }

    private function resolverEstudiante(Request $request, ?User $estudiante): User
    {
        $estudiante = $estudiante ?? $request->user();

        abort_unless(
            $estudiante->id === $request->user()->id || $request->user()->hasRole('SystemAdministrador'),
            403
        );

        return $estudiante;
    }

    /**
     * Listado paginado de perfiles (reservado para cuando Secretaría/
     * SystemAdministrador tengan acceso a este módulo; hoy no está
     * enrutado desde ningún sidebar).
     */
    public function index(Request $request): Response
    {
        $query = PerfilEstudiante::with(['user:id,name,email,cedula', 'facultad:id,nombre', 'carrera:id,nombre']);

        if ($carreraId = $request->input('carrera_id')) {
            $query->porCarrera((int) $carreraId);
        }

        if ($facultadId = $request->input('facultad_id')) {
            $query->porFacultad((int) $facultadId);
        }

        $perfiles = $query->paginate($request->input('per_page', 15))->withQueryString();

        return Inertia::render('Estudiantes/Perfil/Index', [
            'perfiles' => $perfiles,
            'filters' => $request->only(['carrera_id', 'facultad_id', 'per_page']),
            'breadcrumbs' => $this->estudiantesBreadcrumbs('Perfiles de Estudiantes'),
        ]);
    }

    public function show(Request $request, ?User $estudiante = null): Response
    {
        $estudiante = $this->resolverEstudiante($request, $estudiante);
        $estudiante->load(['perfilEstudiante.facultad', 'perfilEstudiante.carrera', 'datosAdicionales', 'familiares']);

        return Inertia::render('Estudiantes/Perfil/Show', [
            'estudiante' => $estudiante,
            'breadcrumbs' => $this->estudiantesBreadcrumbs(
                'Perfiles de Estudiantes',
                'Ver Perfil',
                route('admin.estudiantes.perfiles.index'),
                $estudiante->name,
            ),
        ]);
    }

    /**
     * Pantalla del wizard con los datos actuales (si ya existen) para
     * precargar los 4 pasos.
     */
    public function edit(Request $request, ?User $estudiante = null): Response
    {
        $estudiante = $this->resolverEstudiante($request, $estudiante);
        $estudiante->load(['perfilEstudiante', 'datosAdicionales', 'familiares']);

        return Inertia::render('Estudiantes/Perfil/Edit', [
            'estudiante' => [
                'id' => $estudiante->id,
                'name' => $estudiante->name,
                'email' => $estudiante->email,
                'cedula' => $estudiante->cedula,
            ],
            'perfil' => $estudiante->perfilEstudiante,
            'datosAdicionales' => $estudiante->datosAdicionales,
            'familiares' => $estudiante->familiares,
            'facultadNombre' => $this->facultadPorDefecto()?->nombre ?? 'Ciencias de la Vida y Tecnologías',
            'carreras' => Carrera::where('activa', true)->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    /**
     * Guarda perfil + datos adicionales en una sola transacción (upsert:
     * ambas tablas son 1:1, así que no hay "crear" separado de "editar").
     */
    public function update(UpdatePerfilEstudianteRequest $request, ?User $estudiante = null)
    {
        $estudiante = $estudiante ?? $request->user();
        $validated = $request->validated();

        // itinerario/pensum deliberadamente excluidos: ocultos en el
        // formulario y ya no están en las reglas de validación, así que no
        // se deben tocar aquí (evita sobrescribirlos con null).
        $perfilFields = [
            'tipo_alumno', 'nivel', 'sede', 'carrera_id',
            'anio_ingreso', 'graduado_pregrado', 'fecha_graduacion', 'colegio',
            'anio_graduacion_colegio', 'provincia_colegio', 'universidad_procedencia',
            'provincia_universidad', 'residente', 'direccion', 'provincia_residencia',
            'canton_residencia', 'telefono_casa',
        ];

        $datosAdicionalesFields = [
            'religion', 'estado_civil', 'cantidad_hijos', 'etnia', 'tipo_beca', 'nacionalidad',
            'pais_nacimiento', 'provincia_nacimiento', 'canton_nacimiento', 'empresa',
            'direccion_empresa', 'telefono_empresa', 'cargo', 'ciudad_laboral',
        ];

        $facultadId = $this->facultadPorDefecto()?->id;

        DB::transaction(function () use ($estudiante, $validated, $perfilFields, $datosAdicionalesFields, $facultadId) {
            PerfilEstudiante::updateOrCreate(
                ['user_id' => $estudiante->id],
                [
                    ...array_intersect_key($validated, array_flip($perfilFields)),
                    // Forzado en el servidor: no viene del formulario, todos
                    // los estudiantes pertenecen hoy a la misma facultad.
                    'facultad_id' => $facultadId,
                ]
            );

            EstudianteDatosAdicionales::updateOrCreate(
                ['user_id' => $estudiante->id],
                array_intersect_key($validated, array_flip($datosAdicionalesFields))
            );
        });

        return redirect()->back()->with('success', 'Perfil actualizado correctamente.');
    }
}
