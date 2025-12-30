<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import PublicLayout from '@/layouts/PublicLayout.vue';
import {
    destroy as destroyAddressRoute,
    store as storeAddressRoute,
    update as updateAddressRoute,
} from '@/routes/profile/addresses';
import { update as updateNotificationsRoute } from '@/routes/profile/notifications';
import { update as updatePasswordRoute } from '@/routes/profile/password';
import type { ProfileProps } from '@/types/models';
import { Head, useForm } from '@inertiajs/vue3';
import { Bell, Lock, MapPin } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<ProfileProps>();

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.patch(updatePasswordRoute.url(), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

// Notification preferences
// Backend returns array of objects with: id, channel, name, description, is_subscribed
interface NotificationPreference {
    id: number;
    channel: string;
    name: string;
    description: string;
    is_subscribed: boolean;
}

// Check if it's an array (from backend) or already an object
const isArrayFormat = Array.isArray(props.notification_preferences);

// Store array for template iteration
const notificationPreferencesArray = isArrayFormat
    ? (props.notification_preferences as NotificationPreference[])
    : [];

// Convert to Record<channel_id, boolean> format for form submission
// The update method expects channel_id => is_subscribed
const notificationPreferencesObj = isArrayFormat
    ? notificationPreferencesArray.reduce((acc: Record<string, boolean>, item: NotificationPreference) => {
        if (item && item.id) {
            // Use channel_id (item.id) as key for the update request
            acc[item.id.toString()] = item.is_subscribed || false;
        }
        return acc;
    }, {})
    : (props.notification_preferences as Record<string, boolean>);

const notificationForm = useForm({
    preferences: { ...notificationPreferencesObj },
});

const updateNotifications = () => {
    notificationForm.patch(updateNotificationsRoute.url(), {
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
        addressForm.patch(
            updateAddressRoute.url({ address: editingAddress.value.id }),
            {
                preserveScroll: true,
                onSuccess: () => {
                    showAddressForm.value = false;
                    addressForm.reset();
                },
            },
        );
    } else {
        addressForm.post(storeAddressRoute.url(), {
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
        useForm({}).delete(destroyAddressRoute.url({ address: addressId }), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Profile Settings" />

    <PublicLayout>
        <div class="container mx-auto px-4 py-8">
            <h1 class="mb-8 text-3xl font-bold">Profile Settings</h1>

            <Tabs default-value="password" class="w-full">
                <TabsList class="grid w-full grid-cols-3">
                    <TabsTrigger value="password">
                        <Lock class="mr-2 h-4 w-4" />
                        Password
                    </TabsTrigger>
                    <TabsTrigger value="notifications">
                        <Bell class="mr-2 h-4 w-4" />
                        Notifications
                    </TabsTrigger>
                    <TabsTrigger value="addresses">
                        <MapPin class="mr-2 h-4 w-4" />
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
                            <form
                                @submit.prevent="updatePassword"
                                class="grid gap-4 md:grid-cols-3"
                            >
                                <div>
                                    <Label for="current_password"
                                        >Current Password</Label
                                    >
                                    <Input
                                        id="current_password"
                                        v-model="passwordForm.current_password"
                                        type="password"
                                        class="mt-1.5"
                                    />
                                    <InputError
                                        :message="
                                            passwordForm.errors.current_password
                                        "
                                        class="mt-1"
                                    />
                                </div>

                                <div>
                                    <Label for="password">New Password</Label>
                                    <Input
                                        id="password"
                                        v-model="passwordForm.password"
                                        type="password"
                                        class="mt-1.5"
                                    />
                                    <InputError
                                        :message="passwordForm.errors.password"
                                        class="mt-1"
                                    />
                                </div>

                                <div>
                                    <Label for="password_confirmation"
                                        >Confirm New Password</Label
                                    >
                                    <Input
                                        id="password_confirmation"
                                        v-model="
                                            passwordForm.password_confirmation
                                        "
                                        type="password"
                                        class="mt-1.5"
                                    />
                                </div>

                                <Button
                                    type="submit"
                                    :disabled="passwordForm.processing"
                                >
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
                            <form
                                @submit.prevent="updateNotifications"
                                class="space-y-4"
                            >
                                <template v-if="isArrayFormat">
                                    <div
                                        v-for="pref in notificationPreferencesArray"
                                        :key="pref.id"
                                        class="flex items-center space-x-3 border-b pb-3"
                                    >
                                        <Checkbox
                                            :id="`pref-${pref.id}`"
                                            v-model="
                                                notificationForm.preferences[
                                                    pref.id.toString()
                                                ]
                                            "
                                        />
                                        <div class="flex-1">
                                            <Label
                                                :for="`pref-${pref.id}`"
                                                class="cursor-pointer font-medium"
                                            >
                                                {{ pref.name || (pref.channel && typeof pref.channel === 'string' ? pref.channel.charAt(0).toUpperCase() + pref.channel.slice(1) : 'Notification') }}
                                            </Label>
                                            <p class="text-sm text-gray-600">
                                                {{ pref.description || (pref.channel ? `Receive ${pref.channel} notifications` : '') }}
                                            </p>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <div
                                        v-for="(
                                            isSubscribed, channel
                                        ) in notificationPreferencesObj"
                                        :key="String(channel || '')"
                                        class="flex items-center space-x-3 border-b pb-3"
                                    >
                                        <Checkbox
                                            :id="`pref-${channel}`"
                                            v-model="
                                                notificationForm.preferences[
                                                    channel
                                                ]
                                            "
                                        />
                                        <div class="flex-1">
                                            <Label
                                                :for="`pref-${channel}`"
                                                class="cursor-pointer font-medium"
                                            >
                                                {{
                                                    channel && typeof channel === 'string' && channel.length > 0
                                                        ? channel.charAt(0).toUpperCase() + channel.slice(1)
                                                        : String(channel || '')
                                                }}
                                                Notifications
                                            </Label>
                                            <p class="text-sm text-gray-600">
                                                Receive {{ channel || '' }} notifications
                                            </p>
                                        </div>
                                    </div>
                                </template>

                                <Button
                                    type="submit"
                                    :disabled="notificationForm.processing"
                                >
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
                                    class="rounded-lg border p-4"
                                >
                                    <div
                                        class="flex items-start justify-between"
                                    >
                                        <div>
                                            <p class="font-semibold">
                                                {{ address.full_name }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                {{ address.phone }}
                                            </p>
                                            <p
                                                class="mt-1 text-sm text-gray-600"
                                            >
                                                {{ address.address }}
                                            </p>
                                            <span
                                                v-if="address.is_default"
                                                class="mt-2 inline-block rounded bg-purple-100 px-2 py-1 text-xs text-purple-700"
                                            >
                                                Default
                                            </span>
                                        </div>
                                        <div class="flex gap-2">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                @click="
                                                    openAddressForm(address)
                                                "
                                            >
                                                Edit
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="destructive"
                                                @click="
                                                    deleteAddress(address.id)
                                                "
                                            >
                                                Delete
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <Button
                                    @click="openAddressForm()"
                                    class="w-full"
                                >
                                    + Add New Address
                                </Button>

                                <!-- Address Form Modal (simplified) -->
                                <div
                                    v-if="showAddressForm"
                                    class="mt-4 border-t pt-4"
                                >
                                    <h3 class="mb-4 font-semibold">
                                        {{
                                            editingAddress
                                                ? 'Edit Address'
                                                : 'Add New Address'
                                        }}
                                    </h3>
                                    <form
                                        @submit.prevent="saveAddress"
                                        class="space-y-4"
                                    >
                                        <div>
                                            <Label for="full_name"
                                                >Full Name</Label
                                            >
                                            <Input
                                                id="full_name"
                                                v-model="addressForm.full_name"
                                                class="mt-1.5"
                                            />
                                            <InputError
                                                :message="
                                                    addressForm.errors.full_name
                                                "
                                                class="mt-1"
                                            />
                                        </div>

                                        <div>
                                            <Label for="phone">Phone</Label>
                                            <Input
                                                id="phone"
                                                v-model="addressForm.phone"
                                                class="mt-1.5"
                                            />
                                            <InputError
                                                :message="
                                                    addressForm.errors.phone
                                                "
                                                class="mt-1"
                                            />
                                        </div>

                                        <div>
                                            <Label for="address">Address</Label>
                                            <Input
                                                id="address"
                                                v-model="addressForm.address"
                                                class="mt-1.5"
                                            />
                                            <InputError
                                                :message="
                                                    addressForm.errors.address
                                                "
                                                class="mt-1"
                                            />
                                        </div>

                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <Checkbox
                                                id="is_default"
                                                v-model:checked="
                                                    addressForm.is_default
                                                "
                                            />
                                            <Label
                                                for="is_default"
                                                class="cursor-pointer"
                                            >
                                                Set as default address
                                            </Label>
                                        </div>

                                        <div class="flex gap-2">
                                            <Button
                                                type="submit"
                                                :disabled="
                                                    addressForm.processing
                                                "
                                            >
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
