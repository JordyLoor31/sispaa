<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, FolderOpen, X, Power } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription, SheetFooter } from '@/components/ui/sheet';
import { toast } from 'vue-sonner';

interface Requisito {
    id: number;
    nombre: string;
    orden: number;
    activo: boolean;
}

interface GrupoItem {
    id: number;
    nombre: string;
    descripcion: string | null;
    activo: boolean;
    requisitos: Requisito[];
    creado_por?: { name: string } | null;
}

const props = defineProps<{
    grupos: GrupoItem[];
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Secretaría', href: '#' },
    { title: 'Grupos de Documentos', href: route('secretaria.grupos-documentos.index') },
];

// ── Crear grupo ──────────────────────────────────────────
const showSheet = ref(false);
const requisitosDraft = ref<string[]>(['']);

const form = useForm({
    nombre: '',
    descripcion: '',
    requisitos: [] as string[],
});

const openCreate = () => {
    form.reset();
    requisitosDraft.value = [''];
    form.clearErrors();
    showSheet.value = true;
};

const addRequisitoField = () => requisitosDraft.value.push('');
const removeRequisitoField = (index: number) => requisitosDraft.value.splice(index, 1);

const submit = () => {
    form.requisitos = requisitosDraft.value.map((r) => r.trim()).filter((r) => r.length > 0);

    if (form.requisitos.length === 0) {
        toast.error('Agrega al menos un requisito.');
        return;
    }

    form.post(route('secretaria.grupos-documentos.store'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Grupo creado y estudiantes notificados.');
            showSheet.value = false;
            form.reset();
        },
        onError: () => toast.error('Revisa los campos del formulario.'),
    });
};

// ── Agregar requisito a un grupo existente ────────────────
const addRequisitoTarget = ref<GrupoItem | null>(null);
const nuevoRequisitoForm = useForm({ nombre: '' });

const submitNuevoRequisito = () => {
    if (!addRequisitoTarget.value) return;
    nuevoRequisitoForm.post(route('secretaria.grupos-documentos.requisitos.store', addRequisitoTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Requisito agregado.');
            addRequisitoTarget.value = null;
            nuevoRequisitoForm.reset();
        },
    });
};

// ── Activar/Desactivar ────────────────────────────────────
const toggleGrupo = (grupo: GrupoItem) => {
    router.post(route('secretaria.grupos-documentos.toggle', grupo.id), {}, {
        preserveScroll: true,
        onSuccess: () => toast.success(grupo.activo ? 'Grupo desactivado.' : 'Grupo activado.'),
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Grupos de Documentos" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Grupos de Documentos</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Define catálogos de requisitos documentales. Al crear un grupo, se notifica a todos los estudiantes.
                    </p>
                </div>
                <Button @click="openCreate" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Plus class="h-4 w-4" />
                    Nuevo Grupo
                </Button>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div v-for="g in grupos" :key="g.id"
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                                <FolderOpen class="h-4.5 w-4.5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ g.nombre }}</h3>
                                <p v-if="g.descripcion" class="text-xs text-slate-400">{{ g.descripcion }}</p>
                            </div>
                        </div>
                        <button @click="toggleGrupo(g)"
                            :class="['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold transition-colors', g.activo
                                ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400 hover:bg-emerald-100'
                                : 'bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-500 hover:bg-slate-200']">
                            <Power class="h-3.5 w-3.5" />
                            {{ g.activo ? 'Activo' : 'Inactivo' }}
                        </button>
                    </div>

                    <ul class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <li v-for="r in g.requisitos" :key="r.id" class="text-xs text-slate-600 dark:text-slate-300 flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>
                            {{ r.nombre }}
                            <span v-if="!r.activo" class="text-slate-400">(inactivo)</span>
                        </li>
                        <li v-if="g.requisitos.length === 0" class="text-xs text-slate-400">Sin requisitos aún.</li>
                    </ul>

                    <button @click="addRequisitoTarget = g" class="mt-1 inline-flex items-center gap-1 self-start text-xs text-indigo-600 hover:text-indigo-500 font-semibold">
                        <Plus class="h-3.5 w-3.5" /> Agregar requisito
                    </button>
                </div>

                <div v-if="grupos.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                    No hay grupos de documentos creados.
                </div>
            </div>
        </div>

        <!-- Sheet: Crear grupo -->
        <Sheet :open="showSheet" @update:open="val => { if (!val) { showSheet = false; form.reset(); } }">
            <SheetContent class="sm:max-w-md overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>Nuevo Grupo de Documentos</SheetTitle>
                    <SheetDescription>Todos los estudiantes activos serán notificados al crearlo.</SheetDescription>
                </SheetHeader>

                <form @submit.prevent="submit" class="mt-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre *</label>
                        <input v-model="form.nombre" type="text" placeholder="Ej: Expediente SGA" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        <p v-if="form.errors.nombre" class="text-xs text-rose-500 mt-1">{{ form.errors.nombre }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Descripción</label>
                        <textarea v-model="form.descripcion" rows="2" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Requisitos *</label>
                        <div class="space-y-2">
                            <div v-for="(_, index) in requisitosDraft" :key="index" class="flex items-center gap-2">
                                <input v-model="requisitosDraft[index]" type="text" placeholder="Ej: Cédula de identidad"
                                    class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                                <button type="button" @click="removeRequisitoField(index)" v-if="requisitosDraft.length > 1"
                                    class="shrink-0 rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900">
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <button type="button" @click="addRequisitoField" class="mt-2 inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-500 font-semibold">
                            <Plus class="h-3.5 w-3.5" /> Agregar otro requisito
                        </button>
                    </div>

                    <SheetFooter class="pt-4 gap-2">
                        <Button type="button" variant="outline" @click="showSheet = false">Cancelar</Button>
                        <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            {{ form.processing ? 'Creando...' : 'Crear grupo' }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>

        <!-- Sheet: Agregar requisito a grupo existente -->
        <Sheet :open="!!addRequisitoTarget" @update:open="val => { if (!val) { addRequisitoTarget = null; nuevoRequisitoForm.reset(); } }">
            <SheetContent class="sm:max-w-sm">
                <SheetHeader>
                    <SheetTitle>Agregar requisito</SheetTitle>
                    <SheetDescription v-if="addRequisitoTarget">Grupo: {{ addRequisitoTarget.nombre }}</SheetDescription>
                </SheetHeader>
                <form @submit.prevent="submitNuevoRequisito" class="mt-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre del requisito *</label>
                        <input v-model="nuevoRequisitoForm.nombre" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        <p v-if="nuevoRequisitoForm.errors.nombre" class="text-xs text-rose-500 mt-1">{{ nuevoRequisitoForm.errors.nombre }}</p>
                    </div>
                    <SheetFooter class="pt-2 gap-2">
                        <Button type="button" variant="outline" @click="addRequisitoTarget = null">Cancelar</Button>
                        <Button type="submit" :disabled="nuevoRequisitoForm.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            {{ nuevoRequisitoForm.processing ? 'Guardando...' : 'Agregar' }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    </AppSidebarLayout>
</template>
