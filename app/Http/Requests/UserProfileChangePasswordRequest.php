<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserProfileChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.mixed_case' => 'Password harus mengandung huruf besar dan kecil.',
            'password.numbers' => 'Password harus mengandung angka.',
        ];
    }
}