<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Ubicaciones" />

    <AppLayout>

        <div class="min-h-screen p-6">

            <h1 class="text-3xl font-bold mb-6">
                Ubicaciones de laboratorios
            </h1>

            <div class="grid md:grid-cols-3 gap-4">

                <div class="border rounded-3xl p-6">
                    <h2 class="font-bold text-xl">
                        Laboratorio Química
                    </h2>

                    <p class="text-gray-500 mt-2">
                        Bloque A - Piso 2
                    </p>
                </div>

                <div class="border rounded-3xl p-6">
                    <h2 class="font-bold text-xl">
                        Laboratorio Computación
                    </h2>

                    <p class="text-gray-500 mt-2">
                        Bloque B - Piso 1
                    </p>
                </div>

                <div class="border rounded-3xl p-6">
                    <h2 class="font-bold text-xl">
                        Laboratorio Biología
                    </h2>

                    <p class="text-gray-500 mt-2">
                        Bloque C - Piso 3
                    </p>
                </div>

            </div>

        </div>

    </AppLayout>
</template>