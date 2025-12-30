<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Heart, ShoppingCart, Sparkles, Star } from 'lucide-vue-next';

interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    stock_quantity: number;
    is_active: boolean;
}

interface Props {
    featuredProducts: Product[];
}

defineProps<Props>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};
</script>

<template>
    <Head title="Home - Trust Factory Shop" />

    <PublicLayout>
        <div class="min-h-screen">
            <!-- Hero Section -->
            <section
                class="relative bg-gradient-to-br from-pink-50 via-purple-50 to-blue-50 py-20 md:py-32"
            >
                <div class="container mx-auto px-4">
                    <div class="mx-auto max-w-4xl text-center">
                        <div
                            class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 shadow-sm backdrop-blur-sm"
                        >
                            <Sparkles class="h-4 w-4 text-purple-600" />
                            <span class="text-sm font-medium text-purple-900"
                                >Premium Cosmetics Collection</span
                            >
                        </div>

                        <h1
                            class="mb-6 text-4xl font-bold text-gray-900 md:text-6xl"
                        >
                            Discover Your Natural
                            <span
                                class="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent"
                            >
                                Beauty
                            </span>
                        </h1>

                        <p
                            class="mx-auto mb-8 max-w-2xl text-lg text-gray-600 md:text-xl"
                        >
                            Elevate your skincare routine with our curated
                            collection of premium cosmetics. Natural
                            ingredients, proven results.
                        </p>

                        <div
                            class="flex flex-col justify-center gap-4 sm:flex-row"
                        >
                            <Button as-child size="lg" class="text-base">
                                <Link href="/products">
                                    <ShoppingCart class="mr-2 h-5 w-5" />
                                    Shop Now
                                </Link>
                            </Button>
                            <Button
                                as-child
                                variant="outline"
                                size="lg"
                                class="text-base"
                            >
                                <Link href="/products"> Learn More </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="bg-white py-16">
                <div class="container mx-auto px-4">
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <div class="text-center">
                            <div
                                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-purple-100"
                            >
                                <Sparkles class="h-8 w-8 text-purple-600" />
                            </div>
                            <h3 class="mb-2 text-xl font-semibold">
                                Natural Ingredients
                            </h3>
                            <p class="text-gray-600">
                                100% natural and organic ingredients for healthy
                                skin
                            </p>
                        </div>

                        <div class="text-center">
                            <div
                                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-pink-100"
                            >
                                <Heart class="h-8 w-8 text-pink-600" />
                            </div>
                            <h3 class="mb-2 text-xl font-semibold">
                                Cruelty Free
                            </h3>
                            <p class="text-gray-600">
                                Never tested on animals, always ethical
                            </p>
                        </div>

                        <div class="text-center">
                            <div
                                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100"
                            >
                                <Star class="h-8 w-8 text-blue-600" />
                            </div>
                            <h3 class="mb-2 text-xl font-semibold">
                                Premium Quality
                            </h3>
                            <p class="text-gray-600">
                                Dermatologist-tested and approved formulas
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Featured Products Section -->
            <section class="bg-gray-50 py-16">
                <div class="container mx-auto px-4">
                    <div class="mb-12 text-center">
                        <h2
                            class="mb-4 text-3xl font-bold text-gray-900 md:text-4xl"
                        >
                            Featured Products
                        </h2>
                        <p class="text-lg text-gray-600">
                            Discover our best-selling cosmetics loved by
                            thousands
                        </p>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <Card
                            v-for="product in featuredProducts"
                            :key="product.id"
                            class="group transition-shadow hover:shadow-lg"
                        >
                            <CardHeader class="p-0">
                                <div
                                    class="flex aspect-square items-center justify-center rounded-t-lg bg-gradient-to-br from-purple-100 to-pink-100"
                                >
                                    <Sparkles
                                        class="h-16 w-16 text-purple-400 transition-transform group-hover:scale-110"
                                    />
                                </div>
                            </CardHeader>
                            <CardContent class="p-4">
                                <CardTitle class="mb-2 line-clamp-1 text-lg">{{
                                    product.name
                                }}</CardTitle>
                                <CardDescription class="mb-3 line-clamp-2">{{
                                    product.description
                                }}</CardDescription>
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-2xl font-bold text-purple-600"
                                        >{{ formatPrice(product.price) }}</span
                                    >
                                    <span
                                        v-if="product.stock_quantity > 0"
                                        class="text-sm text-green-600"
                                        >In Stock</span
                                    >
                                    <span v-else class="text-sm text-red-600"
                                        >Out of Stock</span
                                    >
                                </div>
                            </CardContent>
                            <CardFooter class="p-4 pt-0">
                                <Button
                                    as-child
                                    class="w-full"
                                    :disabled="product.stock_quantity === 0"
                                >
                                    <Link :href="`/products/${product.slug}`">
                                        View Details
                                    </Link>
                                </Button>
                            </CardFooter>
                        </Card>
                    </div>

                    <div class="mt-12 text-center">
                        <Button as-child variant="outline" size="lg">
                            <Link href="/products"> View All Products </Link>
                        </Button>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="bg-gradient-to-r from-purple-500 to-pink-500 py-20">
                <div class="container mx-auto px-4 text-center">
                    <h2 class="mb-4 text-3xl font-bold text-white md:text-4xl">
                        Ready to Transform Your Skin?
                    </h2>
                    <p class="mx-auto mb-8 max-w-2xl text-lg text-purple-100">
                        Join thousands of satisfied customers and start your
                        journey to radiant, healthy skin today.
                    </p>
                    <Button as-child size="lg" variant="secondary">
                        <Link href="/products"> Start Shopping </Link>
                    </Button>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
