// vee-validate (>=4.9, instalado: 4.15) expone `componentField` de forma
// nativa en el v-slot de <Field>, listo para v-bind en inputs shadcn-vue
// (modelValue / onUpdate:modelValue), asi que reexportamos Field tal cual.
import { Field as FormField } from 'vee-validate';

export { default as FormControl } from './FormControl.vue';
export { default as FormDescription } from './FormDescription.vue';
export { FormField };
export { default as FormItem } from './FormItem.vue';
export { default as FormLabel } from './FormLabel.vue';
export { default as FormMessage } from './FormMessage.vue';
export { useFormField } from './useFormField';
