<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import adminRoutes from '@/routes/admin';

interface Props {
    open: boolean;
}

defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    name: '',
    email: '',
});

const closeModal = () => {
    emit('update:open', false);
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.post(adminRoutes.admins.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="closeModal">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>Add Admin User</DialogTitle>
                <DialogDescription>
                    Create a new admin user. A temporary password will be sent to their email.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="name">Name *</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="Enter admin name"
                        :class="{
                            'border-red-500': form.errors.name,
                        }"
                        required
                    />
                    <p v-if="form.errors.name" class="text-sm text-red-600">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="email">Email *</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="admin@example.com"
                        :class="{
                            'border-red-500': form.errors.email,
                        }"
                        required
                    />
                    <p v-if="form.errors.email" class="text-sm text-red-600">
                        {{ form.errors.email }}
                    </p>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeModal" :disabled="form.processing">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Creating...' : 'Create Admin' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
