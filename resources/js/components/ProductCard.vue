<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Sparkles } from 'lucide-vue-next';
import type { Product } from '@/types/models';

interface Props {
    product: Product;
    showStock?: boolean;
    buttonText?: string;
    buttonHref?: string;
    onButtonClick?: () => void;
}

const props = withDefaults(defineProps<Props>(), {
    showStock: true,
    buttonText: 'View Details',
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};
</script>

<template>
    <Card class="group hover:shadow-lg transition-shadow">
        <CardHeader class="p-0">
            <div class="aspect-square bg-gradient-to-br from-purple-100 to-pink-100 rounded-t-lg flex items-center justify-center">
                <Sparkles class="w-16 h-16 text-purple-400 group-hover:scale-110 transition-transform" />
            </div>
        </CardHeader>
        <CardContent class="p-4">
            <CardTitle class="text-lg mb-2 line-clamp-1">{{ product.name }}</CardTitle>
            <CardDescription class="line-clamp-2 mb-3">{{ product.description }}</CardDescription>
            <div class="flex items-center justify-between">
                <span class="text-2xl font-bold text-purple-600">{{ formatPrice(product.price) }}</span>
                <span v-if="showStock && product.stock_quantity > 0" class="text-sm text-green-600 font-medium">
                    {{ product.stock_quantity }} in stock
                </span>
                <span v-else-if="showStock" class="text-sm text-red-600 font-medium">Out of Stock</span>
            </div>
        </CardContent>
        <CardFooter class="p-4 pt-0">
            <Button 
                v-if="buttonHref"
                as-child 
                class="w-full cursor-pointer" 
                :disabled="product.stock_quantity === 0"
            >
                <Link :href="buttonHref">
                    {{ buttonText }}
                </Link>
            </Button>
            <Button 
                v-else-if="onButtonClick"
                class="w-full cursor-pointer" 
                :disabled="product.stock_quantity === 0"
                @click="onButtonClick"
            >
                {{ buttonText }}
            </Button>
        </CardFooter>
    </Card>
</template>

