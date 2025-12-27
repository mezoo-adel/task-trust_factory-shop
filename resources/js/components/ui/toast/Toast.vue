<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import type { Toast } from './use-toast';

interface Props {
    toast: Toast;
    onClose: (id: string) => void;
}

const props = defineProps<Props>();

const variantClasses = {
    default: 'bg-background text-foreground border',
    destructive: 'bg-destructive text-destructive-foreground border-destructive',
};
</script>

<template>
    <div
        :class="
            cn(
                'group pointer-events-auto relative flex w-full items-center justify-between space-x-4 overflow-hidden rounded-md border p-6 pr-8 shadow-lg transition-all',
                variantClasses[toast.variant || 'default'],
            )
        "
    >
        <div class="grid gap-1">
            <div v-if="toast.title" class="text-sm font-semibold">
                {{ toast.title }}
            </div>
            <div v-if="toast.description" class="text-sm opacity-90">
                {{ toast.description }}
            </div>
        </div>
        <button
            class="absolute right-2 top-2 rounded-md p-1 text-foreground/50 opacity-0 transition-opacity hover:text-foreground focus:opacity-100 focus:outline-none focus:ring-2 group-hover:opacity-100"
            @click="onClose(toast.id)"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>

