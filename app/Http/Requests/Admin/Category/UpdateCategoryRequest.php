<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')->id;

        return [
            'name'        => ['required', 'string', 'max:150'],
            'parent_id'   => [
                'nullable', 'integer',
                Rule::exists('categories', 'id'),
                // Cannot set parent to itself
                Rule::notIn([$categoryId]),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'image'       => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable'],
            'is_featured' => ['nullable'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }
}
