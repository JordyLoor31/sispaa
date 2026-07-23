<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Progress } from '@/components/ui/progress';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Flag, MessageCircleQuestion, ArrowLeft, Pencil, FileText, Upload, Download, Users } from 'lucide-vue-next';
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
interface InformeTrimestral {
    id: number;
    nombre_original: string | null;
    observaciones: string | null;
    fecha_subida: string | null;
    ver_url: string;
}
interface Persona { id: number; name: string }

const props = defineProps<{
    proyecto: { id: number; titulo: string; estado: string; lider: Persona; colider: Persona | null; miembros: Persona[] };
    hitos: Hito[];
    seguimiento: Seguimiento[];
    informesTrimestrales: InformeTrimestral[];
    puedeGestionar: boolean;
    esDueno: boolean;
    esLider: boolean;
    esColider: boolean;
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

// ── Seguimiento: líder/colíder pregunta ────────────────
const puedePreguntar = props.esLider || props.esColider;
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

// ── Informe trimestral: solo el líder puede subirlo ──
const showInformeForm = ref(false);
const informeForm = useForm<{ archivo: File | null; observaciones: string }>({
    archivo: null,
    observaciones: '',
});
const onArchivoChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    informeForm.archivo = file;
};
const submitInforme = () => {
    informeForm.post(route('investigacion.informes.store', props.proyecto.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            toast.success('Informe trimestral subido.');
            showInformeForm.value = false;
            informeForm.reset();
        },
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="proyecto.titulo" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <Link :href="route('investigacion.index')" class="mb-2 inline-flex items-center gap-1 text-xs font-semibold opacity-70 text-[var(--sispaa-text)] hover:opacity-100 hover:text-[var(--sispaa-primary)]">
                    <ArrowLeft class="h-3.5 w-3.5" /> Volver a proyectos
                </Link>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-3xl">{{ proyecto.titulo }}</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Equipo, hitos, seguimiento e informes del proyecto.</p>
            </div>

            <!-- EQUIPO -->
            <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-bold text-[var(--sispaa-text)]">
                    <Users class="h-4 w-4 text-[var(--sispaa-primary)]" /> Equipo del proyecto
                </h2>
                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full px-3 py-1 font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)] text-[var(--sispaa-primary)]">
                        Líder: {{ proyecto.lider.name }}
                    </span>
                    <span v-if="proyecto.colider" class="rounded-full px-3 py-1 font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-accent)_15%,transparent)] text-[var(--sispaa-accent)]">
                        Colíder: {{ proyecto.colider.name }}
                    </span>
                    <span v-for="m in proyecto.miembros" :key="m.id" class="rounded-full px-3 py-1 font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-surface)_60%,transparent)] text-[var(--sispaa-text)]">
                        {{ m.name }}
                    </span>
                </div>
            </div>

            <!-- HITOS -->
            <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-[var(--sispaa-text)]">
                        <Flag class="h-4 w-4 text-[var(--sispaa-primary)]" /> Hitos
                    </h2>
                    <Button v-if="puedeGestionar" size="sm" @click="openCreateHito" class="inline-flex items-center gap-1.5 font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Plus class="h-3.5 w-3.5" /> Agregar hito
                    </Button>
                </div>

                <div class="space-y-3">
                    <div v-for="h in hitos" :key="h.id" class="rounded-xl p-4 bg-[var(--sispaa-background)]">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ h.nombre }}</p>
                                <p v-if="h.descripcion" class="mt-0.5 text-xs opacity-70 text-[var(--sispaa-text)]">{{ h.descripcion }}</p>
                                <p class="mt-1 text-xs opacity-60 text-[var(--sispaa-text)]">
                                    <span v-if="h.fecha_planificada">Planificado: {{ h.fecha_planificada }}</span>
                                    <span v-if="h.fecha_cumplida"> · Cumplido: {{ h.fecha_cumplida }}</span>
                                </p>
                            </div>
                            <button v-if="puedeGestionar" @click="openEditHito(h)" class="shrink-0 rounded-lg p-1.5 opacity-60 text-[var(--sispaa-text)] hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_60%,transparent)]">
                                <Pencil class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <Progress :model-value="h.porcentaje_avance" class="h-2 flex-1" />
                            <span class="w-10 text-right text-xs font-semibold opacity-60 text-[var(--sispaa-text)]">{{ h.porcentaje_avance }}%</span>
                        </div>
                    </div>

                    <div v-if="hitos.length === 0" class="rounded-xl border border-dashed p-6 text-center text-sm opacity-50 border-[color:color-mix(in_srgb,var(--sispaa-text)_25%,transparent)] text-[var(--sispaa-text)]">
                        Aún no hay hitos registrados.
                    </div>
                </div>
            </div>

            <!-- SEGUIMIENTO -->
            <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-[var(--sispaa-text)]">
                        <MessageCircleQuestion class="h-4 w-4 text-[var(--sispaa-primary)]" /> Seguimiento del líder/colíder
                    </h2>
                    <Button v-if="puedePreguntar" size="sm" @click="showPreguntaForm = true" class="inline-flex items-center gap-1.5 font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Plus class="h-3.5 w-3.5" /> Nueva pregunta
                    </Button>
                </div>

                <div class="space-y-3">
                    <div v-for="s in seguimiento" :key="s.id" class="rounded-xl p-4 bg-[var(--sispaa-background)]">
                        <p class="mb-1 text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Pregunta #{{ s.orden }}</p>
                        <p class="text-sm text-[var(--sispaa-text)]">{{ s.pregunta }}</p>

                        <div v-if="s.respuesta" class="mt-3 rounded-lg p-3 bg-[color:color-mix(in_srgb,var(--sispaa-accent)_12%,transparent)]">
                            <p class="mb-1 text-xs font-semibold text-[var(--sispaa-accent)]">Respuesta del docente</p>
                            <p class="text-sm text-[var(--sispaa-text)] opacity-80">{{ s.respuesta }}</p>
                        </div>
                        <div v-else class="mt-3 flex flex-col gap-2 rounded-lg p-3 sm:flex-row sm:items-center sm:justify-between bg-[color:color-mix(in_srgb,#E4BC57_25%,transparent)]">
                            <p class="text-xs font-semibold text-[color:color-mix(in_srgb,#E4BC57_55%,var(--sispaa-text))]">Pendiente de respuesta</p>
                            <button v-if="esDueno" @click="openResponder(s)" class="text-xs font-semibold text-[var(--sispaa-primary)] hover:opacity-80">
                                Responder
                            </button>
                        </div>
                    </div>

                    <div v-if="seguimiento.length === 0" class="rounded-xl border border-dashed p-6 text-center text-sm opacity-50 border-[color:color-mix(in_srgb,var(--sispaa-text)_25%,transparent)] text-[var(--sispaa-text)]">
                        Aún no hay preguntas de seguimiento registradas.
                    </div>
                </div>
            </div>

            <!-- INFORMES TRIMESTRALES -->
            <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-[var(--sispaa-text)]">
                        <FileText class="h-4 w-4 text-[var(--sispaa-primary)]" /> Informes trimestrales
                    </h2>
                    <Button v-if="esLider" size="sm" @click="showInformeForm = true" class="inline-flex items-center gap-1.5 font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Upload class="h-3.5 w-3.5" /> Subir informe
                    </Button>
                </div>
                <p v-if="!esLider" class="mb-3 text-xs opacity-60 text-[var(--sispaa-text)]">Solo el líder de proyecto puede subir informes trimestrales.</p>

                <div class="space-y-2">
                    <a v-for="i in informesTrimestrales" :key="i.id" :href="i.ver_url" target="_blank" rel="noopener"
                        class="flex items-center justify-between gap-3 rounded-xl p-3 bg-[var(--sispaa-background)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_50%,transparent)]">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <FileText class="h-4 w-4 shrink-0 text-[var(--sispaa-primary)]" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-[var(--sispaa-text)]">{{ i.nombre_original ?? 'Informe' }}</p>
                                <p class="text-xs opacity-60 text-[var(--sispaa-text)]">{{ i.fecha_subida }}<span v-if="i.observaciones"> · {{ i.observaciones }}</span></p>
                            </div>
                        </div>
                        <Download class="h-4 w-4 shrink-0 opacity-50 text-[var(--sispaa-text)]" />
                    </a>

                    <div v-if="informesTrimestrales.length === 0" class="rounded-xl border border-dashed p-6 text-center text-sm opacity-50 border-[color:color-mix(in_srgb,var(--sispaa-text)_25%,transparent)] text-[var(--sispaa-text)]">
                        Aún no se han subido informes trimestrales.
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
                        <label class="mb-1.5 block text-sm font-semibold text-[var(--sispaa-text)]">Nombre *</label>
                        <input v-model="hitoForm.nombre" type="text" placeholder="Ej. Revisión bibliográfica" class="w-full rounded-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)]" />
                        <p v-if="hitoForm.errors.nombre" class="mt-1 text-xs text-rose-500">{{ hitoForm.errors.nombre }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-[var(--sispaa-text)]">Descripción</label>
                        <textarea v-model="hitoForm.descripcion" rows="3" placeholder="Detalles del hito..." class="w-full rounded-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)]"></textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-[var(--sispaa-text)]">Fecha planificada</label>
                        <input v-model="hitoForm.fecha_planificada" type="date" class="w-full rounded-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)]" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-[var(--sispaa-text)]">
                            Avance: {{ hitoForm.porcentaje_avance }}%
                        </label>
                        <input v-model.number="hitoForm.porcentaje_avance" type="range" min="0" max="100" step="5" class="w-full accent-[var(--sispaa-primary)]" />
                        <p v-if="hitoForm.errors.porcentaje_avance" class="mt-1 text-xs text-rose-500">{{ hitoForm.errors.porcentaje_avance }}</p>
                    </div>
                    <SheetFooter class="gap-2 pt-4">
                        <Button type="button" variant="outline" @click="showHitoForm = false">Cancelar</Button>
                        <Button type="submit" :disabled="hitoForm.processing" class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                            {{ hitoForm.processing ? 'Guardando...' : 'Guardar' }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>

        <!-- Sheet: Nueva pregunta (líder/colíder) -->
        <Sheet :open="showPreguntaForm" @update:open="val => showPreguntaForm = val">
            <SheetContent class="sm:max-w-md overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>Nueva Pregunta de Seguimiento</SheetTitle>
                </SheetHeader>
                <form @submit.prevent="submitPregunta" class="mt-6 space-y-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-[var(--sispaa-text)]">Pregunta *</label>
                        <textarea v-model="preguntaForm.pregunta" rows="4" placeholder="Escribe tu pregunta de seguimiento..." class="w-full rounded-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)]"></textarea>
                        <p v-if="preguntaForm.errors.pregunta" class="mt-1 text-xs text-rose-500">{{ preguntaForm.errors.pregunta }}</p>
                    </div>
                    <SheetFooter class="gap-2 pt-4">
                        <Button type="button" variant="outline" @click="showPreguntaForm = false">Cancelar</Button>
                        <Button type="submit" :disabled="preguntaForm.processing" class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
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
                <div class="mt-4 rounded-lg p-3 text-sm text-[var(--sispaa-text)] opacity-80 bg-[var(--sispaa-background)]">
                    {{ respondiendo?.pregunta }}
                </div>
                <form @submit.prevent="submitRespuesta" class="mt-5 space-y-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-[var(--sispaa-text)]">Respuesta *</label>
                        <textarea v-model="respuestaForm.respuesta" rows="5" placeholder="Escribe tu respuesta..." class="w-full rounded-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)]"></textarea>
                        <p v-if="respuestaForm.errors.respuesta" class="mt-1 text-xs text-rose-500">{{ respuestaForm.errors.respuesta }}</p>
                    </div>
                    <SheetFooter class="gap-2 pt-4">
                        <Button type="button" variant="outline" @click="respondiendo = null">Cancelar</Button>
                        <Button type="submit" :disabled="respuestaForm.processing" class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                            {{ respuestaForm.processing ? 'Guardando...' : 'Guardar respuesta' }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>

        <!-- Sheet: Subir informe trimestral (líder) -->
        <Sheet :open="showInformeForm" @update:open="val => showInformeForm = val">
            <SheetContent class="sm:max-w-md overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>Subir Informe Trimestral</SheetTitle>
                </SheetHeader>
                <form @submit.prevent="submitInforme" class="mt-6 space-y-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-[var(--sispaa-text)]">Archivo (PDF o Word) *</label>
                        <input type="file" accept=".pdf,.doc,.docx" @change="onArchivoChange" class="w-full rounded-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)]" />
                        <p v-if="informeForm.errors.archivo" class="mt-1 text-xs text-rose-500">{{ informeForm.errors.archivo }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-[var(--sispaa-text)]">Observaciones</label>
                        <textarea v-model="informeForm.observaciones" rows="3" placeholder="Notas sobre el avance de este trimestre..." class="w-full rounded-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)]"></textarea>
                    </div>
                    <SheetFooter class="gap-2 pt-4">
                        <Button type="button" variant="outline" @click="showInformeForm = false">Cancelar</Button>
                        <Button type="submit" :disabled="informeForm.processing" class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                            {{ informeForm.processing ? 'Subiendo...' : 'Subir' }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    </AppSidebarLayout>
</template>
