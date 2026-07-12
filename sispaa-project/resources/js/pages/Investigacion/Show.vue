<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Flag, MessageCircleQuestion, ArrowLeft, Pencil } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetFooter } from '@/components/ui/sheet';
import { toast } from 'vue-sonner';

interface Hito {
    id: number;
    nombre: string;
    descripcion: string | null;
    fecha_planificada: string | null;
    fecha_cumplida: string | null;
    porcentaje_avance: number;
}
interface Seguimiento {
    id: number;
    pregunta: string;
    respuesta: string | null;
    orden: number;
}

const props = defineProps<{
    proyecto: { id: number; titulo: string; estado: string };
    hitos: Hito[];
    seguimiento: Seguimiento[];
    esDueno: boolean;
    esCoordinador: boolean;
    breadcrumbs?: BreadcrumbItemType[];
}>();

// ── Hitos: crear / editar (acciones inline, no ameritan página propia:
// son sub-recursos de detalle, no entidades navegables por sí mismas) ──
const showHitoForm = ref(false);
const editingHito = ref<Hito | null>(null);
const hitoForm = useForm({
    nombre: '',
    descripcion: '',
    fecha_planificada: '',
    porcentaje_avance: 0,
});

const openCreateHito = () => {
    editingHito.value = null;
    hitoForm.reset();
    hitoForm.clearErrors();
    showHitoForm.value = true;
};

const openEditHito = (h: Hito) => {
    editingHito.value = h;
    hitoForm.nombre = h.nombre;
    hitoForm.descripcion = h.descripcion ?? '';
    hitoForm.fecha_planificada = h.fecha_planificada ?? '';
    hitoForm.porcentaje_avance = h.porcentaje_avance;
    hitoForm.clearErrors();
    showHitoForm.value = true;
};

const submitHito = () => {
    if (editingHito.value) {
        hitoForm.put(route('investigacion.hitos.update', editingHito.value.id), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Hito actualizado.'); showHitoForm.value = false; },
        });
    } else {
        hitoForm.post(route('investigacion.hitos.store', props.proyecto.id), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Hito agregado.'); showHitoForm.value = false; hitoForm.reset(); },
        });
    }
};

// ── Seguimiento: coordinador pregunta ────────────────
const showPreguntaForm = ref(false);
const preguntaForm = useForm({ pregunta: '' });
const submitPregunta = () => {
    preguntaForm.post(route('investigacion.seguimiento.store', props.proyecto.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Pregunta agregada.');
            showPreguntaForm.value = false;
            preguntaForm.reset();
        },
    });
};

// ── Seguimiento: docente responde ────────────────────
const respondiendo = ref<Seguimiento | null>(null);
const respuestaForm = useForm({ respuesta: '' });
const openResponder = (s: Seguimiento) => {
    respondiendo.value = s;
    respuestaForm.respuesta = s.respuesta ?? '';
    respuestaForm.clearErrors();
};
const submitRespuesta = () => {
    if (!respondiendo.value) return;
    respuestaForm.patch(route('investigacion.seguimiento.responder', respondiendo.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Respuesta guardada.'); respondiendo.value = null; },
    });
};

const progresoColor = (pct: number) => {
    if (pct >= 100) return 'bg-emerald-500';
    if (pct >= 50) return 'bg-indigo-500';
    return 'bg-amber-500';
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="proyecto.titulo" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <Link :href="route('investigacion.index')" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-indigo-600 font-semibold mb-2">
                    <ArrowLeft class="h-3.5 w-3.5" /> Volver a proyectos
                </Link>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ proyecto.titulo }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Hitos y seguimiento del proyecto.</p>
            </div>

            <!-- HITOS -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <Flag class="h-4 w-4 text-indigo-500" /> Hitos
                    </h2>
                    <Button v-if="esDueno" size="sm" @click="openCreateHito" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                        <Plus class="h-3.5 w-3.5" /> Agregar hito
                    </Button>
                </div>

                <div class="space-y-3">
                    <div v-for="h in hitos" :key="h.id" class="rounded-xl border border-slate-100 dark:border-slate-800 p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ h.nombre }}</p>
                                <p v-if="h.descripcion" class="text-xs text-slate-500 mt-0.5">{{ h.descripcion }}</p>
                                <p class="text-xs text-slate-400 mt-1">
                                    <span v-if="h.fecha_planificada">Planificado: {{ h.fecha_planificada }}</span>
                                    <span v-if="h.fecha_cumplida"> · Cumplido: {{ h.fecha_cumplida }}</span>
                                </p>
                            </div>
                            <button v-if="esDueno" @click="openEditHito(h)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900">
                                <Pencil class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <div class="h-2 flex-1 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                <div :class="['h-full rounded-full', progresoColor(h.porcentaje_avance)]" :style="{ width: h.porcentaje_avance + '%' }"></div>
                            </div>
                            <span class="text-xs font-semibold text-slate-500 w-10 text-right">{{ h.porcentaje_avance }}%</span>
                        </div>
                    </div>

                    <div v-if="hitos.length === 0" class="rounded-xl border border-dashed border-slate-200 dark:border-slate-800 p-6 text-center text-sm text-slate-400">
                        Aún no hay hitos registrados.
                    </div>
                </div>
            </div>

            <!-- SEGUIMIENTO -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <MessageCircleQuestion class="h-4 w-4 text-indigo-500" /> Seguimiento del coordinador
                    </h2>
                    <Button v-if="esCoordinador" size="sm" @click="showPreguntaForm = true" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                        <Plus class="h-3.5 w-3.5" /> Nueva pregunta
                    </Button>
                </div>

                <div class="space-y-3">
                    <div v-for="s in seguimiento" :key="s.id" class="rounded-xl border border-slate-100 dark:border-slate-800 p-4">
                        <p class="text-xs font-semibold text-slate-500 uppercase mb-1">Pregunta #{{ s.orden }}</p>
                        <p class="text-sm text-slate-900 dark:text-white">{{ s.pregunta }}</p>

                        <div v-if="s.respuesta" class="mt-3 rounded-lg bg-indigo-50/60 dark:bg-indigo-950/20 p-3">
                            <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 mb-1">Respuesta del docente</p>
                            <p class="text-sm text-slate-700 dark:text-slate-300">{{ s.respuesta }}</p>
                        </div>
                        <div v-else class="mt-3 flex items-center justify-between rounded-lg bg-amber-50/60 dark:bg-amber-950/10 p-3">
                            <p class="text-xs font-semibold text-amber-700 dark:text-amber-400">Pendiente de respuesta</p>
                            <button v-if="esDueno" @click="openResponder(s)" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">
                                Responder
                            </button>
                        </div>
                    </div>

                    <div v-if="seguimiento.length === 0" class="rounded-xl border border-dashed border-slate-200 dark:border-slate-800 p-6 text-center text-sm text-slate-400">
                        El coordinador aún no ha registrado preguntas de seguimiento.
                    </div>
                </div>
            </div>
        </div>

        <!-- Sheet: Hito -->
        <Sheet :open="showHitoForm" @update:open="val => showHitoForm = val">
            <SheetContent class="sm:max-w-md overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>{{ editingHito ? 'Editar Hito' : 'Nuevo Hito' }}</SheetTitle>
                </SheetHeader>
                <form @submit.prevent="submitHito" class="mt-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre *</label>
                        <input v-model="hitoForm.nombre" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        <p v-if="hitoForm.errors.nombre" class="text-xs text-rose-500 mt-1">{{ hitoForm.errors.nombre }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Descripción</label>
                        <textarea v-model="hitoForm.descripcion" rows="3" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha planificada</label>
                        <input v-model="hitoForm.fecha_planificada" type="date" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Avance: {{ hitoForm.porcentaje_avance }}%
                        </label>
                        <input v-model.number="hitoForm.porcentaje_avance" type="range" min="0" max="100" step="5" class="w-full accent-indigo-600" />
                        <p v-if="hitoForm.errors.porcentaje_avance" class="text-xs text-rose-500 mt-1">{{ hitoForm.errors.porcentaje_avance }}</p>
                    </div>
                    <SheetFooter class="pt-4 gap-2">
                        <Button type="button" variant="outline" @click="showHitoForm = false">Cancelar</Button>
                        <Button type="submit" :disabled="hitoForm.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            {{ hitoForm.processing ? 'Guardando...' : 'Guardar' }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>

        <!-- Sheet: Nueva pregunta (coordinador) -->
        <Sheet :open="showPreguntaForm" @update:open="val => showPreguntaForm = val">
            <SheetContent class="sm:max-w-md overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>Nueva Pregunta de Seguimiento</SheetTitle>
                </SheetHeader>
                <form @submit.prevent="submitPregunta" class="mt-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pregunta *</label>
                        <textarea v-model="preguntaForm.pregunta" rows="4" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                        <p v-if="preguntaForm.errors.pregunta" class="text-xs text-rose-500 mt-1">{{ preguntaForm.errors.pregunta }}</p>
                    </div>
                    <SheetFooter class="pt-4 gap-2">
                        <Button type="button" variant="outline" @click="showPreguntaForm = false">Cancelar</Button>
                        <Button type="submit" :disabled="preguntaForm.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            {{ preguntaForm.processing ? 'Enviando...' : 'Enviar' }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>

        <!-- Sheet: Responder (docente) -->
        <Sheet :open="!!respondiendo" @update:open="val => { if (!val) respondiendo = null; }">
            <SheetContent class="sm:max-w-md overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>Responder Seguimiento</SheetTitle>
                </SheetHeader>
                <div class="mt-4 rounded-lg bg-slate-50 dark:bg-slate-900 p-3 text-sm text-slate-700 dark:text-slate-300">
                    {{ respondiendo?.pregunta }}
                </div>
                <form @submit.prevent="submitRespuesta" class="mt-5 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Respuesta *</label>
                        <textarea v-model="respuestaForm.respuesta" rows="5" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                        <p v-if="respuestaForm.errors.respuesta" class="text-xs text-rose-500 mt-1">{{ respuestaForm.errors.respuesta }}</p>
                    </div>
                    <SheetFooter class="pt-4 gap-2">
                        <Button type="button" variant="outline" @click="respondiendo = null">Cancelar</Button>
                        <Button type="submit" :disabled="respuestaForm.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            {{ respuestaForm.processing ? 'Guardando...' : 'Guardar respuesta' }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    </AppSidebarLayout>
</template>
