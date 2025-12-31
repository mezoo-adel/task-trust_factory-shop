<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/AdminLayout.vue';
import adminRoutes from '@/routes/admin';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Upload } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    settings: {
        name: string;
        description: string;
        icon_url?: string;
        mail_from_name?: string;
        mail_from_address?: string;
    };
}

const props = defineProps<Props>();

const form = useForm({
    app_name: props.settings.name,
    app_description: props.settings.description,
    app_icon: null as File | null,
    mail_from_name: props.settings.mail_from_name || '',
    mail_from_address: props.settings.mail_from_address || '',
});

const iconPreview = ref<string | null>(props.settings.icon_url || null);

const handleIconChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.app_icon = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            iconPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post('/admin/settings', {
        preserveScroll: true,
        onSuccess: () => {
            form.clearErrors();
        },
    });
};
</script>

<template>
    <Head title="Settings - Admin" />

    <AdminLayout>
        <div class="border-b bg-white">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Button as-child variant="ghost" size="sm">
                            <Link :href="adminRoutes.dashboard.url()">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back
                            </Link>
                        </Button>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                Settings
                            </h1>
                            <p class="text-sm text-gray-600">
                                Manage your application settings and branding
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <div class="mx-auto max-w-4xl space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>System Information</CardTitle>
                        <CardDescription>
                            Update your application name, description, and icon
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- App Name -->
                            <div class="space-y-2">
                                <Label for="app_name">Application Name</Label>
                                <Input
                                    id="app_name"
                                    v-model="form.app_name"
                                    type="text"
                                    placeholder="Trust Factory Shop"
                                    :disabled="form.processing"
                                />
                                <p
                                    v-if="form.errors.app_name"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.app_name }}
                                </p>
                            </div>

                            <!-- App Description -->
                            <div class="space-y-2">
                                <Label for="app_description"
                                    >Application Description</Label
                                >
                                <Textarea
                                    id="app_description"
                                    v-model="form.app_description"
                                    placeholder="Premium cosmetics for your natural beauty."
                                    rows="3"
                                    :disabled="form.processing"
                                />
                                <p
                                    v-if="form.errors.app_description"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.app_description }}
                                </p>
                            </div>

                            <!-- App Icon -->
                            <div class="space-y-2">
                                <Label for="app_icon">Application Icon</Label>
                                <div class="flex items-center gap-4">
                                    <div
                                        v-if="iconPreview"
                                        class="h-16 w-16 overflow-hidden rounded-lg border"
                                    >
                                        <img
                                            :src="iconPreview"
                                            alt="App Icon"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div
                                        v-else
                                        class="flex h-16 w-16 items-center justify-center rounded-lg bg-gradient-to-br from-purple-600 to-pink-600"
                                    >
                                        <span
                                            class="text-2xl font-bold text-white"
                                            >TF</span
                                        >
                                    </div>
                                    <div class="flex-1">
                                        <input
                                            id="app_icon"
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="handleIconChange"
                                            :disabled="form.processing"
                                        />
                                        <label for="app_icon">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                as="span"
                                                :disabled="form.processing"
                                            >
                                                <Upload class="mr-2 h-4 w-4" />
                                                Upload Icon
                                            </Button>
                                        </label>
                                        <p class="mt-1 text-sm text-gray-500">
                                            PNG, JPG or GIF (max. 2MB)
                                        </p>
                                    </div>
                                </div>
                                <p
                                    v-if="form.errors.app_icon"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.app_icon }}
                                </p>
                            </div>

                            <!-- Mail From Name -->
                            <div class="grid gap-2 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="mail_from_name"
                                        >Email Sender Name</Label
                                    >
                                    <Input
                                        id="mail_from_name"
                                        v-model="form.mail_from_name"
                                        type="text"
                                        placeholder="Cosmo Shop"
                                        :disabled="form.processing"
                                    />
                                    <p class="text-sm text-gray-500">
                                        This name will appear as the sender
                                    </p>
                                    <p
                                        v-if="form.errors.mail_from_name"
                                        class="text-sm text-red-600"
                                    >
                                        {{ form.errors.mail_from_name }}
                                    </p>
                                </div>

                                <!-- Mail From Address -->
                                <div class="space-y-2">
                                    <Label for="mail_from_address"
                                        >Email Sender Address</Label
                                    >
                                    <Input
                                        id="mail_from_address"
                                        v-model="form.mail_from_address"
                                        type="email"
                                        placeholder="noreply@cosmoshop.com"
                                        :disabled="form.processing"
                                    />
                                    <p class="text-sm text-gray-500">
                                        This address will appear as the sender
                                    </p>
                                    <p
                                        v-if="form.errors.mail_from_address"
                                        class="text-sm text-red-600"
                                    >
                                        {{ form.errors.mail_from_address }}
                                    </p>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                >
                                    {{
                                        form.processing
                                            ? 'Saving...'
                                            : 'Save Changes'
                                    }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
