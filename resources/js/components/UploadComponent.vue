<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { useUpload } from '@/composables/useUpload';
import { useToast } from '@/components/ui/toast/use-toast';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Trash2, Upload as UploadIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { Upload } from '@/types/models';

interface Props {
    modelValue: Upload[];
    folder?: string;
    uploadableId?: number;
    uploadableType?: string;
    accept?: string;
    maxFiles?: number;
    maxSize?: number; // in MB
    multiple?: boolean;
    label?: string;
    description?: string;
}

const props = withDefaults(defineProps<Props>(), {
    accept: 'image/jpeg,image/jpg,image/png,image/webp,image/gif',
    maxFiles: 30,
    maxSize: 10,
    multiple: true,
    label: 'Upload Images',
    description: 'Drag and drop images here, or click to select files',
});

const emit = defineEmits<{
    'update:modelValue': [value: Upload[]];
}>();

const { uploadFile, deleteUpload, uploading, error } = useUpload();
const { toast } = useToast();

const fileInput = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const localUploads = ref<Upload[]>([...props.modelValue]);
const showDeleteConfirm = ref(false);
const uploadToDelete = ref<Upload | null>(null);

const canUploadMore = computed(() => {
    return localUploads.value.length < props.maxFiles;
});

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const validateFile = (file: File): string | null => {
    // Check file size
    const maxSizeBytes = props.maxSize * 1024 * 1024;
    if (file.size > maxSizeBytes) {
        return `File size must not exceed ${props.maxSize}MB`;
    }

    // Check file type
    const acceptedTypes = props.accept.split(',').map(t => t.trim());
    const fileType = file.type;
    const isValidType = acceptedTypes.some(type => {
        if (type.endsWith('/*')) {
            return fileType.startsWith(type.replace('/*', ''));
        }
        return fileType === type;
    });

    if (!isValidType) {
        return 'Invalid file type';
    }

    return null;
};

const handleFileSelect = async (files: FileList | null) => {
    if (!files || files.length === 0) return;

    const filesToUpload = Array.from(files);

    // Check if we can upload more files
    if (localUploads.value.length + filesToUpload.length > props.maxFiles) {
        toast({
            title: 'Upload Limit Reached',
            description: `You can only upload up to ${props.maxFiles} files`,
            variant: 'destructive',
        });
        return;
    }

    for (const file of filesToUpload) {
        const validationError = validateFile(file);
        if (validationError) {
            toast({
                title: 'Invalid File',
                description: validationError,
                variant: 'destructive',
            });
            continue;
        }

        const upload = await uploadFile(
            file,
            props.folder,
            props.uploadableId,
            props.uploadableType
        );

        if (upload) {
            localUploads.value.push(upload);
            emit('update:modelValue', localUploads.value);
        }
    }

    // Reset file input
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const handleDrop = (e: DragEvent) => {
    isDragging.value = false;
    handleFileSelect(e.dataTransfer?.files || null);
};

const handleDragOver = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = () => {
    isDragging.value = false;
};

const handleDelete = (upload: Upload) => {
    uploadToDelete.value = upload;
    showDeleteConfirm.value = true;
};

const confirmDelete = async () => {
    if (!uploadToDelete.value) return;

    const success = await deleteUpload(uploadToDelete.value.id);
    if (success) {
        localUploads.value = localUploads.value.filter(u => u.id !== uploadToDelete.value!.id);
        emit('update:modelValue', localUploads.value);
        toast({
            title: 'File Deleted',
            description: 'The file has been successfully deleted.',
        });
    } else {
        toast({
            title: 'Delete Failed',
            description: 'Failed to delete the file. Please try again.',
            variant: 'destructive',
        });
    }
    uploadToDelete.value = null;
};

const openFileDialog = () => {
    fileInput.value?.click();
};
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ label }}
            </label>
            <p v-if="description" class="text-sm text-gray-500 mb-3">
                {{ description }}
            </p>
        </div>

        <!-- Upload Area -->
        <div
            v-if="canUploadMore"
            @drop.prevent="handleDrop"
            @dragover.prevent="handleDragOver"
            @dragleave="handleDragLeave"
            @click="openFileDialog"
            :class="[
                'border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors',
                isDragging
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-300 hover:border-gray-400 bg-gray-50',
            ]"
        >
            <UploadIcon class="mx-auto h-12 w-12 text-gray-400" />
            <p class="mt-2 text-sm text-gray-600">
                {{ isDragging ? 'Drop files here' : 'Drag and drop files here, or click to select' }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
                Max {{ maxFiles }} files, {{ maxSize }}MB each
            </p>
            <input
                ref="fileInput"
                type="file"
                :accept="accept"
                :multiple="multiple"
                @change="(e) => handleFileSelect((e.target as HTMLInputElement).files)"
                class="hidden"
            />
        </div>

        <!-- Error Message -->
        <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-600">{{ error }}</p>
        </div>

        <!-- Uploading Indicator -->
        <div v-if="uploading" class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-600">Uploading...</p>
        </div>

        <!-- Uploaded Files Grid -->
        <div v-if="localUploads.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <Card
                v-for="upload in localUploads"
                :key="upload.id"
                class="relative group overflow-hidden"
            >
                <div class="aspect-square relative">
                    <img
                        :src="upload.url"
                        :alt="upload.file_name"
                        class="w-full h-full object-cover"
                    />
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center">
                        <Button
                            @click="handleDelete(upload)"
                            type="button"
                            variant="destructive"
                            size="icon"
                            class="opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
                <div class="p-2 bg-white">
                    <p class="text-xs text-gray-600 truncate" :title="upload.file_name">
                        {{ upload.file_name }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ formatFileSize(upload.file_size) }}
                    </p>
                </div>
            </Card>
        </div>

        <!-- No Files Message -->
        <div v-if="localUploads.length === 0 && !canUploadMore" class="text-center py-8 text-gray-500">
            <p>No files uploaded yet</p>
        </div>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            v-model:open="showDeleteConfirm"
            title="Delete File"
            description="Are you sure you want to delete this file? This action cannot be undone."
            confirm-text="Delete"
            cancel-text="Cancel"
            variant="destructive"
            @confirm="confirmDelete"
        />
    </div>
</template>
