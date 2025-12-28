<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import { ShieldCheck } from 'lucide-vue-next';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('admin.login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Admin Login" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-50 to-blue-50 p-4">
        <Card class="w-full max-w-md">
            <CardHeader class="space-y-1 text-center">
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-blue-600 rounded-full flex items-center justify-center">
                        <ShieldCheck class="w-8 h-8 text-white" />
                    </div>
                </div>
                <CardTitle class="text-2xl font-bold">Admin Portal</CardTitle>
                <CardDescription>
                    Sign in to access the admin dashboard
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="admin@example.com"
                            autocomplete="email"
                            required
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="flex items-center space-x-2">
                        <Checkbox
                            id="remember"
                            v-model:checked="form.remember"
                            :disabled="form.processing"
                        />
                        <Label
                            for="remember"
                            class="text-sm font-normal cursor-pointer"
                        >
                            Remember me
                        </Label>
                    </div>

                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Signing in...' : 'Sign In' }}
                    </Button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-600">
                    <a href="/" class="text-purple-600 hover:text-purple-700 font-medium">
                        ← Back to Store
                    </a>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

