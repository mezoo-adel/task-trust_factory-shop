<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import ProductForm from '@/components/Admin/ProductForm.vue';
import adminRoutes from '@/routes/admin';
import { ArrowLeft } from 'lucide-vue-next';
import type { Upload } from '@/types/models';

interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    stock_quantity: number;
    stock_threshold: number;
    is_active: boolean;
    uploads: Upload[];
}

interface Props {
    product: Product;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.product.name,
    description: props.product.description || '',
    price: props.product.price.toString(),
    stock_quantity: props.product.stock_quantity.toString(),
    stock_threshold: props.product.stock_threshold.toString(),
    is_active: props.product.is_active,
    upload_ids: props.product.uploads.map(u => u.id),
});

const submit = () => {
    form.put(adminRoutes.products.update.url({ product: props.product.id }), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};

const handleCancel = () => {
    window.history.back();
};
</script>

<template>
    <Head :title="`Edit ${product.name} - Admin`" />

    <div class="min-h-screen bg-gray-50">
        <div class="border-b bg-white">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Button as-child variant="ghost" size="sm">
                            <Link :href="adminRoutes.products.show.url({ product: product.id })">
                                <ArrowLeft class="h-4 w-4 mr-2" />
                                Back to Product
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <div class="max-w-7xl mx-auto">
                <ProductForm
                    v-model:name="form.name"
                    v-model:description="form.description"
                    v-model:price="form.price"
                    v-model:stock-quantity="form.stock_quantity"
                    v-model:stock-threshold="form.stock_threshold"
                    v-model:is-active="form.is_active"
                    v-model:upload-ids="form.upload_ids"
                    :errors="form.errors"
                    :processing="form.processing"
                    :existing-uploads="product.uploads"
                    mode="edit"
                    submit-text="Save Changes"
                    @submit="submit"
                    @cancel="handleCancel"
                />
            </div>
        </div>
    </div>
</template>

