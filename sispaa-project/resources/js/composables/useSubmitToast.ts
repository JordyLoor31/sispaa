import type { VisitOptions } from '@inertiajs/core';
import { toast } from 'vue-sonner';

type ErrorMessage = string | ((errors: Record<string, string>) => string);

export function useSubmitToast(
    loadingMessage: string,
    errorMessage: ErrorMessage = 'No se pudo completar la operación.',
) {
    const toastId = toast.loading(loadingMessage);

    function withToast(options: VisitOptions = {}): VisitOptions {
        const onError = options.onError;
        const onFinish = options.onFinish;

        return {
            ...options,
            onError: (errors: Record<string, string>) => {
                if (onError) onError(errors);
                const message = typeof errorMessage === 'function' ? errorMessage(errors) : errorMessage;
                toast.error(message);
            },
            onFinish: () => {
                if (onFinish) onFinish();
                toast.dismiss(toastId);
            },
        };
    }

    return { withToast };
}
