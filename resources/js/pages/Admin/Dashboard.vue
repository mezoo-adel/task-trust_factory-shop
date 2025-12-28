<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    ShoppingCart,
    DollarSign,
    Users,
    Package,
    AlertTriangle,
    TrendingUp,
} from 'lucide-vue-next';

interface Props {
    stats: {
        today_orders: number;
        today_revenue: number;
        week_orders: number;
        month_orders: number;
        total_revenue: number;
        total_users: number;
        total_products: number;
        low_stock_count: number;
    };
    orders_by_status: {
        paid: number;
        processing: number;
        shipped: number;
        delivered: number;
        cancelled: number;
    };
    recent_orders: Array<{
        id: number;
        uuid: string;
        user_name: string;
        user_email: string;
        status: string;
        total: number;
        items_count: number;
        created_at: string;
    }>;
    low_stock_products: Array<{
        id: number;
        name: string;
        stock_quantity: number;
        stock_threshold: number;
        price: number;
    }>;
}

const props = defineProps<Props>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
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
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white border-b">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
                    <div class="flex gap-2">
                        <Button as-child variant="outline">
                            <Link href="/">View Store</Link>
                        </Button>
                        <Button as-child variant="outline">
                            <Link :href="route('admin.logout')" method="post" as="button">
                                Logout
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Today's Orders</CardTitle>
                        <ShoppingCart class="h-4 w-4 text-gray-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.today_orders }}</div>
                        <p class="text-xs text-gray-600 mt-1">
                            {{ stats.week_orders }} this week
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Today's Revenue</CardTitle>
                        <DollarSign class="h-4 w-4 text-gray-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatPrice(stats.today_revenue) }}</div>
                        <p class="text-xs text-gray-600 mt-1">
                            {{ formatPrice(stats.total_revenue) }} total
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Users</CardTitle>
                        <Users class="h-4 w-4 text-gray-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_users }}</div>
                        <p class="text-xs text-gray-600 mt-1">Registered customers</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Low Stock Alert</CardTitle>
                        <AlertTriangle class="h-4 w-4 text-orange-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-orange-600">
                            {{ stats.low_stock_count }}
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Products need restocking</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Orders by Status -->
            <Card class="mb-8">
                <CardHeader>
                    <CardTitle>Orders by Status</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">
                                {{ orders_by_status.paid }}
                            </div>
                            <div class="text-sm text-gray-600">Paid</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">
                                {{ orders_by_status.processing }}
                            </div>
                            <div class="text-sm text-gray-600">Processing</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">
                                {{ orders_by_status.shipped }}
                            </div>
                            <div class="text-sm text-gray-600">Shipped</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">
                                {{ orders_by_status.delivered }}
                            </div>
                            <div class="text-sm text-gray-600">Delivered</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600">
                                {{ orders_by_status.cancelled }}
                            </div>
                            <div class="text-sm text-gray-600">Cancelled</div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Orders -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Orders</CardTitle>
                        <CardDescription>Latest 10 orders</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="order in recent_orders"
                                :key="order.id"
                                class="flex items-center justify-between border-b pb-3 last:border-0"
                            >
                                <div class="flex-1">
                                    <div class="font-medium">{{ order.user_name }}</div>
                                    <div class="text-sm text-gray-600">
                                        #{{ order.uuid.substring(0, 8) }} • {{ order.created_at }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold">{{ formatPrice(order.total) }}</div>
                                    <Badge :class="getStatusColor(order.status)" class="text-xs mt-1">
                                        {{ order.status }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                        <Button as-child variant="outline" class="w-full mt-4">
                            <Link :href="route('admin.orders.index')">View All Orders</Link>
                        </Button>
                    </CardContent>
                </Card>

                <!-- Low Stock Products -->
                <Card>
                    <CardHeader>
                        <CardTitle>Low Stock Products</CardTitle>
                        <CardDescription>Products that need restocking</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="product in low_stock_products"
                                :key="product.id"
                                class="flex items-center justify-between border-b pb-3 last:border-0"
                            >
                                <div class="flex-1">
                                    <div class="font-medium">{{ product.name }}</div>
                                    <div class="text-sm text-gray-600">
                                        {{ formatPrice(product.price) }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-orange-600 font-semibold">
                                        {{ product.stock_quantity }} left
                                    </div>
                                    <div class="text-xs text-gray-600">
                                        Threshold: {{ product.stock_threshold }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <Button as-child variant="outline" class="w-full mt-4">
                            <Link :href="route('admin.stock.index')">Manage Stock</Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
                <Button as-child size="lg" class="h-auto py-4">
                    <Link :href="route('admin.products.index')" class="flex flex-col items-center gap-2">
                        <Package class="h-6 w-6" />
                        <span>Manage Products</span>
                    </Link>
                </Button>
                <Button as-child size="lg" variant="outline" class="h-auto py-4">
                    <Link :href="route('admin.orders.index')" class="flex flex-col items-center gap-2">
                        <ShoppingCart class="h-6 w-6" />
                        <span>View Orders</span>
                    </Link>
                </Button>
                <Button as-child size="lg" variant="outline" class="h-auto py-4">
                    <Link :href="route('admin.stock.index')" class="flex flex-col items-center gap-2">
                        <TrendingUp class="h-6 w-6" />
                        <span>Stock Management</span>
                    </Link>
                </Button>
                <Button as-child size="lg" variant="outline" class="h-auto py-4">
                    <Link :href="route('admin.admins.index')" class="flex flex-col items-center gap-2">
                        <Users class="h-6 w-6" />
                        <span>Admin Users</span>
                    </Link>
                </Button>
            </div>
        </div>
    </div>
</template>

