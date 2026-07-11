<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, FlaskConical, Pencil, Trash2, ClipboardList } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetFooter } from '@/components/ui/sheet';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';

interface Catalogo { id: number; nombre: string }
interface UsoItem { id: number; nombre: string; cantidad_usada: number }
interface PracticaItem {
    id: number;
    numero_practica: number;
    tema: string;
    subtema: string | null;
    logro_aprendizaje: string | null;
    semestre: string | null;
    numero_estudiantes: number | null;
    horario: string | null;
    objetivo: string | null;
    metodologia: string | null;
    resultados: string | null;
    conclusiones: string | null;
    fecha: string | null;
    materia: Catalogo;
    carrera: string;
    docente: { id: number; name: string };
    laboratorio: Catalogo | null;
    periodo: string;
    equipos: UsoItem[];
    reactivos: UsoItem[];
    es_propio: boolean;
}

const props = defineProps<{
    practicas: PracticaItem[];
    materias: Catalogo[];
    laboratorios: Catalogo[];
    periodos: Catalogo[];
    equiposCatalogo: Catalogo[];
    reactivosCatalogo: Catalogo[];
    filters: { periodo_id?: string };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Laboratorio', href: route('laboratorio.index') },
    { title: 'Prácticas', href: route('laboratorio.practicas') },
];

const filterPeriodo = ref(props.filters.periodo_id || 'all');
const applyFilter = () => {
    router.get(route('laboratorio.practicas'), { periodo_id: filterPeriodo.value !== 'all' ? filterPeriodo.value : undefined }, { preserveState: true, replace: true });
};

// ── Crear / Editar ────────────────────────────────────
const showForm = ref(false);
const editing = ref<PracticaItem | null>(null);
const emptyForm = () => ({
    materia_id: '' as number | '',
    periodo_id: '' as number | '',
    laboratorio_id: '' as number | '',
    numero_practica: 1,
    fecha: '',
    tema: '',
    subtema: '',
    logro_aprendizaje: '',
    semestre: '',
    numero_estudiantes: null as number | null,
    horario: '',
    objetivo: '',
    metodologia: '',
    resultados: '',
    conclusiones: '',
    equipos: [] as { id: number; cantidad_usada: number }[],
    reactivos: [] as { id: number; cantidad_usada: number }[],
});
const form = useForm(emptyForm());

const openCreate = () => {
    editing.value = null;
    form.defaults(emptyForm());
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

const openEdit = (p: PracticaItem) => {
    editing.value = p;
    form.materia_id = p.materia.id;
    form.periodo_id = props.periodos.find((per) => per.nombre === p.periodo)?.id ?? '';
    form.laboratorio_id = p.laboratorio?.id ?? '';
    form.numero_practica = p.numero_practica;
    form.fecha = p.fecha ?? '';
    form.tema = p.tema;
    form.subtema = p.subtema ?? '';
    form.logro_aprendizaje = p.logro_aprendizaje ?? '';
    form.semestre = p.semestre ?? '';
    form.numero_estudiantes = p.numero_estudiantes;
    form.horario = p.horario ?? '';
    form.objetivo = p.objetivo ?? '';
    form.metodologia = p.metodologia ?? '';
    form.resultados = p.resultados ?? '';
    form.conclusiones = p.conclusiones ?? '';
    form.equipos = p.equipos.map((e) => ({ id: e.id, cantidad_usada: e.cantidad_usada }));
    form.reactivos = p.reactivos.map((r) => ({ id: r.id, cantidad_usada: r.cantidad_usada }));
    form.clearErrors();
    showForm.value = true;
};

const toggleEquipo = (id: number, checked: boolean) => {
    if (checked) form.equipos.push({ id, cantidad_usada: 1 });
    else form.equipos = form.equipos.filter((e) => e.id !== id);
};
const toggleReactivo = (id: number, checked: boolean) => {
    if (checked) form.reactivos.push({ id, cantidad_usada: 1 });
    else form.reactivos = form.reactivos.filter((r) => r.id !== id);
};
const equipoChecked = (id: number) => form.equipos.some((e) => e.id === id);
const reactivoChecked = (id: number) => form.reactivos.some((r) => r.id === id);

const submit = () => {
    if (editing.value) {
        form.put(route('laboratorio.practicas.update', editing.value.id), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Práctica actualizada.'); showForm.value = false; },
        });
    } else {
        form.post(route('laboratorio.practicas.store'), {
            preserveScroll: true,
            onSuccess: () => { toast.success('Práctica registrada.'); showForm.value = false; form.reset(); },
        });
    }
};

// ── Eliminar ──────────────────────────────────────────
const deleteTarget = ref<PracticaItem | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('laboratorio.practicas.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Práctica eliminada.'); deleteTarget.value = null; },
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Prácticas de Laboratorio" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Prácticas de Laboratorio</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Registro, seguimiento y asistencia de prácticas.</p>
                </div>
                <Button @click="openCreate" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Plus class="h-4 w-4" /> Nueva Práctica
                </Button>
            </div>

            <div class="flex gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                <Select v-model="filterPeriodo" @update:model-value="applyFilter">
                    <SelectTrigger class="w-[200px]"><SelectValue placeholder="Período" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos los períodos</SelectItem>
                        <SelectItem v-for="per in periodos" :key="per.id" :value="String(per.id)">{{ per.nombre }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="p in practicas" :key="p.id"
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                            <FlaskConical class="h-4.5 w-4.5" />
                        </div>
                        <span class="text-xs font-semibold text-slate-400">N° {{ p.numero_practica }}</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ p.tema }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ p.materia.nombre }} · {{ p.carrera }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ p.docente.name }} · {{ p.periodo }}</p>
                        <p class="text-xs text-slate-400">{{ p.laboratorio?.nombre ?? 'Sin laboratorio asignado' }}</p>
                        <p v-if="p.fecha" class="text-xs text-slate-400">{{ p.fecha }}<span v-if="p.horario"> · {{ p.horario }}</span></p>
                        <p v-if="p.numero_estudiantes" class="text-xs text-slate-400">{{ p.numero_estudiantes }} estudiantes</p>
                    </div>
                    <div v-if="p.equipos.length || p.reactivos.length" class="flex flex-wrap gap-1">
                        <span v-for="e in p.equipos" :key="'e'+e.id" class="text-[10px] rounded-full bg-slate-100 dark:bg-slate-900 text-slate-500 px-2 py-0.5">{{ e.nombre }} ({{ e.cantidad_usada }})</span>
                        <span v-for="r in p.reactivos" :key="'r'+r.id" class="text-[10px] rounded-full bg-slate-100 dark:bg-slate-900 text-slate-500 px-2 py-0.5">{{ r.nombre }} ({{ r.cantidad_usada }})</span>
                    </div>
                    <div v-if="p.es_propio" class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <Link :href="route('laboratorio.practicas.asistencia', p.id)" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-500 font-semibold">
                            <ClipboardList class="h-3.5 w-3.5" /> Asistencia
                        </Link>
                        <button @click="openEdit(p)" class="ml-auto rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900">
                            <Pencil class="h-3.5 w-3.5" />
                        </button>
                        <button @click="deleteTarget = p" class="rounded-lg p-1.5 text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20">
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>

                <div v-if="practicas.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                    No hay prácticas registradas.
                </div>
            </div>
        </div>

        <!-- Sheet: Crear/Editar -->
        <Sheet :open="showForm" @update:open="val => { if (!val) { showForm = false; form.reset(); } }">
            <SheetContent class="sm:max-w-xl overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>{{ editing ? 'Editar Práctica' : 'Nueva Práctica de Laboratorio' }}</SheetTitle>
                </SheetHeader>
                <form @submit.prevent="submit" class="mt-6 space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Materia *</label>
                            <Select v-model="form.materia_id">
                                <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona..." /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.materia_id" class="text-xs text-rose-500 mt-1">{{ form.errors.materia_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Período *</label>
                            <Select v-model="form.periodo_id">
                                <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona..." /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="per in periodos" :key="per.id" :value="per.id">{{ per.nombre }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.periodo_id" class="text-xs text-rose-500 mt-1">{{ form.errors.periodo_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Laboratorio</label>
                            <Select v-model="form.laboratorio_id">
                                <SelectTrigger class="w-full"><SelectValue placeholder="Sin asignar" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="l in laboratorios" :key="l.id" :value="l.id">{{ l.nombre }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N° de práctica *</label>
                            <input v-model.number="form.numero_practica" type="number" min="1" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                            <p v-if="form.errors.numero_practica" class="text-xs text-rose-500 mt-1">{{ form.errors.numero_practica }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha *</label>
                            <input v-model="form.fecha" type="date" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                            <p v-if="form.errors.fecha" class="text-xs text-rose-500 mt-1">{{ form.errors.fecha }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Horario</label>
                            <input v-model="form.horario" type="text" placeholder="Ej: 08:00 - 10:00" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tema *</label>
                        <input v-model="form.tema" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        <p v-if="form.errors.tema" class="text-xs text-rose-500 mt-1">{{ form.errors.tema }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Subtema</label>
                            <input v-model="form.subtema" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Logro de aprendizaje</label>
                            <input v-model="form.logro_aprendizaje" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Semestre</label>
                            <input v-model="form.semestre" type="text" placeholder="Ej: Quinto semestre" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">N° de estudiantes</label>
                            <input v-model.number="form.numero_estudiantes" type="number" min="0" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Objetivo</label>
                        <textarea v-model="form.objetivo" rows="2" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Metodología</label>
                        <textarea v-model="form.metodologia" rows="2" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Resultados</label>
                        <textarea v-model="form.resultados" rows="2" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Conclusiones</label>
                        <textarea v-model="form.conclusiones" rows="2" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Equipos usados</label>
                        <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-800 p-3">
                            <label v-for="e in equiposCatalogo" :key="e.id" class="flex items-center gap-2 text-xs">
                                <input type="checkbox" :checked="equipoChecked(e.id)" @change="toggleEquipo(e.id, ($event.target as HTMLInputElement).checked)" class="rounded accent-indigo-600" />
                                {{ e.nombre }}
                            </label>
                            <p v-if="equiposCatalogo.length === 0" class="text-xs text-slate-400 col-span-2">No hay equipos registrados.</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Reactivos usados</label>
                        <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-800 p-3">
                            <label v-for="r in reactivosCatalogo" :key="r.id" class="flex items-center gap-2 text-xs">
                                <input type="checkbox" :checked="reactivoChecked(r.id)" @change="toggleReactivo(r.id, ($event.target as HTMLInputElement).checked)" class="rounded accent-indigo-600" />
                                {{ r.nombre }}
                            </label>
                            <p v-if="reactivosCatalogo.length === 0" class="text-xs text-slate-400 col-span-2">No hay reactivos registrados.</p>
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
                    <AlertDialogTitle>¿Eliminar práctica?</AlertDialogTitle>
                    <AlertDialogDescription>Se eliminará "{{ deleteTarget?.tema }}" junto con su asistencia registrada. Esta acción no se puede deshacer.</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDelete" class="bg-rose-600 hover:bg-rose-500">Eliminar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppSidebarLayout>
</template>
