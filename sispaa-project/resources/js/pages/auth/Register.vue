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

// Feedback inmediato (mismo algoritmo que las reglas de backend
// App\Rules\CedulaEcuatoriana y App\Rules\CorreoInstitucional).
const validarCedula = (): boolean => {
    const error = errorCedulaEcuatoriana(form.cedula);
    error ? form.setError('cedula', error) : form.clearErrors('cedula');
    return !error;
};

const validarCorreo = (): boolean => {
    const error = errorCorreoInstitucional(form.email);
    error ? form.setError('email', error) : form.clearErrors('email');
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

const inputClass =
    'border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] focus-visible:!border-[var(--sispaa-primary)] focus-visible:!ring-2 focus-visible:!ring-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)]';
</script>

<template>

    <Head title="Register" />

    <div
        class="flex min-h-svh flex-col items-center justify-center bg-[color:color-mix(in_srgb,var(--sispaa-surface)_63%,transparent)] p-6 md:p-10">
        <div class="flex w-full max-w-md flex-col items-center gap-6">
            <!-- Encabezado: logo + nombre + descripción -->
            <div class="flex flex-col items-center gap-3">
                <Link :href="route('home')" class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--sispaa-primary)]">
                        <GraduationCap class="size-6 text-white" />
                    </div>
                    <span class="text-lg font-bold text-[var(--sispaa-primary)]">SISPAA</span>
                </Link>
                <p class="max-w-sm text-center text-sm [color:color-mix(in_srgb,var(--sispaa-text)_75%,transparent)]">
                    Sistema Integral de Seguimiento de Procesos Académicos y Administrativos.
                </p>
            </div>

            <!-- Tarjeta de registro -->
            <div
                class="border-[var(--sispaa-text)]/10 w-full rounded-xl border bg-[var(--sispaa-background)] p-6 shadow-sm md:p-8">
                <div class="mb-6 space-y-1">
                    <h1 class="text-xl font-bold text-[var(--sispaa-text)]">Crear cuenta</h1>
                    <p class="text-sm [color:color-mix(in_srgb,var(--sispaa-text)_65%,transparent)]">Complete sus datos
                        para registrarse</p>
                </div>

                <form @submit.prevent="submit" class="flex flex-col gap-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="nombres" class="font-semibold text-[var(--sispaa-text)]">Nombres</Label>
                            <Input id="nombres" type="text" required autofocus tabindex="1" autocomplete="given-name"
                                v-model="form.nombres" placeholder="Ej. José Andrés" :class="inputClass" />
                            <InputError :message="form.errors.nombres" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="apellidos" class="font-semibold text-[var(--sispaa-text)]">Apellidos</Label>
                            <Input id="apellidos" type="text" required tabindex="2" autocomplete="family-name"
                                v-model="form.apellidos" placeholder="Ej. Toala Mero" :class="inputClass" />
                            <InputError :message="form.errors.apellidos" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="cedula" class="font-semibold text-[var(--sispaa-text)]">Cédula de identidad</Label>
                        <Input id="cedula" type="text" inputmode="numeric" maxlength="10" required tabindex="3"
                            autocomplete="off" v-model="form.cedula" placeholder="Ingrese su cédula (solo números)"
                            :class="inputClass" @blur="form.cedula && validarCedula()"
                            @input="form.errors.cedula && form.clearErrors('cedula')" />
                        <InputError :message="form.errors.cedula" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email" class="font-semibold text-[var(--sispaa-text)]">Correo electrónico</Label>
                        <Input id="email" type="email" required tabindex="4" autocomplete="email" v-model="form.email"
                            placeholder="Ingrese su correo institucional" :class="inputClass"
                            @blur="form.email && validarCorreo()"
                            @input="form.errors.email && form.clearErrors('email')" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password" class="font-semibold text-[var(--sispaa-text)]">Contraseña</Label>
                        <div class="relative">
                            <Input id="password" :type="showPassword ? 'text' : 'password'" required tabindex="5"
                                autocomplete="new-password" v-model="form.password" placeholder="Mínimo 8 caracteres"
                                :class="[inputClass, 'pr-10']" />
                            <button type="button" tabindex="-1" @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                class="text-[var(--sispaa-text)]/50 absolute inset-y-0 right-0 flex items-center pr-3 hover:text-[var(--sispaa-text)]">
                                <EyeOff v-if="showPassword" class="size-5" />
                                <Eye v-else class="size-5" />
                            </button>
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <!-- Aparece recién cuando el usuario empieza a escribir la contraseña -->
                    <div v-if="form.password.length > 0"
                        class="animate-in fade-in slide-in-from-top-2 grid gap-2 duration-300">
                        <Label for="password_confirmation" class="font-semibold text-[var(--sispaa-text)]">Confirmar
                            contraseña</Label>
                        <div class="relative">
                            <Input id="password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" required
                                tabindex="6" autocomplete="new-password" v-model="form.password_confirmation"
                                placeholder="Confirme su contraseña" :class="[inputClass, 'pr-10']" />
                            <button type="button" tabindex="-1" @click="showConfirmPassword = !showConfirmPassword"
                                :aria-label="showConfirmPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                class="text-[var(--sispaa-text)]/50 absolute inset-y-0 right-0 flex items-center pr-3 hover:text-[var(--sispaa-text)]">
                                <EyeOff v-if="showConfirmPassword" class="size-5" />
                                <Eye v-else class="size-5" />
                            </button>
                        </div>
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <Button type="submit"
                        class="hover:bg-[var(--sispaa-primary)]/90 mt-1 w-full bg-[var(--sispaa-primary)] text-white"
                        tabindex="7" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ form.processing ? 'Creando cuenta...' : 'Crear cuenta' }}
                    </Button>
                </form>
            </div>

            <!-- Enlace de inicio de sesión -->
            <div class="[color:color-mix(in_srgb,var(--sispaa-text)_75%,transparent)] text-center text-sm">
                ¿Ya tiene cuenta?
                <TextLink :href="route('login')" class="!font-semibold !text-[var(--sispaa-primary)] hover:underline"
                    :tabindex="8">
                    Iniciar sesión
                </TextLink>
            </div>
        </div>
    </div>
</template>