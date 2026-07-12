<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { watch, computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Check, ChevronsUpDown, ArrowLeft } from 'lucide-vue-next';
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

const form = useForm({
    name: props.usuario?.name ?? '',
    email: props.usuario?.email ?? '',
    password: '',
    roles: props.usuario?.roles.map((r) => r.name) ?? [] as string[],
    cedula: props.usuario?.cedula ?? '',
    telefono: props.usuario?.telefono ?? '',
    carrera_id: (props.usuario?.carrera_id ?? '') as number | string,
});

const selectedCarreraObj = ref<{ value: string | number, label: string } | null>(
    props.usuario?.carrera ? { value: props.usuario.carrera.id, label: props.usuario.carrera.nombre } : null
);

// 'coordinador' es un rol adicional sobre 'docente', no un reemplazo:
// solo se puede marcar si 'docente' también está marcado.
const hasDocente = computed(() => form.roles.includes('docente'));

const toggleRole = (roleName: string) => {
    const idx = form.roles.indexOf(roleName);
    if (idx >= 0) {
        form.roles.splice(idx, 1);
        if (roleName === 'docente') {
            const coordIdx = form.roles.indexOf('coordinador');
            if (coordIdx >= 0) form.roles.splice(coordIdx, 1);
        }
    } else {
        form.roles.push(roleName);
    }
};

watch(selectedCarreraObj, (newVal) => {
    form.carrera_id = newVal ? newVal.value : '';
});

const submit = () => {
    const payload = { ...form.data(), carrera_id: form.carrera_id === '' ? null : form.carrera_id };
    form.transform(() => payload);

    if (props.usuario) {
        form.put(route('admin.usuarios.update', props.usuario.id));
    } else {
        form.post(route('admin.usuarios.store'));
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4 max-w-xl">
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase">Nombre Completo *</label>
                <input v-model="form.name" type="text" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                <div v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</div>
            </div>

            <div class="col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase">Correo Electrónico *</label>
                <input v-model="form.email" type="email" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                <div v-if="form.errors.email" class="text-xs text-rose-500 mt-1">{{ form.errors.email }}</div>
            </div>

            <div v-if="!usuario" class="col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase">Contraseña *</label>
                <input v-model="form.password" type="password" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                <div v-if="form.errors.password" class="text-xs text-rose-500 mt-1">{{ form.errors.password }}</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase">Cédula</label>
                <input v-model="form.cedula" type="text" maxlength="10" class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                <div v-if="form.errors.cedula" class="text-xs text-rose-500 mt-1">{{ form.errors.cedula }}</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase">Teléfono</label>
                <input v-model="form.telefono" type="text" class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                <div v-if="form.errors.telefono" class="text-xs text-rose-500 mt-1">{{ form.errors.telefono }}</div>
            </div>

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

        <div class="flex justify-between items-center border-t border-slate-100 pt-4 dark:border-slate-850">
            <Button as-child variant="outline" type="button">
                <Link :href="route('admin.usuarios.index')">
                    <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                </Link>
            </Button>
            <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white">
                {{ usuario ? 'Guardar Cambios' : 'Crear Usuario' }}
            </Button>
        </div>
    </form>
</template>
