<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';
import { Toaster } from '@/components/ui/toast';
import { useAdminNavigation } from '@/composables/useAdminNavigation';
import { Link, usePage } from '@inertiajs/vue3';
import { LogOut, Menu, Store } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const { navItems } = useAdminNavigation();

const isActive = (href: string) => {
    return page.url.startsWith(href);
};

const systemInfo = computed(() => page.props.systemInfo || {
    name: 'Trust Factory',
    description: 'Premium cosmetics for your natural beauty.',
    icon_url: null,
});
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Desktop Sidebar -->
        <aside class="fixed left-0 top-0 hidden h-screen w-64 border-r bg-white lg:block">
            <div class="flex h-full flex-col">
                <!-- Logo -->
                <div class="flex h-16 shrink-0 items-center border-b px-6">
                    <Link href="/admin/dashboard" class="flex items-center gap-2">
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
                        <span class="text-lg font-bold text-gray-900">Admin Panel</span>
                    </Link>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 space-y-1 overflow-y-auto p-4">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                            isActive(item.href)
                                ? 'bg-purple-50 text-purple-700'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900',
                        ]"
                    >
                        <component :is="item.icon" class="h-5 w-5" />
                        <span>{{ item.title }}</span>
                        <span
                            v-if="item.badge"
                            class="ml-auto rounded-full bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-700"
                        >
                            {{ item.badge }}
                        </span>
                    </Link>
                </nav>

                <Separator />

                <!-- User Section -->
                <div class="shrink-0 p-4">
                    <div class="flex items-center gap-3 rounded-lg bg-gray-50 p-3">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-600 text-sm font-semibold text-white"
                        >
                            {{ auth.user?.name?.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <p class="truncate text-sm font-medium text-gray-900">
                                {{ auth.user?.name }}
                            </p>
                            <p class="truncate text-xs text-gray-500">
                                {{ auth.user?.email }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <Link href="/" class="block">
                            <Button variant="ghost" size="sm" class="w-full justify-start">
                                <Store class="mr-2 h-4 w-4" />
                                View Store
                            </Button>
                        </Link>
                        <Link href="/logout" method="post" as="button" class="block w-full">
                            <Button
                                variant="ghost"
                                size="sm"
                                class="w-full justify-start text-red-600 hover:bg-red-50 hover:text-red-700"
                            >
                                <LogOut class="mr-2 h-4 w-4" />
                                Logout
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-1 flex-col lg:ml-64">
            <!-- Mobile Header -->
            <header class="sticky top-0 z-40 border-b bg-white lg:hidden">
                <div class="flex h-16 items-center justify-between px-4">
                    <Sheet>
                        <SheetTrigger as-child>
                            <Button variant="ghost" size="icon">
                                <Menu class="h-6 w-6" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-64 p-0">
                            <div class="flex h-full flex-col">
                                <!-- Mobile Logo -->
                                <div class="flex h-16 items-center border-b px-6">
                                    <Link
                                        href="/admin/dashboard"
                                        class="flex items-center gap-2"
                                    >
                                        <div
                                            class="h-8 w-8 rounded-lg bg-gradient-to-br from-purple-600 to-pink-600"
                                        />
                                        <span class="text-lg font-bold text-gray-900"
                                            >Admin Panel</span
                                        >
                                    </Link>
                                </div>

                                <!-- Mobile Navigation -->
                                <nav class="flex-1 space-y-1 p-4">
                                    <Link
                                        v-for="item in navItems"
                                        :key="item.href"
                                        :href="item.href"
                                        :class="[
                                            'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                            isActive(item.href)
                                                ? 'bg-purple-50 text-purple-700'
                                                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900',
                                        ]"
                                    >
                                        <component :is="item.icon" class="h-5 w-5" />
                                        <span>{{ item.title }}</span>
                                    </Link>
                                </nav>

                                <Separator />

                                <!-- Mobile User Section -->
                                <div class="p-4">
                                    <div class="space-y-1">
                                        <Link href="/" class="block">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="w-full justify-start"
                                            >
                                                <Store class="mr-2 h-4 w-4" />
                                                View Store
                                            </Button>
                                        </Link>
                                        <Link
                                            href="/logout"
                                            method="post"
                                            as="button"
                                            class="block w-full"
                                        >
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="w-full justify-start text-red-600 hover:bg-red-50 hover:text-red-700"
                                            >
                                                <LogOut class="mr-2 h-4 w-4" />
                                                Logout
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>

                    <Link href="/admin/dashboard" class="flex items-center gap-2">
                        <div
                            class="h-8 w-8 rounded-lg bg-gradient-to-br from-purple-600 to-pink-600"
                        />
                        <span class="text-lg font-bold text-gray-900">Admin</span>
                    </Link>

                    <div class="w-10" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 pt-4 px-6">
                <slot />
            </main>
        </div>

        <!-- Toast Notifications -->
        <Toaster />
    </div>
</template>
