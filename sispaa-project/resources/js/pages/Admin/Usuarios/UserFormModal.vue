<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

interface Role {
    id: number;
    name: string;
}

interface Carrera {
    id: number;
    nombre: string;
}

interface UserItem {
    id: number;
    name: string;
    email: string;
    cedula: string | null;
    telefono: string | null;
    is_active: boolean;
    carrera_id: number | null;
    roles: Role[];
}

const props = defineProps<{
    open: boolean;
    user: UserItem | null;
    roles: Role[];
    carreras: Carrera[];
}>();

const emit = defineEmits(['update:open', 'success']);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: '',
    cedula: '',
    telefono: '',
    carrera_id: null as number | null | string,
});

watch(() => props.open, (newVal) => {
    if (newVal) {
        if (props.user) {
            form.name = props.user.name;
            form.email = props.user.email;
            form.password = '';
            form.role = props.user.roles[0]?.name || '';
            form.cedula = props.user.cedula || '';
            form.telefono = props.user.telefono || '';
            form.carrera_id = props.user.carrera_id || '';
        } else {
            form.reset();
        }
        form.clearErrors();
    }
});

const submit = () => {
    if (props.user) {
        const payloadCarrier = form.carrera_id === '' ? null : form.carrera_id;
        form.transform((data) => ({
            ...data,
            carrera_id: payloadCarrier
        })).put(route('admin.usuarios.update', props.user.id), {
            onSuccess: () => {
                emit('update:open', false);
                emit('success');
            }
        });
    } else {
        form.post(route('admin.usuarios.store'), {
            onSuccess: () => {
                emit('update:open', false);
                emit('success');
            }
        });
    }
};
</script>

<template>
    <AlertDialog :open="open" @update:open="val => emit('update:open', val)">
        <AlertDialogContent class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-950 p-6 shadow-xl border border-slate-100 dark:border-slate-900">
            <AlertDialogHeader class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800/85">
                <div class="flex flex-col gap-1 text-left w-full">
                    <AlertDialogTitle class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ user ? 'Editar Usuario' : 'Registrar Nuevo Usuario' }}
                    </AlertDialogTitle>
                    <AlertDialogDescription class="text-xs text-slate-400">
                        {{ user ? 'Modifique los datos correspondientes de la cuenta.' : 'Ingrese los datos para registrar la nueva cuenta.' }}
                    </AlertDialogDescription>
                </div>
            </AlertDialogHeader>

            <form @submit.prevent="submit" class="mt-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <!-- Nombre -->
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase">Nombre Completo *</label>
                        <input v-model="form.name" type="text" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                        <div v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</div>
                    </div>

                    <!-- Email -->
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase">Correo Electrónico *</label>
                        <input v-model="form.email" type="email" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                        <div v-if="form.errors.email" class="text-xs text-rose-500 mt-1">{{ form.errors.email }}</div>
                    </div>

                    <!-- Contraseña (solo si es nuevo) -->
                    <div v-if="!user" class="col-span-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase">Contraseña *</label>
                        <input v-model="form.password" type="password" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                        <div v-if="form.errors.password" class="text-xs text-rose-500 mt-1">{{ form.errors.password }}</div>
                    </div>

                    <!-- Cédula -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase">Cédula</label>
                        <input v-model="form.cedula" type="text" maxlength="10" class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                        <div v-if="form.errors.cedula" class="text-xs text-rose-500 mt-1">{{ form.errors.cedula }}</div>
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase">Teléfono</label>
                        <input v-model="form.telefono" type="text" class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                        <div v-if="form.errors.telefono" class="text-xs text-rose-500 mt-1">{{ form.errors.telefono }}</div>
                    </div>

                    <!-- Rol -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase">Rol *</label>
                        <select v-model="form.role" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350">
                            <option value="" disabled>Selecciona rol...</option>
                            <option v-for="role in roles" :key="role.id" :value="role.name">
                                {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                            </option>
                        </select>
                    </div>

                    <!-- Carrera -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase">Carrera</label>
                        <select v-model="form.carrera_id" class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350">
                            <option :value="''">Ninguna (General)</option>
                            <option v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-4 mt-6 dark:border-slate-850">
                    <Button type="button" variant="outline" @click="emit('update:open', false)">
                        Cancelar
                    </Button>
                    <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white">
                        {{ user ? 'Guardar Cambios' : 'Crear Usuario' }}
                    </Button>
                </div>
            </form>
        </AlertDialogContent>
    </AlertDialog>
</template>