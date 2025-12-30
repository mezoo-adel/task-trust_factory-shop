<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import ProductForm from '@/components/Admin/ProductForm.vue';
import adminRoutes from '@/routes/admin';
import { ArrowLeft } from 'lucide-vue-next';

const form = useForm({
    name: '',
    description: '',
    price: '',
    stock_quantity: '',
    stock_threshold: '3',
    is_active: true,
    upload_ids: [] as number[],
});

const submit = () => {
    form.post(adminRoutes.products.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const handleCancel = () => {
    window.history.back();
};
</script>

<template>
    <Head title="Create Product" />

    <AdminLayout>
        <!-- Header -->
        <div class="border-b bg-white">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center gap-4">
                    <Button as-child variant="ghost" size="icon">
                        <Link :href="adminRoutes.products.index.url()">
                            <ArrowLeft class="h-5 w-5" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Create Product</h1>
                        <p class="text-sm text-gray-500">Add a new product to your inventory</p>
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
                    mode="create"
                    submit-text="Create Product"
                    @submit="submit"
                    @cancel="handleCancel"
                />
            </div>
        </div>
    </AdminLayout>
</template>
