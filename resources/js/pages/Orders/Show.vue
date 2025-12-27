<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import OrderDetailsCard from '@/components/OrderDetailsCard.vue';
import AddressCard from '@/components/AddressCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, CreditCard, Truck } from 'lucide-vue-next';
import type { Order } from '@/types/models';

interface Props {
    order: Order;
}

defineProps<Props>();

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
    <Head :title="`Order #${order.uuid.slice(0, 8)} - Trust Factory Shop`" />

    <PublicLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="container mx-auto px-4 py-8">
                <!-- Back Button -->
                <Button as-child variant="ghost" class="mb-6">
                    <Link href="/orders">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Orders
                    </Link>
                </Button>

                <!-- Order Header -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
                                Order #{{ order.uuid.slice(0, 8) }}
                            </h1>
                            <p class="text-gray-600">Placed on {{ formatDate(order.created_at) }}</p>
                        </div>
                        <Badge :class="getStatusColor(order.status)" class="text-base px-4 py-2">
                            {{ getStatusLabel(order.status) }}
                        </Badge>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Order Items & Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Items -->
                        <OrderDetailsCard :items="order.items" />

                        <!-- Shipping Address -->
                        <AddressCard 
                            v-if="order.address"
                            :address="order.address" 
                            :show-label="false"
                            :show-default-badge="false"
                        />

                        <!-- Payment Info -->
                        <!-- <Card v-if="order.stripe_payment_intent_id">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <CreditCard class="w-5 h-5" />
                                    Payment Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-sm text-gray-600">Payment ID</p>
                                <p class="font-mono text-sm">{{ order.stripe_payment_intent_id }}</p>
                            </CardContent>
                        </Card> -->

                        <!-- Order Notes -->
                        <Card v-if="order.notes">
                            <CardHeader>
                                <CardTitle>Order Notes</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-gray-600">{{ order.notes }}</p>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Order Summary Sidebar -->
                    <div class="lg:col-span-1">
                        <Card class="sticky top-4">
                            <CardHeader>
                                <CardTitle>Order Summary</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal</span>
                                    <span>{{ formatPrice(order.subtotal) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Tax</span>
                                    <span>{{ formatPrice(order.tax) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Shipping</span>
                                    <span>{{ order.shipping > 0 ? formatPrice(order.shipping) : 'Free' }}</span>
                                </div>
                                <div class="border-t pt-3 flex justify-between font-bold text-lg">
                                    <span>Total</span>
                                    <span class="text-purple-600">{{ formatPrice(order.total) }}</span>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Track Order Button -->
                        <Button v-if="order.status === 'shipped'" class="w-full mt-4" size="lg">
                            <Truck class="w-5 h-5 mr-2" />
                            Track Shipment
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
