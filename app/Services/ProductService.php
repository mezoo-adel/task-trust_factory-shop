<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        protected UploadService $uploadService
    ) {}

    /**
     * Create a new product with uploads
     *
     * @param array $data
     * @param array $uploadIds
     * @return Product
     */
    public function create(array $data, array $uploadIds = []): Product
    {
        return DB::transaction(function () use ($data, $uploadIds) {
            // Create the product
            $product = Product::create($data);

            // Attach uploads to the product
            if (!empty($uploadIds)) {
                $this->uploadService->attachToModel(
                    uploadIds: $uploadIds,
                    uploadableId: $product->id,
                    uploadableType: Product::class
                );
            }

            return $product->load('uploads');
        });
    }

    /**
     * Update a product with uploads
     *
     * @param Product $product
     * @param array $data
     * @param array $uploadIds
     * @return Product
     */
    public function update(Product $product, array $data, array $uploadIds = []): Product
    {
        return DB::transaction(function () use ($product, $data, $uploadIds) {
            // Update the product
            $product->update($data);

            // Attach new uploads to the product
            if (!empty($uploadIds)) {
                $this->uploadService->attachToModel(
                    uploadIds: $uploadIds,
                    uploadableId: $product->id,
                    uploadableType: Product::class
                );
            }

            return $product->load('uploads');
        });
    }

    /**
     * Delete a product and its uploads
     *
     * @param Product $product
     * @return bool
     */
    public function delete(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            // Delete all uploads associated with the product
            foreach ($product->uploads as $upload) {
                $this->uploadService->delete($upload);
            }

            // Delete the product
            return $product->delete();
        });
    }
}
