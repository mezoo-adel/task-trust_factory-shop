<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    message?: string | string[] | null | undefined;
}

const props = defineProps<Props>();

/**
 * Normalize the message prop to handle arrays, strings, or null/undefined
 * - If array: show each error on a new line
 * - If string: show as is
 * - If null/undefined: return empty string
 */
const normalizedMessages = computed(() => {
    if (!props.message) {
        return [];
    }

    // If it's already an array, use it
    if (Array.isArray(props.message)) {
        return props.message.filter(Boolean);
    }

    // If it's a string, check if it looks like a JSON array string
    if (typeof props.message === 'string') {
        // Try to parse if it looks like JSON array (e.g., '["Error 1", "Error 2"]')
        if (props.message.trim().startsWith('[') && props.message.trim().endsWith(']')) {
            try {
                const parsed = JSON.parse(props.message);
                if (Array.isArray(parsed)) {
                    return parsed.filter(Boolean);
                }
            } catch {
                // If parsing fails, treat as regular string
            }
        }
        // Return as single-item array for consistent handling
        return [props.message];
    }

    return [];
});

const hasMessages = computed(() => normalizedMessages.value.length > 0);
</script>

<template>
    <div v-if="hasMessages" class="space-y-1">
        <p
            v-for="(msg, index) in normalizedMessages"
            :key="index"
            class="text-sm text-red-600 dark:text-red-500"
        >
            {{ msg }}
        </p>
    </div>
</template>
