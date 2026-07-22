<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Check, ChevronsUpDown, ArrowLeft, User, Mail, Lock, IdCard, Phone } from 'lucide-vue-next';
import { errorCedulaEcuatoriana } from '@/lib/cedula';
import { errorCorreoInstitucional } from '@/lib/correo';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger,
} from '@/components/ui/combobox';
import type { Usuario, Role, Carrera } from './columns';

const props = defineProps<{
    usuario?: Usuario | null;
    roles: Role[];
    carreras: Carrera[];
}>();

const formSchema = toTypedSchema(
    z
        .object({
            name: z.string().min(1, 'El nombre es obligatorio.').max(255, 'El nombre no puede superar los 255 caracteres.'),
            email: z
                .string()
                .min(1, 'El correo es obligatorio.')
                .email('Ingresa un correo electrónico válido.')
                .max(255, 'El correo no puede superar los 255 caracteres.')
                // El sistema solo acepta correos institucionales ULEAM (misma
                // regla que el backend App\Rules\CorreoInstitucional).
                .superRefine((value, ctx) => {
                    const error = errorCorreoInstitucional(value);
                    if (error) {
                        ctx.addIssue({ code: z.ZodIssueCode.custom, message: error });
                    }
                }),
            password: props.usuario
                ? z.string().optional()
                : z.string().min(8, 'La contraseña debe tener al menos 8 caracteres.'),
            roles: z.array(z.string()).min(1, 'Selecciona al menos un rol.'),
            // Cédula opcional, pero si se ingresa debe ser una cédula
            // ecuatoriana válida (mismo algoritmo módulo 10 que la regla de
            // backend App\Rules\CedulaEcuatoriana; ver @/lib/cedula).
            cedula: z
                .string()
                .nullable()
                .optional()
                .superRefine((value, ctx) => {
                    if (!value) return;
                    const error = errorCedulaEcuatoriana(value);
                    if (error) {
                        ctx.addIssue({ code: z.ZodIssueCode.custom, message: error });
                    }
                }),
            telefono: z.string().max(15, 'El teléfono no puede superar los 15 caracteres.').nullable().optional(),
            carrera_id: z.union([z.string(), z.number()]).nullable(),
        })
        .refine((data) => !data.roles.includes('coordinador') || data.roles.includes('docente'), {
            message: 'El rol Coordinador solo se puede asignar a un usuario que también tenga el rol Docente.',
            path: ['roles'],
        }),
);

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        name: props.usuario?.name ?? '',
        email: props.usuario?.email ?? '',
        password: '',
        roles: props.usuario?.roles.map((r) => r.name) ?? ([] as string[]),
        cedula: props.usuario?.cedula ?? '',
        telefono: props.usuario?.telefono ?? '',
        carrera_id: props.usuario?.carrera_id ?? null,
    },
});

const [roles] = defineField('roles');
const [carreraId] = defineField('carrera_id');

const selectedCarreraObj = ref<{ value: string | number; label: string } | null>(
    props.usuario?.carrera ? { value: props.usuario.carrera.id, label: props.usuario.carrera.nombre } : null,
);

// 'coordinador' es un rol adicional sobre 'docente', no un reemplazo:
// solo se puede marcar si 'docente' también está marcado.
const hasDocente = computed(() => (roles.value ?? []).includes('docente'));

const toggleRole = (roleName: string) => {
    const current = [...(roles.value ?? [])];
    const idx = current.indexOf(roleName);
    if (idx >= 0) {
        current.splice(idx, 1);
        if (roleName === 'docente') {
            const coordIdx = current.indexOf('coordinador');
            if (coordIdx >= 0) current.splice(coordIdx, 1);
        }
    } else {
        current.push(roleName);
    }
    roles.value = current;
};

watch(selectedCarreraObj, (newVal) => {
    carreraId.value = newVal ? newVal.value : null;
});

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    const payload = {
        ...values,
        carrera_id: values.carrera_id === '' ? null : values.carrera_id,
    };

    const options = {
        onError: (serverErrors: Record<string, string>) => {
            setErrors(serverErrors);
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    };

    if (props.usuario) {
        router.put(route('admin.usuarios.update', props.usuario.id), payload, options);
    } else {
        router.post(route('admin.usuarios.store'), payload, options);
    }
});
</script>

<template>
    <form class="w-full max-w-xl space-y-4" @submit="onSubmit">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- Encabezados de sección: solo organización visual del formulario -->
            <div class="flex items-center gap-3 sm:col-span-2">
                <h4 class="shrink-0 text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Datos de la cuenta</h4>
                <div class="h-px flex-1 bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]"></div>
            </div>

            <FormField v-slot="{ componentField }" name="name">
                <FormItem class="sm:col-span-2">
                    <FormLabel>Nombre Completo *</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><User class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="text" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="email">
                <FormItem class="sm:col-span-2">
                    <FormLabel>Correo Electrónico *</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><Mail class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="email" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-if="!usuario" v-slot="{ componentField }" name="password">
                <FormItem class="sm:col-span-2">
                    <FormLabel>Contraseña *</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><Lock class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="password" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <div class="mt-2 flex items-center gap-3 sm:col-span-2">
                <h4 class="shrink-0 text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Datos personales</h4>
                <div class="h-px flex-1 bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]"></div>
            </div>

            <FormField v-slot="{ componentField }" name="cedula">
                <FormItem>
                    <FormLabel>Cédula</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><IdCard class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="text" maxlength="10" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="telefono">
                <FormItem>
                    <FormLabel>Teléfono</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><Phone class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="text" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <div class="mt-2 flex items-center gap-3 sm:col-span-2">
                <h4 class="shrink-0 text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Roles y alcance</h4>
                <div class="h-px flex-1 bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]"></div>
            </div>

            <FormField v-slot="{ errorMessage }" name="roles">
                <FormItem class="sm:col-span-2">
                    <FormLabel>Roles *</FormLabel>
                    <div class="mt-1 flex flex-wrap gap-2">
                        <button
                            v-for="role in props.roles"
                            :key="role.id"
                            type="button"
                            :disabled="role.name === 'coordinador' && !hasDocente"
                            :class="[
                                'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors',
                                (roles ?? []).includes(role.name)
                                    ? 'border-[var(--sispaa-primary)] bg-[var(--sispaa-primary)] text-white'
                                    : 'text-[var(--sispaa-text)] bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] hover:border-[color:var(--sispaa-primary)]',
                                role.name === 'coordinador' && !hasDocente ? 'cursor-not-allowed opacity-40 hover:border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]' : '',
                            ]"
                            @click="toggleRole(role.name)"
                        >
                            <Check v-if="(roles ?? []).includes(role.name)" class="h-3.5 w-3.5" />
                            {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                        </button>
                    </div>
                    <p class="mt-1.5 text-xs opacity-60 text-[var(--sispaa-text)]">"Coordinador" es un rol adicional: solo se puede marcar junto con "Docente".</p>
                    <p v-if="errorMessage" class="mt-1 text-sm font-medium text-destructive">{{ errorMessage }}</p>
                </FormItem>
            </FormField>

            <FormItem>
                <FormLabel>Carrera</FormLabel>
                <Combobox v-model="selectedCarreraObj" by="value">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <Button type="button" variant="outline" class="mt-1 w-full justify-between text-left text-sm font-normal">
                                {{ selectedCarreraObj ? selectedCarreraObj.label : 'Ninguna (General)' }}
                                <ChevronsUpDown class="h-4 w-4 opacity-50" />
                            </Button>
                        </ComboboxTrigger>
                    </ComboboxAnchor>

                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <ComboboxInput placeholder="Buscar carrera..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                        <ComboboxEmpty class="py-2 text-center text-xs opacity-60 text-[var(--sispaa-text)]">No se encontraron carreras.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                :value="{ value: '', label: 'Ninguna (General)' }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"
                            >
                                Ninguna (General)
                                <ComboboxItemIndicator>
                                    <Check class="h-4 w-4 text-[var(--sispaa-primary)]" />
                                </ComboboxItemIndicator>
                            </ComboboxItem>
                            <ComboboxItem
                                v-for="c in carreras"
                                :key="c.id"
                                :value="{ value: c.id, label: c.nombre }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"
                            >
                                {{ c.nombre }}
                                <ComboboxItemIndicator>
                                    <Check class="h-4 w-4 text-[var(--sispaa-primary)]" />
                                </ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
            </FormItem>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
            <Button as-child variant="outline" type="button">
                <Link :href="route('admin.usuarios.index')">
                    <ArrowLeft class="mr-1.5 h-4 w-4" /> Volver
                </Link>
            </Button>
            <Button type="submit" :disabled="processing" class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                {{ usuario ? 'Guardar Cambios' : 'Crear Usuario' }}
            </Button>
        </div>
    </form>
</template>
