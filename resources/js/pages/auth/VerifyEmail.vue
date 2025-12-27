<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <PublicLayout>
        <Head title="Email verification" />

        <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
            <div class="w-full max-w-md">
                <Card>
                    <CardHeader class="space-y-1 text-center">
                        <CardTitle class="text-2xl font-bold">Verify email</CardTitle>
                        <CardDescription>Please verify your email address by clicking on the link we just emailed to you.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mb-4 text-center text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to the email address you
                            provided during registration.
                        </div>

                        <Form
                            v-bind="send.form()"
                            class="space-y-6 text-center"
                            v-slot="{ processing }"
                        >
                            <Button :disabled="processing" variant="secondary" class="w-full">
                                <Spinner v-if="processing" />
                                Resend verification email
                            </Button>

                            <TextLink
                                :href="logout()"
                                as="button"
                                class="mx-auto block text-sm"
                            >
                                Log out
                            </TextLink>
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </PublicLayout>
</template>
