<?php

namespace App\Services;

use App\Models\Requirement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RequirementService
{
    /**
     * Create a new requirement.
     *
     * @param array $data
     * @param User $buyer
     * @param \Illuminate\Http\UploadedFile|null $primaryImage
     * @param array|null $additionalImages
     * @return Requirement
     * @throws \Exception
     */
    public function createRequirement(array $data, User $buyer, $primaryImage = null, $additionalImages = null): Requirement
    {
        try {
            DB::beginTransaction();

            // Fallback to buyer's delivery_location info if not provided
            $data['address'] = $data['address'] ?? $buyer->address;
            $data['city'] = $data['city'] ?? $buyer->city;
            $data['state'] = $data['state'] ?? $buyer->state;
            $data['pincode'] = $data['pincode'] ?? $buyer->pincode;
            $data['latitude'] = $data['latitude'] ?? $buyer->latitude;
            $data['longitude'] = $data['longitude'] ?? $buyer->longitude;

            if (empty($data['city']) || empty($data['latitude']) || empty($data['longitude'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'location' => 'Location is required. Please provide latitude, longitude and city with your requirement, or complete your profile with location details first.'
                ]);
            }

            // Set default data
            $data['user_id'] = $buyer->id;
            $data['status'] = $data['status'] ?? Requirement::STATUS_OPEN;
            $data['expires_at'] = $data['expires_at'] ?? now()->addDays(30);
            
            // Set default booleans
            $data['is_negotiable'] = $data['is_negotiable'] ?? false;
            $data['is_featured'] = $data['is_featured'] ?? false;

            $requirement = Requirement::create($data);

            // Handle primary image
            if ($primaryImage) {
                $path = $primaryImage->store('requirements/images', 'public');
                $requirement->images()->create([
                    'image_path' => $path,
                    'sort_order' => 0,
                    'is_primary' => true,
                ]);
            }

            // Handle additional images
            if ($additionalImages && is_array($additionalImages)) {
                $hasPrimary = $requirement->primaryImage()->exists();
                
                foreach ($additionalImages as $index => $image) {
                    $path = $image->store('requirements/images', 'public');
                    $requirement->images()->create([
                        'image_path' => $path,
                        'sort_order' => $index + 1,
                        'is_primary' => (!$hasPrimary && $index === 0 && !$primaryImage),
                    ]);
                }
            }

            DB::commit();

            return $requirement->load(['buyer', 'category', 'images']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Requirement creation failed: ' . $e->getMessage());
            throw new \Exception('Failed to create requirement. ' . $e->getMessage());
        }
    }

    public function updateRequirement(Requirement $requirement, array $data, $primaryImage = null, $additionalImages = null, $deletedImages = null): Requirement
    {
        try {
            DB::beginTransaction();

            $requirement->update($data);

            // Handle deleted images
            if ($deletedImages && is_array($deletedImages)) {
                $imagesToDelete = $requirement->images()->whereIn('id', $deletedImages)->get();
                foreach ($imagesToDelete as $img) {
                    Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                }
            }

            // Handle primary image update
            if ($primaryImage) {
                // Delete old primary
                if ($oldPrimary = $requirement->primaryImage) {
                    Storage::disk('public')->delete($oldPrimary->image_path);
                    $oldPrimary->delete();
                }

                $path = $primaryImage->store('requirements/images', 'public');
                $requirement->images()->create([
                    'image_path' => $path,
                    'sort_order' => 0,
                    'is_primary' => true,
                ]);
            } else {
                // If the primary image was deleted but no new one provided, make the first available image primary
                if (!$requirement->primaryImage()->exists() && $firstImage = $requirement->images()->first()) {
                    $firstImage->update(['is_primary' => true, 'sort_order' => 0]);
                }
            }

            // Handle new additional images
            if ($additionalImages && is_array($additionalImages)) {
                $maxSortOrder = $requirement->images()->max('sort_order') ?? 0;
                $hasPrimary = $requirement->primaryImage()->exists();

                foreach ($additionalImages as $index => $image) {
                    $path = $image->store('requirements/images', 'public');
                    $requirement->images()->create([
                        'image_path' => $path,
                        'sort_order' => $maxSortOrder + $index + 1,
                        'is_primary' => (!$hasPrimary && $index === 0),
                    ]);
                    $hasPrimary = true; // Set to true if it just became primary
                }
            }

            DB::commit();

            return $requirement->load(['buyer', 'category', 'images' => fn($q) => $q->orderBy('sort_order')]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Requirement update failed: ' . $e->getMessage());
            throw new \Exception('Failed to update requirement. ' . $e->getMessage());
        }
    }
}
