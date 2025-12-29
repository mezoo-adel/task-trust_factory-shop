<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import UploadComponent from '@/components/UploadComponent.vue';
import type { Upload } from '@/types/models';
import { Save, X } from 'lucide-vue-next';

interface Props {
    errors?: Record<string, string>;
    processing?: boolean;
    mode?: 'create' | 'edit';
    existingUploads?: Upload[];
    submitText?: string;
    cancelText?: string;
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    existingUploads: () => [],
    submitText: 'Create Product',
    cancelText: 'Cancel',
    processing: false,
});

const emit = defineEmits<{
    submit: [];
    cancel: [];
}>();

const name = defineModel<string>('name', { required: true });
const description = defineModel<string>('description', { required: true });
const price = defineModel<string>('price', { required: true });
const stockQuantity = defineModel<string>('stockQuantity', { required: true });
const stockThreshold = defineModel<string>('stockThreshold', { required: true });
const isActive = defineModel<boolean>('isActive', { required: true });
const uploadIds = defineModel<number[]>('uploadIds', { default: () => [] });

const handleUploadsChange = (uploads: Upload[]) => {
    uploadIds.value = uploads.map((u) => u.id);
};
</script>

<template>
    <form @submit.prevent="emit('submit')" class="space-y-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Left Column -->
            <div class="space-y-6">
                <!-- Basic Information Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Information</CardTitle>
                        <CardDescription
                            >Enter the product details</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Product Name -->
                        <div class="space-y-2">
                            <Label for="name">Product Name *</Label>
                            <Input
                                id="name"
                                v-model="name"
                                type="text"
                                placeholder="Enter product name"
                                :class="{ 'border-red-500': errors?.name }"
                                required
                            />
                            <p v-if="errors?.name" class="text-sm text-red-600">
                                {{ errors.name }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="description"
                                placeholder="Enter product description"
                                rows="6"
                                :class="{ 'border-red-500': errors?.description }"
                            />
                            <p class="text-xs text-gray-500">
                                Provide a detailed description of the product
                            </p>
                            <p
                                v-if="errors?.description"
                                class="text-sm text-red-600"
                            >
                                {{ errors.description }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Pricing & Stock Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Pricing & Inventory</CardTitle>
                        <CardDescription
                            >Set price and stock levels</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Price -->
                        <div class="space-y-2">
                            <Label for="price">Price (USD) *</Label>
                            <Input
                                id="price"
                                v-model="price"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                :class="{ 'border-red-500': errors?.price }"
                                required
                            />
                            <p
                                v-if="errors?.price"
                                class="text-sm text-red-600"
                            >
                                {{ errors.price }}
                            </p>
                        </div>

                        <!-- Stock Quantity -->
                        <div class="space-y-2">
                            <Label for="stock_quantity">Stock Quantity *</Label>
                            <Input
                                id="stock_quantity"
                                v-model="stockQuantity"
                                type="number"
                                min="0"
                                placeholder="0"
                                :class="{ 'border-red-500': errors?.stock_quantity }"
                                required
                            />
                            <p
                                v-if="errors?.stock_quantity"
                                class="text-sm text-red-600"
                            >
                                {{ errors.stock_quantity }}
                            </p>
                        </div>

                        <!-- Stock Threshold -->
                        <div class="space-y-2">
                            <Label for="stock_threshold"
                                >Low Stock Threshold *</Label
                            >
                            <Input
                                id="stock_threshold"
                                v-model="stockThreshold"
                                type="number"
                                min="1"
                                placeholder="3"
                                :class="{ 'border-red-500': errors?.stock_threshold }"
                                required
                            />
                            <p class="text-xs text-gray-500">
                                Alert when stock falls below this number (ex: 3)
                            </p>
                            <p
                                v-if="errors?.stock_threshold"
                                class="text-sm text-red-600"
                            >
                                {{ errors.stock_threshold }}
                            </p>
                        </div>

                        <!-- Active Status -->
                        <div
                            class="flex items-center justify-between space-x-2 pt-2"
                        >
                            <div class="space-y-0.5">
                                <Label for="is_active">Product Status</Label>
                                <p class="text-xs text-gray-500">
                                    Make this product visible to customers
                                </p>
                            </div>
                            <Switch
                                id="is_active"
                                v-model="isActive"
                            />
                        </div>
                        <p
                            v-if="errors?.is_active"
                            class="text-sm text-red-600"
                        >
                            {{ errors.is_active }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Product Images Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Product Images</CardTitle>
                        <CardDescription
                            >Upload product photos (max 10 images, 5MB
                            each)</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <UploadComponent
                            :model-value="existingUploads"
                            @update:model-value="handleUploadsChange"
                            folder="products"
                            :max-files="10"
                            :max-size="5"
                            accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
                            label="Product Images"
                            description="Upload high-quality images of your product"
                        />
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-4">
            <Button type="button" variant="outline" @click="emit('cancel')">
                <X class="mr-2 h-4 w-4" />
                {{ cancelText }}
            </Button>
            <Button type="submit" :disabled="processing">
                <Save class="mr-2 h-4 w-4" />
                {{ processing ? 'Saving...' : submitText }}
            </Button>
        </div>
    </form>
</template>
