<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import type { SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, Clock, Inbox, MailOpen } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

/**
 * Campanita de notificaciones del header (a la derecha de los breadcrumbs).
 * Reemplaza el item "Notificaciones" que antes vivía suelto en cada bloque
 * del Sidebar (docente/coordinador/secretaría/SystemAdministrador/estudiante):
 * ahora es un solo componente reutilizado por AppSidebarHeader.vue, ya que
 * TODOS los roles (staff y estudiante) comparten el mismo layout/header
 * (AppLayout -> AppSidebarLayout -> AppSidebarHeader).
 *
 * El estudiante tiene sus propias rutas de notificaciones
 * (student.notificaciones*), separadas de las de staff (notificaciones.*)
 * porque así están particionadas por middleware de rol; este componente
 * detecta el rol del usuario autenticado y elige el set de rutas correcto.
 */

interface Notificacion {
    id: number;
    titulo: string;
    mensaje: string;
    leido: boolean;
    created_at: string;
}

const page = usePage<SharedData>();

const roles = computed<string[]>(() => page.props.auth?.user?.roles ?? []);

const ROLES_STAFF = ['docente', 'coordinador', 'secretaria', 'SystemAdministrador'];
const esStaff = computed(() => roles.value.some((rol) => ROLES_STAFF.includes(rol)));

const rutas = computed(() =>
    esStaff.value
        ? {
              recientes: route('notificaciones.recientes'),
              leer: route('notificaciones.read'),
              verTodas: route('notificaciones.index'),
          }
        : {
              recientes: route('student.notificaciones.recientes'),
              leer: route('student.notificaciones.read'),
              verTodas: route('student.notificaciones'),
          },
);

/** Contador compartido globalmente vía HandleInertiaRequests (auth.user.unread_notifications). */
const unreadCount = computed(() => page.props.auth?.user?.unread_notifications ?? 0);

const abierto = ref(false);
const cargando = ref(false);
const yaCargadas = ref(false);
const notificaciones = ref<Notificacion[]>([]);

async function cargarRecientes() {
    if (yaCargadas.value || cargando.value) return;
    cargando.value = true;
    try {
        const response = await fetch(rutas.value.recientes, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        const data = await response.json();
        notificaciones.value = data.notificaciones ?? [];
        yaCargadas.value = true;
    } finally {
        cargando.value = false;
    }
}

watch(abierto, (open) => {
    if (open) {
        cargarRecientes();
    }
});

const hayNoLeidas = computed(() => notificaciones.value.some((n) => !n.leido) || unreadCount.value > 0);

function marcarLeidas() {
    router.post(
        rutas.value.leer,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                notificaciones.value = notificaciones.value.map((n) => ({ ...n, leido: true }));
                router.reload({ only: ['auth'] });
            },
        },
    );
}

function formatearFecha(fecha: string) {
    return new Date(fecha).toLocaleString('es-EC', { dateStyle: 'medium', timeStyle: 'short' });
}
</script>

<template>
    <AlertDialog v-model:open="abierto">
        <AlertDialogTrigger as-child>
            <Button variant="ghost" size="icon" class="relative">
                <span class="sr-only">Notificaciones</span>
                <Bell class="h-5 w-5" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                >
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </span>
            </Button>
        </AlertDialogTrigger>
        <AlertDialogContent class="max-w-md">
            <AlertDialogHeader>
                <AlertDialogTitle class="flex items-center justify-between gap-2">
                    Notificaciones
                    <button
                        v-if="hayNoLeidas"
                        type="button"
                        class="inline-flex items-center gap-1 text-xs font-medium text-[var(--sispaa-primary)] hover:underline"
                        @click="marcarLeidas"
                    >
                        <MailOpen class="h-3.5 w-3.5" />
                        Marcar todo como leído
                    </button>
                </AlertDialogTitle>
                <AlertDialogDescription as-child>
                    <div class="max-h-96 overflow-y-auto">
                        <div v-if="cargando" class="py-10 text-center text-sm [color:color-mix(in_srgb,var(--sispaa-text)_50%,transparent)]">Cargando...</div>
                        <div v-else-if="notificaciones.length === 0" class="flex flex-col items-center gap-2 py-10 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)]">
                                <Inbox class="h-6 w-6 text-[color:color-mix(in_srgb,var(--sispaa-primary)_55%,var(--sispaa-text))]" />
                            </div>
                            <span class="text-sm [color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]">No tienes notificaciones.</span>
                        </div>
                        <div v-else class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                            <div v-for="n in notificaciones" :key="n.id" class="flex items-start gap-3 py-3 text-left">
                                <span
                                    class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                                    :class="n.leido ? 'bg-transparent' : 'bg-[var(--sispaa-primary)]'"
                                />
                                <div class="flex-1 space-y-0.5">
                                    <p
                                        class="text-sm font-semibold"
                                        :class="n.leido ? '[color:color-mix(in_srgb,var(--sispaa-text)_70%,transparent)]' : 'text-[var(--sispaa-text)]'"
                                    >
                                        {{ n.titulo }}
                                    </p>
                                    <p class="text-xs leading-relaxed [color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]">{{ n.mensaje }}</p>
                                    <p class="flex items-center gap-1 text-[11px] [color:color-mix(in_srgb,var(--sispaa-text)_45%,transparent)]">
                                        <Clock class="h-3 w-3" />
                                        {{ formatearFecha(n.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cerrar</AlertDialogCancel>
                <AlertDialogAction
                    as-child
                    class="bg-[var(--sispaa-primary)] text-white hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]"
                >
                    <Link :href="rutas.verTodas">Ver todas</Link>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
