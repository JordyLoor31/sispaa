<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Building2, Hash, Briefcase, Phone, MapPin } from 'lucide-vue-next';
import type { Beneficiario, TipoBeneficiario } from './types';

const props = defineProps<{
    beneficiario?: Beneficiario | null;
}>();

const isEditing = !!props.beneficiario;

const formSchema = toTypedSchema(
    z
        .object({
            tipo: z.enum(['persona_natural', 'persona_juridica', 'comunidad_organizacion']),
            nombre: z.string().min(1, 'El nombre es obligatorio.').max(255, 'El nombre no puede superar los 255 caracteres.'),
            ruc: z.string().refine((v) => !v || /^\d{13}$/.test(v), 'El RUC debe tener 13 dígitos.').nullable().optional(),
            sector: z.string().max(255, 'El sector no puede superar los 255 caracteres.').nullable().optional(),
            contacto: z.string().refine((v) => !v || /^\d{10}$/.test(v), 'El contacto debe tener 10 dígitos.').nullable().optional(),
        })
        .refine((data) => data.tipo !== 'persona_juridica' || /^\d{13}$/.test(data.ruc ?? ''), {
            message: 'El RUC es obligatorio para una empresa o institución.',
            path: ['ruc'],
        }),
);

const { handleSubmit, setErrors, values } = useForm({
    validationSchema: formSchema,
    initialValues: {
        tipo: (props.beneficiario?.tipo ?? 'persona_juridica') as TipoBeneficiario,
        nombre: props.beneficiario?.nombre ?? '',
        ruc: props.beneficiario?.ruc ?? '',
        sector: props.beneficiario?.sector ?? '',
        contacto: props.beneficiario?.contacto ?? '',
    },
});

const esEmpresa = computed(() => values.tipo === 'persona_juridica');

const nombreLabel = computed(() => {
    if (values.tipo === 'comunidad_organizacion') return 'Nombre de la comunidad, barrio u organización *';
    if (values.tipo === 'persona_natural') return 'Nombre del lugar o sector *';
    return 'Nombre de la empresa o institución *';
});

const nombrePlaceholder = computed(() => {
    if (values.tipo === 'comunidad_organizacion') return 'Ej. Barrio Los Esteros, Asociación de agricultores';
    if (values.tipo === 'persona_natural') return 'Ej. Mercado municipal, Cancha del sector';
    return 'Razón social de la empresa o institución';
});

const processing = ref(false);

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('vinculacion.beneficiarios'));
    }
};

const onSubmit = handleSubmit((formValues) => {
    processing.value = true;

    const options = {
        preserveScroll: true,
        onError: (serverErrors: Record<string, string>) => setErrors(serverErrors),
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEditing && props.beneficiario) {
        router.put(route('vinculacion.beneficiarios.update', props.beneficiario.id), formValues, options);
    } else {
        router.post(route('vinculacion.beneficiarios.store'), formValues, options);
    }
});
</script>

<template>
    <form class="space-y-5" @submit="onSubmit">
        <FormField v-slot="{ componentField }" name="tipo">
            <FormItem>
                <FormLabel>Tipo de beneficiario *</FormLabel>
                <Select v-bind="componentField">
                    <FormControl>
                        <SelectTrigger class="w-full">
                            <div class="flex items-center gap-2"><MapPin class="h-4 w-4 opacity-60" /><SelectValue placeholder="Selecciona el tipo" /></div>
                        </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                        <SelectItem value="persona_juridica">Empresa o institución</SelectItem>
                        <SelectItem value="comunidad_organizacion">Comunidad, barrio u organización</SelectItem>
                        <SelectItem value="persona_natural">Otro lugar o sector</SelectItem>
                    </SelectContent>
                </Select>
                <p class="text-xs opacity-60 text-[var(--sispaa-text)]">Se refiere al lugar o sector beneficiado (empresa, barrio, comunidad), no a una persona.</p>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="nombre">
            <FormItem>
                <FormLabel>{{ nombreLabel }}</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><Building2 class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" :placeholder="nombrePlaceholder" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-if="esEmpresa" v-slot="{ componentField }" name="ruc">
            <FormItem>
                <FormLabel>RUC *</FormLabel>
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
                        <InputGroupInput type="text" v-bind="componentField" placeholder="Ej. Agrícola, Educación, Salud, Comercio" />
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
                {{ processing ? 'Guardando...' : isEditing ? 'Guardar cambios' : 'Registrar beneficiario' }}
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
