<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, MapPin, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetFooter } from '@/components/ui/sheet';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';

interface Catalogo { id: number; nombre: string }
interface LaboratorioItem {
    id: number;
    nombre: string;
    ubicacion: string | null;
    capacidad: number | null;
    estado: 'activo' | 'inactivo';
    carrera: string | null;
    carrera_id: number | null;
    responsable: { id: number; name: string } | null;
    equipos_count: number;
    reactivos_count: number;
    practicas_count: number;
}

const props = defineProps<{
    laboratorios: LaboratorioItem[];
    carreras: Catalogo[];
    responsables: { id: number; name: string }[];
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Laboratorio', href: route('laboratorio.index') },
    { title: 'Laboratorios', href: route('laboratorio.laboratorios') },
];

const showForm = ref(false);
const editing = ref<LaboratorioItem | null>(null);
const form = useForm({
    nombre: '',
    ubicacion: '',
    carrera_id: '' as number | '',
    capacidad: null as number | null,
    responsable_id: '' as number | '',
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

const openEdit = (l: LaboratorioItem) => {
    editing.value = l;
    form.nombre = l.nombre;
    form.ubicacion = l.ubicacion ?? '';
    form.carrera_id = l.carrera_id ?? '';
    form.capacidad = l.capacidad;
    form.responsable_id = l.responsable?.id ?? '';
    form.clearErrors();
    showForm.value = true;
};

const submit = () => {
    if (editing.value) {
        form.put(route('laboratorio.laboratorios.update', editing.value.id), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Laboratorio actualizado.'); showForm.value = false; },
        });
    } else {
        form.post(route('laboratorio.laboratorios.store'), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Laboratorio registrado.'); showForm.value = false; form.reset(); },
        });
    }
};

const toggleEstado = (l: LaboratorioItem) => {
    router.put(route('laboratorio.laboratorios.update', l.id), { estado: l.estado === 'activo' ? 'inactivo' : 'activo' }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Estado actualizado.'),
    });
};

const deleteTarget = ref<LaboratorioItem | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('laboratorio.laboratorios.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Laboratorio eliminado.'); deleteTarget.value = null; },
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Laboratorios" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Laboratorios</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Espacios físicos disponibles para prácticas.</p>
                </div>
                <Button @click="openCreate" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Plus class="h-4 w-4" /> Nuevo Laboratorio
                </Button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="l in laboratorios" :key="l.id"
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                            <MapPin class="h-4.5 w-4.5" />
                        </div>
                        <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', l.estado === 'activo' ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-400']">
                            {{ l.estado === 'activo' ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ l.nombre }}</h3>
                        <p v-if="l.ubicacion" class="text-xs text-slate-500 mt-0.5">{{ l.ubicacion }}</p>
                        <p v-if="l.carrera" class="text-xs text-slate-400 mt-1">{{ l.carrera }}</p>
                        <p v-if="l.capacidad" class="text-xs text-slate-400">Capacidad: {{ l.capacidad }}</p>
                        <p v-if="l.responsable" class="text-xs text-slate-400">Responsable: {{ l.responsable.name }}</p>
                    </div>
                    <div class="flex gap-3 text-xs text-slate-500">
                        <span>{{ l.equipos_count }} equipos</span>
                        <span>{{ l.reactivos_count }} reactivos</span>
                        <span>{{ l.practicas_count }} prácticas</span>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button @click="toggleEstado(l)" class="text-xs font-semibold text-slate-500 hover:text-indigo-600">
                            {{ l.estado === 'activo' ? 'Desactivar' : 'Activar' }}
                        </button>
                        <button @click="openEdit(l)" class="ml-auto rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900">
                            <Pencil class="h-3.5 w-3.5" />
                        </button>
                        <button @click="deleteTarget = l" class="rounded-lg p-1.5 text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20">
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>

                <div v-if="laboratorios.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                    No hay laboratorios registrados.
                </div>
            </div>
        </div>

        <!-- Sheet: Crear/Editar -->
        <Sheet :open="showForm" @update:open="val => { if (!val) { showForm = false; form.reset(); } }">
            <SheetContent class="sm:max-w-md overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>{{ editing ? 'Editar Laboratorio' : 'Nuevo Laboratorio' }}</SheetTitle>
                </SheetHeader>
                <form @submit.prevent="submit" class="mt-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre *</label>
                        <input v-model="form.nombre" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        <p v-if="form.errors.nombre" class="text-xs text-rose-500 mt-1">{{ form.errors.nombre }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Ubicación</label>
                        <input v-model="form.ubicacion" type="text" placeholder="Bloque A - Piso 2" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Carrera</label>
                        <Select v-model="form.carrera_id">
                            <SelectTrigger class="w-full"><SelectValue placeholder="Sin asignar" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Capacidad</label>
                        <input v-model.number="form.capacidad" type="number" min="1" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Responsable</label>
                        <Select v-model="form.responsable_id">
                            <SelectTrigger class="w-full"><SelectValue placeholder="Sin asignar" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="r in responsables" :key="r.id" :value="r.id">{{ r.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <SheetFooter class="pt-4 gap-2">
                        <Button type="button" variant="outline" @click="showForm = false">Cancelar</Button>
                        <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            {{ form.processing ? 'Guardando...' : 'Guardar' }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>

        <!-- Confirmar eliminación -->
        <AlertDialog :open="!!deleteTarget" @update:open="val => { if (!val) deleteTarget = null; }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Eliminar laboratorio?</AlertDialogTitle>
                    <AlertDialogDescription>Se eliminará "{{ deleteTarget?.nombre }}". Esta acción no se puede deshacer.</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDelete" class="bg-rose-600 hover:bg-rose-500">Eliminar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppSidebarLayout>
</template>
