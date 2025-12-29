<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/toast';
import { ShoppingCart, User } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const cartItemCount = computed(() => page.props.cartItemCount || 0);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation Header -->
        <header class="bg-white border-b sticky top-0 z-50">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg" />
                        <span class="text-xl font-bold text-gray-900">Trust Factory</span>
                    </Link>

                    <!-- Desktop Navigation -->
                    <nav class="hidden md:flex items-center gap-6">
                        <Link href="/" class="text-gray-600 hover:text-gray-900 font-medium">
                            Home
                        </Link>
                        <Link href="/products" class="text-gray-600 hover:text-gray-900 font-medium">
                            Products
                        </Link>
                    </nav>

                    <!-- Right Side Actions -->
                    <div class="flex items-center gap-4">
                        <!-- Cart -->
                        <Link href="/cart" class="relative">
                            <Button variant="ghost" size="icon">
                                <ShoppingCart class="w-5 h-5" />
                                <span v-if="cartItemCount > 0" class="absolute -top-1 -right-1 bg-purple-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                    {{ cartItemCount }}
                                </span>
                            </Button>
                        </Link>

                        <!-- User Menu -->
                        <div v-if="auth.user">
                            <Link href="/orders">
                                <Button variant="ghost">
                                    My Orders
                                </Button>
                            </Link>
                            <Link href="/profile">
                                <Button variant="ghost" size="icon">
                                    <User class="w-5 h-5" />
                                </Button>
                            </Link>
                        </div>
                        <div v-else class="flex items-center gap-2">
                            <Link href="/login">
                                <Button variant="ghost">
                                    Log in
                                </Button>
                            </Link>
                            <Link href="/register">
                                <Button>
                                    Register
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gradient-to-br from-pink-50 via-purple-50 to-blue-50 border-t border-purple-100 py-12 mt-16">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="font-bold mb-4">Trust Factory Shop</h3>
                        <p class="text-sm text-gray-700">Premium cosmetics for your natural beauty.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Shop</h4>
                        <ul class="space-y-2 text-sm">
                            <li><Link href="/products" class="text-gray-700 hover:text-gray-900 transition-colors">All Products</Link></li>
                            <li><Link href="/cart" class="text-gray-700 hover:text-gray-900 transition-colors">Shopping Cart</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Account</h4>
                        <ul class="space-y-2 text-sm">
                            <template v-if="auth.user">
                                <li><Link href="/orders" class="text-gray-700 hover:text-gray-900 transition-colors">My Orders</Link></li>
                                <li><Link href="/dashboard" class="text-gray-700 hover:text-gray-900 transition-colors">Dashboard</Link></li>
                            </template>
                            <template v-else>
                                <li><Link href="/login" class="text-gray-700 hover:text-gray-900 transition-colors">Login</Link></li>
                                <li><Link href="/register" class="text-gray-700 hover:text-gray-900 transition-colors">Register</Link></li>
                            </template>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Support</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="text-gray-700 hover:text-gray-900 transition-colors">Contact Us</a></li>
                            <li><a href="#" class="text-gray-700 hover:text-gray-900 transition-colors">FAQ</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-purple-200 mt-8 pt-8 text-center text-sm">
                    <p class="text-gray-700">&copy; {{ new Date().getFullYear() }} Trust Factory Shop. All rights reserved.</p>
                </div>
            </div>
        </footer>

        <!-- Toast Notifications -->
        <Toaster />
    </div>
</template>
