<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Toaster } from '@/components/ui/toast';
import { Link, router, usePage } from '@inertiajs/vue3';
import { LogOut, ShoppingCart, User } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const cartItemCount = computed(() => page.props.cartItemCount || 0);
const systemInfo = computed(
    () =>
        page.props.systemInfo || {
            name: 'Trust Factory',
            description: 'Premium cosmetics for your natural beauty.',
            icon_url: null,
        },
);

const handleLogout = () => {
    router.post('/logout');
};

const isAdmin = computed(() => auth.value.user && auth.value.user?.is_admin);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation Header -->
        <header class="sticky top-0 z-50 border-b bg-white">
            <div class="container mx-auto px-4">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center gap-2">
                        <img
                            v-if="systemInfo.icon_url"
                            :src="systemInfo.icon_url"
                            :alt="systemInfo.name"
                            class="h-8 w-8 rounded-lg object-cover"
                        />
                        <div
                            v-else
                            class="h-8 w-8 rounded-lg bg-gradient-to-br from-purple-600 to-pink-600"
                        />
                        <span class="text-xl font-bold text-gray-900">{{
                            systemInfo.name
                        }}</span>
                    </Link>

                    <!-- Desktop Navigation -->
                    <nav class="hidden items-center gap-6 md:flex">
                        <Link
                            href="/"
                            class="font-medium text-gray-600 hover:text-gray-900"
                        >
                            Home
                        </Link>
                        <Link
                            href="/products"
                            class="font-medium text-gray-600 hover:text-gray-900"
                        >
                            Products
                        </Link>
                    </nav>

                    <!-- Right Side Actions -->
                    <div class="flex items-center gap-4">
                        <!-- Cart -->
                        <Link href="/cart" class="relative">
                            <Button variant="ghost" size="icon">
                                <ShoppingCart class="h-5 w-5" />
                                <span
                                    v-if="cartItemCount > 0"
                                    class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-purple-600 text-xs text-white"
                                >
                                    {{ cartItemCount }}
                                </span>
                            </Button>
                        </Link>

                        <!-- User Menu -->
                        <div v-if="auth.user" class="flex items-center gap-2">
                            <Link href="/orders">
                                <Button variant="ghost"> My Orders </Button>
                            </Link>

                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon">
                                        <User class="h-5 w-5" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56">
                                    <DropdownMenuLabel>
                                        <div class="flex flex-col space-y-1">
                                            <p class="text-sm font-medium">
                                                {{ auth.user.name }}
                                            </p>
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                {{ auth.user.email }}
                                            </p>
                                        </div>
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem as-child>
                                        <Link
                                            :href="isAdmin? '/admin/dashboard' : '/profile'"
                                            class="cursor-pointer"
                                        >
                                            <User class="mr-2 h-4 w-4" />
                                            <span>{{ isAdmin? 'Dashboard' : 'Profile' }}</span>
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        @click="handleLogout"
                                        class="cursor-pointer text-red-600"
                                    >
                                        <LogOut class="mr-2 h-4 w-4" />
                                        <span>Log out</span>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <div v-else class="flex items-center gap-2">
                            <Link href="/login">
                                <Button variant="ghost"> Log in </Button>
                            </Link>
                            <Link href="/register">
                                <Button> Register </Button>
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
        <footer
            class="mt-16 border-t border-purple-100 bg-gradient-to-br from-pink-50 via-purple-50 to-blue-50 py-12"
        >
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                    <div>
                        <h3 class="mb-4 font-bold">{{ systemInfo.name }}</h3>
                        <p class="text-sm text-gray-700">
                            {{ systemInfo.description }}
                        </p>
                    </div>
                    <div>
                        <h4 class="mb-4 font-semibold">Shop</h4>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <Link
                                    href="/products"
                                    class="text-gray-700 transition-colors hover:text-gray-900"
                                    >All Products</Link
                                >
                            </li>
                            <li>
                                <Link
                                    href="/cart"
                                    class="text-gray-700 transition-colors hover:text-gray-900"
                                    >Shopping Cart</Link
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="mb-4 font-semibold">Account</h4>
                        <ul class="space-y-2 text-sm">
                            <template v-if="auth.user">
                                <li>
                                    <Link
                                        href="/profile"
                                        class="text-gray-700 transition-colors hover:text-gray-900"
                                        >Profile</Link
                                    >
                                </li>
                                <li>
                                    <Link
                                        href="/orders"
                                        class="text-gray-700 transition-colors hover:text-gray-900"
                                        >My Orders</Link
                                    >
                                </li>
                            </template>
                            <template v-else>
                                <li>
                                    <Link
                                        href="/login"
                                        class="text-gray-700 transition-colors hover:text-gray-900"
                                        >Login</Link
                                    >
                                </li>
                                <li>
                                    <Link
                                        href="/register"
                                        class="text-gray-700 transition-colors hover:text-gray-900"
                                        >Register</Link
                                    >
                                </li>
                            </template>
                        </ul>
                    </div>
                    <div>
                        <h4 class="mb-4 font-semibold">Support</h4>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <a
                                    href="#"
                                    class="text-gray-700 transition-colors hover:text-gray-900"
                                    >Contact Us</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="text-gray-700 transition-colors hover:text-gray-900"
                                    >FAQ</a
                                >
                            </li>
                        </ul>
                    </div>
                </div>
                <div
                    class="mt-8 border-t border-purple-200 pt-8 text-center text-sm"
                >
                    <p class="text-gray-700">
                        &copy; {{ new Date().getFullYear() }}
                        {{ systemInfo.name }}. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>

        <!-- Toast Notifications -->
        <Toaster />
    </div>
</template>
