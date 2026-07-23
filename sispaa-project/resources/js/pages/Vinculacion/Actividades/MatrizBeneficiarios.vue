<script setup lang="ts">
import { computed } from 'vue';
import { emptyMatriz, GENEROS, RANGOS_EDAD, type Genero, type Matriz, type RangoEdad } from './types';

const props = withDefaults(
    defineProps<{
        modelValue?: Matriz | null;
        readonly?: boolean;
    }>(),
    { readonly: false, modelValue: null },
);

const emit = defineEmits<{ 'update:modelValue': [Matriz] }>();

// Blindaje: si por alguna razón llega null/undefined, usamos una matriz vacía.
const m = computed<Matriz>(() => props.modelValue ?? emptyMatriz());

const setCell = (g: Genero, r: RangoEdad, value: string | number) => {
    const n = Math.max(0, Math.floor(Number(value) || 0));
    const next: Matriz = {
        hombre: { ...m.value.hombre },
        mujer: { ...m.value.mujer },
    };
    next[g][r] = n;
    emit('update:modelValue', next);
};

const totalGenero = (g: Genero) => RANGOS_EDAD.reduce((acc, r) => acc + (Number(m.value[g][r.key]) || 0), 0);
const totalEdad = (r: RangoEdad) => GENEROS.reduce((acc, g) => acc + (Number(m.value[g.key][r]) || 0), 0);
const totalGeneral = computed(() => GENEROS.reduce((acc, g) => acc + totalGenero(g.key), 0));
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
        <table class="w-full min-w-[420px] border-collapse text-sm">
            <thead>
                <tr class="bg-[color:color-mix(in_srgb,var(--sispaa-primary)_8%,transparent)]">
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide opacity-60 text-[var(--sispaa-text)]">Rango de edad</th>
                    <th v-for="g in GENEROS" :key="g.key" class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide opacity-60 text-[var(--sispaa-text)]">
                        {{ g.label }}
                    </th>
                    <th class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-[var(--sispaa-primary)]">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="r in RANGOS_EDAD" :key="r.key" class="border-t border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <td class="px-3 py-2 text-[var(--sispaa-text)]">
                        <span class="font-medium">{{ r.label }}</span>
                        <span class="ml-1 text-xs opacity-50">{{ r.hint }}</span>
                    </td>
                    <td v-for="g in GENEROS" :key="g.key" class="px-2 py-1.5 text-center">
                        <span v-if="readonly" class="font-semibold text-[var(--sispaa-text)]">{{ m[g.key][r.key] }}</span>
                        <input
                            v-else
                            type="number"
                            min="0"
                            inputmode="numeric"
                            :value="m[g.key][r.key]"
                            @input="setCell(g.key, r.key, ($event.target as HTMLInputElement).value)"
                            class="w-16 rounded-md border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] bg-[var(--sispaa-background)] px-2 py-1 text-center text-sm text-[var(--sispaa-text)] focus:border-[var(--sispaa-primary)] focus:outline-none focus:ring-1 focus:ring-[var(--sispaa-primary)]"
                        />
                    </td>
                    <td class="px-3 py-2 text-center font-semibold text-[var(--sispaa-primary)]">{{ totalEdad(r.key) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="border-t border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_6%,transparent)]">
                    <td class="px-3 py-2 text-xs font-semibold uppercase tracking-wide opacity-60 text-[var(--sispaa-text)]">Total</td>
                    <td v-for="g in GENEROS" :key="g.key" class="px-3 py-2 text-center font-semibold text-[var(--sispaa-text)]">{{ totalGenero(g.key) }}</td>
                    <td class="px-3 py-2 text-center text-base font-extrabold text-[var(--sispaa-primary)]">{{ totalGeneral }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</template>
