<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, GraduationCap, LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const form = useForm({
    name: '',
    cedula: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const inputClass =
    'border-[var(--sispaa-surface)] focus-visible:!border-[var(--sispaa-primary)] focus-visible:!ring-2 focus-visible:!ring-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)]';
</script>

<template>
    <Head title="Register" />

    <div class="relative flex min-h-svh bg-[var(--sispaa-background)]">
        <!-- Panel lateral izquierdo -->
        <aside class="hidden w-1/3 flex-col bg-[var(--sispaa-sidebar)] p-8 lg:flex">
            <div class="flex items-start gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--sispaa-primary)]">
                    <GraduationCap class="h-6 w-6 text-white" />
                </div>
                <div class="space-y-0.5">
                    <p class="text-base font-semibold leading-none text-[var(--sispaa-primary)]">SISPAA</p>
                    <p class="text-sm leading-5 text-[#9199A7]">
                        Sistema Integral de Seguimiento de Procesos Académicos y Administrativos.
                    </p>
                </div>
            </div>

            <div class="mt-40 space-y-2">
                <h3 class="text-xl font-bold leading-tight text-[#E0E0E0]">Tu carpeta de documentos se crea al registrarte</h3>
                <p class="text-base leading-7 text-[#9199A7]">
                    Nombre + cédula → Carpeta personal
                    <br />
                    Los grupos (SGA, etc.) aparecen como subcarpetas para subir archivos
                </p>
            </div>
        </aside>

        <!-- Panel central / derecho -->
        <div class="flex flex-1 flex-col bg-[color:color-mix(in_srgb,var(--sispaa-surface)_63%,transparent)] items-center justify-center p-6">
            <div class="w-full max-w-md">
                <div class="rounded-2xl border border-[var(--sispaa-surface)] bg-[var(--sispaa-background)] p-6 shadow-sm">
                    <div class="mb-4">
                        <h2 class="text-lg font-bold text-[var(--sispaa-text)]">Crear cuenta</h2>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-1.5">
                            <Label for="name" class="text-sm font-medium text-[var(--sispaa-text)]">Nombre completo *</Label>
                            <Input
                                id="name"
                                type="text"
                                required
                                autofocus
                                tabindex="1"
                                autocomplete="name"
                                v-model="form.name"
                                placeholder="Nombres y apellidos"
                                :class="['h-10', inputClass]"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="cedula" class="text-sm font-medium text-[var(--sispaa-text)]">Cédula de identidad *</Label>
                            <Input
                                id="cedula"
                                type="text"
                                inputmode="numeric"
                                maxlength="10"
                                required
                                tabindex="2"
                                autocomplete="off"
                                v-model="form.cedula"
                                placeholder="Ingrese su cédula (solo números)"
                                :class="['h-10', inputClass]"
                            />
                            <InputError :message="form.errors.cedula" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="email" class="text-sm font-medium text-[var(--sispaa-text)]">Correo electrónico *</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                tabindex="3"
                                autocomplete="email"
                                v-model="form.email"
                                placeholder="Ingrese su correo institucional"
                                :class="['h-10', inputClass]"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="password" class="text-sm font-medium text-[var(--sispaa-text)]">Contraseña *</Label>
                            <div class="relative">
                                <Input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    tabindex="4"
                                    autocomplete="new-password"
                                    v-model="form.password"
                                    placeholder="Mínimo 6 caracteres alfanuméricos"
                                    :class="['h-10 pr-10', inputClass]"
                                />
                                <button
                                    type="button"
                                    tabindex="-1"
                                    @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-[var(--sispaa-text)]/50 hover:text-[var(--sispaa-text)]"
                                >
                                    <EyeOff v-if="showPassword" class="size-4" />
                                    <Eye v-else class="size-4" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="password_confirmation" class="text-sm font-medium text-[var(--sispaa-text)]">Confirmar contraseña *</Label>
                            <div class="relative">
                                <Input
                                    id="password_confirmation"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    required
                                    tabindex="5"
                                    autocomplete="new-password"
                                    v-model="form.password_confirmation"
                                    placeholder="Confirme su contraseña"
                                    :class="['h-10 pr-10', inputClass]"
                                />
                                <button
                                    type="button"
                                    tabindex="-1"
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    :aria-label="showConfirmPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-[var(--sispaa-text)]/50 hover:text-[var(--sispaa-text)]"
                                >
                                    <EyeOff v-if="showConfirmPassword" class="size-4" />
                                    <Eye v-else class="size-4" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password_confirmation" />
                        </div>

                        <Button
                            type="submit"
                            class="mt-2 h-10 w-full bg-[var(--sispaa-primary)] font-semibold text-white hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]"
                            tabindex="6"
                            :disabled="form.processing"
                        >
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            {{ form.processing ? 'Creando cuenta...' : 'Crear cuenta' }}
                        </Button>
                    </form>
                </div>

                <p class="mt-4 text-center text-sm [color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]">
                    ¿Ya tiene cuenta?
                    <TextLink :href="route('login')" class="!font-semibold !text-[var(--sispaa-primary)] hover:underline" :tabindex="7">
                        Iniciar sesión
                    </TextLink>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <footer class="absolute bottom-4 left-6 text-sm [color:color-mix(in_srgb,var(--sispaa-text)_55%,transparent)] lg:text-[#9199A7]">
            © 2026 SISPAA. Todos los derechos reservados.
        </footer>
    </div>
</template>
