<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Package } from 'lucide-vue-next';
import type { OrderItem } from '@/types/models';

interface Props {
    items: OrderItem[];
    title?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'Order Items',
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="flex items-center gap-2">
                <Package class="w-5 h-5" />
                {{ title }}
            </CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
            <div 
                v-for="item in items" 
                :key="item.id" 
                class="flex items-center gap-4 pb-4 border-b last:border-0 last:pb-0"
            >
                <div class="w-20 h-20 bg-gradient-to-br from-purple-100 to-pink-100 rounded-lg flex-shrink-0" />
                <div class="flex-1 min-w-0">
                    <Link :href="`/products/${item.product_id}`" class="hover:text-purple-600">
                        <p class="font-semibold truncate">{{ item.product.name }}</p>
                    </Link>
                    <p class="text-sm text-gray-600">
                        {{ formatPrice(item.price) }} × {{ item.quantity }}
                    </p>
                    <p v-if="item.discount > 0" class="text-sm text-green-600">
                        Discount: -{{ formatPrice(item.discount) }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="font-bold">{{ formatPrice(item.total) }}</p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>



