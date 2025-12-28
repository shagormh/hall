<?php

namespace App\Http\Requests\HallAttachment;

use Illuminate\Foundation\Http\FormRequest;

class CreateHallAttachmentRequest extends FormRequest
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
            'student_id' => 'required|exists:students,id',
            'hall_id' => 'required|exists:halls,id',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Please select a student.',
            'student_id.exists'   => 'The selected student is invalid or not found.',

            'hall_id.required'    => 'Please select a hall.',
            'hall_id.exists'      => 'The selected hall is invalid or not found.',
        ];
    }
}
