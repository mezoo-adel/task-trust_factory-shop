<script setup lang="ts">
import StockInputs from '@/components/Admin/StockInputs.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useApiFetch } from '@/composables/useApiFetch';
import adminRoutes from '@/routes/admin';
import type { Product } from '@/types/models';
import { router } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Props {
    product?: Product;
    open: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const { apiFetch, isLoading, errors } = useApiFetch();

const form = ref({
    stock_quantity: '',
    stock_threshold: '',
    reason: '',
});

const showReason = ref(false);

const closeModal = () => {
    emit('update:open', false);
    form.value = {
        stock_quantity: '',
        stock_threshold: '',
        reason: '',
    };
    errors.value = {};
    showReason.value = false;
};

const submit = async () => {
    if (!props.product) return;

    const response = await apiFetch(
        adminRoutes.stock.adjust.url({ product: props.product.id }),
        {
            method: 'POST',
            body: {
                stock_quantity: parseInt(form.value.stock_quantity),
                stock_threshold: parseInt(form.value.stock_threshold),
                reason: form.value.reason || null,
            },
            showSuccessToast: true,
            successMessage: 'Stock adjusted successfully',
        }
    );

    if (response) {
        closeModal();
        // Reload the page data to reflect changes
        router.reload({ only: ['products'] });
    }
};

// Watch for product changes to update form
const updateFormData = () => {
    if (props.product) {
        form.value.stock_quantity = props.product.stock_quantity.toString();
        form.value.stock_threshold = props.product.stock_threshold.toString();
        form.value.reason = '';
    }
};

watch(
    () => props.product?.id,
    () => {
        updateFormData();
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="closeModal">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>Adjust Stock</DialogTitle>
                <DialogDescription>
                    Update stock quantity and threshold for {{ product?.name }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <StockInputs
                    v-model:stock-quantity="form.stock_quantity"
                    v-model:stock-threshold="form.stock_threshold"
                    :errors="errors"
                />

                <!-- Optional Reason Field -->
                <div class="space-y-2">
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        class="w-full justify-between"
                        @click="showReason = !showReason"
                    >
                        <span class="text-sm">Add reason/note (optional)</span>
                        <ChevronDown v-if="!showReason" class="h-4 w-4" />
                        <ChevronUp v-else class="h-4 w-4" />
                    </Button>

                    <div v-if="showReason" class="space-y-2">
                        <Label for="reason">Reason</Label>
                        <Textarea
                            id="reason"
                            v-model="form.reason"
                            placeholder="Enter reason for stock adjustment (e.g., 'Inventory recount', 'Damaged items', 'New shipment')..."
                            rows="3"
                            :class="{
                                'border-red-500': errors.reason,
                            }"
                        />
                        <p v-if="errors.reason" class="text-sm text-red-600">
                            {{ errors.reason }}
                        </p>
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="closeModal"
                        :disabled="isLoading"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="isLoading">
                        {{ isLoading ? 'Saving...' : 'Save Changes' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
