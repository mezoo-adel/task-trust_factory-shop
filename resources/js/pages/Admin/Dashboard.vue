<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import adminRoutes from '@/routes/admin';
import type {
    AdminDashboardStats,
    LowStockProduct,
    OrdersByStatus,
    RecentOrder,
} from '@/types/models';
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    DollarSign,
    Package,
    ShoppingCart,
    TrendingUp,
    Users,
} from 'lucide-vue-next';

interface Props {
    stats: AdminDashboardStats;
    orders_by_status: OrdersByStatus;
    recent_orders: RecentOrder[];
    low_stock_products: LowStockProduct[];
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

    <AdminLayout>
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-4">
                <Button as-child size="lg" class="h-auto py-4">
                    <Link
                        :href="adminRoutes.products.index.url()"
                        class="flex flex-col items-center gap-2"
                    >
                        <Package class="h-6 w-6" />
                        <span>Manage Products</span>
                    </Link>
                </Button>
                <Button as-child size="lg" class="h-auto py-4">
                    <Link
                        :href="adminRoutes.stock.index.url()"
                        class="flex flex-col items-center gap-2"
                    >
                        <TrendingUp class="h-6 w-6" />
                        <span>Stock Management</span>
                    </Link>
                </Button>
                <Button as-child size="lg" class="h-auto py-4">
                    <Link
                        :href="adminRoutes.orders.index.url()"
                        class="flex flex-col items-center gap-2"
                    >
                        <ShoppingCart class="h-6 w-6" />
                        <span>View Orders</span>
                    </Link>
                </Button>
                <Button as-child size="lg" class="h-auto py-4">
                    <Link
                        :href="adminRoutes.admins.index.url()"
                        class="flex flex-col items-center gap-2"
                    >
                        <Users class="h-6 w-6" />
                        <span>Admin Users</span>
                    </Link>
                </Button>
            </div>

            <!-- Stats Grid -->
            <div
                class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4"
            >
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Today's Orders</CardTitle
                        >
                        <ShoppingCart class="h-4 w-4 text-gray-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.today_orders }}
                        </div>
                        <p class="mt-1 text-xs text-gray-600">
                            {{ stats.week_orders }} this week
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Today's Revenue</CardTitle
                        >
                        <DollarSign class="h-4 w-4 text-gray-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ formatPrice(stats.today_revenue) }}
                        </div>
                        <p class="mt-1 text-xs text-gray-600">
                            {{ formatPrice(stats.total_revenue) }} total
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Users</CardTitle
                        >
                        <Users class="h-4 w-4 text-gray-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.total_users }}
                        </div>
                        <p class="mt-1 text-xs text-gray-600">
                            Registered customers
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Low Stock Alert</CardTitle
                        >
                        <AlertTriangle class="h-4 w-4 text-orange-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-orange-600">
                            {{ stats.low_stock_count }}
                        </div>
                        <p class="mt-1 text-xs text-gray-600">
                            Products need restocking
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Orders by Status -->
            <Card class="mb-8">
                <CardHeader>
                    <CardTitle>Orders by Status</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
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

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
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
                                    <div class="font-medium">
                                        {{ order.user_name }}
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        #{{ order.uuid.substring(0, 8) }} •
                                        {{ order.created_at }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold">
                                        {{ formatPrice(order.total) }}
                                    </div>
                                    <Badge
                                        :class="getStatusColor(order.status)"
                                        class="mt-1 text-xs"
                                    >
                                        {{ order.status }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                        <Button as-child variant="outline" class="mt-4 w-full">
                            <Link :href="adminRoutes.orders.index.url()"
                                >View All Orders</Link
                            >
                        </Button>
                    </CardContent>
                </Card>

                <!-- Low Stock Products -->
                <Card>
                    <CardHeader>
                        <CardTitle>Low Stock Products</CardTitle>
                        <CardDescription
                            >Products that need restocking</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="product in low_stock_products"
                                :key="product.id"
                                class="flex items-center justify-between border-b pb-3 last:border-0"
                            >
                                <div class="flex-1">
                                    <div class="font-medium">
                                        {{ product.name }}
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        {{ formatPrice(product.price) }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-orange-600">
                                        {{ product.stock_quantity }} left
                                    </div>
                                    <div class="text-xs text-gray-600">
                                        Threshold: {{ product.stock_threshold }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <Button as-child variant="outline" class="mt-4 w-full">
                            <Link :href="adminRoutes.stock.index.url()"
                                >Manage Stock</Link
                            >
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
