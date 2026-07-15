<?php

namespace App\Http\Controllers\Traits;

/**
 * Construye arrays de breadcrumbs consistentes para pasarlos como prop
 * Inertia (en vez de hardcodearlos en cada archivo .vue). Cada método de
 * módulo antepone su "sección" y agrega la acción/ítem actual si se indica.
 *
 * Uso típico en un controlador:
 *
 *   return Inertia::render('Titulacion/Index', [
 *       ...
 *       'breadcrumbs' => $this->titulacionBreadcrumbs('Titulación'),
 *   ]);
 *
 *   return Inertia::render('Admin/Periodos/Edit', [
 *       ...
 *       'breadcrumbs' => $this->adminBreadcrumbs('Gestión de Periodos', 'Editar Período'),
 *   ]);
 */
trait HasBreadcrumbs
{
    /**
     * Siempre incluye "Vista general" (dashboard) al inicio de la ruta.
     */
    protected function breadcrumbs(array $items): array
    {
        return array_merge(
            [['title' => 'Vista general', 'href' => route('dashboard')]],
            $items
        );
    }

    /**
     * Helper genérico usado por todos los métodos de módulo: arma
     * [Sección] > [Acción] > [Ítem] con el mismo esqueleto.
     */
    private function moduleBreadcrumbs(string $moduleLabel, ?string $moduleRoute, string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        $crumbs = $moduleRoute
            ? [['title' => $moduleLabel, 'href' => $moduleRoute]]
            : [['title' => $moduleLabel, 'href' => '#']];

        $crumbs[] = $sectionRoute
            ? ['title' => $section, 'href' => $sectionRoute]
            : ['title' => $section, 'href' => '#'];

        if ($action) {
            $crumbs[] = ['title' => $action, 'href' => '#'];
        }

        if ($itemTitle) {
            $crumbs[] = ['title' => $itemTitle, 'href' => '#'];
        }

        return $this->breadcrumbs($crumbs);
    }

    /** Breadcrumbs para Administración (Usuarios, Malla Curricular, Fechas y Convocatorias) */
    protected function adminBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Administración', null, $section, $action, $sectionRoute, $itemTitle);
    }

    /** Breadcrumbs para Secretaría (Expediente, Justificaciones, Matrículas, Convocatorias, etc.) */
    protected function secretariaBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Secretaría', null, $section, $action, $sectionRoute, $itemTitle);
    }

    /** Breadcrumbs para Investigación */
    protected function investigacionBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Investigación', route('investigacion.index'), $section, $action, $sectionRoute, $itemTitle);
    }

    /** Breadcrumbs para Titulación */
    protected function titulacionBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Titulación', route('titulacion.index'), $section, $action, $sectionRoute, $itemTitle);
    }

    /** Breadcrumbs para Vinculación (Actividades, Empresas beneficiadas) */
    protected function vinculacionBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Vinculación', route('vinculacion.actividades'), $section, $action, $sectionRoute, $itemTitle);
    }

    /** Breadcrumbs para Laboratorio (Prácticas, Laboratorios, Equipos, Reactivos, Por carrera) */
    protected function laboratorioBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Laboratorio', route('laboratorio.index'), $section, $action, $sectionRoute, $itemTitle);
    }

    /** Breadcrumbs para Docencia (Mis Sílabos, Mis Informes) */
    protected function docenciaBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Docencia', null, $section, $action, $sectionRoute, $itemTitle);
    }

    /** Breadcrumbs para Estudiantes (gestión staff: Matriculados, Faltas, Justificaciones) */
    protected function estudiantesBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Estudiantes', route('estudiantes.index'), $section, $action, $sectionRoute, $itemTitle);
    }

    /** Breadcrumbs para Reportes */
    protected function reportesBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Reportes', route('reportes.index'), $section, $action, $sectionRoute, $itemTitle);
    }

    /** Breadcrumbs para el Portal del Estudiante */
    protected function estudianteBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Mi Portal', null, $section, $action, $sectionRoute, $itemTitle);
    }

    /** Breadcrumbs para el centro de notificaciones (staff) */
    protected function notificacionesBreadcrumbs(?string $action = null): array
    {
        return $this->breadcrumbs(array_filter([
            ['title' => 'Notificaciones', 'href' => route('notificaciones.index')],
            $action ? ['title' => $action, 'href' => '#'] : null,
        ]));
    }
}
