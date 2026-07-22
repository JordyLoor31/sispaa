<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { errorCedulaEcuatoriana } from '@/lib/cedula';
import { errorCorreoInstitucional } from '@/lib/correo';
import { Eye, EyeOff, GraduationCap, LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const form = useForm({
    nombres: '',
    apellidos: '',
    cedula: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

/**
 * Feedback inmediato de la cédula (mismo algoritmo módulo 10 que la regla
 * de backend App\Rules\CedulaEcuatoriana, que sigue siendo la fuente de
 * verdad). Devuelve true si la cédula es válida.
 */
const validarCedula = (): boolean => {
    const error = errorCedulaEcuatoriana(form.cedula);
    if (error) {
        form.setError('cedula', error);
    } else {
        form.clearErrors('cedula');
    }
    return !error;
};

/**
 * Feedback inmediato del correo institucional (mismos dominios que la regla
 * de backend App\Rules\CorreoInstitucional: @live.uleam.edu.ec y
 * @uleam.edu.ec). Devuelve true si el correo es válido.
 */
const validarCorreo = (): boolean => {
    const error = errorCorreoInstitucional(form.email);
    if (error) {
        form.setError('email', error);
    } else {
        form.clearErrors('email');
    }
    return !error;
};

const submit = () => {
    const cedulaValida = validarCedula();
    const correoValido = validarCorreo();
    if (!cedulaValida || !correoValido) return;

    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

// text/caret/placeholder explícitos: estas páginas fuerzan fondos claros
// (variables --sispaa-*, que no cambian con .dark), pero el Input de shadcn
// hereda el color de texto del body, que en modo oscuro es casi blanco —
// sin esto, lo escrito quedaba invisible (blanco sobre blanco).
const inputClass =
    'h-11 rounded-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_18%,transparent)] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_18%,var(--sispaa-background))] text-[var(--sispaa-text)] caret-[var(--sispaa-primary)] placeholder:text-[color:color-mix(in_srgb,var(--sispaa-text)_45%,transparent)] focus-visible:!border-[var(--sispaa-primary)] focus-visible:!ring-2 focus-visible:!ring-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)]';
</script>

<template>
    <Head title="Register" />

    <div class="grid min-h-svh lg:grid-cols-[1.1fr_1fr]">
        <!-- Panel institucional (solo escritorio) -->
        <aside
            class="relative hidden flex-col justify-between overflow-hidden p-12 text-white lg:flex"
            style="background: linear-gradient(160deg, color-mix(in srgb, var(--sispaa-primary) 88%, #0b2a2c) 0%, var(--sispaa-primary) 55%, color-mix(in srgb, var(--sispaa-primary) 72%, var(--sispaa-accent)) 100%)"
        >
            <!-- Trama decorativa tipo campus -->
            <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-[0.07]" aria-hidden="true">
                <defs>
                    <pattern id="grid-uni-reg" width="48" height="48" patternUnits="userSpaceOnUse">
                        <path d="M 48 0 L 0 0 0 48" fill="none" stroke="white" stroke-width="1" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid-uni-reg)" />
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

            <!-- Mensaje central: pasos del registro -->
            <div class="relative z-10 max-w-lg space-y-8">
                <h2 class="text-4xl font-bold leading-tight">
                    Empieza tu camino
                    <span class="text-[color:color-mix(in_srgb,var(--sispaa-secondary)_80%,white)]">universitario.</span>
                </h2>
                <p class="text-base leading-relaxed text-white/80">
                    Crea tu cuenta de estudiante y accede a tu expediente digital, justificaciones, plantillas y notificaciones desde el primer
                    día.
                </p>

                <ol class="space-y-4 text-sm">
                    <li class="flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/12 text-sm font-bold ring-1 ring-white/20"
                        >
                            1
                        </span>
                        <span class="text-white/90">Regístrate con tu cédula y correo institucional</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/12 text-sm font-bold ring-1 ring-white/20"
                        >
                            2
                        </span>
                        <span class="text-white/90">Completa tu perfil de estudiante paso a paso</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/12 text-sm font-bold ring-1 ring-white/20"
                        >
                            3
                        </span>
                        <span class="text-white/90">Sube tus documentos al expediente y sigue su aprobación</span>
                    </li>
                </ol>
            </div>

            <p class="relative z-10 text-xs text-white/60">© 2026 SISPAA. Todos los derechos reservados.</p>
        </aside>

        <!-- Panel del formulario -->
        <main class="flex flex-col bg-[var(--sispaa-background)]">
            <div class="flex flex-1 items-center justify-center p-6 md:p-10">
                <div class="w-full max-w-sm">
                    <!-- Marca (solo móvil/tablet) -->
                    <Link :href="route('home')" class="mb-8 flex items-center justify-center gap-3 lg:hidden">
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
                        <h1 class="text-2xl font-bold tracking-tight text-[var(--sispaa-text)]">Crea tu cuenta</h1>
                        <p class="text-sm [color:color-mix(in_srgb,var(--sispaa-text)_65%,transparent)]">
                            El registro es para estudiantes; completa tus datos para comenzar.
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="flex flex-col gap-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="nombres" class="font-semibold text-[var(--sispaa-text)]">Nombres</Label>
                                <Input
                                    id="nombres"
                                    type="text"
                                    required
                                    autofocus
                                    tabindex="1"
                                    autocomplete="given-name"
                                    v-model="form.nombres"
                                    placeholder="Ej. José Andrés"
                                    :class="inputClass"
                                />
                                <InputError :message="form.errors.nombres" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="apellidos" class="font-semibold text-[var(--sispaa-text)]">Apellidos</Label>
                                <Input
                                    id="apellidos"
                                    type="text"
                                    required
                                    tabindex="2"
                                    autocomplete="family-name"
                                    v-model="form.apellidos"
                                    placeholder="Ej. Toala Mero"
                                    :class="inputClass"
                                />
                                <InputError :message="form.errors.apellidos" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="cedula" class="font-semibold text-[var(--sispaa-text)]">Cédula de identidad</Label>
                            <Input
                                id="cedula"
                                type="text"
                                inputmode="numeric"
                                maxlength="10"
                                required
                                tabindex="3"
                                autocomplete="off"
                                v-model="form.cedula"
                                placeholder="10 dígitos, solo números"
                                :class="inputClass"
                                @blur="form.cedula && validarCedula()"
                                @input="form.errors.cedula && form.clearErrors('cedula')"
                            />
                            <InputError :message="form.errors.cedula" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email" class="font-semibold text-[var(--sispaa-text)]">Correo electrónico</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                tabindex="4"
                                autocomplete="email"
                                v-model="form.email"
                                placeholder="usuario@live.uleam.edu.ec"
                                :class="inputClass"
                                @blur="form.email && validarCorreo()"
                                @input="form.errors.email && form.clearErrors('email')"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password" class="font-semibold text-[var(--sispaa-text)]">Contraseña</Label>
                            <div class="relative">
                                <Input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    tabindex="5"
                                    autocomplete="new-password"
                                    v-model="form.password"
                                    placeholder="Mínimo 8 caracteres"
                                    :class="[inputClass, 'pr-10']"
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

                        <!-- Aparece recién cuando el usuario empieza a escribir la contraseña -->
                        <div v-if="form.password.length > 0" class="animate-in fade-in slide-in-from-top-2 grid gap-2 duration-300">
                            <Label for="password_confirmation" class="font-semibold text-[var(--sispaa-text)]">Confirmar contraseña</Label>
                            <div class="relative">
                                <Input
                                    id="password_confirmation"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    required
                                    tabindex="6"
                                    autocomplete="new-password"
                                    v-model="form.password_confirmation"
                                    placeholder="Repita su contraseña"
                                    :class="[inputClass, 'pr-10']"
                                />
                                <button
                                    type="button"
                                    tabindex="-1"
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    :aria-label="showConfirmPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                    class="text-[var(--sispaa-text)]/50 absolute inset-y-0 right-0 flex items-center pr-3 transition-colors hover:text-[var(--sispaa-text)]"
                                >
                                    <EyeOff v-if="showConfirmPassword" class="size-5" />
                                    <Eye v-else class="size-5" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password_confirmation" />
                        </div>

                        <Button
                            type="submit"
                            class="mt-1 h-11 w-full rounded-lg bg-[var(--sispaa-primary)] text-[15px] font-semibold text-white shadow-md shadow-[color:color-mix(in_srgb,var(--sispaa-primary)_35%,transparent)] transition-all hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_88%,black)] hover:shadow-lg"
                            tabindex="7"
                            :disabled="form.processing"
                        >
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            {{ form.processing ? 'Creando cuenta...' : 'Crear cuenta' }}
                        </Button>
                    </form>

                    <!-- Separador -->
                    <div class="my-7 flex items-center gap-4" aria-hidden="true">
                        <div class="h-px flex-1 bg-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"></div>
                        <span class="text-xs font-medium uppercase tracking-wider [color:color-mix(in_srgb,var(--sispaa-text)_45%,transparent)]">
                            ¿Ya tienes cuenta?
                        </span>
                        <div class="h-px flex-1 bg-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"></div>
                    </div>

                    <!-- Enlace de inicio de sesión -->
                    <div class="text-center text-sm [color:color-mix(in_srgb,var(--sispaa-text)_75%,transparent)]">
                        Accede con tu correo institucional
                        <TextLink :href="route('login')" class="!font-semibold !text-[var(--sispaa-primary)] hover:underline" :tabindex="8">
                            Iniciar sesión
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
