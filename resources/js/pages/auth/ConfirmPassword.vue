<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { store } from '@/routes/password/confirm';
import { Form, Head } from '@inertiajs/vue3';
</script>

<template>
    <PublicLayout>
        <Head title="Confirm password" />

        <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
            <div class="w-full max-w-md">
                <Card>
                    <CardHeader class="space-y-1 text-center">
                        <CardTitle class="text-2xl font-bold">Confirm your password</CardTitle>
                        <CardDescription>This is a secure area of the application. Please confirm your password before continuing.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            v-bind="store.form()"
                            reset-on-success
                            v-slot="{ errors, processing }"
                        >
                            <div class="space-y-6">
                                <div class="grid gap-2">
                                    <Label htmlFor="password">Password</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        name="password"
                                        class="mt-1 block w-full"
                                        required
                                        autocomplete="current-password"
                                        autofocus
                                    />

                                    <InputError :message="errors.password" />
                                </div>

                                <div class="flex items-center">
                                    <Button
                                        class="w-full"
                                        :disabled="processing"
                                        data-test="confirm-password-button"
                                    >
                                        <Spinner v-if="processing" />
                                        Confirm Password
                                    </Button>
                                </div>
                            </div>
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </PublicLayout>
</template>
