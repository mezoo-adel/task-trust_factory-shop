<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import Pagination from '@/components/Pagination.vue';
import adminRoutes from '@/routes/admin';
import { ShoppingCart, Search, Eye, ArrowLeft } from 'lucide-vue-next';
import type { Order, PaginatedData } from '@/types/models';

interface Props {
    orders: PaginatedData<Order>;
    filters: {
        search?: string;
        status?: string;
        sort_by?: string;
        sort_order?: string;
    };
    statuses: string[];
}

const props = defineProps<Props>();

const filters = ref({ ...props.filters });

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        paid: 'bg-green-100 text-green-800',
        processing: 'bg-blue-100 text-blue-800',
        shipped: 'bg-purple-100 text-purple-800',
        delivered: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const applyFilters = () => {
    router.get(adminRoutes.orders.index.url(), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout>
    <Head title="Orders - Admin" />

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
                        <h1 class="text-2xl font-bold text-gray-900">Orders</h1>
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
                            placeholder="Search orders..."
                            class="flex-1"
                            @keyup.enter="applyFilters"
                        />
                        <Button @click="applyFilters">Apply</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Orders Grid -->
            <div v-if="orders.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <Card v-for="order in orders.data" :key="order.id" class="hover:shadow-lg transition-shadow">
                    <CardContent class="p-6">
                        <div class="flex flex-col h-full">
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold">Order #{{ order.uuid.substring(0, 10) }}</h3>
                                        <p class="text-sm text-gray-500 mt-1">{{ formatDate(order.created_at) }}</p>
                                    </div>
                                    <Badge :class="getStatusColor(order.status)">
                                        {{ order.status }}
                                    </Badge>
                                </div>
                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Items:</span>
                                        <span class="font-medium">{{ order.items?.length || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Total:</span>
                                        <span class="font-bold text-lg">{{ formatPrice(order.total) }}</span>
                                    </div>
                                </div>
                            </div>
                            <Button as-child variant="outline" class="w-full">
                                <Link :href="adminRoutes.orders.show.url({ order: order.id })">
                                    <Eye class="mr-2 h-4 w-4" />
                                    View Details
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <ShoppingCart class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <h2 class="text-xl font-semibold text-gray-900 mb-2">No orders found</h2>
                <p class="text-gray-600">Try adjusting your filters</p>
            </div>

            <!-- Pagination -->
            <Pagination :data="orders" item-name="orders" />
        </div>
    </div>
    </AdminLayout>
</template>

