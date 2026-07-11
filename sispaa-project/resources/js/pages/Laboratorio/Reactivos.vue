<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Beaker, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetFooter } from '@/components/ui/sheet';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogTitle, AlertDialogHeader } from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';

interface Catalogo { id: number; nombre: string }
interface ReactivoItem {
    id: number;
    nombre: string;
    formula: string | null;
    cantidad: number;
    unidad: string | null;
    estado: 'disponible' | 'agotado' | 'vencido';
    laboratorio: string;
    laboratorio_id: number;
}

const props = defineProps<{
    reactivos: ReactivoItem[];
    laboratorios: Catalogo[];
    filters: { laboratorio_id?: string };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Laboratorio', href: route('laboratorio.index') },
    { title: 'Reactivos', href: route('laboratorio.reactivos') },
];

const filterLab = ref(props.filters.laboratorio_id || 'all');
const applyFilter = () => {
    router.get(route('laboratorio.reactivos'), { laboratorio_id: filterLab.value !== 'all' ? filterLab.value : undefined }, { preserveState: true, replace: true });
};

const showForm = ref(false);
const editing = ref<ReactivoItem | null>(null);
const form = useForm({ laboratorio_id: '' as number | '', nombre: '', formula: '', cantidad: 0, unidad: '' });

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

const openEdit = (r: ReactivoItem) => {
    editing.value = r;
    form.laboratorio_id = r.laboratorio_id;
    form.nombre = r.nombre;
    form.formula = r.formula ?? '';
    form.cantidad = r.cantidad;
    form.unidad = r.unidad ?? '';
    form.clearErrors();
    showForm.value = true;
};

const submit = () => {
    if (editing.value) {
        form.put(route('laboratorio.reactivos.update', editing.value.id), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Reactivo actualizado.'); showForm.value = false; },
        });
    } else {
        form.post(route('laboratorio.reactivos.store'), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Reactivo registrado.'); showForm.value = false; form.reset(); },
        });
    }
};

const changeEstado = (r: ReactivoItem, estado: string) => {
    router.put(route('laboratorio.reactivos.update', r.id), { estado }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Estado actualizado.'),
    });
};

const deleteTarget = ref<ReactivoItem | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('laboratorio.reactivos.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Reactivo eliminado.'); deleteTarget.value = null; },
    });
};

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        disponible: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
        agotado: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        vencido: 'bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400',
    };
    return map[estado] ?? map.disponible;
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Reactivos de Laboratorio" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Reactivos</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Inventario de reactivos químicos.</p>
                </div>
                <Button @click="openCreate" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Plus class="h-4 w-4" /> Nuevo Reactivo
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
                            <th class="px-4 py-3 text-left">Reactivo</th>
                            <th class="px-4 py-3 text-left">Fórmula</th>
                            <th class="px-4 py-3 text-left">Laboratorio</th>
                            <th class="px-4 py-3 text-left">Cantidad</th>
                            <th class="px-4 py-3 text-left">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in reactivos" :key="r.id" class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                                <Beaker class="h-4 w-4 text-indigo-500" /> {{ r.nombre }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ r.formula ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ r.laboratorio }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ r.cantidad }} {{ r.unidad ?? '' }}</td>
                            <td class="px-4 py-3">
                                <Select :model-value="r.estado" @update:model-value="val => changeEstado(r, val as string)">
                                    <SelectTrigger :class="['h-7 w-[130px] text-xs border-none', estadoBadge(r.estado)]"><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="disponible">Disponible</SelectItem>
                                        <SelectItem value="agotado">Agotado</SelectItem>
                                        <SelectItem value="vencido">Vencido</SelectItem>
                                    </SelectContent>
                                </Select>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEdit(r)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900">
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button @click="deleteTarget = r" class="rounded-lg p-1.5 text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="reactivos.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">No hay reactivos registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sheet: Crear/Editar -->
        <Sheet :open="showForm" @update:open="val => { if (!val) { showForm = false; form.reset(); } }">
            <SheetContent class="sm:max-w-md overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>{{ editing ? 'Editar Reactivo' : 'Nuevo Reactivo' }}</SheetTitle>
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
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fórmula</label>
                        <input v-model="form.formula" type="text" placeholder="Ej: NaCl" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Cantidad *</label>
                            <input v-model.number="form.cantidad" type="number" min="0" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                            <p v-if="form.errors.cantidad" class="text-xs text-rose-500 mt-1">{{ form.errors.cantidad }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Unidad</label>
                            <input v-model="form.unidad" type="text" placeholder="Ej: ml, g" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        </div>
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
                    <AlertDialogTitle>¿Eliminar reactivo?</AlertDialogTitle>
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
