import { ref } from 'vue';
import type { Upload, UploadProgress } from '@/types/models';
import useCsrf from '@/composables/useCsrf';

export function useUpload() {
    const uploading = ref(false);
    const uploadProgress = ref<UploadProgress[]>([]);
    const error = ref<string | null>(null);

    /**
     * Upload a single file
     */
    const uploadFile = async (
        file: File,
        folder?: string,
        uploadableId?: number,
        uploadableType?: string,
    ): Promise<Upload | null> => {
        const uploadId = `${Date.now()}_${Math.random()}`;

        try {
            uploading.value = true;
            error.value = null;

            // Add to progress tracking
            uploadProgress.value.push({
                uploadId,
                progress: 0,
                file,
            });

            const formData = new FormData();
            formData.append('file', file);
            if (folder) formData.append('folder', folder);
            if (uploadableId)
                formData.append('uploadable_id', uploadableId.toString());
            if (uploadableType)
                formData.append('uploadable_type', uploadableType);

            const response = await fetch('/uploads', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': useCsrf(),
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include',
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Upload failed');
            }

            const data: Upload = await response.json();

            // Update progress to 100%
            const progressIndex = uploadProgress.value.findIndex(
                (p) => p.uploadId === uploadId,
            );
            if (progressIndex !== -1) {
                uploadProgress.value[progressIndex].progress = 100;
            }

            return data;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Upload failed';
            console.error('Upload error:', err);
            return null;
        } finally {
            uploading.value = false;
            // Remove from progress after a delay
            setTimeout(() => {
                uploadProgress.value = uploadProgress.value.filter(
                    (p) => p.uploadId !== uploadId,
                );
            }, 1000);
        }
    };

    /**
     * Upload multiple files
     */
    const uploadFiles = async (
        files: File[],
        folder?: string,
        uploadableId?: number,
        uploadableType?: string,
    ): Promise<Upload[]> => {
        const uploads: Upload[] = [];

        for (const file of files) {
            const upload = await uploadFile(
                file,
                folder,
                uploadableId,
                uploadableType,
            );
            if (upload) {
                uploads.push(upload);
            }
        }

        return uploads;
    };

    /**
     * Delete an upload
     */
    const deleteUpload = async (uploadId: number): Promise<boolean> => {
        try {
            const response = await fetch(`/uploads/${uploadId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': useCsrf(),
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include',
            });

            if (!response.ok) {
                throw new Error('Delete failed');
            }

            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Delete failed';
            console.error('Delete error:', err);
            return false;
        }
    };

    return {
        uploading,
        uploadProgress,
        error,
        uploadFile,
        uploadFiles,
        deleteUpload,
    };
}
