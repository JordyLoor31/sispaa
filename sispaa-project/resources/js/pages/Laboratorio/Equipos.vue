<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Microscope, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetFooter } from '@/components/ui/sheet';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogTitle, AlertDialogHeader } from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';

interface Catalogo { id: number; nombre: string }
interface EquipoItem {
    id: number;
    nombre: string;
    codigo: string;
    cantidad: number;
    estado: 'operativo' | 'mantenimiento' | 'dañado';
    laboratorio: string;
    laboratorio_id: number;
}

const props = defineProps<{
    equipos: EquipoItem[];
    laboratorios: Catalogo[];
    filters: { laboratorio_id?: string };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Laboratorio', href: route('laboratorio.index') },
    { title: 'Equipos', href: route('laboratorio.equipos') },
];

const filterLab = ref(props.filters.laboratorio_id || 'all');
const applyFilter = () => {
    router.get(route('laboratorio.equipos'), { laboratorio_id: filterLab.value !== 'all' ? filterLab.value : undefined }, { preserveState: true, replace: true });
};

const showForm = ref(false);
const editing = ref<EquipoItem | null>(null);
const form = useForm({ laboratorio_id: '' as number | '', nombre: '', codigo: '', cantidad: 1 });

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

const openEdit = (e: EquipoItem) => {
    editing.value = e;
    form.laboratorio_id = e.laboratorio_id;
    form.nombre = e.nombre;
    form.codigo = e.codigo;
    form.cantidad = e.cantidad;
    form.clearErrors();
    showForm.value = true;
};

const submit = () => {
    if (editing.value) {
        form.put(route('laboratorio.equipos.update', editing.value.id), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Equipo actualizado.'); showForm.value = false; },
        });
    } else {
        form.post(route('laboratorio.equipos.store'), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Equipo registrado.'); showForm.value = false; form.reset(); },
        });
    }
};

const changeEstado = (e: EquipoItem, estado: string) => {
    router.put(route('laboratorio.equipos.update', e.id), { estado }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Estado actualizado.'),
    });
};

const deleteTarget = ref<EquipoItem | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('laboratorio.equipos.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Equipo eliminado.'); deleteTarget.value = null; },
    });
};

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        operativo: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
        mantenimiento: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        'dañado': 'bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400',
    };
    return map[estado] ?? map.operativo;
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Equipos de Laboratorio" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Equipos</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Inventario de equipos de laboratorio.</p>
                </div>
                <Button @click="openCreate" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Plus class="h-4 w-4" /> Nuevo Equipo
                </Button>
            </div>

            <div class="flex gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                <Select v-model="filterLab" @update:model-value="applyFilter">
                    <SelectTrigger class="w-[220px]"><SelectValue placeholder="Laboratorio" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos los laboratorios</SelectItem>
                        <SelectItem v-for="l in laboratorios" :key="l.id" :value="String(l.id)">{{ l.nombre }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Equipo</th>
                            <th class="px-4 py-3 text-left">Código</th>
                            <th class="px-4 py-3 text-left">Laboratorio</th>
                            <th class="px-4 py-3 text-left">Cantidad</th>
                            <th class="px-4 py-3 text-left">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="e in equipos" :key="e.id" class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                                <Microscope class="h-4 w-4 text-indigo-500" /> {{ e.nombre }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ e.codigo }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ e.laboratorio }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ e.cantidad }}</td>
                            <td class="px-4 py-3">
                                <Select :model-value="e.estado" @update:model-value="val => changeEstado(e, val as string)">
                                    <SelectTrigger :class="['h-7 w-[140px] text-xs border-none', estadoBadge(e.estado)]"><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="operativo">Operativo</SelectItem>
                                        <SelectItem value="mantenimiento">Mantenimiento</SelectItem>
                                        <SelectItem value="dañado">Dañado</SelectItem>
                                    </SelectContent>
                                </Select>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEdit(e)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900">
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button @click="deleteTarget = e" class="rounded-lg p-1.5 text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="equipos.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">No hay equipos registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sheet: Crear/Editar -->
        <Sheet :open="showForm" @update:open="val => { if (!val) { showForm = false; form.reset(); } }">
            <SheetContent class="sm:max-w-md overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>{{ editing ? 'Editar Equipo' : 'Nuevo Equipo' }}</SheetTitle>
                </SheetHeader>
                <form @submit.prevent="submit" class="mt-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Laboratorio *</label>
                        <Select v-model="form.laboratorio_id">
                            <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona..." /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="l in laboratorios" :key="l.id" :value="l.id">{{ l.nombre }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.laboratorio_id" class="text-xs text-rose-500 mt-1">{{ form.errors.laboratorio_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre *</label>
                        <input v-model="form.nombre" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        <p v-if="form.errors.nombre" class="text-xs text-rose-500 mt-1">{{ form.errors.nombre }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Código *</label>
                        <input v-model="form.codigo" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        <p v-if="form.errors.codigo" class="text-xs text-rose-500 mt-1">{{ form.errors.codigo }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Cantidad *</label>
                        <input v-model.number="form.cantidad" type="number" min="1" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        <p v-if="form.errors.cantidad" class="text-xs text-rose-500 mt-1">{{ form.errors.cantidad }}</p>
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
                    <AlertDialogTitle>¿Eliminar equipo?</AlertDialogTitle>
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
