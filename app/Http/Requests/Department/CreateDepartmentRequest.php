<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class CreateDepartmentRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'required|string|max:50|unique:departments,code',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     */

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.unique' => 'This department name already exists.',
            'code.required' => 'Code is required.',
            'code.unique' => 'This department code already exists.',
            'is_active.boolean' => 'The active status must be true or false.',
        ];
    }
}
