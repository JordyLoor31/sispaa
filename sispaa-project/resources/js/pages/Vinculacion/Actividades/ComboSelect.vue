<script setup lang="ts">
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
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
import { Check, ChevronsUpDown } from 'lucide-vue-next';

interface Option {
    value: number | string;
    label: string;
    sublabel?: string;
}

interface Selected {
    value: number | string;
    label: string;
}

const props = withDefaults(
    defineProps<{
        modelValue: number | string | null;
        options: Option[];
        placeholder?: string;
        searchPlaceholder?: string;
        emptyText?: string;
        allowEmpty?: boolean;
        emptyLabel?: string;
    }>(),
    {
        placeholder: 'Selecciona...',
        searchPlaceholder: 'Buscar...',
        emptyText: 'Sin resultados.',
        allowEmpty: false,
        emptyLabel: 'Sin selección',
    },
);

const emit = defineEmits<{ 'update:modelValue': [number | string | null] }>();

// Patrón probado del proyecto: ref local sincronizado por watch (no computed).
const findOption = (val: number | string | null): Selected | null => {
    const found = props.options.find((o) => o.value === val);
    return found ? { value: found.value, label: found.label } : null;
};

const selectedObj = ref<Selected | null>(findOption(props.modelValue));

watch(
    () => props.modelValue,
    (val) => {
        const current = selectedObj.value ? selectedObj.value.value : null;
        if (current !== val) {
            selectedObj.value = findOption(val);
        }
    },
);

watch(selectedObj, (val) => {
    const next = val && val.value !== '' ? val.value : null;
    if (next !== props.modelValue) {
        emit('update:modelValue', next);
    }
});
</script>

<template>
    <Combobox v-model="selectedObj" by="value">
        <ComboboxAnchor as-child>
            <ComboboxTrigger as-child>
                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                    <span class="truncate">{{ selectedObj ? selectedObj.label : placeholder }}</span>
                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                </Button>
            </ComboboxTrigger>
        </ComboboxAnchor>
        <ComboboxList
            class="w-[var(--reka-combobox-trigger-width)] min-w-[220px] rounded-lg border border-[var(--sispaa-surface)] bg-[var(--sispaa-background)] shadow-lg"
        >
            <ComboboxInput
                :placeholder="searchPlaceholder"
                class="w-full border-0 border-b border-[var(--sispaa-surface)] bg-transparent px-3 py-2.5 text-sm focus:ring-0"
            />
            <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">{{ emptyText }}</ComboboxEmpty>
            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                <ComboboxItem
                    v-if="allowEmpty"
                    :value="{ value: '', label: emptyLabel }"
                    class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_18%,transparent)]"
                >
                    {{ emptyLabel }}
                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                </ComboboxItem>
                <ComboboxItem
                    v-for="o in options"
                    :key="o.value"
                    :value="{ value: o.value, label: o.label }"
                    class="flex cursor-pointer items-center justify-between gap-2 rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_18%,transparent)]"
                >
                    <span class="flex flex-col">
                        <span>{{ o.label }}</span>
                        <span v-if="o.sublabel" class="text-xs opacity-60">{{ o.sublabel }}</span>
                    </span>
                    <ComboboxItemIndicator><Check class="h-4 w-4 shrink-0 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                </ComboboxItem>
            </ComboboxGroup>
        </ComboboxList>
    </Combobox>
</template>
