<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserLoginRequest extends FormRequest
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
            "username" => ['required' , 'min:1' , 'max:100'],
            "password" => ['required' , 'min:1' , 'max:100'],
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator){
        throw new HttpResponseException(response([
            "errors" => $validator->getMessageBag(),
        ] , 401));
    }
}
