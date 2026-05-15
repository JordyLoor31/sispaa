<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue' // layout correcto

// Breadcrumb del módulo
const breadcrumbs = [
    { title: 'Vinculación', href: '/vinculacion' }, // módulo
    { title: 'Actividades', href: '/vinculacion/actividades' }, // vista actual
]

//
// Datos simulados
const ejecutadas = 10
const pendientes = 5
const empresas = 3
</script>

<template>
    <!-- Layout con breadcrumb -->
    <AppSidebarLayout :breadcrumbs="breadcrumbs">

        <!-- Contenedor principal -->
        <div class="min-h-screen pt-4 px-4">

            <!-- Título -->
            <h1 class="text-2xl font-bold mb-6">
                Gestión de Actividades
            </h1>

            <!-- KPIs -->
            <div class="grid grid-cols-3 gap-6 mb-8">

                <div class="card bg-green-custom">
                    <p class="title">Ejecutadas</p>
                    <h2 class="value">{{ ejecutadas }}</h2>
                </div>

                <div class="card bg-beige-custom">
                    <p class="title">Pendientes</p>
                    <h2 class="value">{{ pendientes }}</h2>
                </div>

                <div class="card bg-blue-custom">
                    <p class="title">Empresas</p>
                    <h2 class="value">{{ empresas }}</h2>
                </div>

            </div>

        </div>

    </AppSidebarLayout>
</template>

<style scoped>
.card {
    padding: 20px;
    border-radius: 12px; /* estándar */
    color: white;
}

.title {
    font-size: 14px;
}


.value {
    font-size: 28px;
    font-weight: bold;
}

.bg-green-custom {
    background-color: #86B86B;
}

.bg-beige-custom {
    background-color: #E6D8C3;
    color: #000;
}

.bg-blue-custom {
    background-color: #5C6C9E;
}
</style>