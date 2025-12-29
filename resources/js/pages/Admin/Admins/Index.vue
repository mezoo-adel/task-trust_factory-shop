<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import adminRoutes from '@/routes/admin';
import { Users, Plus, Shield } from 'lucide-vue-next';
import type { AdminUser } from '@/types/models';

interface Props {
    admins: AdminUser[];
}

const props = defineProps<Props>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Admin Users - Admin" />

    <div class="min-h-screen bg-gray-50">
        <div class="border-b bg-white">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Admin Users</h1>
                    <div class="flex gap-2">
                        <Button as-child>
                            <Link :href="adminRoutes.admins.store.url()">
                                <Plus class="mr-2 h-4 w-4" />
                                Add Admin
                            </Link>
                        </Button>
                        <Button as-child variant="outline">
                            <Link :href="adminRoutes.dashboard.url()">Dashboard</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <!-- Admins Grid -->
            <div v-if="admins.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card v-for="admin in admins" :key="admin.id" class="hover:shadow-lg transition-shadow">
                    <CardContent class="p-6">
                        <div class="flex flex-col h-full">
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                                    <Shield class="w-6 h-6 text-white" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold mb-1">{{ admin.name }}</h3>
                                    <p class="text-sm text-gray-600">{{ admin.email }}</p>
                                </div>
                            </div>
                            <div class="mt-auto pt-4 border-t">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">
                                        Created {{ formatDate(admin.created_at) }}
                                    </span>
                                    <Badge variant="secondary">Admin</Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <Users class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <h2 class="text-xl font-semibold text-gray-900 mb-2">No admin users</h2>
                <p class="text-gray-600 mb-6">Create your first admin user to get started</p>
                <Button as-child>
                    <Link :href="adminRoutes.admins.store.url()">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Admin
                    </Link>
                </Button>
            </div>
        </div>
    </div>
</template>

