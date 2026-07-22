<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { BookOpen, Eye, EyeOff, GraduationCap, LoaderCircle, ShieldCheck, Users } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="grid min-h-svh lg:grid-cols-[1.1fr_1fr]">
        <!-- Panel institucional (solo escritorio) -->
        <aside
            class="relative hidden flex-col justify-between overflow-hidden p-12 text-white lg:flex"
            style="background: linear-gradient(160deg, color-mix(in srgb, var(--sispaa-primary) 88%, #0b2a2c) 0%, var(--sispaa-primary) 55%, color-mix(in srgb, var(--sispaa-primary) 72%, var(--sispaa-accent)) 100%)"
        >
            <!-- Trama decorativa tipo campus -->
            <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-[0.07]" aria-hidden="true">
                <defs>
                    <pattern id="grid-uni" width="48" height="48" patternUnits="userSpaceOnUse">
                        <path d="M 48 0 L 0 0 0 48" fill="none" stroke="white" stroke-width="1" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid-uni)" />
            </svg>
            <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-[var(--sispaa-secondary)]/20 blur-3xl" aria-hidden="true"></div>

            <!-- Marca -->
            <Link :href="route('home')" class="relative z-10 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 shadow-inner ring-1 ring-white/25 backdrop-blur-sm">
                    <GraduationCap class="size-7 text-white" />
                </div>
                <div class="leading-tight">
                    <span class="block text-xl font-bold tracking-wide">SISPAA</span>
                    <span class="block text-xs font-medium uppercase tracking-[0.18em] text-white/70">Portal Académico</span>
                </div>
            </Link>

            <!-- Mensaje central -->
            <div class="relative z-10 max-w-lg space-y-8">
                <h2 class="text-4xl font-bold leading-tight">
                    Tu vida universitaria,
                    <span class="text-[color:color-mix(in_srgb,var(--sispaa-secondary)_80%,white)]">en un solo lugar.</span>
                </h2>
                <p class="text-base leading-relaxed text-white/80">
                    Sistema Integral de Seguimiento de Procesos Académicos y Administrativos: expediente, sílabos, justificaciones y titulación
                    para toda la comunidad universitaria.
                </p>

                <ul class="space-y-4 text-sm">
                    <li class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/12 ring-1 ring-white/20">
                            <BookOpen class="size-5 text-white" />
                        </span>
                        <span class="text-white/90">Expediente y documentos académicos en línea</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/12 ring-1 ring-white/20">
                            <Users class="size-5 text-white" />
                        </span>
                        <span class="text-white/90">Un portal para estudiantes, docentes y administración</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/12 ring-1 ring-white/20">
                            <ShieldCheck class="size-5 text-white" />
                        </span>
                        <span class="text-white/90">Acceso seguro con tu cuenta institucional</span>
                    </li>
                </ul>
            </div>

            <p class="relative z-10 text-xs text-white/60">© 2026 SISPAA. Todos los derechos reservados.</p>
        </aside>

        <!-- Panel del formulario -->
        <main class="flex flex-col bg-[var(--sispaa-background)]">
            <div class="flex flex-1 items-center justify-center p-6 md:p-10">
                <div class="w-full max-w-sm">
                    <!-- Marca (solo móvil/tablet) -->
                    <Link :href="route('home')" class="mb-10 flex items-center justify-center gap-3 lg:hidden">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--sispaa-primary)] shadow-md">
                            <GraduationCap class="size-6 text-white" />
                        </div>
                        <div class="leading-tight">
                            <span class="block text-lg font-bold text-[var(--sispaa-primary)]">SISPAA</span>
                            <span class="block text-[10px] font-medium uppercase tracking-[0.18em] [color:color-mix(in_srgb,var(--sispaa-text)_55%,transparent)]">
                                Portal Académico
                            </span>
                        </div>
                    </Link>

                    <div class="mb-8 space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-[var(--sispaa-text)]">Bienvenido de nuevo</h1>
                        <p class="text-sm [color:color-mix(in_srgb,var(--sispaa-text)_65%,transparent)]">
                            Ingresa con tu correo institucional para continuar.
                        </p>
                    </div>

                    <div
                        v-if="status"
                        class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-center text-sm font-medium text-green-700"
                    >
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="flex flex-col gap-5">
                        <div class="grid gap-2">
                            <Label for="email" class="font-semibold text-[var(--sispaa-text)]">Correo electrónico</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                autofocus
                                tabindex="1"
                                autocomplete="email"
                                v-model="form.email"
                                placeholder="usuario@live.uleam.edu.ec"
                                class="h-11 rounded-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_18%,transparent)] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_18%,var(--sispaa-background))] text-[var(--sispaa-text)] caret-[var(--sispaa-primary)] placeholder:text-[color:color-mix(in_srgb,var(--sispaa-text)_45%,transparent)] focus-visible:!border-[var(--sispaa-primary)] focus-visible:!ring-2 focus-visible:!ring-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)]"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="font-semibold text-[var(--sispaa-text)]">Contraseña</Label>
                                <TextLink
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-sm text-[var(--sispaa-accent)] hover:text-[var(--sispaa-primary)]"
                                    :tabindex="5"
                                >
                                    ¿Olvidó su contraseña?
                                </TextLink>
                            </div>
                            <div class="relative">
                                <Input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    tabindex="2"
                                    autocomplete="current-password"
                                    v-model="form.password"
                                    placeholder="••••••••"
                                    class="h-11 rounded-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_18%,transparent)] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_18%,var(--sispaa-background))] text-[var(--sispaa-text)] caret-[var(--sispaa-primary)] placeholder:text-[color:color-mix(in_srgb,var(--sispaa-text)_45%,transparent)] pr-10 focus-visible:!border-[var(--sispaa-primary)] focus-visible:!ring-2 focus-visible:!ring-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)]"
                                />
                                <button
                                    type="button"
                                    tabindex="-1"
                                    @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                    class="text-[var(--sispaa-text)]/50 absolute inset-y-0 right-0 flex items-center pr-3 transition-colors hover:text-[var(--sispaa-text)]"
                                >
                                    <EyeOff v-if="showPassword" class="size-5" />
                                    <Eye v-else class="size-5" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <Button
                            type="submit"
                            class="mt-1 h-11 w-full rounded-lg bg-[var(--sispaa-primary)] text-[15px] font-semibold text-white shadow-md shadow-[color:color-mix(in_srgb,var(--sispaa-primary)_35%,transparent)] transition-all hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_88%,black)] hover:shadow-lg"
                            tabindex="3"
                            :disabled="form.processing"
                        >
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Iniciar sesión
                        </Button>
                    </form>

                    <!-- Separador -->
                    <div class="my-7 flex items-center gap-4" aria-hidden="true">
                        <div class="h-px flex-1 bg-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"></div>
                        <span class="text-xs font-medium uppercase tracking-wider [color:color-mix(in_srgb,var(--sispaa-text)_45%,transparent)]">
                            ¿Eres nuevo?
                        </span>
                        <div class="h-px flex-1 bg-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"></div>
                    </div>

                    <!-- Enlace de registro -->
                    <div class="text-center text-sm [color:color-mix(in_srgb,var(--sispaa-text)_75%,transparent)]">
                        Crea tu cuenta de estudiante
                        <TextLink :href="route('register')" class="!font-semibold !text-[var(--sispaa-primary)] hover:underline" :tabindex="5">
                            Registrarse
                        </TextLink>
                    </div>
                </div>
            </div>

            <!-- Footer (solo móvil/tablet; en escritorio vive en el panel institucional) -->
            <footer class="pb-5 text-center text-xs [color:color-mix(in_srgb,var(--sispaa-text)_55%,transparent)] lg:hidden">
                © 2026 SISPAA. Todos los derechos reservados.
            </footer>
        </main>
    </div>
</template>
