<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { useApiFetch } from '@/composables/useApiFetch';
import { ArrowLeft, ShoppingBag } from 'lucide-vue-next';
import type { Cart, Address } from '@/types/models';

interface Props {
    cart: Cart;
    addresses: Address[];
}

const props = defineProps<Props>();
const page = usePage();
const { apiFetch, isLoading: isSubmitting, errors: apiErrors } = useApiFetch();

const validationErrors = ref<Record<string, string>>({});
const touched = ref<Record<string, boolean>>({});

const isGuest = computed(() => !page.props.auth?.user);
const showAddressForm = ref(false);
const selectedAddressId = ref<number | null>(
    props.addresses.find(a => a.is_default)?.id || null
);

const form = ref({
    address_id: selectedAddressId.value,
    full_name: '',
    email: page.props.auth?.user?.email || '',
    phone: '',
    address: '',
    name: '',
    password: '',
    password_confirmation: '',
});

// Validation functions
const validateEmail = (email: string): boolean => {
    if (!email.trim()) return false;
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
};

const validatePhone = (phone: string): boolean => {
    if (!phone.trim()) return false;
    const re = /^[\d\s\-\+\(\)]+$/;
    return re.test(phone) && phone.replace(/\D/g, '').length >= 5;
};

const validatePassword = (password: string): boolean => {
    return password.length >= 8;
};

// Individual field validation on blur
const validateField = (fieldName: string, value: string) => {
    touched.value[fieldName] = true;

    switch (fieldName) {
        case 'name':
            if (isGuest.value && !value.trim()) {
                validationErrors.value.name = 'Name is required';
            } else {
                delete validationErrors.value.name;
            }
            break;

        case 'email':
            if (!value.trim()) {
                validationErrors.value.email = 'Email is required';
            } else if (!validateEmail(value)) {
                validationErrors.value.email = 'Please enter a valid email address';
            } else {
                delete validationErrors.value.email;
            }
            break;

        case 'phone':
            if (!validatePhone(value)) {
                validationErrors.value.phone = 'Please enter a valid phone number';
            } else {
                delete validationErrors.value.phone;
            }
            break;

        case 'address':
            if (!value.trim()) {
                validationErrors.value.address = 'Shipping address is required';
            } else {
                delete validationErrors.value.address;
            }
            break;

        case 'password':
            if (isGuest.value) {
                if (!value) {
                    validationErrors.value.password = 'Password is required';
                } else if (!validatePassword(value)) {
                    validationErrors.value.password = 'Password must be at least 8 characters';
                } else {
                    delete validationErrors.value.password;
                }
                // Re-validate password confirmation if it's been touched
                if (touched.value.password_confirmation) {
                    validateField('password_confirmation', form.value.password_confirmation);
                }
            }
            break;

        case 'password_confirmation':
            if (isGuest.value) {
                if (!value) {
                    validationErrors.value.password_confirmation = 'Please confirm your password';
                } else if (form.value.password !== value) {
                    validationErrors.value.password_confirmation = 'Passwords do not match';
                } else {
                    delete validationErrors.value.password_confirmation;
                }
            }
            break;
    }
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const calculateTax = () => {
    const subtotal = (props.cart as any).sub_total || props.cart.subtotal || 0;
    return subtotal * 0.1; // 10% tax
};

const calculateShipping = () => {
    return 0; // Free shipping
};

const calculateTotal = () => {
    const subtotal = (props.cart as any).sub_total || props.cart.subtotal || 0;
    return subtotal + calculateTax() + calculateShipping();
};

const selectAddress = (addressId: number) => {
    selectedAddressId.value = addressId;
    form.value.address_id = addressId;
    showAddressForm.value = false;
};

const toggleAddressForm = () => {
    showAddressForm.value = !showAddressForm.value;
    if (showAddressForm.value) {
        selectedAddressId.value = null;
        form.value.address_id = null;
    }
};

const validateForm = (): boolean => {
    // Mark all fields as touched
    Object.keys(form.value).forEach(key => {
        touched.value[key] = true;
    });

    // Validate all fields
    if (isGuest.value) {
        validateField('name', form.value.name);
        validateField('full_name', form.value.full_name);
        validateField('phone', form.value.phone);
        validateField('address', form.value.address);
    } else {
        // For authenticated users
        if (showAddressForm.value || !selectedAddressId.value) {
            validateField('full_name', form.value.full_name);
            validateField('phone', form.value.phone);
            validateField('address', form.value.address);
        }
    }

    validateField('email', form.value.email);

    if (isGuest.value) {
        validateField('password', form.value.password);
        validateField('password_confirmation', form.value.password_confirmation);
    }

    return Object.keys(validationErrors.value).length === 0;
};

const submitCheckout = async () => {
    // Frontend validation
    if (!validateForm()) {
        return;
    }

    // Create a FormData object
    const formData = new FormData();

    // Add form fields
    Object.keys(form.value).forEach(key => {
        const value = (form.value as any)[key];
        if (value !== null && value !== undefined && value !== '') {
            formData.append(key, value);
        }
    });

    const response = await apiFetch<{ checkout_url?: string }>('/checkout', {
        method: 'POST',
        body: formData,
        showErrorToast: true,
        errorMessage: 'Please check your information and try again.',
    });

    if (response?.checkout_url) {
        // Redirect to Stripe checkout
        window.location.href = response.checkout_url;
    }
};
</script>

<template>
    <Head title="Checkout - Trust Factory Shop" />

    <PublicLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="container mx-auto px-4 py-8">
                <!-- Back Button -->
                <Button as-child variant="ghost" class="mb-6">
                    <Link href="/cart">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Cart
                    </Link>
                </Button>

                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-8">
                    Checkout
                </h1>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Checkout Form -->
                    <div class="lg:col-span-2">
                        <!-- Address Selection for Authenticated Users -->
                        <Card v-if="!isGuest && addresses.length > 0" class="mb-6">
                            <CardHeader>
                                <CardTitle>Shipping Address</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <!-- Existing Addresses -->
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div
                                            v-for="address in addresses"
                                            :key="address.id"
                                            @click="selectAddress(address.id)"
                                            :class="[
                                                'border-2 rounded-lg p-4 cursor-pointer transition-all',
                                                selectedAddressId === address.id
                                                    ? 'border-purple-600 bg-purple-50'
                                                    : 'border-gray-200 hover:border-purple-300'
                                            ]"
                                        >
                                            <div class="flex items-start">
                                                <input
                                                    type="radio"
                                                    :checked="selectedAddressId === address.id"
                                                    class="mt-1 mr-3"
                                                    readonly
                                                />
                                                <div class="flex-1">
                                                    <p class="font-semibold">{{ address.full_name }}</p>
                                                    <p class="text-sm text-gray-600 mt-1">{{ address.phone }}</p>
                                                    <p class="text-sm text-gray-600 mt-1">{{ address.address }}</p>
                                                    <span v-if="address.is_default" class="inline-block mt-2 text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">
                                                        Default
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add New Address Button -->
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="w-full"
                                        @click="toggleAddressForm"
                                    >
                                        {{ showAddressForm ? 'Cancel' : '+ Add New Address' }}
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Customer Information -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Customer Information</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-5">
                                    <!-- Guest Account Creation -->
                                    <div v-if="isGuest" class="grid gap-4 md:grid-cols-2">
                                        <div class="md:col-span-2">
                                            <Label for="name">Full Name</Label>
                                            <Input
                                                id="name"
                                                v-model="form.name"
                                                type="text"
                                                placeholder="John Doe"
                                                autocomplete="name"
                                                :aria-invalid="!!(apiErrors.name || (touched.name && validationErrors.name))"
                                                class="mt-1.5"
                                                @blur="validateField('name', form.name)"
                                            />
                                            <InputError
                                                :message="apiErrors.name || (touched.name ? validationErrors.name : '')"
                                                class="mt-1"
                                            />
                                        </div>
                                    </div>

                                    <!-- Contact Information -->
                                    <div v-if="isGuest" class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <Label for="email">Email Address</Label>
                                            <Input
                                                id="email"
                                                v-model="form.email"
                                                type="email"
                                                placeholder="john@example.com"
                                                autocomplete="email"
                                                :aria-invalid="!!(apiErrors.email || (touched.email && validationErrors.email))"
                                                class="mt-1.5"
                                                @blur="validateField('email', form.email)"
                                            />
                                            <InputError
                                                :message="apiErrors.email || (touched.email ? validationErrors.email : '')"
                                                class="mt-1"
                                            />
                                        </div>
                                    </div>

                                    <!-- Address Form (for guests or when adding new address) -->
                                    <div v-if="isGuest || showAddressForm" class="grid gap-4 md:grid-cols-2">
                                        <div>
                                             <Label for="full_name">Shipping Name</Label>
                                            <Input
                                                id="full_name"
                                                v-model="form.full_name"
                                                type="text"
                                                placeholder="John Doe"
                                                autocomplete="name"
                                                :aria-invalid="!!(apiErrors.full_name || (touched.full_name && validationErrors.full_name))"
                                                class="mt-1.5"
                                                @blur="validateField('full_name', form.full_name)"
                                            />
                                            <InputError
                                                :message="apiErrors.full_name || (touched.full_name ? validationErrors.full_name : '')"
                                                class="mt-1"
                                            />
                                        </div>
                                        <div>
                                            <Label for="phone_new">Phone Number</Label>
                                            <Input
                                                id="phone_new"
                                                v-model="form.phone"
                                                type="tel"
                                                placeholder="+1 (555) 123-4567"
                                                autocomplete="tel"
                                                :aria-invalid="!!(apiErrors.phone || (touched.phone && validationErrors.phone))"
                                                class="mt-1.5"
                                                @blur="validateField('phone', form.phone)"
                                            />
                                            <InputError
                                                :message="apiErrors.phone || (touched.phone ? validationErrors.phone : '')"
                                                class="mt-1"
                                            />
                                        </div>

                                        <div class="md:col-span-2">
                                            <Label for="address">Shipping Address</Label>
                                            <Input
                                                id="address"
                                                v-model="form.address"
                                                type="text"
                                                placeholder="123 Main St, City, State, ZIP Code"
                                                autocomplete="street-address"
                                                :aria-invalid="!!(apiErrors.address || (touched.address && validationErrors.address))"
                                                class="mt-1.5"
                                                @blur="validateField('address', form.address)"
                                            />
                                            <InputError
                                                :message="apiErrors.address || (touched.address ? validationErrors.address : '')"
                                                class="mt-1"
                                            />
                                        </div>
                                    </div>

                                    <!-- Password Fields for Guests -->
                                    <div v-if="isGuest" class="grid gap-4 md:grid-cols-2 border-t pt-5">
                                        <div>
                                            <Label for="password">Password</Label>
                                            <Input
                                                id="password"
                                                v-model="form.password"
                                                type="password"
                                                placeholder="At least 8 characters"
                                                autocomplete="new-password"
                                                :aria-invalid="!!(apiErrors.password || (touched.password && validationErrors.password))"
                                                class="mt-1.5"
                                                @blur="validateField('password', form.password)"
                                            />
                                            <InputError
                                                :message="apiErrors.password || (touched.password ? validationErrors.password : '')"
                                                class="mt-1"
                                            />
                                        </div>

                                        <div>
                                            <Label for="password_confirmation">Confirm Password</Label>
                                            <Input
                                                id="password_confirmation"
                                                v-model="form.password_confirmation"
                                                type="password"
                                                placeholder="Confirm your password"
                                                autocomplete="new-password"
                                                :aria-invalid="!!(apiErrors.password_confirmation || (touched.password_confirmation && validationErrors.password_confirmation))"
                                                class="mt-1.5"
                                                @blur="validateField('password_confirmation', form.password_confirmation)"
                                            />
                                            <InputError
                                                :message="apiErrors.password_confirmation || (touched.password_confirmation ? validationErrors.password_confirmation : '')"
                                                class="mt-1"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <Card class="sticky top-4">
                            <CardHeader>
                                <CardTitle>Order Summary</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Cart Items -->
                                <div class="space-y-3">
                                    <div
                                        v-for="item in cart.items"
                                        :key="item.id"
                                        class="flex justify-between text-sm"
                                    >
                                        <div class="flex-1">
                                            <p class="font-medium">{{ item.product.name }}</p>
                                            <p class="text-gray-600">Qty: {{ item.quantity }}</p>
                                        </div>
                                        <p class="font-medium">{{ formatPrice(item.total) }}</p>
                                    </div>
                                </div>

                                <div class="border-t pt-4 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Subtotal</span>
                                        <span>{{ formatPrice((cart as any).sub_total || cart.subtotal || 0) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Tax</span>
                                        <span>{{ formatPrice(calculateTax()) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Shipping</span>
                                        <span>{{ calculateShipping() === 0 ? 'Free' : formatPrice(calculateShipping()) }}</span>
                                    </div>
                                    <div class="flex justify-between font-bold text-lg pt-2 border-t">
                                        <span>Total</span>
                                        <span class="text-purple-600">{{ formatPrice(calculateTotal()) }}</span>
                                    </div>
                                </div>

                                <Button
                                    type="button"
                                    class="w-full"
                                    size="lg"
                                    @click="submitCheckout"
                                    :disabled="isSubmitting"
                                >
                                    <ShoppingBag class="w-5 h-5 mr-2" />
                                    {{ isSubmitting ? 'Processing...' : 'Place Order' }}
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
