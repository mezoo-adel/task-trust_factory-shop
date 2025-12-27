<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { MapPin, Phone, User } from 'lucide-vue-next';
import type { Address } from '@/types/models';

interface Props {
    address: Address;
    showLabel?: boolean;
    showDefaultBadge?: boolean;
    title?: string;
}

withDefaults(defineProps<Props>(), {
    showLabel: true,
    showDefaultBadge: true,
    title: 'Shipping Address',
});
</script>

<template>
    <Card>
        <CardHeader v-if="title">
            <CardTitle class="flex items-center gap-2">
                <MapPin class="w-5 h-5" />
                {{ title }}
            </CardTitle>
        </CardHeader>
        <CardContent :class="title ? '' : 'p-6'">
            <div class="space-y-2">
                <div v-if="showLabel && address.label" class="flex items-center gap-2 mb-3">
                    <Badge variant="outline">{{ address.label }}</Badge>
                    <Badge v-if="showDefaultBadge && address.is_default" variant="default" class="bg-purple-600">
                        Default
                    </Badge>
                </div>
                
                <div class="flex items-start gap-2">
                    <User class="w-4 h-4 text-gray-500 mt-0.5 flex-shrink-0" />
                    <p class="font-semibold">{{ address.full_name }}</p>
                </div>
                
                <div class="flex items-start gap-2">
                    <Phone class="w-4 h-4 text-gray-500 mt-0.5 flex-shrink-0" />
                    <p class="text-gray-600">{{ address.phone }}</p>
                </div>
                
                <div class="flex items-start gap-2">
                    <MapPin class="w-4 h-4 text-gray-500 mt-0.5 flex-shrink-0" />
                    <p class="text-gray-600">{{ address.address }}</p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

