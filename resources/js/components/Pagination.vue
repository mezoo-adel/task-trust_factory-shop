<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import type { PaginatedData, PaginationLink } from '@/types/models';

interface Props {
    data: PaginatedData<any>;
    itemName?: string;
}

const props = withDefaults(defineProps<Props>(), {
    itemName: 'items',
});

const goToPage = (url: string | null) => {
    if (url) {
        router.visit(url, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

// Get page numbers to display (with ellipsis handling)
const getPageNumbers = () => {
    const links = props.data.links;
    // Skip first (Previous) and last (Next) links
    const pageLinks = links.slice(1, -1);
    
    if (pageLinks.length <= 7) {
        // Show all pages if 7 or fewer
        return pageLinks;
    }
    
    const pageNumbers: (PaginationLink | 'ellipsis')[] = [];
    const currentIndex = pageLinks.findIndex(link => link.active);
    
    if (currentIndex === -1) {
        return pageLinks;
    }
    
    // Always show first page
    pageNumbers.push(pageLinks[0]);
    
    // Calculate range around current page
    const range = 2; // Show 2 pages before and after current
    let start = Math.max(1, currentIndex - range);
    let end = Math.min(pageLinks.length - 2, currentIndex + range);
    
    // Add ellipsis before if needed
    if (start > 1) {
        pageNumbers.push('ellipsis');
    }
    
    // Add pages in range
    for (let i = start; i <= end; i++) {
        pageNumbers.push(pageLinks[i]);
    }
    
    // Add ellipsis after if needed
    if (end < pageLinks.length - 2) {
        pageNumbers.push('ellipsis');
    }
    
    // Always show last page
    if (pageLinks.length > 1) {
        pageNumbers.push(pageLinks[pageLinks.length - 1]);
    }
    
    return pageNumbers;
};
</script>

<template>
    <div v-if="data.links.length > 3" class="space-y-4">
        <!-- Pagination Controls -->
        <div class="flex items-center justify-center gap-2">
            <!-- Previous Button -->
            <Button
                variant="outline"
                size="sm"
                :disabled="!data.links[0].url"
                @click="goToPage(data.links[0].url)"
            >
                <ChevronLeft class="h-4 w-4" />
            </Button>

            <!-- Page Numbers -->
            <div class="flex gap-1">
                <template v-for="(item, index) in getPageNumbers()" :key="item === 'ellipsis' ? `ellipsis-${index}` : item.label">
                    <Button
                        v-if="item !== 'ellipsis'"
                        variant="outline"
                        size="sm"
                        :class="{ 'bg-purple-600 text-white border-purple-600': item.active }"
                        :disabled="!item.url || item.active"
                        @click="goToPage(item.url)"
                    >
                        <span v-html="item.label"></span>
                    </Button>
                    <span v-else class="px-2 text-gray-400 flex items-center">...</span>
                </template>
            </div>

            <!-- Next Button -->
            <Button
                variant="outline"
                size="sm"
                :disabled="!data.links[data.links.length - 1].url"
                @click="goToPage(data.links[data.links.length - 1].url)"
            >
                <ChevronRight class="h-4 w-4" />
            </Button>
        </div>

        <!-- Pagination Info -->
        <div v-if="data.total > 0" class="text-center text-sm text-gray-600">
            Showing {{ data.from }} to {{ data.to }} of {{ data.total }} {{ itemName }}
        </div>
    </div>
</template>

