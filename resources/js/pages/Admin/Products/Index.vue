<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import Pagination from '@/components/Pagination.vue';
import adminRoutes from '@/routes/admin';
import { Package, Plus, Search, Edit, ArrowLeft, Eye, Sparkles } from 'lucide-vue-next';
import type { Product, PaginatedData } from '@/types/models';

interface Props {
    products: PaginatedData<Product>;
    filters: {
        search?: string;
        stock_filter?: string;
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
    router.get(adminRoutes.products.index.url(), {
        search: filters.value.search,
        stock_filter: filters.value.stock_filter,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {};
    router.get(adminRoutes.products.index.url(), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Products - Admin" />

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
                        <h1 class="text-2xl font-bold text-gray-900">Products</h1>
                    </div>
                    <div class="flex gap-2">
                        <Button as-child>
                            <Link :href="adminRoutes.products.create.url()">
                                <Plus class="mr-2 h-4 w-4" />
                                Add Product
                            </Link>
                        </Button>
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
                        <div class="flex-1">
                            <Input
                                v-model="filters.search"
                                placeholder="Search products..."
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <Button @click="applyFilters">Apply</Button>
                        <Button variant="outline" @click="clearFilters">Clear</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Products Grid -->
            <div v-if="products.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <Card v-for="product in products.data" :key="product.id" class="hover:shadow-lg transition-shadow overflow-hidden">
                    <div class="aspect-video bg-gray-100 overflow-hidden">
                        <img
                            v-if="product.image_urls && product.image_urls.length > 0"
                            :src="product.image_urls[0]"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full bg-gradient-to-br from-purple-100 to-pink-100 flex items-center justify-center"
                        >
                            <Sparkles class="w-12 h-12 text-purple-400" />
                        </div>
                    </div>
                    <CardContent class="pt-4">
                        <div class="flex flex-col h-full">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold mb-2">{{ product.name }}</h3>
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ product.description }}</p>
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-xl font-bold text-purple-600">{{ formatPrice(product.price) }}</span>
                                    <Badge :variant="product.stock_quantity > product.stock_threshold ? 'default' : 'destructive'">
                                        {{ product.stock_quantity }} in stock
                                    </Badge>
                                </div>
                            </div>
                            <div class="flex gap-2 pt-4 border-t">
                                <Button as-child  size="sm" class="flex-1">
                                    <Link :href="adminRoutes.products.show.url({ product: product.id })">
                                        <Eye class="mr-2 h-4 w-4" />
                                        Show
                                    </Link>
                                </Button>

                                <Button as-child variant="outline" size="sm" class="flex-1">
                                    <Link :href="adminRoutes.products.edit.url({ product: product.id })">
                                        <Edit class="mr-2 h-4 w-4" />
                                        Edit
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <Package class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <h2 class="text-xl font-semibold text-gray-900 mb-2">No products found</h2>
                <p class="text-gray-600 mb-6">Try adjusting your filters or add a new product</p>
                <Button as-child>
                    <Link :href="adminRoutes.products.create.url()">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Product
                    </Link>
                </Button>
            </div>

            <!-- Pagination -->
            <Pagination :data="products" item-name="products" />
        </div>
    </div>
</template>

