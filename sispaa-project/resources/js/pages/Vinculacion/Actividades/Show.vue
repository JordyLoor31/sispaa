<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Handshake, Users, Plus, CheckCircle2, XCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { toast } from 'vue-sonner';
import { ref } from 'vue';
import MatrizBeneficiarios from './MatrizBeneficiarios.vue';
import { type Actividad, type EstadoActividad, type Matriz, ESTADO_LABELS, GENEROS, RANGOS_EDAD, emptyMatriz, matrizToConteos } from './types';

const props = defineProps<{
    actividad: Actividad;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formatDate = (date?: string | null) => {
    if (!date) return '—';
    return new Date(date + 'T00:00:00').toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const estadoBadge = (estado: EstadoActividad) => {
    if (estado === 'ejecutado') return 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]';
    if (estado === 'cancelado') return 'bg-rose-500/15 text-rose-600';
    return 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_55%,var(--sispaa-text))]';
};

const beneficiario = props.actividad.beneficiario;
const beneficiarioObj = typeof beneficiario === 'object' ? beneficiario : null;

// --- Agregar beneficiarios adicionales ---
const addOpen = ref(false);
const addMatriz = ref<Matriz>(emptyMatriz());
const addFecha = ref<string>(new Date().toISOString().slice(0, 10));
const addObs = ref<string>('');
const addProcessing = ref(false);

const submitAdicionales = () => {
    addProcessing.value = true;
    router.post(
        route('vinculacion.actividades.beneficiarios.store', props.actividad.id),
        { fecha: addFecha.value, observacion: addObs.value, conteos: matrizToConteos(addMatriz.value) },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Beneficiarios adicionales registrados.');
                addOpen.value = false;
                addMatriz.value = emptyMatriz();
                addObs.value = '';
            },
            onError: () => toast.error('Revisa los datos: registra al menos una cantidad.'),
            onFinish: () => { addProcessing.value = false; },
        },
    );
};

// --- Marcar como Ejecutada ---
const ejecutarOpen = ref(false);
const fechaFin = ref<string>('');
const ejecutarProcessing = ref(false);

const submitEjecutar = () => {
    ejecutarProcessing.value = true;
    router.put(
        route('vinculacion.actividades.update', props.actividad.id),
        { estado: 'ejecutado', fecha_fin: fechaFin.value },
        {
            preserveScroll: true,
            onSuccess: () => { toast.success('Actividad marcada como ejecutada.'); ejecutarOpen.value = false; },
            onError: (e) => toast.error(e.fecha_fin ?? 'No se pudo actualizar el estado.'),
            onFinish: () => { ejecutarProcessing.value = false; },
        },
    );
};

// --- Cancelar ---
const cancelarOpen = ref(false);
const fechaCierre = ref<string>('');
const motivo = ref<string>('');
const cancelarProcessing = ref(false);

const submitCancelar = () => {
    cancelarProcessing.value = true;
    router.put(
        route('vinculacion.actividades.update', props.actividad.id),
        { estado: 'cancelado', fecha_cierre: fechaCierre.value || null, motivo_cancelacion: motivo.value || null },
        {
            preserveScroll: true,
            onSuccess: () => { toast.success('Actividad cancelada.'); cancelarOpen.value = false; },
            onError: () => toast.error('No se pudo cancelar la actividad.'),
            onFinish: () => { cancelarProcessing.value = false; },
        },
    );
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.actividad.nombre" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                        <Handshake class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">{{ actividad.nombre }}</h1>
                            <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(actividad.estado)]">{{ ESTADO_LABELS[actividad.estado] }}</span>
                        </div>
                        <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">{{ actividad.carrera }} · {{ actividad.periodo }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child class="text-white bg-[var(--sispaa-accent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-accent)_85%,black)]">
                        <Link :href="route('vinculacion.actividades')"><ArrowLeft class="h-4 w-4 mr-1.5" /> Volver</Link>
                    </Button>
                    <Button as-child class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Link :href="route('vinculacion.actividades.edit', actividad.id)"><Pencil class="h-4 w-4 mr-1.5" /> Editar</Link>
                    </Button>
                </div>
            </div>

            <div class="mx-auto w-full max-w-3xl space-y-4 sm:space-y-6">
                <!-- Datos generales -->
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                        <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Equipo interno</h4>
                        <p class="mt-2 text-sm text-[var(--sispaa-text)]"><span class="opacity-60">Supervisor:</span> <span class="font-semibold">{{ actividad.supervisor?.name ?? '—' }}</span></p>
                        <p class="mt-1 text-sm text-[var(--sispaa-text)]"><span class="opacity-60">Líder:</span> <span class="font-semibold">{{ actividad.docente_lider?.name ?? '—' }}</span></p>
                    </div>
                    <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                        <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Fechas</h4>
                        <p class="mt-2 text-sm text-[var(--sispaa-text)]"><span class="opacity-60">Inicio:</span> <span class="font-semibold">{{ formatDate(actividad.fecha_inicio) }}</span></p>
                        <p class="mt-1 text-sm text-[var(--sispaa-text)]"><span class="opacity-60">Fin:</span> <span class="font-semibold">{{ formatDate(actividad.fecha_fin) }}</span></p>
                        <p v-if="actividad.estado === 'cancelado'" class="mt-1 text-sm text-[var(--sispaa-text)]"><span class="opacity-60">Cierre:</span> <span class="font-semibold">{{ formatDate(actividad.fecha_cierre) }}</span></p>
                    </div>
                    <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                        <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Beneficiario</h4>
                        <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ beneficiarioObj?.nombre ?? 'Sin beneficiario definido' }}</p>
                        <p v-if="beneficiarioObj?.ruc" class="text-xs opacity-60 text-[var(--sispaa-text)]">RUC: {{ beneficiarioObj.ruc }}</p>
                        <p v-else-if="beneficiarioObj?.cedula" class="text-xs opacity-60 text-[var(--sispaa-text)]">Cédula: {{ beneficiarioObj.cedula }}</p>
                    </div>
                    <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                        <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Representante</h4>
                        <template v-if="actividad.representante">
                            <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ actividad.representante.nombre }}</p>
                            <p v-if="actividad.representante.cargo" class="text-xs opacity-60 text-[var(--sispaa-text)]">{{ actividad.representante.cargo }}</p>
                            <p v-if="actividad.representante.telefono" class="text-xs opacity-60 text-[var(--sispaa-text)]">Tel: {{ actividad.representante.telefono }}</p>
                        </template>
                        <p v-else class="mt-2 text-sm text-[var(--sispaa-text)] opacity-70">Sin representante</p>
                    </div>
                </div>

                <div v-if="actividad.estado === 'cancelado' && actividad.motivo_cancelacion" class="rounded-2xl border border-rose-500/20 bg-rose-500/5 p-4 text-sm text-[var(--sispaa-text)]">
                    <span class="font-semibold text-rose-600">Motivo de cancelación:</span> {{ actividad.motivo_cancelacion }}
                </div>

                <!-- Resumen consolidado de beneficiarios -->
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="flex items-center gap-1.5 text-sm font-bold text-[var(--sispaa-text)]"><Users class="h-4 w-4 text-[var(--sispaa-primary)]" /> Beneficiarios (consolidado)</h4>
                        <span class="text-sm font-extrabold text-[var(--sispaa-primary)]">Total: {{ actividad.resumen?.total ?? 0 }}</span>
                    </div>
                    <MatrizBeneficiarios v-if="actividad.resumen" :model-value="actividad.resumen.matriz" readonly />
                    <div class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-xs text-[var(--sispaa-text)]">
                        <span v-for="g in GENEROS" :key="g.key" class="opacity-70">{{ g.label }}: <strong>{{ actividad.resumen?.por_genero[g.key] ?? 0 }}</strong></span>
                        <span v-for="r in RANGOS_EDAD" :key="r.key" class="opacity-70">{{ r.label }}: <strong>{{ actividad.resumen?.por_edad[r.key] ?? 0 }}</strong></span>
                    </div>

                    <Button
                        v-if="actividad.estado === 'en_ejecucion'"
                        type="button"
                        class="mt-4 inline-flex items-center gap-1.5 bg-[var(--sispaa-primary)] font-semibold text-white hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]"
                        @click="addOpen = true"
                    >
                        <Plus class="h-4 w-4" /> Agregar beneficiarios adicionales
                    </Button>
                </div>

                <!-- Registros -->
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="mb-3 text-sm font-bold text-[var(--sispaa-text)]">Registros de conteo</h4>
                    <div class="space-y-2">
                        <div v-for="r in actividad.registros" :key="r.id" class="flex items-center justify-between rounded-lg border border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] px-3 py-2 text-sm">
                            <div class="flex items-center gap-2">
                                <span :class="['inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide', r.tipo === 'inicial' ? 'bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)] text-[var(--sispaa-primary)]' : 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[var(--sispaa-text)]']">{{ r.tipo }}</span>
                                <span class="opacity-70 text-[var(--sispaa-text)]">{{ formatDate(r.fecha) }}</span>
                                <span v-if="r.observacion" class="opacity-50 text-[var(--sispaa-text)]">— {{ r.observacion }}</span>
                            </div>
                            <span class="font-semibold text-[var(--sispaa-text)]">{{ r.total }} pers.</span>
                        </div>
                        <p v-if="!actividad.registros?.length" class="text-sm opacity-50 text-[var(--sispaa-text)]">Sin registros de conteo.</p>
                    </div>
                </div>

                <!-- Gestión de estado -->
                <div v-if="actividad.estado === 'en_ejecucion'" class="flex flex-wrap gap-2 rounded-2xl border border-dashed p-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)]">
                    <Button type="button" class="inline-flex items-center gap-1.5 bg-[var(--sispaa-secondary)] font-semibold text-white hover:bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_85%,black)]" @click="ejecutarOpen = true">
                        <CheckCircle2 class="h-4 w-4" /> Marcar como Ejecutada
                    </Button>
                    <Button type="button" variant="outline" class="inline-flex items-center gap-1.5 border-rose-500/40 font-semibold text-rose-600 hover:bg-rose-500/10" @click="cancelarOpen = true">
                        <XCircle class="h-4 w-4" /> Cancelar actividad
                    </Button>
                </div>
            </div>
        </div>

        <!-- Dialog: agregar adicionales -->
        <Dialog v-model:open="addOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Agregar beneficiarios adicionales</DialogTitle>
                    <DialogDescription>Se suman al total de la actividad. Registra al menos una cantidad.</DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[var(--sispaa-text)]">Fecha</label>
                        <input v-model="addFecha" type="date" class="w-full rounded-md border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] bg-[var(--sispaa-background)] px-3 py-2 text-sm text-[var(--sispaa-text)]" />
                    </div>
                    <MatrizBeneficiarios v-model="addMatriz" />
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[var(--sispaa-text)]">Observación (opcional)</label>
                        <Textarea v-model="addObs" placeholder="Nota del avance..." rows="2" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="addOpen = false">Cancelar</Button>
                    <Button type="button" :disabled="addProcessing" class="bg-[var(--sispaa-primary)] text-white hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]" @click="submitAdicionales">
                        {{ addProcessing ? 'Guardando...' : 'Agregar' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Dialog: marcar ejecutada -->
        <Dialog v-model:open="ejecutarOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Marcar como Ejecutada</DialogTitle>
                    <DialogDescription>Ingresa la fecha de fin. El conteo de beneficiarios queda consolidado (no editable).</DialogDescription>
                </DialogHeader>
                <div>
                    <label class="mb-1 block text-sm font-medium text-[var(--sispaa-text)]">Fecha de fin *</label>
                    <input v-model="fechaFin" type="date" :min="actividad.fecha_inicio ?? undefined" class="w-full rounded-md border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] bg-[var(--sispaa-background)] px-3 py-2 text-sm text-[var(--sispaa-text)]" />
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="ejecutarOpen = false">Cancelar</Button>
                    <Button type="button" :disabled="ejecutarProcessing || !fechaFin" class="bg-[var(--sispaa-secondary)] text-white hover:bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_85%,black)]" @click="submitEjecutar">
                        {{ ejecutarProcessing ? 'Guardando...' : 'Confirmar' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Dialog: cancelar -->
        <Dialog v-model:open="cancelarOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Cancelar actividad</DialogTitle>
                    <DialogDescription>La fecha de cierre y el motivo son opcionales.</DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[var(--sispaa-text)]">Fecha de cierre (opcional)</label>
                        <input v-model="fechaCierre" type="date" :min="actividad.fecha_inicio ?? undefined" class="w-full rounded-md border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] bg-[var(--sispaa-background)] px-3 py-2 text-sm text-[var(--sispaa-text)]" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-[var(--sispaa-text)]">Motivo (opcional)</label>
                        <Textarea v-model="motivo" placeholder="Motivo de la cancelación..." rows="2" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="cancelarOpen = false">Volver</Button>
                    <Button type="button" :disabled="cancelarProcessing" class="bg-rose-600 text-white hover:bg-rose-500" @click="submitCancelar">
                        {{ cancelarProcessing ? 'Guardando...' : 'Cancelar actividad' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppSidebarLayout>
</template>
