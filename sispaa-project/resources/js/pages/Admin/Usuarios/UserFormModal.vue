<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch, computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Check, ChevronsUpDown } from 'lucide-vue-next';
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
    roles: [] as string[],
    cedula: '',
    telefono: '',
    carrera_id: null as number | null | string,
});

const selectedCarreraObj = ref<{ value: string | number, label: string } | null>(null);

// 'coordinador' es un rol adicional sobre 'docente', no un reemplazo:
// solo se puede marcar si 'docente' también está marcado.
const hasDocente = computed(() => form.roles.includes('docente'));

const toggleRole = (roleName: string) => {
    const idx = form.roles.indexOf(roleName);
    if (idx >= 0) {
        form.roles.splice(idx, 1);
        // Si se desmarca docente, coordinador ya no puede quedar activo.
        if (roleName === 'docente') {
            const coordIdx = form.roles.indexOf('coordinador');
            if (coordIdx >= 0) form.roles.splice(coordIdx, 1);
        }
    } else {
        form.roles.push(roleName);
    }
};

watch(() => form.carrera_id, (newVal) => {
    if (!newVal || newVal === '') {
        selectedCarreraObj.value = { value: '', label: 'Ninguna (General)' };
    } else {
        const carrera = props.carreras.find(c => c.id === Number(newVal));
        if (carrera) {
            selectedCarreraObj.value = { value: carrera.id, label: carrera.nombre };
        } else {
            selectedCarreraObj.value = { value: '', label: 'Ninguna (General)' };
        }
    }
}, { immediate: true });

watch(selectedCarreraObj, (newVal) => {
    form.carrera_id = newVal ? newVal.value : '';
});

watch(() => props.open, (newVal) => {
    if (newVal) {
        if (props.user) {
            form.name = props.user.name;
            form.email = props.user.email;
            form.password = '';
            form.roles = props.user.roles.map(r => r.name);
            form.cedula = props.user.cedula || '';
            form.telefono = props.user.telefono || '';
            form.carrera_id = props.user.carrera_id || '';
        } else {
            form.reset();
            form.roles = [];
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

                    <!-- Roles (multi-selección) -->
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Roles *</label>
                        <div class="flex flex-wrap gap-2 mt-1">
                            <button
                                v-for="role in roles"
                                :key="role.id"
                                type="button"
                                @click="toggleRole(role.name)"
                                :disabled="role.name === 'coordinador' && !hasDocente"
                                :class="['inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold border transition-colors',
                                    form.roles.includes(role.name)
                                        ? 'bg-indigo-600 border-indigo-600 text-white'
                                        : 'bg-white border-slate-200 text-slate-600 hover:border-indigo-300 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-300',
                                    (role.name === 'coordinador' && !hasDocente) ? 'opacity-40 cursor-not-allowed hover:border-slate-200' : '']"
                            >
                                <Check v-if="form.roles.includes(role.name)" class="h-3.5 w-3.5" />
                                {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                            </button>
                        </div>
                        <p class="text-xs text-slate-400 mt-1.5">
                            "Coordinador" es un rol adicional: solo se puede marcar junto con "Docente".
                        </p>
                        <div v-if="form.errors.roles" class="text-xs text-rose-500 mt-1">{{ form.errors.roles }}</div>
                    </div>

                    <!-- Carrera -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Carrera</label>
                        <Combobox v-model="selectedCarreraObj" by="value">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350 mt-1">
                                        {{ selectedCarreraObj ? selectedCarreraObj.label : 'Ninguna (General)' }}
                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] bg-white dark:bg-slate-950 border border-slate-100 dark:border-slate-900 rounded-lg shadow-lg">
                                <ComboboxInput placeholder="Buscar carrera..." class="w-full border-0 border-b border-slate-105 dark:border-slate-850 bg-transparent text-sm focus:ring-0 py-2.5 px-3" />
                                <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron carreras.</ComboboxEmpty>
                                <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                    <ComboboxItem :value="{ value: '', label: 'Ninguna (General)' }" class="flex items-center justify-between px-3 py-2 text-sm rounded-md cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900 data-[state=checked]:bg-slate-100 dark:data-[state=checked]:bg-slate-800">
                                        Ninguna (General)
                                        <ComboboxItemIndicator>
                                            <Check class="h-4 w-4 text-indigo-650" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                    <ComboboxItem
                                        v-for="c in carreras"
                                        :key="c.id"
                                        :value="{ value: c.id, label: c.nombre }"
                                        class="flex items-center justify-between px-3 py-2 text-sm rounded-md cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900 data-[state=checked]:bg-slate-100 dark:data-[state=checked]:bg-slate-800"
                                    >
                                        {{ c.nombre }}
                                        <ComboboxItemIndicator>
                                            <Check class="h-4 w-4 text-indigo-650" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
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