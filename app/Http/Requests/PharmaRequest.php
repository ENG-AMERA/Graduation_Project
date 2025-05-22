<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PharmaRequest extends FormRequest
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
        'length' => 'required',
        'width' => 'required',
        'name' => 'required|string|max:255',
        'license' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image file, size limit 2MB
        'phone' => 'required|string',
        'certificate' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image file, size limit 2MB
        'description' => 'nullable|string',
    ];
       
    }
}
