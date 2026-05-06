<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcademicYearUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year_start' => [
                'required',
                'integer',
                'min:2020',
                'max:2030',
                Rule::unique('academic_years')->ignore($this->route('academicYear'))
            ],
            'year_end' => 'required|integer|gt:year_start',
            'promotion_deadline' => 'nullable|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'year_start.required' => 'Tahun mulai wajib diisi.',
            'year_start.integer' => 'Tahun mulai harus berupa angka.',
            'year_start.min' => 'Tahun mulai minimal 2020.',
            'year_start.max' => 'Tahun mulai maksimal 2030.',
            'year_start.unique' => 'Tahun ajaran dengan tahun mulai tersebut sudah ada.',
            'year_end.required' => 'Tahun akhir wajib diisi.',
            'year_end.integer' => 'Tahun akhir harus berupa angka.',
            'year_end.gt' => 'Tahun akhir harus lebih besar dari tahun mulai.',
            'promotion_deadline.date' => 'Deadline promosi harus berupa tanggal yang valid.',
            'promotion_deadline.after' => 'Deadline promosi harus setelah hari ini.',
        ];
    }

    public function attributes(): array
    {
        return [
            'year_start' => 'Tahun mulai',
            'year_end' => 'Tahun akhir',
            'promotion_deadline' => 'Deadline promosi',
        ];
    }
}