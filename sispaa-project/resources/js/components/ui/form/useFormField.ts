import { useFieldError, useIsFieldDirty, useIsFieldTouched, useIsFieldValid } from 'vee-validate';
import { inject } from 'vue';
import { FORM_ITEM_INJECTION_KEY } from './injectionKeys';

/**
 * Debe usarse dentro de un <FormField v-slot="..."> > <FormItem>. Las
 * composables useFieldError/useIsFieldDirty/useIsFieldTouched/useIsFieldValid
 * de vee-validate, llamadas sin argumentos, resuelven automáticamente el
 * campo del <FormField> (Field de vee-validate) más cercano en el árbol.
 */
export function useFormField() {
    const fieldItemId = inject(FORM_ITEM_INJECTION_KEY);

    if (!fieldItemId) {
        throw new Error('useFormField debe usarse dentro de <FormItem>');
    }

    const error = useFieldError();
    const isDirty = useIsFieldDirty();
    const isTouched = useIsFieldTouched();
    const isValid = useIsFieldValid();

    return {
        id: fieldItemId,
        formItemId: `${fieldItemId}-form-item`,
        formDescriptionId: `${fieldItemId}-form-item-description`,
        formMessageId: `${fieldItemId}-form-item-message`,
        error,
        isDirty,
        isTouched,
        isValid,
    };
}
