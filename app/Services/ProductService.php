<?php

namespace App\Services;

use App\Exceptions\ProductServiceException;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductService
{
    /**
     * Create a new product.
     *
     * @param array $data
     * @param User $seller
     * @param \Illuminate\Http\UploadedFile|null $primaryImage
     * @param array|null $additionalImages
     * @return Product
     * @throws ProductServiceException
     */
    public function createProduct(array $data, User $seller, $primaryImage = null, $additionalImages = null): Product
    {
        try {
            DB::beginTransaction();

            // Fallback to seller's location info if not provided
            $data['address'] = $data['address'] ?? $seller->address;
            $data['city'] = $data['city'] ?? $seller->city;
            $data['state'] = $data['state'] ?? $seller->state;
            $data['pincode'] = $data['pincode'] ?? $seller->pincode;
            $data['latitude'] = $data['latitude'] ?? $seller->latitude;
            $data['longitude'] = $data['longitude'] ?? $seller->longitude;

            if (empty($data['city']) || empty($data['latitude']) || empty($data['longitude'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'location' => 'Location is required. Please provide latitude, longitude and city with your listing, or complete your profile with location details first.'
                ]);
            }

            // Set default data
            $data['user_id'] = $seller->id;
            $data['status'] = $data['status'] ?? Product::STATUS_ACTIVE;
            $data['expires_at'] = $data['expires_at'] ?? now()->addDays(30);
            
            // Set default booleans
            $data['is_negotiable'] = $data['is_negotiable'] ?? false;
            $data['is_featured'] = $data['is_featured'] ?? false;

            $product = Product::create($data);

            // Handle primary image
            if ($primaryImage) {
                $path = $primaryImage->store('products/images', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'sort_order' => 0,
                    'is_primary' => true,
                ]);
            }

            // Handle additional images
            if ($additionalImages && is_array($additionalImages)) {
                $hasPrimary = $product->primaryImage()->exists();
                
                foreach ($additionalImages as $index => $image) {
                    $path = $image->store('products/images', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'sort_order' => $index + 1,
                        'is_primary' => (!$hasPrimary && $index === 0 && !$primaryImage),
                    ]);
                }
            }

            DB::commit();

            return $product->load(['seller', 'category', 'images']);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Product creation failed: ' . $e->getMessage());
            throw new ProductServiceException('Failed to create product. ' . $e->getMessage());
        }
    }

    public function updateProduct(Product $product, array $data, $primaryImage = null, $additionalImages = null, $deletedImages = null): Product
    {
        try {
            DB::beginTransaction();

            $product->update($data);

            // Handle deleted images
            if ($deletedImages && is_array($deletedImages)) {
                $imagesToDelete = $product->images()->whereIn('id', $deletedImages)->get();
                foreach ($imagesToDelete as $img) {
                    Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                }
            }

            // Handle primary image update
            if ($primaryImage) {
                // Delete old primary
                if ($oldPrimary = $product->primaryImage) {
                    Storage::disk('public')->delete($oldPrimary->image_path);
                    $oldPrimary->delete();
                }

                $path = $primaryImage->store('products/images', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'sort_order' => 0,
                    'is_primary' => true,
                ]);
            } else {
                // If the primary image was deleted but no new one provided, make the first available image primary
                if (!$product->primaryImage()->exists() && $firstImage = $product->images()->first()) {
                    $firstImage->update(['is_primary' => true, 'sort_order' => 0]);
                }
            }

            // Handle new additional images
            if ($additionalImages && is_array($additionalImages)) {
                $maxSortOrder = $product->images()->max('sort_order') ?? 0;
                $hasPrimary = $product->primaryImage()->exists();

                foreach ($additionalImages as $index => $image) {
                    $path = $image->store('products/images', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'sort_order' => $maxSortOrder + $index + 1,
                        'is_primary' => (!$hasPrimary && $index === 0),
                    ]);
                    $hasPrimary = true; // Set to true if it just became primary
                }
            }

            DB::commit();

            return $product->load(['seller', 'category', 'images' => fn($q) => $q->orderBy('sort_order')]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Product update failed: ' . $e->getMessage());
            throw new ProductServiceException('Failed to update product. ' . $e->getMessage());
        }
    }
}
