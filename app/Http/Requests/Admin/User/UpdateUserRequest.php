<?php

namespace App\Http\Requests\Admin\User;

use App\Models\User;
use App\Rules\IndianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get user from route binding
        $userId = $this->route('user')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', new IndianPhoneNumber, Rule::unique('users', 'phone')->ignore($userId)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'status' => ['required', 'integer', Rule::in(array_keys(User::getStatusList()))],
            'pincode' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:100'],
        ];
    }
}
