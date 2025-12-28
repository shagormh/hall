<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
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
            'room_type_id' => 'nullable|exists:room_types,id',
            'room_number' => 'required',
            'capacity' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'room_type_id.exists'  => 'The selected room type is invalid.',
            'room_number.required' => 'The room number is required.',
            'capacity.integer'     => 'The capacity must be a number.',
            'capacity.min'         => 'The capacity must be at least 1.',
        ];
    }
}
