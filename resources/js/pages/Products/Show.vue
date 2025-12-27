<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/components/ui/toast/use-toast';
import { ShoppingCart, Minus, Plus, Package, Sparkles, ArrowLeft, Heart } from 'lucide-vue-next';

interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    stock_quantity: number;
    stock_threshold: number;
    is_active: boolean;
}

interface Props {
    product: Product;
}

const props = defineProps<Props>();
const { toast } = useToast();

const quantity = ref(1);
const isAddingToCart = ref(false);

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const incrementQuantity = () => {
    if (quantity.value < props.product.stock_quantity) {
        quantity.value++;
    }
};

const decrementQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const addToCart = () => {
    isAddingToCart.value = true;

    router.post('/cart/add', {
        product_id: props.product.id,
        quantity: quantity.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: 'Added to cart',
                description: `${quantity.value} × ${props.product.name} added to your cart`,
            });
            quantity.value = 1;
        },
        onError: (errors) => {
            toast({
                title: 'Error',
                description: errors.message || 'Failed to add item to cart',
                variant: 'destructive',
            });
        },
        onFinish: () => {
            isAddingToCart.value = false;
        },
    });
};

const isLowStock = props.product.stock_quantity <= props.product.stock_threshold;
</script>

<template>
    <Head :title="`${product.name} - Trust Factory Shop`" />

    <PublicLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="container mx-auto px-4 py-8">
                <!-- Back Button -->
                <Button as-child variant="ghost" class="mb-6">
                    <Link href="/products">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Products
                    </Link>
                </Button>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Product Image -->
                    <div class="bg-white rounded-lg shadow-sm p-8">
                        <div class="aspect-square bg-gradient-to-br from-purple-100 to-pink-100 rounded-lg flex items-center justify-center">
                            <Sparkles class="w-32 h-32 text-purple-400" />
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="space-y-6">
                        <div>
                            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                                {{ product.name }}
                            </h1>
                            <p class="text-2xl font-bold text-purple-600">
                                {{ formatPrice(product.price) }}
                            </p>
                        </div>

                        <!-- Stock Status -->
                        <Card>
                            <CardContent class="p-4">
                                <div class="flex items-center gap-2">
                                    <Package class="w-5 h-5" :class="product.stock_quantity > 0 ? 'text-green-600' : 'text-red-600'" />
                                    <span v-if="product.stock_quantity > 0" class="font-medium">
                                        <span :class="isLowStock ? 'text-orange-600' : 'text-green-600'">
                                            {{ product.stock_quantity }} in stock
                                        </span>
                                        <span v-if="isLowStock" class="text-orange-600 ml-2">
                                            (Limited availability)
                                        </span>
                                    </span>
                                    <span v-else class="font-medium text-red-600">Out of Stock</span>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Description -->
                        <div>
                            <h2 class="text-xl font-semibold mb-3">Description</h2>
                            <p class="text-gray-600 leading-relaxed">
                                {{ product.description }}
                            </p>
                        </div>

                        <!-- Add to Cart Section -->
                        <Card v-if="product.stock_quantity > 0">
                            <CardContent class="p-6 space-y-4">
                                <div>
                                    <Label for="quantity" class="mb-2 block">Quantity</Label>
                                    <div class="flex items-center gap-3">
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            @click="decrementQuantity"
                                            :disabled="quantity <= 1"
                                        >
                                            <Minus class="w-4 h-4" />
                                        </Button>
                                        <Input
                                            id="quantity"
                                            v-model.number="quantity"
                                            type="number"
                                            min="1"
                                            :max="product.stock_quantity"
                                            class="w-20 text-center"
                                        />
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            @click="incrementQuantity"
                                            :disabled="quantity >= product.stock_quantity"
                                        >
                                            <Plus class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </div>

                                <div class="flex gap-3">
                                    <Button
                                        class="flex-1"
                                        size="lg"
                                        @click="addToCart"
                                        :disabled="isAddingToCart"
                                    >
                                        <ShoppingCart class="w-5 h-5 mr-2" />
                                        {{ isAddingToCart ? 'Adding...' : 'Add to Cart' }}
                                    </Button>
                                    <Button variant="outline" size="lg">
                                        <Heart class="w-5 h-5" />
                                    </Button>
                                </div>

                                <p class="text-sm text-gray-600 text-center">
                                    Total: <span class="font-bold text-purple-600">{{ formatPrice(product.price * quantity) }}</span>
                                </p>
                            </CardContent>
                        </Card>

                        <!-- Out of Stock Message -->
                        <Card v-else>
                            <CardContent class="p-6 text-center">
                                <p class="text-gray-600 mb-4">This product is currently out of stock.</p>
                                <Button variant="outline" disabled>
                                    Notify When Available
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
