<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;


class AddRecommendationRequest extends FormRequest
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
            'content'        => 'required|string|max:1000',
            'product_id'     => 'required|exists:products,id',
            'pharmacist_id'  => 'required|exists:pharmacists,id',
            'starnumber'     => 'required|max:5|min:1'
        ];
    }
        protected function failedValidation(Validator $validator): void
{
    throw new HttpResponseException(
        response()->json(['errors' => $validator->errors()], 422)
    );
}
}
