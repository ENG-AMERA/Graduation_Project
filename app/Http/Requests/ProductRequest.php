<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ProductRequest extends FormRequest
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
public function rules() : array
{
        $rules = [
            'name'        => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'category_id' => ['required','exists:categories,id'],
            'pharma_id'   => ['required','exists:pharmas,id'],
            'has_types'   => ['required','in:0,1'],
            'image'       => ['nullable','image','mimes:jpeg,png,jpg'],
            'price'       => ['required_if:has_types,0','numeric','min:0'],
            'quantity'    => ['required_if:has_types,0','integer','min:0'],
        ];

        if ($this->input('has_types') == '1') {
            $rules = array_merge($rules, [
                'tname'       => ['required','array'],
                'tname.*'     => ['required','string','max:255'],
                'tprice'      => ['required','array'],
                'tprice.*'    => ['required','numeric','min:0'],
                'tquantity'  => ['required','array'],
                'tquantity.*'=> ['required','integer','min:0'],
                'timage'      => ['required','array'],
                'timage.*'    => ['required','image','mimes:jpeg,png,jpg'],
            ]);
        }

        return $rules;
    }

protected function failedValidation(Validator $validator): void
{
    throw new HttpResponseException(
        response()->json(['errors' => $validator->errors()], 422)
    );
}

}


