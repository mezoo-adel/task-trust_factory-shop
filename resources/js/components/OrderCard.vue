<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Calendar, Package, CreditCard } from 'lucide-vue-next';
import type { Order } from '@/types/models';

interface Props {
    order: Order;
    showActions?: boolean;
}

withDefaults(defineProps<Props>(), {
    showActions: true,
});

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
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-gray-100 text-gray-800',
        paid: 'bg-green-100 text-green-800',
        processing: 'bg-blue-100 text-blue-800',
        shipped: 'bg-purple-100 text-purple-800',
        delivered: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};
</script>

<template>
    <Card class="overflow-hidden">
        <CardHeader class="bg-gray-50 border-b">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <CardTitle class="text-lg mb-1">Order #{{ order.uuid.slice(0, 8) }}</CardTitle>
                    <CardDescription class="flex items-center gap-2">
                        <Calendar class="w-4 h-4" />
                        {{ formatDate(order.created_at) }}
                    </CardDescription>
                </div>
                <div class="flex items-center gap-4">
                    <Badge :class="getStatusColor(order.status)">
                        {{ getStatusLabel(order.status) }}
                    </Badge>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Total</p>
                        <p class="text-lg font-bold text-purple-600">{{ formatPrice(order.total) }}</p>
                    </div>
                </div>
            </div>
        </CardHeader>

        <CardContent class="p-6">
            <!-- Order Items -->
            <div class="space-y-4 mb-6">
                <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-pink-100 rounded-lg flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                        <Link :href="`/products/${item.product_id}`" class="hover:text-purple-600">
                            <p class="font-medium truncate">{{ item.product.name }}</p>
                        </Link>
                        <p class="text-sm text-gray-600">
                            {{ formatPrice(item.price) }} × {{ item.quantity }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold">{{ formatPrice(item.total) }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="border-t pt-4 space-y-2">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Subtotal</span>
                    <span>{{ formatPrice(order.subtotal) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Tax</span>
                    <span>{{ formatPrice(order.tax) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Shipping</span>
                    <span>{{ order.shipping > 0 ? formatPrice(order.shipping) : 'Free' }}</span>
                </div>
                <div class="flex justify-between font-bold text-lg pt-2 border-t">
                    <span>Total</span>
                    <span class="text-purple-600">{{ formatPrice(order.total) }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div v-if="showActions" class="mt-6 flex gap-3">
                <Button as-child variant="outline" class="flex-1 cursor-pointer">
                    <Link :href="`/orders/${order.uuid}`">
                        <Package class="w-4 h-4 mr-2" />
                        View Details
                    </Link>
                </Button>
                <Button v-if="order.status === 'delivered'" variant="outline" class="flex-1 cursor-pointer">
                    <CreditCard class="w-4 h-4 mr-2" />
                    Reorder
                </Button>
            </div>
        </CardContent>
    </Card>
</template>

