<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AddressCard from '@/components/AddressCard.vue';
import OrderDetailsCard from '@/components/OrderDetailsCard.vue';
import adminRoutes from '@/routes/admin';
import { ArrowLeft, User, Package, FileText } from 'lucide-vue-next';
import type { Order } from '@/types/models';

interface OrderUser {
    id: number;
    name: string;
    email: string;
}

interface OrderWithRelations extends Order {
    user: OrderUser;
}

interface Props {
    order: OrderWithRelations;
    statuses: string[];
}

const props = defineProps<Props>();

const statusForm = useForm({
    status: props.order.status,
});

const notesForm = useForm({
    notes: props.order.notes || '',
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
        hour: '2-digit',
        minute: '2-digit',
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

const updateStatus = () => {
    statusForm.patch(adminRoutes.orders.updateStatus.url({ order: props.order.id }), {
        preserveScroll: true,
        onSuccess: () => {
            // Status updated successfully
        },
    });
};

const updateNotes = () => {
    notesForm.patch(adminRoutes.orders.updateNotes.url({ order: props.order.id }), {
        preserveScroll: true,
        onSuccess: () => {
            // Notes updated successfully
        },
    });
};
</script>

<template>
    <Head :title="`Order #${order.uuid.substring(0, 8)} - Admin`" />

    <div class="min-h-screen bg-gray-50">
        <div class="border-b bg-white">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Button as-child variant="ghost" size="sm">
                            <Link :href="adminRoutes.orders.index.url()">
                                <ArrowLeft class="h-4 w-4 mr-2" />
                                Back to Orders
                            </Link>
                        </Button>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Order #{{ order.uuid.substring(0, 10) }}
                        </h1>
                    </div>
                    <Badge :class="getStatusColor(order.status)" class="text-base px-4 py-2">
                        {{ order.status }}
                    </Badge>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Customer Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <User class="h-5 w-5" />
                                Customer Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Name</label>
                                <p class="text-lg font-semibold mt-1">{{ order.user.name }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Email</label>
                                <p class="text-gray-700 mt-1">{{ order.user.email }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Order Items -->
                    <OrderDetailsCard :items="order.items" title="Order Items" />

                    <!-- Shipping Address -->
                    <AddressCard
                        v-if="order.address"
                        :address="order.address"
                        :show-label="false"
                        :show-default-badge="false"
                        title="Shipping Address"
                    />

                    <!-- Order Status Update -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Update Order Status</CardTitle>
                            <CardDescription>Change the current status of this order</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="updateStatus" class="space-y-4">
                                <div>
                                    <Label for="status">Status</Label>
                                    <select
                                        id="status"
                                        v-model="statusForm.status"
                                        class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option v-for="status in statuses" :key="status" :value="status">
                                            {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                                        </option>
                                    </select>
                                </div>
                                <Button type="submit" :disabled="statusForm.processing">
                                    {{ statusForm.processing ? 'Updating...' : 'Update Status' }}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    <!-- Order Notes -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FileText class="h-5 w-5" />
                                Order Notes
                            </CardTitle>
                            <CardDescription>Add internal notes about this order</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="updateNotes" class="space-y-4">
                                <div>
                                    <Label for="notes">Notes</Label>
                                    <textarea
                                        id="notes"
                                        v-model="notesForm.notes"
                                        rows="4"
                                        placeholder="Add notes about this order..."
                                        class="mt-1.5 flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    />
                                </div>
                                <Button type="submit" :disabled="notesForm.processing">
                                    {{ notesForm.processing ? 'Saving...' : 'Save Notes' }}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Order Summary -->
                    <Card class="sticky top-4">
                        <CardHeader>
                            <CardTitle>Order Summary</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="space-y-2">
                                <div>
                                    <label class="text-xs text-gray-500">Order Date</label>
                                    <p class="text-sm font-medium mt-1">{{ formatDate(order.created_at) }}</p>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Order ID</label>
                                    <p class="text-sm font-medium mt-1">#{{ order.uuid }}</p>
                                </div>
                            </div>
                            <div class="border-t pt-3 space-y-2">
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
                                <div class="border-t pt-3 flex justify-between font-bold text-lg">
                                    <span>Total</span>
                                    <span class="text-purple-600">{{ formatPrice(order.total) }}</span>
                                </div>
                            </div>
                            <div v-if="order.stripe_payment_intent_id" class="pt-3 border-t">
                                <label class="text-xs text-gray-500">Payment Intent</label>
                                <p class="text-xs font-mono text-gray-600 mt-1 break-all">
                                    {{ order.stripe_payment_intent_id }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>

