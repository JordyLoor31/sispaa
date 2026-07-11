<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    name: '',
    cedula: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Crear una cuenta" description="Ingrese sus datos para crear una cuenta">
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-5">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="name" class="text-[var(--sispaa-text)]">Nombre</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        tabindex="1"
                        autocomplete="name"
                        v-model="form.name"
                        placeholder="Nombre Apellido"
                        class="border-[var(--sispaa-text)]/20 focus-visible:ring-[var(--sispaa-primary)]"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="cedula" class="text-[var(--sispaa-text)]">Cédula</Label>
                    <Input
                        id="cedula"
                        type="text"
                        inputmode="numeric"
                        maxlength="10"
                        required
                        tabindex="2"
                        autocomplete="off"
                        v-model="form.cedula"
                        placeholder="10 dígitos"
                        class="border-[var(--sispaa-text)]/20 focus-visible:ring-[var(--sispaa-primary)]"
                    />
                    <InputError :message="form.errors.cedula" />
                </div>

                <div class="grid gap-2">
                    <Label for="email" class="text-[var(--sispaa-text)]">Correo electrónico</Label>
                    <Input id="email" type="email" required tabindex="3" autocomplete="email" v-model="form.email" placeholder="email@ejemplo.com" class="border-[var(--sispaa-text)]/20 focus-visible:ring-[var(--sispaa-primary)]" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password" class="text-[var(--sispaa-text)]">Contraseña</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        tabindex="4"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="********"
                        class="border-[var(--sispaa-text)]/20 focus-visible:ring-[var(--sispaa-primary)]"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" class="text-[var(--sispaa-text)]">Confirmar contraseña</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        tabindex="5"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="********"
                        class="border-[var(--sispaa-text)]/20 focus-visible:ring-[var(--sispaa-primary)]"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full bg-[var(--sispaa-primary)] text-white hover:bg-[var(--sispaa-primary)]/90" tabindex="6" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Crear cuenta
                </Button>
            </div>

            <div class="text-center text-sm text-[var(--sispaa-text)]/70">
                ¿Ya tiene una cuenta?
                <TextLink :href="route('login')" class="text-[var(--sispaa-primary)] font-semibold hover:underline" :tabindex="7">Iniciar sesión</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
