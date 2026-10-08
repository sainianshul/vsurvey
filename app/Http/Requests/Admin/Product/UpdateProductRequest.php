<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'other_category_name' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'price_unit' => 'nullable|string|max:50',
            'is_negotiable' => 'nullable|boolean',
            'condition' => 'required|integer|in:1,2',
            'minimum_quantity' => 'required|integer|min:1',
            'address' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'status' => 'required|integer|in:1,2,3,4',
            'is_featured' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
            'primary_image' => 'nullable|image|max:5120', // 5MB max
            'additional_images.*' => 'nullable|image|max:5120', // 5MB max
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'exists:product_images,id',
        ];
    }
}
