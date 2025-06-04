<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class orderprivate extends FormRequest
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
        'name_medicine' => 'nullable|string|max:255',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png',
        'length' => 'required',
        'width' => 'required',
        'type' => 'required|in:Urgent,Later',
        'time' => 'nullable|date',
        'pharma_id' => 'required|exists:pharmas,id', 
    ];
    }
}
