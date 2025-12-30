<?php

namespace App\Services;

use App\Models\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadService
{
    /**
     * Upload a file and create an Upload record
     *
     * @param UploadedFile $file
     * @param string|null $folder
     * @param int|null $uploadableId
     * @param string|null $uploadableType
     * @return Upload
     */
    public function upload(
        UploadedFile $file,
        ?string $folder = null,
        ?int $uploadableId = null,
        ?string $uploadableType = null
    ): Upload {
        $folder = $folder ?? 'uploads';
        $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs($folder, $fileName, 'public');

        $upload = Upload::create([
            'uploadable_id' => $uploadableId,
            'uploadable_type' => $uploadableType,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'order' => 0,
        ]);

        return $upload;
    }

    /**
     * Delete an upload and its file from storage
     *
     * @param Upload $upload
     * @return bool
     */
    public function delete(Upload $upload): bool
    {
        // Delete file from storage
        if (Storage::disk('public')->exists($upload->file_path)) {
            Storage::disk('public')->delete($upload->file_path);
        }

        // Delete database record
        return $upload->delete();
    }

    /**
     * Attach uploads to an uploadable model
     *
     * @param array $uploadIds
     * @param int $uploadableId
     * @param string $uploadableType
     * @return void
     */
    public function attachToModel(array $uploadIds, int $uploadableId, string $uploadableType): void
    {
        if (empty($uploadIds)) {
            return;
        }

        Upload::whereIn('id', $uploadIds)
            ->whereNull('uploadable_id')
            ->update([
                'uploadable_id' => $uploadableId,
                'uploadable_type' => $uploadableType,
            ]);

        // Update order for the uploads
        $this->updateOrder($uploadIds);
    }

    /**
     * Update the order of uploads
     *
     * @param array $uploadIds
     * @return void
     */
    public function updateOrder(array $uploadIds): void
    {
        foreach ($uploadIds as $index => $uploadId) {
            Upload::where('id', $uploadId)->update(['order' => $index]);
        }
    }

    /**
     * Get upload URL
     *
     * @param Upload $upload
     * @return string
     */
    public function getUrl(Upload $upload): string
    {
       return asset('storage/' . $upload->file_path);
    }
}
