<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, GraduationCap, LoaderCircle } from 'lucide-vue-next';
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

    <div class="flex min-h-svh flex-col items-center justify-center bg-[color:color-mix(in_srgb,var(--sispaa-surface)_63%,transparent)] p-6 md:p-10">
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

            <div v-if="status" class="w-full text-center text-sm font-medium text-green-600">
                {{ status }}
            </div>

            <!-- Tarjeta de inicio de sesión -->
            <div class="border-[var(--sispaa-text)]/10 w-full rounded-xl border bg-[var(--sispaa-background)] p-6 shadow-sm md:p-8">
                <div class="mb-6 space-y-1">
                    <h1 class="text-xl font-bold text-[var(--sispaa-text)]">Iniciar sesión</h1>
                    <p class="text-sm [color:color-mix(in_srgb,var(--sispaa-text)_65%,transparent)]">Utilice su correo y contraseña</p>
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
                            placeholder="Ingrese su correo institucional"
                            class="border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] focus-visible:!border-[var(--sispaa-primary)] focus-visible:!ring-2 focus-visible:!ring-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)]"
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
                                class="border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] pr-10 focus-visible:!border-[var(--sispaa-primary)] focus-visible:!ring-2 focus-visible:!ring-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)]"
                            />
                            <button
                                type="button"
                                tabindex="-1"
                                @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                class="text-[var(--sispaa-text)]/50 absolute inset-y-0 right-0 flex items-center pr-3 hover:text-[var(--sispaa-text)]"
                            >
                                <EyeOff v-if="showPassword" class="size-5" />
                                <Eye v-else class="size-5" />
                            </button>
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <Button
                        type="submit"
                        class="hover:bg-[var(--sispaa-primary)]/90 mt-1 w-full bg-[var(--sispaa-primary)] text-white"
                        tabindex="3"
                        :disabled="form.processing"
                    >
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Iniciar sesión
                    </Button>
                </form>
            </div>

            <!-- Enlace de registro -->
            <div class="[color:color-mix(in_srgb,var(--sispaa-text)_75%,transparent)] text-center text-sm">
                ¿No tiene cuenta?
                <TextLink :href="route('register')" class="!font-semibold !text-[var(--sispaa-primary)] hover:underline" :tabindex="5">
                    Registrarse
                </TextLink>
            </div>
        </div>

        <!-- Footer -->
        <footer class="absolute bottom-4 right-6 text-sm [color:color-mix(in_srgb,var(--sispaa-text)_75%,transparent)]">© 2026 SISPAA. Todos los derechos reservados</footer>
    </div>
</template>
