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
     * institucional: no se elige en el formulario, se fuerza aquí. No se
     * busca por un código fijo (antes era 'CVT') porque ese código es un
     * dato de la base y puede no coincidir exactamente con lo sembrado por
     * la migración (ej. se insertó manualmente con otro código) — eso
     * causaba que facultad_id se guardara como null sin ningún error
     * visible. Al haber una sola facultad activa hoy, basta con tomar esa.
     * Si en el futuro se necesitan más facultades seleccionables, hay que
     * quitar este forzado y volver a mostrar el combobox en el wizard.
     */
    private function facultadPorDefecto(): ?Facultad
    {
        return Facultad::where('activa', true)->orderBy('id')->first();
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
     * Carga las relaciones que necesita cualquier pantalla de "solo lectura"
     * del perfil (Show de Secretaría/SystemAdministrador y "Mis Datos" del
     * propio estudiante), para no repetir el load() en cada método.
     */
    private function cargarDetalle(User $estudiante): User
    {
        return tap($estudiante)->load(['perfilEstudiante.facultad', 'perfilEstudiante.carrera', 'datosAdicionales', 'familiares']);
    }

    /**
     * Convierte el usuario y sus relaciones en una estructura estable para
     * Inertia. Así evitamos depender de cómo Eloquent serializa los nombres
     * de las relaciones anidadas.
     */
    private function serializarDetalle(User $estudiante): array
    {
        return [
            'id' => $estudiante->id,
            'name' => $estudiante->name,
            'email' => $estudiante->email,
            'cedula' => $estudiante->cedula,
            'perfilEstudiante' => $estudiante->perfilEstudiante?->toArray(),
            'datosAdicionales' => $estudiante->datosAdicionales?->toArray(),
            'familiares' => $estudiante->familiares->values()->toArray(),
        ];
    }

    /**
     * Vista de solo lectura para Secretaría/SystemAdministrador: se llega
     * desde el data table de Usuarios (Admin/Usuarios/Show.vue) con el
     * botón "Ver Datos Adicionales", no desde un listado propio del módulo.
     */
    public function show(Request $request, ?User $estudiante = null): Response
    {
        $estudiante = $this->resolverEstudiante($request, $estudiante);
        $this->cargarDetalle($estudiante);

        return Inertia::render('Estudiantes/Perfil/Show', [
            'estudiante' => $this->serializarDetalle($estudiante),
            'breadcrumbs' => $this->adminBreadcrumbs(
                'Usuarios',
                'Datos Adicionales',
                route('admin.usuarios.show', $estudiante->id),
                $estudiante->name,
            ),
        ]);
    }

    /**
     * "Mis Datos": vista de solo lectura del propio estudiante con todo lo
     * completado en el wizard (académico, residencia, adicionales,
     * familiares). Siempre resuelve al usuario autenticado: no hay
     * {estudiante} en la URL porque es autoservicio.
     */
    public function misDatos(Request $request): Response
    {
        $estudiante = $this->cargarDetalle($request->user());

        return Inertia::render('Estudiantes/Perfil/MisDatos', [
            'estudiante' => $this->serializarDetalle($estudiante),
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
