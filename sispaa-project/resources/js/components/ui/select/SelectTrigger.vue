<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { 
  SelectTrigger as SelectTriggerPrimitive, 
  useForwardProps, 
  type SelectTriggerProps 
} from 'radix-vue';
import { ChevronDown } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

// Definimos las props incluyendo la clase de Tailwind
const props = defineProps<SelectTriggerProps & { class?: HTMLAttributes['class'] }>();

// Limpiamos las props para pasarlas a Radix sin conflictos
const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});

// Usamos useForwardProps en lugar de useForwardPropsEmits
const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
  <SelectTriggerPrimitive
    v-bind="forwardedProps"
    :class="
      cn(
        'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1',
        props.class,
      )
    "
  >
    <slot />
    <ChevronDown class="h-4 w-4 opacity-50" />
  </SelectTriggerPrimitive>
</template>