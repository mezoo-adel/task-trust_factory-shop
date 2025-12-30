<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import adminRoutes from '@/routes/admin';
import { ArrowLeft, Edit, Trash2, Package, Image as ImageIcon, TrendingUp, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';

interface Upload {
    id: number;
    file_name: string;
    file_path: string;
    file_type: string;
    order: number;
}

interface StockTransaction {
    id: number;
    operation: string;
    quantity: number;
    previous_stock: number;
    new_stock: number;
    reason: string | null;
    created_at: string;
    performed_by: number | null;
    performedBy: {
        id: number;
        name: string;
        email: string;
    } | null;
}

interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    stock_quantity: number;
    stock_threshold: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    uploads: Upload[];
    stockTransactions: StockTransaction[];
    image_urls: string[];
}

interface Props {
    product: Product;
}

const props = defineProps<Props>();

const deleteForm = useForm({});
const currentImageIndex = ref(0);

const nextImage = () => {
    if (props.product.image_urls && props.product.image_urls.length > 0) {
        currentImageIndex.value = (currentImageIndex.value + 1) % props.product.image_urls.length;
    }
};

const prevImage = () => {
    if (props.product.image_urls && props.product.image_urls.length > 0) {
        currentImageIndex.value = currentImageIndex.value === 0
            ? props.product.image_urls.length - 1
            : currentImageIndex.value - 1;
    }
};

const selectImage = (index: number) => {
    currentImageIndex.value = index;
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const deleteProduct = () => {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        deleteForm.delete(adminRoutes.products.destroy.url({ product: props.product.id }), {
            onSuccess: () => {
                router.visit(adminRoutes.products.index.url());
            },
        });
    }
};

const getImageUrl = (filePath: string) => {
    return `/storage/${filePath}`;
};
</script>

<template>
    <Head :title="`${product.name} - Admin`" />

    <AdminLayout>
        <div class="border-b bg-white">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Button as-child variant="ghost" size="sm">
                            <Link :href="adminRoutes.products.index.url()">
                                <ArrowLeft class="h-4 w-4 mr-2" />
                                Back to Products
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold text-gray-900">{{ product.name }}</h1>
                    </div>
                    <div class="flex gap-2">
                        <Button as-child variant="outline">
                            <Link :href="adminRoutes.products.edit.url({ product: product.id })">
                                <Edit class="mr-2 h-4 w-4" />
                                Edit Product
                            </Link>
                        </Button>
                        <Button variant="destructive" @click="deleteProduct" :disabled="deleteForm.processing">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Product Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Product Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Name</label>
                                <p class="text-lg font-semibold mt-1">{{ product.name }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Description</label>
                                <p class="text-gray-700 mt-1 whitespace-pre-line">{{ product.description }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Price</label>
                                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ formatPrice(product.price) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Status</label>
                                    <div class="mt-1">
                                        <Badge :variant="product.is_active ? 'default' : 'secondary'">
                                            {{ product.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Stock Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Package class="h-5 w-5" />
                                Stock Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Current Stock</label>
                                    <div class="mt-1">
                                        <Badge :variant="product.stock_quantity > product.stock_threshold ? 'default' : 'destructive'" class="text-lg px-3 py-1">
                                            {{ product.stock_quantity }} units
                                        </Badge>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Stock Threshold</label>
                                    <p class="text-lg font-semibold mt-1">{{ product.stock_threshold }} units</p>
                                </div>
                            </div>
                            <div v-if="product.stock_quantity <= product.stock_threshold" class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                <p class="text-sm font-medium text-orange-800">⚠️ Low Stock Alert</p>
                                <p class="text-xs text-orange-600 mt-1">Stock is at or below the threshold level.</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Image Gallery -->
                    <Card v-if="product.image_urls && product.image_urls.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <ImageIcon class="h-5 w-5" />
                                Product Images
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <!-- Main Image -->
                                <div class="relative aspect-video overflow-hidden rounded-lg bg-gray-100">
                                    <img
                                        :src="product.image_urls[currentImageIndex]"
                                        :alt="product.name"
                                        class="h-full w-full object-contain"
                                    />

                                    <!-- Navigation Arrows -->
                                    <div v-if="product.image_urls.length > 1" class="absolute inset-0 flex items-center justify-between p-4">
                                        <Button
                                            variant="secondary"
                                            size="icon"
                                            class="h-10 w-10 rounded-full bg-white/80 hover:bg-white"
                                            @click="prevImage"
                                        >
                                            <ChevronLeft class="h-6 w-6" />
                                        </Button>
                                        <Button
                                            variant="secondary"
                                            size="icon"
                                            class="h-10 w-10 rounded-full bg-white/80 hover:bg-white"
                                            @click="nextImage"
                                        >
                                            <ChevronRight class="h-6 w-6" />
                                        </Button>
                                    </div>

                                    <!-- Image Counter -->
                                    <div v-if="product.image_urls.length > 1" class="absolute bottom-4 right-4 rounded-full bg-black/60 px-3 py-1 text-sm text-white">
                                        {{ currentImageIndex + 1 }} / {{ product.image_urls.length }}
                                    </div>
                                </div>

                                <!-- Thumbnails -->
                                <div v-if="product.image_urls.length > 1" class="grid grid-cols-5 gap-2">
                                    <button
                                        v-for="(imageUrl, index) in product.image_urls"
                                        :key="index"
                                        @click="selectImage(index)"
                                        class="aspect-square overflow-hidden rounded-lg border-2 transition-all"
                                        :class="currentImageIndex === index ? 'border-purple-600' : 'border-gray-200 hover:border-gray-400'"
                                    >
                                        <img
                                            :src="imageUrl"
                                            :alt="`${product.name} thumbnail ${index + 1}`"
                                            class="h-full w-full object-cover"
                                        />
                                    </button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Stock Transactions -->
                    <Card v-if="product.stockTransactions && product.stockTransactions.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <TrendingUp class="h-5 w-5" />
                                Stock Transaction History
                            </CardTitle>
                            <CardDescription>Recent stock adjustments for this product</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="text-left py-2 px-3">Date</th>
                                            <th class="text-left py-2 px-3">Operation</th>
                                            <th class="text-right py-2 px-3">Quantity</th>
                                            <th class="text-right py-2 px-3">Previous</th>
                                            <th class="text-right py-2 px-3">New</th>
                                            <th class="text-left py-2 px-3">Performed By</th>
                                            <th class="text-left py-2 px-3">Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="transaction in product.stockTransactions" :key="transaction.id" class="border-b">
                                            <td class="py-2 px-3">{{ formatDate(transaction.created_at) }}</td>
                                            <td class="py-2 px-3">
                                                <Badge :variant="transaction.operation === 'add' ? 'default' : 'destructive'">
                                                    {{ transaction.operation }}
                                                </Badge>
                                            </td>
                                            <td class="text-right py-2 px-3">{{ transaction.quantity }}</td>
                                            <td class="text-right py-2 px-3">{{ transaction.previous_stock }}</td>
                                            <td class="text-right py-2 px-3 font-semibold">{{ transaction.new_stock }}</td>
                                            <td class="py-2 px-3">
                                                {{ transaction.performedBy ? transaction.performedBy.name : 'System' }}
                                            </td>
                                            <td class="py-2 px-3 text-gray-600">{{ transaction.reason || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Quick Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Info</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <label class="text-xs text-gray-500">Created</label>
                                <p class="text-sm font-medium mt-1">{{ formatDate(product.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Last Updated</label>
                                <p class="text-sm font-medium mt-1">{{ formatDate(product.updated_at) }}</p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Product ID</label>
                                <p class="text-sm font-medium mt-1">#{{ product.id }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

