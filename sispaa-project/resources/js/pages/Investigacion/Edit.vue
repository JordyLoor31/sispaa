<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupTextarea } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { FlaskConical } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps<{
    proyecto: { id: number; titulo: string; objetivo: string | null };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formSchema = toTypedSchema(
    z.object({
        titulo: z.string().min(1, 'El título es obligatorio.'),
        objetivo: z.string().nullable().optional(),
    }),
);

const { handleSubmit, setErrors } = useForm({
    validationSchema: formSchema,
    initialValues: {
        titulo: props.proyecto.titulo,
        objetivo: props.proyecto.objetivo ?? '',
    },
});

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    router.put(route('investigacion.update', props.proyecto.id), values, {
        preserveScroll: true,
        onSuccess: () => toast.success('Proyecto actualizado.'),
        onError: (serverErrors: Record<string, string>) => setErrors(serverErrors),
        onFinish: () => {
            processing.value = false;
        },
    });
});
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.proyecto.titulo}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-3xl">Editar Proyecto</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">{{ props.proyecto.titulo }}</p>
            </div>

            <div class="w-full max-w-xl mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <form class="space-y-5" @submit="onSubmit">
                    <FormField v-slot="{ componentField }" name="titulo">
                        <FormItem>
                            <FormLabel class="text-[var(--sispaa-text)]">Título *</FormLabel>
                            <FormControl>
                                <InputGroup>
                                    <InputGroupAddon><FlaskConical class="h-4 w-4" /></InputGroupAddon>
                                    <InputGroupInput type="text" v-bind="componentField" />
                                </InputGroup>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="objetivo">
                        <FormItem>
                            <FormLabel class="text-[var(--sispaa-text)]">Objetivo</FormLabel>
                            <FormControl>
                                <InputGroup>
                                    <InputGroupTextarea v-bind="componentField" rows="3" />
                                </InputGroup>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <div class="flex items-center gap-2 pt-2">
                        <Button type="submit" :disabled="processing" class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                            {{ processing ? 'Guardando...' : 'Guardar cambios' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
