<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { UserPlus, Search } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { useDebounceFn } from '@vueuse/core';
import { makeUserColumns, type Usuario, type Role } from './columns';

interface PaginatedUsers {
    data: Usuario[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
}

const props = defineProps<{
    usuarios: PaginatedUsers;
    roles: Role[];
    filters: {
        q?: string;
        role?: string;
        per_page?: string;
    };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const search = ref(props.filters.q || '');
const filterRole = ref(props.filters.role || 'all');

const handleSearch = () => {
    router.get(route('admin.usuarios.index'), {
        q: search.value,
        role: filterRole.value,
        per_page: props.usuarios.per_page
    }, {
        preserveState: true,
        replace: true
    });
};

const debouncedSearch = useDebounceFn(() => {
    handleSearch();
}, 300);

const handleRoleChange = (val: string) => {
    filterRole.value = val;
    handleSearch();
};

const toggleUserStatus = (user: Usuario) => {
    router.post(route('admin.usuarios.toggle-status', user.id), {}, {
        preserveScroll: true
    });
};

const navigateToPage = (url: string | null) => {
    if (url) {
        router.get(url, {}, { preserveState: true });
    }
};

const columns = makeUserColumns({ onToggleStatus: toggleUserStatus });

const table = useVueTable({
    get data() { return props.usuarios.data },
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Gestión de Usuarios" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Gestión de Usuarios
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Crea, edita, deshabilita y administra los roles de acceso de la plataforma.
                    </p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition-colors">
                    <Link :href="route('admin.usuarios.create')">
                        <UserPlus class="h-4 w-4" />
                        Nuevo Usuario
                    </Link>
                </Button>
            </div>

            <div class="w-full space-y-4">
                <!-- Filtros -->
                <div class="flex flex-col sm:flex-row gap-4 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800/85">
                    <div class="flex-1 relative">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por nombre, correo o cédula..."
                            @input="debouncedSearch"
                            class="pl-9 w-full rounded-lg border-slate-200 bg-slate-50/30 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-slate-500">Filtrar Rol:</span>
                        <Select v-model="filterRole" @update:model-value="handleRoleChange">
                            <SelectTrigger class="w-[180px] rounded-lg border-slate-200 dark:border-slate-850">
                                <SelectValue placeholder="Todos los roles" />
                            </SelectTrigger>
                            <SelectContent class="rounded-lg">
                                <SelectItem value="all">Todos los roles</SelectItem>
                                <SelectItem v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="rounded-lg border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-950 overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" class="border-b border-slate-200/80 dark:border-slate-850">
                                    <TableHead v-for="header in headerGroup.headers" :key="header.id" class="h-12 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="divide-y divide-slate-100 dark:divide-slate-850 text-sm">
                                <template v-if="table.getRowModel().rows?.length">
                                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-slate-50/30 dark:hover:bg-slate-900/10">
                                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-4">
                                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="columns.length" class="h-24 text-center text-slate-500">
                                        No se encontraron usuarios.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Paginación -->
                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-850 px-6 py-4">
                        <span class="text-xs text-slate-500">
                            Mostrando {{ usuarios.data.length }} de {{ usuarios.total }} usuarios
                        </span>
                        <div class="flex items-center gap-1">
                            <button
                                v-for="link in usuarios.links"
                                :key="link.label"
                                @click="navigateToPage(link.url)"
                                :disabled="!link.url || link.active"
                                v-html="link.label"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                :class="[
                                    link.active ? 'bg-indigo-600 text-white' : 'border border-slate-200/80 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-350 disabled:opacity-50'
                                ]"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
