<?php

namespace App\Http\Controllers\Traits;

trait HasBreadcrumbs
{
    protected function breadcrumbs(array $items): array
    {
        return array_merge(
            [['title' => 'Vista general', 'href' => route('dashboard')]],
            $items
        );
    }

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

    protected function adminBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Administración', null, $section, $action, $sectionRoute, $itemTitle);
    }

    protected function secretariaBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Secretaría', null, $section, $action, $sectionRoute, $itemTitle);
    }

    protected function investigacionBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Investigación', route('investigacion.index'), $section, $action, $sectionRoute, $itemTitle);
    }

    protected function titulacionBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Titulación', route('titulacion.index'), $section, $action, $sectionRoute, $itemTitle);
    }

    protected function silabosBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Sílabos', route('coordinador.silabos.index'), $section, $action, $sectionRoute, $itemTitle);
    }

    protected function vinculacionBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Vinculación', route('vinculacion.actividades'), $section, $action, $sectionRoute, $itemTitle);
    }

    protected function laboratorioBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Laboratorio', route('laboratorio.index'), $section, $action, $sectionRoute, $itemTitle);
    }

    protected function docenciaBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Docencia', null, $section, $action, $sectionRoute, $itemTitle);
    }

    protected function estudiantesBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Estudiantes', route('estudiantes.index'), $section, $action, $sectionRoute, $itemTitle);
    }

    protected function reportesBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Reportes', route('reportes.index'), $section, $action, $sectionRoute, $itemTitle);
    }

    protected function estudianteBreadcrumbs(string $section, ?string $action = null, ?string $sectionRoute = null, ?string $itemTitle = null): array
    {
        return $this->moduleBreadcrumbs('Mi Portal', null, $section, $action, $sectionRoute, $itemTitle);
    }

    protected function notificacionesBreadcrumbs(?string $action = null): array
    {
        return $this->breadcrumbs(array_filter([
            ['title' => 'Notificaciones', 'href' => route('notificaciones.index')],
            $action ? ['title' => $action, 'href' => '#'] : null,
        ]));
    }
}
