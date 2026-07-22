<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { UserPlus, Search } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                        Gestión de Usuarios
                    </h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                        Crea, edita, deshabilita y administra los roles de acceso de la plataforma.
                    </p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 font-semibold text-white transition-colors bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                    <Link :href="route('admin.usuarios.create')">
                        <UserPlus class="h-4 w-4" />
                        Nuevo Usuario
                    </Link>
                </Button>
            </div>

            <div class="w-full space-y-4">
                <!-- Filtros -->
                <div class="flex flex-col gap-4 sm:flex-row">
                    <div class="relative w-full max-w-sm">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 opacity-50 text-[var(--sispaa-text)]" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por nombre, correo o cédula..."
                            @input="debouncedSearch"
                            class="pl-9"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold opacity-60 text-[var(--sispaa-text)]">Filtrar Rol:</span>
                        <Select v-model="filterRole" @update:model-value="handleRoleChange">
                            <SelectTrigger class="w-[180px] rounded-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
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
                <div class="overflow-hidden rounded-lg border bg-[var(--sispaa-surface)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" class="border-b border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                                    <TableHead v-for="header in headerGroup.headers" :key="header.id" class="h-12 px-4 text-sm font-medium opacity-60 text-[var(--sispaa-text)]">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-sm">
                                <template v-if="table.getRowModel().rows?.length">
                                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_4%,transparent)]">
                                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-4">
                                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="columns.length" class="h-24 text-center opacity-60 text-[var(--sispaa-text)]">
                                        No se encontraron usuarios.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Paginación -->
                    <div class="flex flex-col gap-2 border-t px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <span class="text-xs opacity-60 text-[var(--sispaa-text)]">
                            Mostrando {{ usuarios.data.length }} de {{ usuarios.total }} usuarios
                        </span>
                        <div class="flex flex-wrap items-center gap-1">
                            <button
                                v-for="link in usuarios.links"
                                :key="link.label"
                                @click="navigateToPage(link.url)"
                                :disabled="!link.url || link.active"
                                v-html="link.label"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                :class="[
                                    link.active
                                        ? 'text-white bg-[var(--sispaa-primary)]'
                                        : 'border text-[var(--sispaa-text)] bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] disabled:opacity-50'
                                ]"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
