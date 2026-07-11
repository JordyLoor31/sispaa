<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, GraduationCap, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetFooter } from '@/components/ui/sheet';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';

interface Persona { id: number; name: string }
interface TitulacionItem {
    id: number;
    tema: string;
    estado: 'en_proceso' | 'defendido' | 'graduado';
    fecha_inicio: string | null;
    fecha_graduacion: string | null;
    estudiante: Persona;
    tutor: Persona;
}

const props = defineProps<{
    titulaciones: TitulacionItem[];
    estudiantes: Persona[];
    tutores: Persona[];
    filters: { estado?: string };
    stats: { en_proceso: number; defendido: number; graduado: number; total: number };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Titulación', href: route('titulacion.index') },
];

const filterEstado = ref(props.filters.estado || 'all');
const applyFilter = () => {
    router.get(route('titulacion.index'), { estado: filterEstado.value !== 'all' ? filterEstado.value : undefined }, { preserveState: true, replace: true });
};

// ── Crear ─────────────────────────────────────────────
const showCreate = ref(false);
const form = useForm({
    estudiante_id: '' as number | '',
    tutor_id: '' as number | '',
    tema: '',
    fecha_inicio: '',
});
const submitCreate = () => {
    form.post(route('titulacion.store'), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Proceso de titulación registrado.'); showCreate.value = false; form.reset(); },
        onError: () => toast.error('Revisa los campos del formulario.'),
    });
};

// ── Editar ────────────────────────────────────────────
const editing = ref<TitulacionItem | null>(null);
const editForm = useForm({ tema: '', tutor_id: '' as number | '', fecha_inicio: '' });
const openEdit = (t: TitulacionItem) => {
    editing.value = t;
    editForm.tema = t.tema;
    editForm.tutor_id = t.tutor.id;
    editForm.fecha_inicio = t.fecha_inicio ?? '';
};
const submitEdit = () => {
    if (!editing.value) return;
    editForm.put(route('titulacion.update', editing.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Proceso actualizado.'); editing.value = null; },
    });
};

// ── Cambiar estado ────────────────────────────────────
const changeEstado = (t: TitulacionItem, estado: string) => {
    router.put(route('titulacion.update', t.id), { estado }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Estado actualizado.'),
    });
};

// ── Eliminar ──────────────────────────────────────────
const deleteTarget = ref<TitulacionItem | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('titulacion.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Proceso eliminado.'); deleteTarget.value = null; },
    });
};

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        en_proceso: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        defendido: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400',
        graduado: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
    };
    return map[estado] ?? map.en_proceso;
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Titulación" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Titulación</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Temas, procesos en curso y estudiantes titulados.</p>
                </div>
                <Button @click="showCreate = true" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Plus class="h-4 w-4" /> Registrar Tema
                </Button>
            </div>

            <div class="grid grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl border border-amber-200/60 bg-amber-50/40 p-5 shadow-sm dark:border-amber-900/30 dark:bg-amber-950/10">
                    <p class="text-xs font-semibold text-amber-700 uppercase">En proceso</p>
                    <p class="mt-1 text-3xl font-extrabold text-amber-700 dark:text-amber-300">{{ stats.en_proceso }}</p>
                </div>
                <div class="rounded-2xl border border-indigo-200/60 bg-indigo-50/40 p-5 shadow-sm dark:border-indigo-900/30 dark:bg-indigo-950/10">
                    <p class="text-xs font-semibold text-indigo-700 uppercase">Defendidos</p>
                    <p class="mt-1 text-3xl font-extrabold text-indigo-700 dark:text-indigo-300">{{ stats.defendido }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200/60 bg-emerald-50/40 p-5 shadow-sm dark:border-emerald-900/30 dark:bg-emerald-950/10">
                    <p class="text-xs font-semibold text-emerald-700 uppercase">Titulados</p>
                    <p class="mt-1 text-3xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ stats.graduado }}</p>
                </div>
            </div>

            <div class="flex gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                <Select v-model="filterEstado" @update:model-value="applyFilter">
                    <SelectTrigger class="w-[200px]"><SelectValue placeholder="Estado" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos los estados</SelectItem>
                        <SelectItem value="en_proceso">En proceso</SelectItem>
                        <SelectItem value="defendido">Defendido</SelectItem>
                        <SelectItem value="graduado">Graduado</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Estudiante</th>
                            <th class="px-4 py-3 text-left">Tema</th>
                            <th class="px-4 py-3 text-left">Tutor</th>
                            <th class="px-4 py-3 text-left">Estado</th>
                            <th class="px-4 py-3 text-left">Inicio</th>
                            <th class="px-4 py-3 text-left">Graduación</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in titulaciones" :key="t.id" class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                                <GraduationCap class="h-4 w-4 text-indigo-500" /> {{ t.estudiante.name }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300 max-w-xs truncate">{{ t.tema }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ t.tutor.name }}</td>
                            <td class="px-4 py-3">
                                <Select :model-value="t.estado" @update:model-value="val => changeEstado(t, val as string)">
                                    <SelectTrigger :class="['h-7 w-[130px] text-xs border-none', estadoBadge(t.estado)]"><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="en_proceso">En proceso</SelectItem>
                                        <SelectItem value="defendido">Defendido</SelectItem>
                                        <SelectItem value="graduado">Graduado</SelectItem>
                                    </SelectContent>
                                </Select>
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ t.fecha_inicio ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ t.fecha_graduacion ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEdit(t)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900">
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button @click="deleteTarget = t" class="rounded-lg p-1.5 text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="titulaciones.length === 0">
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">No hay procesos de titulación registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sheet: Crear -->
        <Sheet :open="showCreate" @update:open="val => { if (!val) { showCreate = false; form.reset(); } }">
            <SheetContent class="sm:max-w-md overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>Registrar Tema de Titulación</SheetTitle>
                </SheetHeader>
                <form @submit.prevent="submitCreate" class="mt-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Estudiante *</label>
                        <Select v-model="form.estudiante_id">
                            <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un estudiante..." /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="e in estudiantes" :key="e.id" :value="e.id">{{ e.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.estudiante_id" class="text-xs text-rose-500 mt-1">{{ form.errors.estudiante_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tutor *</label>
                        <Select v-model="form.tutor_id">
                            <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un tutor..." /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="t in tutores" :key="t.id" :value="t.id">{{ t.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.tutor_id" class="text-xs text-rose-500 mt-1">{{ form.errors.tutor_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tema *</label>
                        <textarea v-model="form.tema" rows="3" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                        <p v-if="form.errors.tema" class="text-xs text-rose-500 mt-1">{{ form.errors.tema }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha de inicio</label>
                        <input v-model="form.fecha_inicio" type="date" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                    </div>
                    <SheetFooter class="pt-4 gap-2">
                        <Button type="button" variant="outline" @click="showCreate = false">Cancelar</Button>
                        <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            {{ form.processing ? 'Guardando...' : 'Registrar' }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>

        <!-- Sheet: Editar -->
        <Sheet :open="!!editing" @update:open="val => { if (!val) editing = null; }">
            <SheetContent class="sm:max-w-md overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>Editar Proceso de Titulación</SheetTitle>
                </SheetHeader>
                <form @submit.prevent="submitEdit" class="mt-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tutor *</label>
                        <Select v-model="editForm.tutor_id">
                            <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un tutor..." /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="t in tutores" :key="t.id" :value="t.id">{{ t.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="editForm.errors.tutor_id" class="text-xs text-rose-500 mt-1">{{ editForm.errors.tutor_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tema *</label>
                        <textarea v-model="editForm.tema" rows="3" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                        <p v-if="editForm.errors.tema" class="text-xs text-rose-500 mt-1">{{ editForm.errors.tema }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha de inicio</label>
                        <input v-model="editForm.fecha_inicio" type="date" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                    </div>
                    <SheetFooter class="pt-4 gap-2">
                        <Button type="button" variant="outline" @click="editing = null">Cancelar</Button>
                        <Button type="submit" :disabled="editForm.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            {{ editForm.processing ? 'Guardando...' : 'Guardar cambios' }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>

        <!-- Confirmar eliminación -->
        <AlertDialog :open="!!deleteTarget" @update:open="val => { if (!val) deleteTarget = null; }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Eliminar proceso de titulación?</AlertDialogTitle>
                    <AlertDialogDescription>Se eliminará el proceso de "{{ deleteTarget?.estudiante.name }}". Esta acción no se puede deshacer.</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDelete" class="bg-rose-600 hover:bg-rose-500">Eliminar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppSidebarLayout>
</template>
