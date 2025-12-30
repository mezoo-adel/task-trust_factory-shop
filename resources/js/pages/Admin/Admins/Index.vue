<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import AddAdminModal from '@/components/Admin/AddAdminModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import adminRoutes from '@/routes/admin';
import type { AdminUser } from '@/types/models';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Shield, Users } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    admins: AdminUser[];
}

const props = defineProps<Props>();

const isModalOpen = ref(false);

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <AdminLayout>
    <Head title="Admin Users - Admin" />

    <div class="min-h-screen bg-gray-50">
        <div class="border-b bg-white">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="adminRoutes.dashboard.url()">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back
                        </Link>
                    </Button>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Admin Users
                    </h1>
                    <div class="flex gap-2">
                        <Button @click="isModalOpen = true">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Admin
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <!-- Admins Grid -->
            <div
                v-if="admins.length > 0"
                class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <Card
                    v-for="admin in admins"
                    :key="admin.id"
                    class="transition-shadow hover:shadow-lg"
                >
                    <CardContent class="p-6">
                        <div class="flex h-full flex-col">
                            <div class="mb-4 flex items-start gap-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-blue-500"
                                >
                                    <Shield class="h-6 w-6 text-white" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="mb-1 text-lg font-semibold">
                                        {{ admin.name }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        {{ admin.email }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-auto border-t pt-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">
                                        Created
                                        {{ formatDate(admin.created_at) }}
                                    </span>
                                    <Badge variant="secondary">Admin</Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-else class="py-16 text-center">
                <Users class="mx-auto mb-4 h-16 w-16 text-gray-400" />
                <h2 class="mb-2 text-xl font-semibold text-gray-900">
                    No admin users
                </h2>
                <p class="mb-6 text-gray-600">
                    Create your first admin user to get started
                </p>
                <Button @click="isModalOpen = true">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Admin
                </Button>
            </div>
        </div>

        <!-- Add Admin Modal -->
        <AddAdminModal v-model:open="isModalOpen" />
    </div>
    </AdminLayout>
</template>
