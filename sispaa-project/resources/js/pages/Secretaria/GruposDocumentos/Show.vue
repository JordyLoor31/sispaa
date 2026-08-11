<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, FolderOpen, Plus, Pencil, Check, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import type { GrupoDocumento, Requisito } from './types';
import { FORMATOS_DOCUMENTO, etiquetasFormatos } from '@/lib/formatos-documento';

const props = defineProps<{
    grupo: GrupoDocumento;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formatDate = (date?: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

// Edición del grupo en sí (nombre + descripción)
const editandoGrupo = ref(false);
const editarGrupoForm = useForm({ nombre: '', descripcion: '' });

const startEditGrupo = () => {
    editarGrupoForm.nombre = props.grupo.nombre;
    editarGrupoForm.descripcion = props.grupo.descripcion ?? '';
    editarGrupoForm.clearErrors();
    editandoGrupo.value = true;
};

const cancelEditGrupo = () => {
    editandoGrupo.value = false;
    editarGrupoForm.reset();
};

const submitEditarGrupo = () => {
    editarGrupoForm.put(route('secretaria.grupos-documentos.update', props.grupo.id), {
        preserveScroll: true,
        onSuccess: () => {
            editandoGrupo.value = false;
        },
    });
};

const nuevoRequisitoForm = useForm({ nombre: '', formatos: [] as string[] });

const toggleFormato = (ext: string) => {
    const idx = nuevoRequisitoForm.formatos.indexOf(ext);
    if (idx >= 0) {
        nuevoRequisitoForm.formatos.splice(idx, 1);
    } else {
        nuevoRequisitoForm.formatos.push(ext);
    }
};

const submitNuevoRequisito = () => {
    nuevoRequisitoForm.post(route('secretaria.grupos-documentos.requisitos.store', props.grupo.id), {
        preserveScroll: true,
        onSuccess: () => {
            nuevoRequisitoForm.reset();
        },
    });
};

// Edición de un requisito existente (nombre + formatos permitidos)
const editandoId = ref<number | null>(null);
const editarRequisitoForm = useForm({ nombre: '', formatos: [] as string[] });

const toggleFormatoEditar = (ext: string) => {
    const idx = editarRequisitoForm.formatos.indexOf(ext);
    if (idx >= 0) {
        editarRequisitoForm.formatos.splice(idx, 1);
    } else {
        editarRequisitoForm.formatos.push(ext);
    }
};

const startEdit = (requisito: Requisito) => {
    editandoId.value = requisito.id;
    editarRequisitoForm.nombre = requisito.nombre;
    editarRequisitoForm.formatos = [...(requisito.formatos_permitidos ?? [])];
    editarRequisitoForm.clearErrors();
};

const cancelEdit = () => {
    editandoId.value = null;
    editarRequisitoForm.reset();
};

const submitEditarRequisito = () => {
    if (editandoId.value === null) return;
    editarRequisitoForm.put(route('secretaria.grupos-documentos.requisitos.update', editandoId.value), {
        preserveScroll: true,
        onSuccess: () => {
            editandoId.value = null;
        },
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.grupo.nombre" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                        <FolderOpen class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">{{ grupo.nombre }}</h1>
                        <p v-if="grupo.descripcion" class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">{{ grupo.descripcion }}</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <Button v-if="!editandoGrupo" variant="outline" @click="startEditGrupo">
                        <Pencil class="h-4 w-4 mr-1.5" /> Editar
                    </Button>
                    <Button as-child variant="outline">
                        <Link :href="route('secretaria.grupos-documentos.index')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                </div>
            </div>

            <div v-if="editandoGrupo" class="max-w-5xl mx-auto w-full rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-4">Editar grupo de documentos</h4>
                <form @submit.prevent="submitEditarGrupo" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-[var(--sispaa-text)] mb-1.5">Nombre *</label>
                        <input v-model="editarGrupoForm.nombre" type="text" class="w-full rounded-lg border-0 bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)] focus:ring-2 focus:ring-[var(--sispaa-primary)]" />
                        <p v-if="editarGrupoForm.errors.nombre" class="text-xs text-rose-500 mt-1">{{ editarGrupoForm.errors.nombre }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-[var(--sispaa-text)] mb-1.5">Descripción</label>
                        <textarea v-model="editarGrupoForm.descripcion" rows="2" class="w-full rounded-lg border-0 bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)] focus:ring-2 focus:ring-[var(--sispaa-primary)]"></textarea>
                        <p v-if="editarGrupoForm.errors.descripcion" class="text-xs text-rose-500 mt-1">{{ editarGrupoForm.errors.descripcion }}</p>
                    </div>
                    <div class="flex items-center gap-2 pt-1">
                        <Button type="submit" :disabled="editarGrupoForm.processing" class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                            <Check class="h-4 w-4 mr-1" /> Guardar cambios
                        </Button>
                        <Button type="button" variant="outline" :disabled="editarGrupoForm.processing" @click="cancelEditGrupo">
                            Cancelar
                        </Button>
                    </div>
                </form>
            </div>

            <div class="max-w-5xl mx-auto w-full space-y-4 sm:space-y-6">
            <div class="w-full grid gap-4 sm:gap-6 sm:grid-cols-2">
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Estado</h4>
                    <p class="mt-2 text-sm font-semibold" :class="grupo.activo ? 'text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]' : 'opacity-50 text-[var(--sispaa-text)]'">
                        {{ grupo.activo ? 'Activo' : 'Inactivo' }}
                    </p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Creado por</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">
                        {{ grupo.creator?.name ?? grupo.creadoPor?.name ?? '—' }}
                    </p>
                    <p class="text-xs opacity-50 text-[var(--sispaa-text)] mt-0.5">{{ formatDate(grupo.created_at) }}</p>
                </div>
            </div>

            <div class="w-full rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-3">Requisitos</h4>
                <ul class="space-y-2">
                    <li v-for="r in grupo.requisitos" :key="r.id" class="text-sm text-[var(--sispaa-text)]">
                        <div v-if="editandoId === r.id" class="rounded-xl border p-3 space-y-2 border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]">
                            <div class="flex items-center gap-2">
                                <input v-model="editarRequisitoForm.nombre" type="text" class="flex-1 rounded-lg border-0 bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)] focus:ring-2 focus:ring-[var(--sispaa-primary)]" />
                                <button type="button" @click="cancelEdit" class="shrink-0 rounded-lg p-2 opacity-50 text-[var(--sispaa-text)] hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" title="Cancelar">
                                    <X class="h-4 w-4" />
                                </button>
                                <Button type="button" size="sm" :disabled="editarRequisitoForm.processing" class="shrink-0 font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]" @click="submitEditarRequisito">
                                    <Check class="h-4 w-4 mr-1" /> Guardar
                                </Button>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 pl-1">
                                <span class="text-xs font-semibold opacity-60 text-[var(--sispaa-text)]">Formatos permitidos:</span>
                                <label
                                    v-for="f in FORMATOS_DOCUMENTO"
                                    :key="f.ext"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold transition-colors"
                                    :class="editarRequisitoForm.formatos.includes(f.ext)
                                        ? 'border-[var(--sispaa-primary)] text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)]'
                                        : 'opacity-50 text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] hover:opacity-100'"
                                >
                                    <input type="checkbox" class="hidden" :checked="editarRequisitoForm.formatos.includes(f.ext)" @change="toggleFormatoEditar(f.ext)" />
                                    {{ f.label }}
                                </label>
                                <span v-if="editarRequisitoForm.formatos.length === 0" class="text-xs opacity-50 text-[var(--sispaa-text)]">(todos: PDF, JPG, PNG, JPEG)</span>
                            </div>
                            <p v-if="editarRequisitoForm.errors.nombre" class="text-xs text-rose-500 mt-1">{{ editarRequisitoForm.errors.nombre }}</p>
                        </div>
                        <div v-else class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-[var(--sispaa-primary)]"></span>
                            {{ r.nombre }}
                            <span class="text-[10px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded-full bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] text-[var(--sispaa-primary)]">
                                {{ etiquetasFormatos(r.formatos_permitidos) }}
                            </span>
                            <span v-if="!r.activo" class="text-xs opacity-50 text-[var(--sispaa-text)]">(inactivo)</span>
                            <button type="button" @click="startEdit(r)" class="ml-1 rounded-lg p-1.5 opacity-40 text-[var(--sispaa-text)] hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" title="Editar requisito">
                                <Pencil class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </li>
                    <li v-if="grupo.requisitos.length === 0" class="text-sm opacity-50 text-[var(--sispaa-text)]">Sin requisitos aún.</li>
                </ul>

                <form @submit.prevent="submitNuevoRequisito" class="mt-5 pt-4 border-t border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-[var(--sispaa-text)] mb-1.5">Nuevo requisito</label>
                        <input v-model="nuevoRequisitoForm.nombre" type="text" placeholder="Ej: Certificado médico" class="w-full rounded-lg border-0 bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)] focus:ring-2 focus:ring-[var(--sispaa-primary)]" />
                        <p v-if="nuevoRequisitoForm.errors.nombre" class="text-xs text-rose-500 mt-1">{{ nuevoRequisitoForm.errors.nombre }}</p>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-[var(--sispaa-text)] mb-1.5">Formatos permitidos</label>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <label
                                v-for="f in FORMATOS_DOCUMENTO"
                                :key="f.ext"
                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold transition-colors"
                                :class="nuevoRequisitoForm.formatos.includes(f.ext)
                                    ? 'border-[var(--sispaa-primary)] text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)]'
                                    : 'opacity-50 text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] hover:opacity-100'"
                            >
                                <input type="checkbox" class="hidden" :checked="nuevoRequisitoForm.formatos.includes(f.ext)" @change="toggleFormato(f.ext)" />
                                {{ f.label }}
                            </label>
                            <span v-if="nuevoRequisitoForm.formatos.length === 0" class="text-xs opacity-50 text-[var(--sispaa-text)]">(todos: PDF, JPG, PNG, JPEG)</span>
                        </div>
                    </div>
                    <Button type="submit" :disabled="nuevoRequisitoForm.processing" class="font-semibold text-white shrink-0 bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Plus class="h-4 w-4 mr-1" /> Agregar
                    </Button>
                </form>
            </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
