<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import adminRoutes from '@/routes/admin';
import type { PaginatedData, Product } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Edit, Eye, Package, Plus, Sparkles } from 'lucide-vue-next';
import { ref } from 'vue';

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
    router.get(
        adminRoutes.products.index.url(),
        {
            search: filters.value.search,
            stock_filter: filters.value.stock_filter,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const clearFilters = () => {
    filters.value = {};
    router.get(
        adminRoutes.products.index.url(),
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <Head title="Products - Admin" />

    <AdminLayout>
        <div class="border-b bg-white">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="adminRoutes.dashboard.url()">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back
                        </Link>
                    </Button>
                    <h1 class="text-2xl font-bold text-gray-900">Products</h1>
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
                        <Button variant="outline" @click="clearFilters"
                            >Clear</Button
                        >
                    </div>
                </CardContent>
            </Card>

            <!-- Products Grid -->
            <div
                v-if="products.data.length > 0"
                class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <Card
                    v-for="product in products.data"
                    :key="product.id"
                    class="overflow-hidden transition-shadow hover:shadow-lg"
                >
                    <div class="aspect-video overflow-hidden bg-gray-100">
                        <img
                            v-if="
                                product.image_urls &&
                                product.image_urls.length > 0
                            "
                            :src="product.image_urls[0]"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-purple-100 to-pink-100"
                        >
                            <Sparkles class="h-12 w-12 text-purple-400" />
                        </div>
                    </div>
                    <CardContent class="pt-4">
                        <div class="flex h-full flex-col">
                            <div class="flex-1">
                                <h3 class="mb-2 text-lg font-semibold">
                                    {{ product.name }}
                                </h3>
                                <p
                                    class="mb-4 line-clamp-2 text-sm text-gray-600"
                                >
                                    {{ product.description }}
                                </p>
                                <div
                                    class="mb-4 flex items-center justify-between"
                                >
                                    <span
                                        class="text-xl font-bold text-purple-600"
                                        >{{ formatPrice(product.price) }}</span
                                    >
                                    <Badge
                                        :variant="
                                            product.stock_quantity >
                                            product.stock_threshold
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{ product.stock_quantity }} in stock
                                    </Badge>
                                </div>
                            </div>
                            <div class="flex gap-2 border-t pt-4">
                                <Button as-child size="sm" class="flex-1">
                                    <Link
                                        :href="
                                            adminRoutes.products.show.url({
                                                product: product.id,
                                            })
                                        "
                                    >
                                        <Eye class="mr-2 h-4 w-4" />
                                        Show
                                    </Link>
                                </Button>

                                <Button
                                    as-child
                                    variant="outline"
                                    size="sm"
                                    class="flex-1"
                                >
                                    <Link
                                        :href="
                                            adminRoutes.products.edit.url({
                                                product: product.id,
                                            })
                                        "
                                    >
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
            <div v-else class="py-16 text-center">
                <Package class="mx-auto mb-4 h-16 w-16 text-gray-400" />
                <h2 class="mb-2 text-xl font-semibold text-gray-900">
                    No products found
                </h2>
                <p class="mb-6 text-gray-600">
                    Try adjusting your filters or add a new product
                </p>
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
    </AdminLayout>
</template>
