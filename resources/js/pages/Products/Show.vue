<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useToast } from '@/components/ui/toast/use-toast';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Heart,
    Minus,
    Plus,
    ShoppingCart,
    Sparkles,
} from 'lucide-vue-next';
import { ref } from 'vue';

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
const quantityError = ref<string>('');

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const incrementQuantity = () => {
    if (quantity.value < props.product.stock_quantity) {
        quantity.value++;
        quantityError.value = '';
    }
};

const decrementQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
        quantityError.value = '';
    }
};

const validateQuantity = (): boolean => {
    if (
        quantity.value === null ||
        quantity.value === undefined ||
        quantity.value === ''
    ) {
        quantityError.value = 'Quantity is required';
        return false;
    }

    const qty = Number(quantity.value);

    if (isNaN(qty)) {
        quantityError.value = 'Quantity must be a valid number';
        return false;
    }

    if (qty < 1) {
        quantityError.value = 'Quantity must be at least 1';
        return false;
    }

    if (qty > props.product.stock_quantity) {
        quantityError.value = `Quantity cannot exceed available stock (${props.product.stock_quantity})`;
        return false;
    }

    quantityError.value = '';
    return true;
};

const addToCart = () => {
    // Reset error
    quantityError.value = '';

    // Validate quantity
    if (!validateQuantity()) {
        toast({
            title: 'Error',
            description: quantityError.value || 'Please enter a valid quantity',
            variant: 'destructive',
        });
        return;
    }

    isAddingToCart.value = true;

    router.post(
        '/cart/add',
        {
            product_id: props.product.id,
            quantity: Number(quantity.value),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    title: 'Added to cart',
                    description: `${quantity.value} × ${props.product.name} added to your cart`,
                });
                quantity.value = 1;
                quantityError.value = '';
            },
            onError: (errors) => {
                toast({
                    title: 'Error',
                    description:
                        errors.message ||
                        errors.quantity?.[0] ||
                        'Failed to add item to cart',
                    variant: 'destructive',
                });
            },
            onFinish: () => {
                isAddingToCart.value = false;
            },
        },
    );
};

const isLowStock =
    props.product.stock_quantity <= props.product.stock_threshold;
</script>

<template>
    <Head :title="`${product.name} - Trust Factory Shop`" />

    <PublicLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="container mx-auto px-4 py-8">
                <!-- Back Button -->
                <Button as-child variant="ghost" class="mb-6">
                    <Link href="/products">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to Products
                    </Link>
                </Button>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Product Image -->
                    <div class="rounded-lg bg-white p-8 shadow-sm">
                        <div
                            class="flex aspect-square items-center justify-center rounded-lg bg-gradient-to-br from-purple-100 to-pink-100"
                        >
                            <Sparkles class="h-32 w-32 text-purple-400" />
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="space-y-6">
                        <div>
                            <h1
                                class="mb-2 text-3xl font-bold text-gray-900 md:text-4xl"
                            >
                                {{ product.name }}
                            </h1>
                            <p class="text-2xl font-bold text-purple-600">
                                {{ formatPrice(product.price) }}
                            </p>
                        </div>

                        <!-- Stock Status -->
                        <!-- <Card>
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
                        </Card> -->

                        <!-- Description -->
                        <div>
                            <h2 class="mb-3 text-xl font-semibold">
                                Description
                            </h2>
                            <p class="leading-relaxed text-gray-600">
                                {{ product.description }}
                            </p>
                        </div>

                        <!-- Add to Cart Section -->
                        <Card v-if="product.stock_quantity > 0">
                            <CardContent class="space-y-4 p-6">
                                <div class="flex flex-wrap items-end gap-3">
                                    <div
                                        id="quantity-section"
                                        class="w-full sm:w-fit"
                                    >
                                        <Label for="quantity" class="mb-2 block"
                                            >Quantity</Label
                                        >
                                        <div class="flex items-center gap-3">
                                            <Button
                                                variant="outline"
                                                size="icon"
                                                @click="decrementQuantity"
                                                :disabled="quantity <= 1"
                                            >
                                                <Minus class="h-4 w-4" />
                                            </Button>
                                            <div class="">
                                                <Input
                                                    id="quantity"
                                                    v-model.number="quantity"
                                                    type="number"
                                                    min="1"
                                                    required
                                                    :max="
                                                        product.stock_quantity
                                                    "
                                                    class="w-full text-center"
                                                    @blur="validateQuantity"
                                                    @input="quantityError = ''"
                                                />
                                                <InputError
                                                    v-if="quantityError"
                                                    :message="quantityError"
                                                />
                                            </div>
                                            <Button
                                                variant="outline"
                                                size="icon"
                                                @click="incrementQuantity"
                                                :disabled="
                                                    quantity >=
                                                    product.stock_quantity
                                                "
                                            >
                                                <Plus class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>

                                    <div
                                        id="add-to-cart-section"
                                        class="flex flex-1 gap-3"
                                    >
                                        <Button
                                            class="flex-1"
                                            size="lg"
                                            @click="addToCart"
                                            :disabled="isAddingToCart"
                                        >
                                            <ShoppingCart
                                                class="mr-2 h-5 w-5"
                                            />
                                            {{
                                                isAddingToCart
                                                    ? 'Adding...'
                                                    : 'Add to Cart'
                                            }}
                                        </Button>
                                        <Button variant="outline" size="lg">
                                            <Heart class="h-5 w-5" />
                                        </Button>
                                    </div>
                                </div>

                                <p class="text-center text-sm text-gray-600">
                                    Total:
                                    <span class="font-bold text-purple-600">{{
                                        formatPrice(product.price * quantity)
                                    }}</span>
                                </p>
                            </CardContent>
                        </Card>

                        <!-- Out of Stock Message -->
                        <Card v-else>
                            <CardContent class="p-6 text-center">
                                <p class="mb-4 text-gray-600">
                                    This product is currently out of stock.
                                </p>
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
