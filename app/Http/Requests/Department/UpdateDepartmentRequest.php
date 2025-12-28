<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
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
        $departmentId = $this->route('department')->id; // Assuming the route parameter is named 'department'
        return [
            'name' => 'required|string|max:255|unique:departments,name,' . $departmentId,
            'code' => 'required|string|max:50|unique:departments,code,' . $departmentId,
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
