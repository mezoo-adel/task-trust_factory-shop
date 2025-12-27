<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import ProductCard from '@/components/ProductCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Search } from 'lucide-vue-next';
import type { Product, ProductFilters } from '@/types/models';

interface Props {
    products: Product[];
    filters?: ProductFilters;
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort || 'name');

const applyFilters = () => {
    router.get('/products', {
        search: searchQuery.value,
        sort: sortBy.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    sortBy.value = 'name';
    router.get('/products');
};
</script>

<template>
    <Head title="Products - Trust Factory Shop" />

    <PublicLayout>
        <div class="min-h-screen bg-gray-50">
            <!-- Page Header -->
            <section class="bg-white border-b">
                <div class="container mx-auto px-4 py-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                        Our Products
                    </h1>
                    <p class="text-gray-600">
                        Explore our complete collection of premium cosmetics
                    </p>
                </div>
            </section>

            <div class="container mx-auto px-4 py-8">
                <!-- Filters Section -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1">
                            <Label for="search" class="sr-only">Search products</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                <Input
                                    id="search"
                                    v-model="searchQuery"
                                    placeholder="Search products..."
                                    class="pl-10"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="w-full md:w-48">
                            <select
                                v-model="sortBy"
                                @change="applyFilters"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            >
                                <option value="name">Name (A-Z)</option>
                                <option value="price_asc">Price (Low to High)</option>
                                <option value="price_desc">Price (High to Low)</option>
                                <option value="newest">Newest First</option>
                            </select>
                        </div>

                        <!-- Filter Buttons -->
                        <div class="flex gap-2">
                            <Button @click="applyFilters">
                                Apply
                            </Button>
                            <Button variant="outline" @click="clearFilters">
                                Clear
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div v-if="products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <ProductCard 
                        v-for="product in products" 
                        :key="product.id" 
                        :product="product"
                        :button-href="`/products/${product.id}`"
                    />
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <Search class="w-12 h-12 text-gray-400" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No products found</h3>
                    <p class="text-gray-600 mb-6">Try adjusting your search or filters</p>
                    <Button @click="clearFilters">Clear Filters</Button>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
