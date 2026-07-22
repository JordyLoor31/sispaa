<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Building2, Hash, Briefcase, Phone } from 'lucide-vue-next';
import type { Empresa } from './types';

const props = defineProps<{
    empresa?: Empresa | null;
}>();

const isEditing = !!props.empresa;

const formSchema = toTypedSchema(
    z.object({
        nombre: z.string().min(1, 'El nombre es obligatorio.').max(255, 'El nombre no puede superar los 255 caracteres.'),
        ruc: z
            .string()
            .refine((v) => !v || /^\d{13}$/.test(v), 'El RUC debe tener 13 dígitos.')
            .nullable()
            .optional(),
        sector: z.string().max(255, 'El sector no puede superar los 255 caracteres.').nullable().optional(),
        contacto: z
            .string()
            .refine((v) => !v || /^\d{10}$/.test(v), 'El contacto debe tener 10 dígitos.')
            .nullable()
            .optional(),
    }),
);

const { handleSubmit, setErrors } = useForm({
    validationSchema: formSchema,
    initialValues: {
        nombre: props.empresa?.nombre ?? '',
        ruc: props.empresa?.ruc ?? '',
        sector: props.empresa?.sector ?? '',
        contacto: props.empresa?.contacto ?? '',
    },
});

const processing = ref(false);

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('vinculacion.empresas'));
    }
};

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    const options = {
        preserveScroll: true,
        onError: (serverErrors: Record<string, string>) => setErrors(serverErrors),
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEditing && props.empresa) {
        router.put(route('vinculacion.empresas.update', props.empresa.id), values, options);
    } else {
        router.post(route('vinculacion.empresas.store'), values, options);
    }
});
</script>

<template>
    <form class="space-y-5" @submit="onSubmit">
        <FormField v-slot="{ componentField }" name="nombre">
            <FormItem>
                <FormLabel>Nombre *</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><Building2 class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" placeholder="Razón social de la empresa" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="ruc">
            <FormItem>
                <FormLabel>RUC</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><Hash class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" inputmode="numeric" maxlength="13" v-bind="componentField" placeholder="13 dígitos (ej. 1790012345001)" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="sector">
            <FormItem>
                <FormLabel>Sector</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><Briefcase class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" placeholder="Ej. Agrícola, Construcción, Comercio" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="contacto">
            <FormItem>
                <FormLabel>Contacto</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><Phone class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" inputmode="numeric" maxlength="10" v-bind="componentField" placeholder="10 dígitos (ej. 0991234567)" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center">
            <Button
                type="submit"
                :disabled="processing"
                class="w-full bg-[var(--sispaa-primary)] font-semibold text-white hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)] sm:w-auto"
            >
                {{ processing ? 'Guardando...' : isEditing ? 'Guardar cambios' : 'Registrar empresa' }}
            </Button>
            <Button
                type="button"
                :disabled="processing"
                class="w-full bg-[var(--sispaa-accent)] font-semibold text-white hover:bg-[color:color-mix(in_srgb,var(--sispaa-accent)_85%,black)] sm:w-auto"
                @click="goBack"
            >
                Cancelar
            </Button>
        </div>
    </form>
</template>
