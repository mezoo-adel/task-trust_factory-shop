<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import { User, Lock, Bell, MapPin } from 'lucide-vue-next';

interface Props {
    user: any;
    addresses: any[];
    notification_preferences: any[];
}

const props = defineProps<Props>();

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.patch(route('profile.password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

// Notification preferences
const notificationForm = useForm({
    preferences: props.notification_preferences.reduce((acc: any, pref: any) => {
        acc[pref.id] = pref.is_subscribed;
        return acc;
    }, {}),
});

const updateNotifications = () => {
    notificationForm.patch(route('profile.notifications.update'), {
        preserveScroll: true,
    });
};

// Address management
const showAddressForm = ref(false);
const editingAddress = ref<any>(null);

const addressForm = useForm({
    label: '',
    full_name: '',
    phone: '',
    address: '',
    is_default: false,
});

const openAddressForm = (address: any = null) => {
    if (address) {
        editingAddress.value = address;
        addressForm.label = address.label || '';
        addressForm.full_name = address.full_name;
        addressForm.phone = address.phone;
        addressForm.address = address.address;
        addressForm.is_default = address.is_default;
    } else {
        editingAddress.value = null;
        addressForm.reset();
    }
    showAddressForm.value = true;
};

const saveAddress = () => {
    if (editingAddress.value) {
        addressForm.patch(route('profile.addresses.update', editingAddress.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showAddressForm.value = false;
                addressForm.reset();
            },
        });
    } else {
        addressForm.post(route('profile.addresses.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showAddressForm.value = false;
                addressForm.reset();
            },
        });
    }
};

const deleteAddress = (addressId: number) => {
    if (confirm('Are you sure you want to delete this address?')) {
        useForm({}).delete(route('profile.addresses.destroy', addressId), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Profile Settings" />

    <PublicLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-8">Profile Settings</h1>

            <Tabs default-value="password" class="w-full">
                <TabsList class="grid w-full grid-cols-3">
                    <TabsTrigger value="password">
                        <Lock class="w-4 h-4 mr-2" />
                        Password
                    </TabsTrigger>
                    <TabsTrigger value="notifications">
                        <Bell class="w-4 h-4 mr-2" />
                        Notifications
                    </TabsTrigger>
                    <TabsTrigger value="addresses">
                        <MapPin class="w-4 h-4 mr-2" />
                        Addresses
                    </TabsTrigger>
                </TabsList>

                <!-- Password Tab -->
                <TabsContent value="password">
                    <Card>
                        <CardHeader>
                            <CardTitle>Change Password</CardTitle>
                            <CardDescription>
                                Update your password to keep your account secure
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="updatePassword" class="space-y-4">
                                <div>
                                    <Label for="current_password">Current Password</Label>
                                    <Input
                                        id="current_password"
                                        v-model="passwordForm.current_password"
                                        type="password"
                                        class="mt-1.5"
                                    />
                                    <InputError :message="passwordForm.errors.current_password" class="mt-1" />
                                </div>

                                <div>
                                    <Label for="password">New Password</Label>
                                    <Input
                                        id="password"
                                        v-model="passwordForm.password"
                                        type="password"
                                        class="mt-1.5"
                                    />
                                    <InputError :message="passwordForm.errors.password" class="mt-1" />
                                </div>

                                <div>
                                    <Label for="password_confirmation">Confirm New Password</Label>
                                    <Input
                                        id="password_confirmation"
                                        v-model="passwordForm.password_confirmation"
                                        type="password"
                                        class="mt-1.5"
                                    />
                                </div>

                                <Button type="submit" :disabled="passwordForm.processing">
                                    Update Password
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Notifications Tab -->
                <TabsContent value="notifications">
                    <Card>
                        <CardHeader>
                            <CardTitle>Notification Preferences</CardTitle>
                            <CardDescription>
                                Manage how you receive notifications
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="updateNotifications" class="space-y-4">
                                <div
                                    v-for="pref in notification_preferences"
                                    :key="pref.id"
                                    class="flex items-center space-x-3 border-b pb-3"
                                >
                                    <Checkbox
                                        :id="`pref-${pref.id}`"
                                        v-model:checked="notificationForm.preferences[pref.id]"
                                    />
                                    <div class="flex-1">
                                        <Label :for="`pref-${pref.id}`" class="font-medium cursor-pointer">
                                            {{ pref.name }}
                                        </Label>
                                        <p class="text-sm text-gray-600">{{ pref.description }}</p>
                                    </div>
                                </div>

                                <Button type="submit" :disabled="notificationForm.processing">
                                    Save Preferences
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Addresses Tab -->
                <TabsContent value="addresses">
                    <Card>
                        <CardHeader>
                            <CardTitle>Saved Addresses</CardTitle>
                            <CardDescription>
                                Manage your shipping addresses
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div
                                    v-for="address in addresses"
                                    :key="address.id"
                                    class="border rounded-lg p-4"
                                >
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-semibold">{{ address.full_name }}</p>
                                            <p class="text-sm text-gray-600">{{ address.phone }}</p>
                                            <p class="text-sm text-gray-600 mt-1">{{ address.address }}</p>
                                            <span
                                                v-if="address.is_default"
                                                class="inline-block mt-2 text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded"
                                            >
                                                Default
                                            </span>
                                        </div>
                                        <div class="flex gap-2">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                @click="openAddressForm(address)"
                                            >
                                                Edit
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="destructive"
                                                @click="deleteAddress(address.id)"
                                            >
                                                Delete
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <Button @click="openAddressForm()" class="w-full">
                                    + Add New Address
                                </Button>

                                <!-- Address Form Modal (simplified) -->
                                <div v-if="showAddressForm" class="border-t pt-4 mt-4">
                                    <h3 class="font-semibold mb-4">
                                        {{ editingAddress ? 'Edit Address' : 'Add New Address' }}
                                    </h3>
                                    <form @submit.prevent="saveAddress" class="space-y-4">
                                        <div>
                                            <Label for="full_name">Full Name</Label>
                                            <Input
                                                id="full_name"
                                                v-model="addressForm.full_name"
                                                class="mt-1.5"
                                            />
                                            <InputError :message="addressForm.errors.full_name" class="mt-1" />
                                        </div>

                                        <div>
                                            <Label for="phone">Phone</Label>
                                            <Input
                                                id="phone"
                                                v-model="addressForm.phone"
                                                class="mt-1.5"
                                            />
                                            <InputError :message="addressForm.errors.phone" class="mt-1" />
                                        </div>

                                        <div>
                                            <Label for="address">Address</Label>
                                            <Input
                                                id="address"
                                                v-model="addressForm.address"
                                                class="mt-1.5"
                                            />
                                            <InputError :message="addressForm.errors.address" class="mt-1" />
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <Checkbox
                                                id="is_default"
                                                v-model:checked="addressForm.is_default"
                                            />
                                            <Label for="is_default" class="cursor-pointer">
                                                Set as default address
                                            </Label>
                                        </div>

                                        <div class="flex gap-2">
                                            <Button type="submit" :disabled="addressForm.processing">
                                                Save Address
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                @click="showAddressForm = false"
                                            >
                                                Cancel
                                            </Button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </PublicLayout>
</template>

