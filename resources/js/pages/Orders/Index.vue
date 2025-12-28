<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { ShoppingBag, Package, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import type { Order } from '@/types/models';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedOrders {
    data: Order[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: PaginationLink[];
}

interface Props {
    orders: PaginatedOrders;
    auth: {
        user: any;
    };
}

const props = defineProps<Props>();

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
        pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        paid: 'bg-green-100 text-green-800 border-green-200',
        processing: 'bg-blue-100 text-blue-800 border-blue-200',
        shipped: 'bg-purple-100 text-purple-800 border-purple-200',
        delivered: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        cancelled: 'bg-red-100 text-red-800 border-red-200',
    };
    return colors[status] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const getStatusLabel = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const goToPage = (url: string | null) => {
    if (url) {
        router.visit(url, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="My Orders - Trust Factory Shop" />

    <PublicLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="container mx-auto px-4 py-8">
                <div class="mb-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                        My Orders
                    </h1>
                    <p class="text-gray-600">View and track your order history</p>
                </div>

                <!-- No Orders State -->
                <div v-if="orders.data.length === 0" class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <ShoppingBag class="w-12 h-12 text-gray-400" />
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-2">No orders yet</h2>
                    <p class="text-gray-600 mb-6">Start shopping to see your orders here</p>
                    <Button as-child size="lg">
                        <Link href="/products">
                            Browse Products
                        </Link>
                    </Button>
                </div>

                <!-- Orders List - Compact Design -->
                <div v-else class="space-y-4">
                    <div class="grid gap-4 lg:grid-cols-2">
                        <Card 
                            v-for="order in orders.data" 
                            :key="order.id"
                            class="hover:shadow-md transition-shadow"
                        >
                            <CardContent class="p-4">
                                <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                                    <!-- Order Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between mb-3">
                                            <div>
                                                <h3 class="font-semibold text-gray-900 mb-1">
                                                    Order #{{ order.uuid.slice(0, 10) +"..." }}
                                                </h3>
                                                <p class="text-sm text-gray-500">
                                                    {{ formatDate(order.created_at) }}
                                                </p>
                                            </div>
                                            <Badge 
                                                :class="getStatusColor(order.status)"
                                                class="ml-2 border"
                                            >
                                                {{ getStatusLabel(order.status) }}
                                            </Badge>
                                        </div>

                                        <!-- Compact Items List -->
                                        <div class="space-y-2">
                                            <div 
                                                v-for="item in order.items.slice(0, 2)" 
                                                :key="item.id"
                                                class="flex items-center gap-3 text-sm"
                                            >
                                                <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-pink-100 rounded flex-shrink-0" />
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium text-gray-900 truncate">
                                                        {{ item.product.name }}
                                                    </p>
                                                    <p class="text-gray-500">
                                                        Qty: {{ item.quantity }} × {{ formatPrice(item.price) }}
                                                    </p>
                                                </div>
                                                <p class="font-medium text-gray-900">
                                                    {{ formatPrice(item.total) }}
                                                </p>
                                            </div>
                                            
                                            <!-- Show more items indicator -->
                                            <p 
                                                v-if="order.items.length > 2" 
                                                class="text-sm text-gray-500 pl-15"
                                            >
                                                + {{ order.items.length - 2 }} more item{{ order.items.length - 2 > 1 ? 's' : '' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Total & Actions -->
                                    <div class="flex lg:flex-col items-center lg:items-end justify-between lg:justify-center gap-4 border-t lg:border-t-0 lg:border-l pt-4 lg:pt-0 lg:pl-4">
                                        <div class="text-left lg:text-right">
                                            <p class="text-sm text-gray-600 mb-1">Total</p>
                                            <p class="text-2xl font-bold text-purple-600">
                                                {{ formatPrice(order.total) }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ order.items.length }} item{{ order.items.length > 1 ? 's' : '' }}
                                            </p>
                                        </div>
                                        
                                        <Button 
                                            as-child 
                                            size="sm"
                                            class="cursor-pointer"
                                        >
                                            <Link :href="`/orders/${order.uuid}`">
                                                <Package class="w-4 h-4 mr-2" />
                                                View Details
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <!-- Pagination -->
                    <div v-if="orders.last_page > 1" class="flex items-center justify-between mt-8 pt-6 border-t">
                        <div class="text-sm text-gray-600">
                            Showing {{ orders.from }} to {{ orders.to }} of {{ orders.total }} orders
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <!-- Previous Button -->
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="orders.current_page === 1"
                                @click="goToPage(orders.links[0]?.url)"
                            >
                                <ChevronLeft class="w-4 h-4" />
                                Previous
                            </Button>

                            <!-- Page Numbers -->
                            <div class="hidden sm:flex items-center gap-1">
                                <template v-for="(link, index) in orders.links.slice(1, -1)" :key="index">
                                    <Button
                                        v-if="link.label !== '...'"
                                        :variant="link.active ? 'default' : 'outline'"
                                        size="sm"
                                        class="min-w-[2.5rem]"
                                        @click="goToPage(link.url)"
                                    >
                                        {{ link.label }}
                                    </Button>
                                    <span v-else class="px-2 text-gray-400">...</span>
                                </template>
                            </div>

                            <!-- Current Page (mobile) -->
                            <div class="sm:hidden text-sm text-gray-600">
                                Page {{ orders.current_page }} of {{ orders.last_page }}
                            </div>

                            <!-- Next Button -->
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="orders.current_page === orders.last_page"
                                @click="goToPage(orders.links[orders.links.length - 1]?.url)"
                            >
                                Next
                                <ChevronRight class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
