<template>
  <Head title="Estudiantes Matriculados" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-2xl font-semibold">Estudiantes Matriculados</h1>
          <p class="mt-2 text-sm text-muted-foreground">Lista de estudiantes matriculados en la institución.</p>
        </div>

        <div class="flex gap-2">
          <input v-model="q" @keyup.enter="() => fetchWithInertia(1)" placeholder="Buscar por nombre, correo o cédula" class="input" />
          <select v-model="filters.carrera_id" @change="() => fetchWithInertia(1)" class="input">
            <option value="">Todas las carreras</option>
            <option v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre }}</option>
          </select>
          <select v-model="filters.estado" @change="() => fetchWithInertia(1)" class="input">
            <option value="">Todos estados</option>
            <option value="activo">Activo</option>
            <option value="retirado">Retirado</option>
            <option value="egresado">Egresado</option>
          </select>
        </div>
      </div>

      <div class="mb-4">
        <div class="border rounded-md">
          <Table>
            <TableHeader>
              <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                <TableHead v-for="header in headerGroup.headers" :key="header.id">
                  <FlexRender
                    v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                    :props="header.getContext()"
                  />
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <template v-if="table.getRowModel().rows?.length">
                <TableRow
                  v-for="row in table.getRowModel().rows" :key="row.id"
                  :data-state="row.getIsSelected() ? 'selected' : undefined"
                >
                  <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                    <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                  </TableCell>
                </TableRow>
              </template>
              <template v-else>
                <TableRow>
                  <TableCell :colspan="8" class="h-24 text-center">
                    No results.
                  </TableCell>
                </TableRow>
              </template>
            </TableBody>
          </Table>
        </div>
      </div>

      <div class="mt-4 flex items-center justify-between">
        <div>
          <button @click="prevPage" :disabled="!meta.prev_page_url" class="btn">Anterior</button>
          <button @click="nextPage" :disabled="!meta.next_page_url" class="btn ml-2">Siguiente</button>
        </div>
        <div class="text-sm text-muted-foreground">Página {{ meta.current_page }} de {{ meta.last_page }} — Total: {{ meta.total }}</div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, reactive, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import makeColumns from './Matriculados/column';
import {
  FlexRender,
  getCoreRowModel,
  useVueTable,
} from '@tanstack/vue-table'

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Estudiantes', href: '/estudiantes' },
  { title: 'Matriculados', href: '/estudiantes/matriculados' },
];

const data = ref<Array<any>>([]);
const meta = reactive({ current_page: 1, last_page: 1, total: 0, per_page: 12, prev_page_url: null, next_page_url: null });
const q = ref('');
const filters = reactive({ carrera_id: '', estado: '' });
const carreras = ref<Array<any>>([]);

const page = usePage();

function syncFromProps() {
  const props = (page && page.props && page.props.value) ? page.props.value : {};
  if (props.students) {
    const s = props.students;
    data.value = s.data || [];
    meta.current_page = s.current_page || 1;
    meta.last_page = s.last_page || 1;
    meta.total = s.total || 0;
    meta.per_page = s.per_page || meta.per_page;
    meta.prev_page_url = s.prev_page_url || null;
    meta.next_page_url = s.next_page_url || null;
  }

  if (props.carreras) {
    carreras.value = props.carreras;
  }
}

function fetchWithInertia(pageNum = 1) {
  const params: Record<string, any> = { page: pageNum, per_page: meta.per_page };
  if (filters.carrera_id) params.carrera_id = filters.carrera_id;
  if (filters.estado) params.estado = filters.estado;
  if (q.value) params.q = q.value;

  Inertia.get(route('estudiantes.matriculados'), params, { preserveState: true, replace: true, only: ['students', 'carreras'] });
}

function prevPage() {
  if (meta.current_page > 1) fetchWithInertia(meta.current_page - 1);
}

function nextPage() {
  if (meta.current_page < meta.last_page) fetchWithInertia(meta.current_page + 1);
}

function viewStudent(id: number) {
  Inertia.get(`/estudiantes/${id}`);
}

const columns = makeColumns(viewStudent)

const table = useVueTable({ get data() { return data.value }, get columns() { return columns }, getCoreRowModel: getCoreRowModel(), })

onMounted(() => {
  syncFromProps();
  console.log('Inertia page props (debug):', page && page.props ? page.props.value : null);

  // Fallback: si no hay estudiantes en props, intentar cargar desde el endpoint API
  if ((!page || !page.props || !page.props.value || !page.props.value.students || (page.props.value.students.data || []).length === 0)) {
    fetchDataApi();
  }
});

watch(() => (page && page.props && page.props.value && page.props.value.students) ? page.props.value.students : null, () => {
  syncFromProps();
});

async function fetchDataApi(pageNum = 1) {
  const params = new URLSearchParams();
  params.set('per_page', String(meta.per_page));
  params.set('page', String(pageNum));
  if (filters.carrera_id) params.set('carrera_id', String(filters.carrera_id));
  if (filters.estado) params.set('estado', String(filters.estado));
  if (q.value) params.set('q', q.value);

  const url = route('estudiantes.api.list') + '?' + params.toString();
  try {
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' });
    if (!res.ok) return;
    const json = await res.json();
    data.value = json.data || [];
    meta.current_page = json.current_page || 1;
    meta.last_page = json.last_page || 1;
    meta.total = json.total || 0;
    meta.per_page = json.per_page || meta.per_page;
    meta.prev_page_url = json.prev_page_url || null;
    meta.next_page_url = json.next_page_url || null;
  } catch (e) {
    console.error('Error fetching students from API fallback', e);
  }
}
</script>

<style scoped>
.input { padding: 0.5rem; border-radius: 0.375rem; border: 1px solid #d1d5db }
.btn { padding: 0.4rem 0.75rem; border-radius: 0.375rem; background: #f3f4f6; border: 1px solid #d1d5db }
</style>
