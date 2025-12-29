<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useToast } from '@/components/ui/toast/use-toast';
import { ShoppingCart, Trash2, Plus, Minus, ArrowRight, ShoppingBag } from 'lucide-vue-next';

interface CartItem {
    id: number;
    product_id: number;
    quantity: number;
    discount: number;
    total: number;
    product: {
        id: number;
        name: string;
        price: number;
        stock_quantity: number;
    };
}

interface Cart {
    id: number;
    discount: number;
    tax: number;
    subtotal: number;
    total: number;
    items: CartItem[];
}

interface Props {
    cart: Cart | null;
}

const props = defineProps<Props>();
const { toast } = useToast();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const updateQuantity = (itemId: number, quantity: number) => {
    if (quantity < 1) return;

    router.put(`/cart/items/${itemId}`, { quantity }, {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: 'Cart updated',
                description: 'Item quantity has been updated',
            });
        },
    });
};

const removeItem = (itemId: number) => {
    router.delete(`/cart/items/${itemId}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: 'Item removed',
                description: 'Item has been removed from your cart',
            });
        },
    });
};

const clearCart = () => {
    if (confirm('Are you sure you want to clear your cart?')) {
        router.delete('/cart', {
            onSuccess: () => {
                toast({
                    title: 'Cart cleared',
                    description: 'All items have been removed from your cart',
                });
            },
        });
    }
};
</script>

<template>
    <Head title="Shopping Cart - Trust Factory Shop" />

    <PublicLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="container mx-auto px-4 py-8">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8">
                    Shopping Cart
                </h1>

                <!-- Empty Cart State -->
                <div v-if="!cart || cart.items.length === 0" class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <ShoppingBag class="w-12 h-12 text-gray-400" />
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-2">Your cart is empty</h2>
                    <p class="text-gray-600 mb-6">Add some products to get started</p>
                    <Button as-child size="lg">
                        <Link href="/products">
                            Continue Shopping
                        </Link>
                    </Button>
                </div>

                <!-- Cart with Items -->
                <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="flex justify-between items-center mb-4">
                            <p class="text-gray-600">{{ cart.items.length }} item(s) in cart</p>
                            <Button variant="ghost" size="sm" @click="clearCart">
                                Clear Cart
                            </Button>
                        </div>

                        <Card v-for="item in cart.items" :key="item.id">
                            <CardContent class="p-6">
                                <div class="flex gap-4">
                                    <!-- Product Image Placeholder -->
                                    <div class="w-24 h-24 bg-gradient-to-br from-purple-100 to-pink-100 rounded-lg flex-shrink-0" />

                                    <!-- Product Details -->
                                    <div class="flex-1 min-w-0">
                                        <Link :href="`/products/${item.product.slug}`" class="hover:text-purple-600">
                                            <h3 class="font-semibold text-lg mb-1 truncate">{{ item.product.name }}</h3>
                                        </Link>
                                        <p class="text-gray-600 mb-3">{{ formatPrice(item.product.price) }} each</p>

                                        <!-- Quantity Controls -->
                                        <div class="flex items-center gap-3">
                                            <Button
                                                variant="outline"
                                                size="icon"
                                                class="h-8 w-8"
                                                @click="updateQuantity(item.id, item.quantity - 1)"
                                                :disabled="item.quantity <= 1"
                                            >
                                                <Minus class="w-4 h-4" />
                                            </Button>
                                            <span class="w-12 text-center font-medium">{{ item.quantity }}</span>
                                            <Button
                                                variant="outline"
                                                size="icon"
                                                class="h-8 w-8"
                                                @click="updateQuantity(item.id, item.quantity + 1)"
                                                :disabled="item.quantity >= item.product.stock_quantity"
                                            >
                                                <Plus class="w-4 h-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50 ml-auto"
                                                @click="removeItem(item.id)"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </div>

                                        <!-- Stock Warning -->
                                        <p v-if="item.quantity >= item.product.stock_quantity" class="text-sm text-orange-600 mt-2">
                                            Maximum available quantity
                                        </p>
                                    </div>

                                    <!-- Item Total -->
                                    <div class="text-right">
                                        <p class="font-bold text-lg">{{ formatPrice(item.total) }}</p>
                                        <p v-if="item.discount > 0" class="text-sm text-green-600">
                                            -{{ formatPrice(item.discount) }} discount
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <Card class="sticky top-4">
                            <CardHeader>
                                <CardTitle>Order Summary</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal</span>
                                    <span>{{ formatPrice(cart.subtotal) }}</span>
                                </div>
                                <div v-if="cart.discount > 0" class="flex justify-between text-green-600">
                                    <span>Discount</span>
                                    <span>-{{ formatPrice(cart.discount) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Tax</span>
                                    <span>{{ formatPrice(cart.tax) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Shipping</span>
                                    <span class="text-green-600">Free</span>
                                </div>
                                <div class="border-t pt-3 flex justify-between font-bold text-lg">
                                    <span>Total</span>
                                    <span class="text-purple-600">{{ formatPrice(cart.total) }}</span>
                                </div>
                            </CardContent>
                            <CardFooter class="flex-col gap-3">
                                <Button as-child class="w-full" size="lg">
                                    <Link href="/checkout">
                                        Proceed to Checkout
                                        <ArrowRight class="w-5 h-5 ml-2" />
                                    </Link>
                                </Button>
                                <Button as-child variant="outline" class="w-full">
                                    <Link href="/products">
                                        Continue Shopping
                                    </Link>
                                </Button>
                            </CardFooter>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
