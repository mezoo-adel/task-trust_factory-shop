<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUploadRequest;
use App\Models\Upload;
use App\Services\UploadService;
use Illuminate\Http\JsonResponse;

class UploadController extends Controller
{
    public function __construct(
        protected UploadService $uploadService
    ) {}

    /**
     * Upload a file
     */
    public function store(StoreUploadRequest $request): JsonResponse
    {
        $upload = $this->uploadService->upload(
            file: $request->file('file'),
            folder: $request->input('folder'),
            uploadableId: $request->input('uploadable_id'),
            uploadableType: $request->input('uploadable_type')
        );

        return response()->json([
            'id' => $upload->id,
            'file_name' => $upload->file_name,
            'file_path' => $upload->file_path,
            'url' => $this->uploadService->getUrl($upload),
            'file_size' => $upload->file_size,
            'mime_type' => $upload->mime_type,
        ], 201);
    }

    /**
     * Delete an upload
     */
    public function destroy(Upload $upload): JsonResponse
    {
        $this->uploadService->delete($upload);

        return response()->json([
            'message' => 'Upload deleted successfully.',
        ]);
    }
}
