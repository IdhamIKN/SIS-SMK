<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->user()->id),
            ],
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'avatar.image' => 'Avatar harus berupa file gambar.',
            'avatar.mimes' => 'Avatar harus berformat JPEG, PNG, JPG, atau GIF.',
            'avatar.max' => 'Ukuran avatar maksimal 2MB.',
        ];
    }
}