<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
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
        $studentId = $this->route('student')->id;

        return [
            'roll'          => 'required_without:block_reason|integer|unique:students,roll,' . $studentId,
            'registration'  => 'required_without:block_reason|string|max:20|unique:students,registration,' . $studentId,
            'name'          => 'required_without:block_reason|string|max:255',
            'department_id' => 'required_without:block_reason|exists:departments,id',
            'father_name'   => 'nullable|string|max:255',
            'mother_name'   => 'nullable|string|max:255',
            'email'         => 'nullable|email|unique:students,email,' . $studentId,
            'mobile_number' => 'required_without:block_reason|string|max:11|unique:students,mobile_number,' . $studentId,
            'address'       => 'nullable|string|max:255',
            'is_active'     => 'nullable|boolean',
            'block_reason'  => 'nullable|string|max:1000',
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
