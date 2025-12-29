<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import Pagination from '@/components/Pagination.vue';
import adminRoutes from '@/routes/admin';
import { TrendingUp, Search, Package, ArrowLeft } from 'lucide-vue-next';
import type { Product, PaginatedData } from '@/types/models';

interface Props {
    products: PaginatedData<Product>;
    filters: {
        filter?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const filters = ref({ ...props.filters });

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const applyFilters = () => {
    router.get(adminRoutes.stock.index.url(), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Stock Management - Admin" />

    <div class="min-h-screen bg-gray-50">
        <div class="border-b bg-white">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Button as-child variant="ghost" size="sm">
                            <Link :href="adminRoutes.dashboard.url()">
                                <ArrowLeft class="h-4 w-4 mr-2" />
                                Back
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold text-gray-900">Stock Management</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Filters</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex gap-4">
                        <Input
                            v-model="filters.search"
                            placeholder="Search products..."
                            class="flex-1"
                            @keyup.enter="applyFilters"
                        />
                        <Button @click="applyFilters">Apply</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Products Grid -->
            <div v-if="products.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <Card v-for="product in products.data" :key="product.id" class="hover:shadow-lg transition-shadow">
                    <CardContent class="p-6">
                        <div class="flex flex-col h-full">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold mb-2">{{ product.name }}</h3>
                                <div class="space-y-3 mb-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Price:</span>
                                        <span class="font-semibold">{{ formatPrice(product.price) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Current Stock:</span>
                                        <Badge :variant="product.stock_quantity > product.stock_threshold ? 'default' : 'destructive'">
                                            {{ product.stock_quantity }}
                                        </Badge>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Threshold:</span>
                                        <span class="text-sm font-medium">{{ product.stock_threshold }}</span>
                                    </div>
                                    <div v-if="product.stock_quantity <= product.stock_threshold" class="bg-orange-50 border border-orange-200 rounded p-2">
                                        <p class="text-xs text-orange-800 font-medium">⚠️ Low Stock Alert</p>
                                    </div>
                                </div>
                            </div>
                            <Button variant="outline" class="w-full">
                                Adjust Stock
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <Package class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <h2 class="text-xl font-semibold text-gray-900 mb-2">No products found</h2>
                <p class="text-gray-600">Try adjusting your filters</p>
            </div>

            <!-- Pagination -->
            <Pagination :data="products" item-name="products" />
        </div>
    </div>
</template>

