<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Inicio de sesión" description="Ingrese su correo y contraseña para iniciar sesión">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email" class="text-[var(--sispaa-text)]">Correo electrónico</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autofocus
                        tabindex="1"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="email@ejemplo.com"
                        class="border-[var(--sispaa-text)]/20 focus-visible:ring-[var(--sispaa-primary)]"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-[var(--sispaa-text)]">Contraseña</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm text-[var(--sispaa-accent)] hover:text-[var(--sispaa-primary)]" :tabindex="5"> ¿Olvidó su contraseña? </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        required
                        tabindex="2"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="********"
                        class="border-[var(--sispaa-text)]/20 focus-visible:ring-[var(--sispaa-primary)]"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between" tabindex="3">
                    <Label for="remember" class="flex items-center space-x-3 text-[var(--sispaa-text)]/80">
                        <Checkbox id="remember" v-model:checked="form.remember" tabindex="4" class="border-[var(--sispaa-text)]/30 data-[state=checked]:bg-[var(--sispaa-primary)] data-[state=checked]:text-white" />
                        <span>Recordarme</span>
                    </Label>
                </div>

                <Button type="submit" class="mt-4 w-full bg-[var(--sispaa-primary)] text-white hover:bg-[var(--sispaa-primary)]/90" tabindex="4" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Iniciar sesión
                </Button>
            </div>

            <div class="text-center text-sm text-[var(--sispaa-text)]/70">
                ¿No tiene una cuenta?
                <TextLink :href="route('register')" class="text-[var(--sispaa-primary)] font-semibold hover:underline" :tabindex="5">Registrarse</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
