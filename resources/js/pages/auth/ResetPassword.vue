<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { update } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    token: string;
    email: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <PublicLayout>
        <Head title="Reset password" />

        <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
            <div class="w-full max-w-md">
                <Card>
                    <CardHeader class="space-y-1 text-center">
                        <CardTitle class="text-2xl font-bold">Reset password</CardTitle>
                        <CardDescription>Please enter your new password below</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            v-bind="update.form()"
                            :transform="(data) => ({ ...data, token, email })"
                            :reset-on-success="['password', 'password_confirmation']"
                            v-slot="{ errors, processing }"
                        >
                            <div class="grid gap-6">
                                <div class="grid gap-2">
                                    <Label for="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        autocomplete="email"
                                        v-model="inputEmail"
                                        class="mt-1 block w-full"
                                        readonly
                                    />
                                    <InputError :message="errors.email" class="mt-2" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="password">Password</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        name="password"
                                        autocomplete="new-password"
                                        class="mt-1 block w-full"
                                        autofocus
                                        placeholder="Password"
                                    />
                                    <InputError :message="errors.password" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="password_confirmation">
                                        Confirm Password
                                    </Label>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        autocomplete="new-password"
                                        class="mt-1 block w-full"
                                        placeholder="Confirm password"
                                    />
                                    <InputError :message="errors.password_confirmation" />
                                </div>

                                <Button
                                    type="submit"
                                    class="mt-4 w-full"
                                    :disabled="processing"
                                    data-test="reset-password-button"
                                >
                                    <Spinner v-if="processing" />
                                    Reset password
                                </Button>
                            </div>
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </PublicLayout>
</template>
