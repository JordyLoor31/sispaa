<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Building2, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetFooter } from '@/components/ui/sheet';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';

interface EmpresaItem {
    id: number;
    nombre: string;
    ruc: string | null;
    sector: string | null;
    contacto: string | null;
    actividades_count: number;
}

const props = defineProps<{ empresas: EmpresaItem[] }>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Vinculación', href: route('vinculacion.actividades') },
    { title: 'Empresas Beneficiadas', href: route('vinculacion.empresas') },
];

const showForm = ref(false);
const editing = ref<EmpresaItem | null>(null);
const form = useForm({ nombre: '', ruc: '', sector: '', contacto: '' });

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

const openEdit = (e: EmpresaItem) => {
    editing.value = e;
    form.nombre = e.nombre;
    form.ruc = e.ruc ?? '';
    form.sector = e.sector ?? '';
    form.contacto = e.contacto ?? '';
    form.clearErrors();
    showForm.value = true;
};

const submit = () => {
    if (editing.value) {
        form.put(route('vinculacion.empresas.update', editing.value.id), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Empresa actualizada.'); showForm.value = false; },
        });
    } else {
        form.post(route('vinculacion.empresas.store'), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Empresa registrada.'); showForm.value = false; form.reset(); },
        });
    }
};

const deleteTarget = ref<EmpresaItem | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('vinculacion.empresas.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Empresa eliminada.'); deleteTarget.value = null; },
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Empresas Beneficiadas" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Empresas Beneficiadas</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Catálogo de empresas vinculadas a las actividades.</p>
                </div>
                <Button @click="openCreate" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Plus class="h-4 w-4" /> Nueva Empresa
                </Button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="e in empresas" :key="e.id"
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                            <Building2 class="h-4.5 w-4.5" />
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                            {{ e.actividades_count }} actividad(es)
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ e.nombre }}</h3>
                        <p v-if="e.ruc" class="text-xs text-slate-500 mt-0.5">RUC: {{ e.ruc }}</p>
                        <p v-if="e.sector" class="text-xs text-slate-400 mt-1">{{ e.sector }}</p>
                        <p v-if="e.contacto" class="text-xs text-slate-400">{{ e.contacto }}</p>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button @click="openEdit(e)" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-indigo-600 font-semibold">
                            <Pencil class="h-3.5 w-3.5" /> Editar
                        </button>
                        <button @click="deleteTarget = e" class="ml-auto inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-600 font-semibold">
                            <Trash2 class="h-3.5 w-3.5" /> Eliminar
                        </button>
                    </div>
                </div>

                <div v-if="empresas.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                    No hay empresas registradas.
                </div>
            </div>
        </div>

        <!-- Sheet: Crear/Editar -->
        <Sheet :open="showForm" @update:open="val => { if (!val) { showForm = false; form.reset(); } }">
            <SheetContent class="sm:max-w-md overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>{{ editing ? 'Editar Empresa' : 'Nueva Empresa' }}</SheetTitle>
                </SheetHeader>
                <form @submit.prevent="submit" class="mt-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre *</label>
                        <input v-model="form.nombre" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        <p v-if="form.errors.nombre" class="text-xs text-rose-500 mt-1">{{ form.errors.nombre }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">RUC</label>
                        <input v-model="form.ruc" type="text" maxlength="13" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        <p v-if="form.errors.ruc" class="text-xs text-rose-500 mt-1">{{ form.errors.ruc }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Sector</label>
                        <input v-model="form.sector" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Contacto</label>
                        <input v-model="form.contacto" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
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
                    <AlertDialogTitle>¿Eliminar empresa?</AlertDialogTitle>
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
