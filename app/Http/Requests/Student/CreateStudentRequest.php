<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class CreateStudentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'roll' => 'required|integer|unique:students,roll',
            'registration' => 'required|string|max:20|unique:students,registration',
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:students,email',
            'mobile_number' => 'nullable|unique:students,mobile_number|max:11',
            'address' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'roll.required' => 'The roll field is required.',
            'registration.required' => 'The registration field is required.',
            'name.required' => 'The name field is required.',
            'department_id.required' => 'The department field is required.',
            'department_id.exists'   => 'The selected department is invalid.',
            'mobile_number.required' => 'The mobile number field is required.',
        ];
    }
}
