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
                'width'=>'required',
                'name' => 'required|string|max:255',
                'license' => 'required|string',
                'phone' => 'required|string',
                'certificate' => 'required|string',
                'description' => 'nullable|string',
                
               
            ];
       
    }
}
