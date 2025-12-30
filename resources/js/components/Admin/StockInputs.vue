<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Props {
    errors?: Record<string, string>;
    showThresholdHelp?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showThresholdHelp: true,
});

const stockQuantity = defineModel<string>('stockQuantity', { required: true });
const stockThreshold = defineModel<string>('stockThreshold', { required: true });
</script>

<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="space-y-2">
            <Label for="stock_quantity">Stock Quantity *</Label>
            <Input
                id="stock_quantity"
                v-model="stockQuantity"
                type="number"
                min="0"
                placeholder="0"
                :class="{
                    'border-red-500': errors?.stock_quantity,
                }"
                required
            />
            <p v-if="errors?.stock_quantity" class="text-sm text-red-600">
                {{ errors.stock_quantity }}
            </p>
        </div>

        <div class="space-y-2">
            <Label for="stock_threshold">Low Stock Threshold *</Label>
            <Input
                id="stock_threshold"
                v-model="stockThreshold"
                type="number"
                min="1"
                placeholder="3"
                :class="{
                    'border-red-500': errors?.stock_threshold,
                }"
                required
            />
            <p v-if="showThresholdHelp" class="text-xs text-gray-500">
                Alert when stock falls below this number (ex: 3)
            </p>
            <p v-if="errors?.stock_threshold" class="text-sm text-red-600">
                {{ errors.stock_threshold }}
            </p>
        </div>
    </div>
</template>
