<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import {
    FlaskConical,
    CalendarDays,
    Users,
    BookOpen,
    MapPin,
    FileText,
    Save,
    ClipboardCheck,
} from 'lucide-vue-next'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Laboratorio',
        href: '/laboratorio',
    },
    {
        title: 'Registrar práctica',
        href: '/laboratorio/crear',
    },
]

const form = useForm({
    materia: '',
    docente: '',
    carrera: '',
    laboratorio: '',
    numero_practica: '',
    tema: '',
    cantidad_estudiantes: '',
    observaciones: '',
    fecha: '',
})

const materias = [
    'Química Orgánica',
    'Biotecnología',
    'Microbiología',
    'Análisis de Suelos',
]

const docentes = [
    'Ing. Carlos Mendoza',
    'Dra. María Torres',
    'Ing. Andrea Paredes',
]

const carreras = [
    'Agroindustrial',
    'Agronegocios',
    'Agropecuaria',
    'Ambiental',
]

const laboratorios = [
    'Laboratorio Químico',
    'Laboratorio Biológico',
    'Laboratorio Industrial',
    'Laboratorio de Suelos',
]
</script>

<template>
    <Head title="Registrar práctica" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="min-h-screen p-4 sm:p-6"
            :style="{
                backgroundColor: 'var(--sispaa-background)',
                color: 'var(--sispaa-text)',
            }"
        >
            <div class="mx-auto flex max-w-7xl flex-col gap-6">

                <!-- HEADER -->
                <section
                    class="rounded-3xl p-6 shadow-sm"
                    :style="{
                        backgroundColor: 'var(--sispaa-surface)',
                    }"
                >
                    <div
                        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div>
                            <p
                                class="text-sm font-semibold uppercase tracking-[0.25em]"
                                :style="{ color: '#536493' }"
                            >
                                Laboratorio
                            </p>

                            <h1
                                class="mt-2 text-3xl font-bold"
                            >
                                Registro de práctica
                            </h1>

                            <p
                                class="mt-2 max-w-2xl text-sm"
                                :style="{ color: '#666' }"
                            >
                                Formulario visual para registrar
                                prácticas de laboratorio, docentes,
                                estudiantes y laboratorios utilizados.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl px-5 py-4"
                            :style="{
                                backgroundColor: '#53649315',
                            }"
                        >
                            <div
                                class="flex items-center gap-3"
                            >
                                <ClipboardCheck
                                    class="h-10 w-10 text-[#536493]"
                                />

                                <div>
                                    <p
                                        class="text-sm"
                                        :style="{ color: '#666' }"
                                    >
                                        Estado
                                    </p>

                                    <p
                                        class="text-lg font-semibold"
                                    >
                                        Nuevo registro
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- KPIS -->
                <section
                    class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                >
                    <article
                        class="rounded-3xl border p-5"
                        :style="{
                            backgroundColor:
                                'var(--sispaa-surface)',
                            borderColor: '#d9d9d9',
                        }"
                    >
                        <div
                            class="flex items-center justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm"
                                    :style="{ color: '#666' }"
                                >
                                    Prácticas este mes
                                </p>

                                <h2
                                    class="mt-2 text-3xl font-bold"
                                >
                                    34
                                </h2>
                            </div>

                            <div
                                class="rounded-2xl bg-[#53649315] p-4"
                            >
                                <FlaskConical
                                    class="h-7 w-7 text-[#536493]"
                                />
                            </div>
                        </div>
                    </article>

                    <article
                        class="rounded-3xl border p-5"
                        :style="{
                            backgroundColor:
                                'var(--sispaa-surface)',
                            borderColor: '#d9d9d9',
                        }"
                    >
                        <div
                            class="flex items-center justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm"
                                    :style="{ color: '#666' }"
                                >
                                    Estudiantes
                                </p>

                                <h2
                                    class="mt-2 text-3xl font-bold"
                                >
                                    1,284
                                </h2>
                            </div>

                            <div
                                class="rounded-2xl bg-[#88C27315] p-4"
                            >
                                <Users
                                    class="h-7 w-7 text-[#88C273]"
                                />
                            </div>
                        </div>
                    </article>

                    <article
                        class="rounded-3xl border p-5"
                        :style="{
                            backgroundColor:
                                'var(--sispaa-surface)',
                            borderColor: '#d9d9d9',
                        }"
                    >
                        <div
                            class="flex items-center justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm"
                                    :style="{ color: '#666' }"
                                >
                                    Laboratorios activos
                                </p>

                                <h2
                                    class="mt-2 text-3xl font-bold"
                                >
                                    12
                                </h2>
                            </div>

                            <div
                                class="rounded-2xl bg-[#D4BDAC25] p-4"
                            >
                                <MapPin
                                    class="h-7 w-7 text-[#D4BDAC]"
                                />
                            </div>
                        </div>
                    </article>

                    <article
                        class="rounded-3xl border p-5"
                        :style="{
                            backgroundColor:
                                'var(--sispaa-surface)',
                            borderColor: '#d9d9d9',
                        }"
                    >
                        <div
                            class="flex items-center justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm"
                                    :style="{ color: '#666' }"
                                >
                                    Carreras
                                </p>

                                <h2
                                    class="mt-2 text-3xl font-bold"
                                >
                                    6
                                </h2>
                            </div>

                            <div
                                class="rounded-2xl bg-[#7C5CFC15] p-4"
                            >
                                <BookOpen
                                    class="h-7 w-7 text-[#7C5CFC]"
                                />
                            </div>
                        </div>
                    </article>
                </section>

                <!-- FORM -->
                <section
                    class="grid gap-6 xl:grid-cols-3"
                >
                    <!-- FORMULARIO -->
                    <article
                        class="rounded-3xl border p-6 xl:col-span-2"
                        :style="{
                            backgroundColor:
                                'var(--sispaa-surface)',
                            borderColor: '#d9d9d9',
                        }"
                    >
                        <div class="mb-6">
                            <h2
                                class="text-2xl font-semibold"
                            >
                                Información de la práctica
                            </h2>

                            <p
                                class="mt-1 text-sm"
                                :style="{ color: '#666' }"
                            >
                                Complete todos los campos para
                                registrar la práctica de laboratorio.
                            </p>
                        </div>

                        <form
                            class="space-y-6"
                        >
                            <!-- GRID -->
                            <div
                                class="grid gap-5 md:grid-cols-2"
                            >
                                <!-- MATERIA -->
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium"
                                    >
                                        Materia
                                    </label>

                                    <select
                                        v-model="form.materia"
                                        class="w-full rounded-2xl border p-3 outline-none"
                                    >
                                        <option value="">
                                            Seleccionar
                                        </option>

                                        <option
                                            v-for="item in materias"
                                            :key="item"
                                        >
                                            {{ item }}
                                        </option>
                                    </select>
                                </div>

                                <!-- DOCENTE -->
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium"
                                    >
                                        Docente
                                    </label>

                                    <select
                                        v-model="form.docente"
                                        class="w-full rounded-2xl border p-3 outline-none"
                                    >
                                        <option value="">
                                            Seleccionar
                                        </option>

                                        <option
                                            v-for="item in docentes"
                                            :key="item"
                                        >
                                            {{ item }}
                                        </option>
                                    </select>
                                </div>

                                <!-- CARRERA -->
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium"
                                    >
                                        Carrera
                                    </label>

                                    <select
                                        v-model="form.carrera"
                                        class="w-full rounded-2xl border p-3 outline-none"
                                    >
                                        <option value="">
                                            Seleccionar
                                        </option>

                                        <option
                                            v-for="item in carreras"
                                            :key="item"
                                        >
                                            {{ item }}
                                        </option>
                                    </select>
                                </div>

                                <!-- LABORATORIO -->
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium"
                                    >
                                        Laboratorio
                                    </label>

                                    <select
                                        v-model="form.laboratorio"
                                        class="w-full rounded-2xl border p-3 outline-none"
                                    >
                                        <option value="">
                                            Seleccionar
                                        </option>

                                        <option
                                            v-for="item in laboratorios"
                                            :key="item"
                                        >
                                            {{ item }}
                                        </option>
                                    </select>
                                </div>

                                <!-- FECHA -->
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium"
                                    >
                                        Fecha
                                    </label>

                                    <div class="relative">
                                        <CalendarDays
                                            class="absolute left-3 top-3.5 h-5 w-5 text-[#666]"
                                        />

                                        <input
                                            v-model="form.fecha"
                                            type="date"
                                            class="w-full rounded-2xl border py-3 pl-11 pr-4 outline-none"
                                        />
                                    </div>
                                </div>

                                <!-- NUMERO -->
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium"
                                    >
                                        Número práctica
                                    </label>

                                    <input
                                        v-model="form.numero_practica"
                                        type="number"
                                        placeholder="Ej: 12"
                                        class="w-full rounded-2xl border p-3 outline-none"
                                    />
                                </div>

                                <!-- TEMA -->
                                <div class="md:col-span-2">
                                    <label
                                        class="mb-2 block text-sm font-medium"
                                    >
                                        Tema
                                    </label>

                                    <input
                                        v-model="form.tema"
                                        type="text"
                                        placeholder="Tema de la práctica"
                                        class="w-full rounded-2xl border p-3 outline-none"
                                    />
                                </div>

                                <!-- ESTUDIANTES -->
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium"
                                    >
                                        Cantidad estudiantes
                                    </label>

                                    <input
                                        v-model="
                                            form.cantidad_estudiantes
                                        "
                                        type="number"
                                        placeholder="Ej: 28"
                                        class="w-full rounded-2xl border p-3 outline-none"
                                    />
                                </div>

                                <!-- UBICACION -->
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium"
                                    >
                                        Ubicación
                                    </label>

                                    <input
                                        v-model="form.laboratorio"
                                        type="text"
                                        placeholder="Bloque A - Piso 2"
                                        class="w-full rounded-2xl border p-3 outline-none"
                                    />
                                </div>

                                <!-- OBSERVACIONES -->
                                <div class="md:col-span-2">
                                    <label
                                        class="mb-2 block text-sm font-medium"
                                    >
                                        Observaciones
                                    </label>

                                    <textarea
                                        v-model="
                                            form.observaciones
                                        "
                                        rows="5"
                                        placeholder="Observaciones generales..."
                                        class="w-full rounded-2xl border p-3 outline-none"
                                    />
                                </div>
                            </div>

                            <!-- BUTTONS -->
                            <div
                                class="flex flex-wrap gap-4 pt-2"
                            >
                                <button
                                    type="submit"
                                    class="flex items-center gap-2 rounded-2xl bg-[#536493] px-6 py-3 font-medium text-white transition hover:opacity-90"
                                >
                                    <Save class="h-5 w-5" />

                                    Guardar práctica
                                </button>

                                <button
                                    type="reset"
                                    class="rounded-2xl border px-6 py-3 font-medium transition hover:bg-gray-100"
                                >
                                    Limpiar formulario
                                </button>
                            </div>
                        </form>
                    </article>

                    <!-- PANEL DERECHO -->
                    <article
                        class="space-y-6"
                    >
                        <!-- RESUMEN -->
                        <div
                            class="rounded-3xl border p-5"
                            :style="{
                                backgroundColor:
                                    'var(--sispaa-surface)',
                                borderColor: '#d9d9d9',
                            }"
                        >
                            <h2
                                class="text-xl font-semibold"
                            >
                                Resumen rápido
                            </h2>

                            <div class="mt-5 space-y-4">
                                <div
                                    class="rounded-2xl bg-[#53649315] p-4"
                                >
                                    <p
                                        class="text-sm"
                                        :style="{ color: '#666' }"
                                    >
                                        Última práctica
                                    </p>

                                    <h3
                                        class="mt-1 font-semibold"
                                    >
                                        Microbiología aplicada
                                    </h3>
                                </div>

                                <div
                                    class="rounded-2xl bg-[#88C27315] p-4"
                                >
                                    <p
                                        class="text-sm"
                                        :style="{ color: '#666' }"
                                    >
                                        Docente activo
                                    </p>

                                    <h3
                                        class="mt-1 font-semibold"
                                    >
                                        Dra. María Torres
                                    </h3>
                                </div>

                                <div
                                    class="rounded-2xl bg-[#D4BDAC25] p-4"
                                >
                                    <p
                                        class="text-sm"
                                        :style="{ color: '#666' }"
                                    >
                                        Laboratorio más usado
                                    </p>

                                    <h3
                                        class="mt-1 font-semibold"
                                    >
                                        Lab. Químico
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <!-- INFO -->
                        <div
                            class="rounded-3xl border p-5"
                            :style="{
                                backgroundColor:
                                    'var(--sispaa-surface)',
                                borderColor: '#d9d9d9',
                            }"
                        >
                            <div
                                class="flex items-center gap-3"
                            >
                                <FileText
                                    class="h-8 w-8 text-[#536493]"
                                />

                                <div>
                                    <h2
                                        class="text-lg font-semibold"
                                    >
                                        Información
                                    </h2>

                                    <p
                                        class="text-sm"
                                        :style="{ color: '#666' }"
                                    >
                                        Datos simulados
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mt-5 space-y-3 text-sm"
                                :style="{ color: '#555' }"
                            >
                                <p>
                                    • Sistema visual para prácticas
                                    de laboratorio.
                                </p>

                                <p>
                                    • Integración con indicadores KPI.
                                </p>

                                <p>
                                    • Compatible con gráficos
                                    estadísticos.
                                </p>

                                <p>
                                    • Diseño adaptado al dashboard
                                    institucional.
                                </p>
                            </div>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AppLayout>
</template>