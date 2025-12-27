import { ref, type Ref } from 'vue';

export type ToastVariant = 'default' | 'destructive';

export interface Toast {
    id: string;
    title: string;
    description?: string;
    variant?: ToastVariant;
    duration?: number;
}

// Shared state for all toast instances
const toasts: Ref<Toast[]> = ref([]);

export function useToast() {
    const toast = (options: Omit<Toast, 'id'>) => {
        const id = Math.random().toString(36).substring(2, 9);
        const newToast: Toast = {
            id,
            variant: 'default',
            duration: 5000,
            ...options,
        };

        toasts.value.push(newToast);

        if (newToast.duration && newToast.duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, newToast.duration);
        }

        return id;
    };

    const removeToast = (id: string) => {
        const index = toasts.value.findIndex((t) => t.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    return {
        toast,
        toasts,
        removeToast,
    };
}

