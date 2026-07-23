<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { MoreHorizontal, Eye, Pencil, Trash2 } from 'lucide-vue-next';

/**
 * Dropdown genérico de acciones (Ver/Editar/Eliminar) para una fila de tabla,
 * para dejar de repetir todo este bloque (dropdown + AlertDialog de
 * confirmación) en cada columns.ts de cada módulo.
 *
 * A diferencia del equivalente en DPPDI (que arma los 3 permisos a partir de
 * un string 'resource' tipo 'recurso.view'/'recurso.edit'/'recurso.delete'),
 * aquí el llamador decide directamente qué acciones mostrar vía
 * canView/canEdit/canDelete, porque en sispaa el control de acceso real es
 * por rol en la ruta (no permisos finos por acción) y muchas veces la regla
 * es de negocio (ej. "solo el dueño del registro puede editar/eliminar"),
 * no solo de permisos.
 */
interface Props {
    resourceName: string; // p.ej. 'la carrera', usado en los textos ("Ver la carrera", confirmación de borrado)
    showRoute?: string; // nombre de ruta Ziggy, p.ej. 'admin.carreras.show'
    editRoute?: string;
    deleteRoute?: string;
    routeParams?: unknown; // parámetro(s) para route(), normalmente el id del recurso
    itemLabel?: string; // texto del elemento en el diálogo de confirmación, p.ej. "la carrera \"Agronomía\""
    canView?: boolean;
    canEdit?: boolean;
    canDelete?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showRoute: undefined,
    editRoute: undefined,
    deleteRoute: undefined,
    routeParams: undefined,
    itemLabel: 'este elemento',
    canView: true,
    canEdit: true,
    canDelete: true,
});

const showEnabled = props.canView && !!props.showRoute;
const editEnabled = props.canEdit && !!props.editRoute;
const deleteEnabled = props.canDelete && !!props.deleteRoute;
const hasAnyAction = showEnabled || editEnabled || deleteEnabled;

// Bug conocido de Radix (dropdown-menu + alert-dialog anidados): el ítem
// "Eliminar" es a la vez un DropdownMenuItem y un AlertDialogTrigger. Radix
// cierra/desmonta el DropdownMenu al "seleccionar" un ítem (evento `select`,
// no `click`), y ese cierre ocurre ANTES de que el AlertDialog termine de
// abrirse y tomar el control del foco/overlay. Resultado: el diálogo de
// confirmación aparece y se cierra solo de inmediato, y la página queda
// "congelada" porque el <body> se queda con `pointer-events: none` (lo puso
// el DropdownMenu al cerrarse) sin que nada lo quite. La solución oficial de
// Radix/shadcn es prevenir el cierre por defecto en el evento `select` del
// ítem (no en `click`), para que el DropdownMenu no se desmonte hasta que el
// AlertDialog ya tomó el control.
const preventDropdownClose = (event: Event) => {
    event.preventDefault();
};
</script>

<template>
    <div v-if="hasAnyAction" class="flex justify-end">
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button
                    variant="ghost"
                    size="sm"
                    class="h-8 w-8 rounded-full p-0 opacity-60 transition-all hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)]"
                >
                    <span class="sr-only">Abrir menú</span>
                    <MoreHorizontal class="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem v-if="showEnabled" as-child>
                    <Link :href="route(showRoute!, routeParams)" class="w-full">
                        <Eye class="mr-2 h-4 w-4" />
                        Ver {{ resourceName }}
                    </Link>
                </DropdownMenuItem>

                <DropdownMenuItem v-if="editEnabled" as-child>
                    <Link :href="route(editRoute!, routeParams)" class="w-full">
                        <Pencil class="mr-2 h-4 w-4" />
                        Editar {{ resourceName }}
                    </Link>
                </DropdownMenuItem>

                <DropdownMenuSeparator v-if="deleteEnabled && (showEnabled || editEnabled)" />

                <AlertDialog v-if="deleteEnabled">
                    <AlertDialogTrigger as-child>
                        <DropdownMenuItem
                            class="text-destructive focus:text-destructive"
                            @select="preventDropdownClose"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Eliminar {{ resourceName }}
                        </DropdownMenuItem>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>¿Estás completamente seguro?</AlertDialogTitle>
                            <AlertDialogDescription>
                                Esta acción no se puede deshacer. Esto eliminará permanentemente {{ itemLabel }}.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancelar</AlertDialogCancel>
                            <AlertDialogAction
                                class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                @click="router.delete(route(deleteRoute!, routeParams), {
                                    preserveScroll: true,
                                })"
                            >
                                Sí, eliminar
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
