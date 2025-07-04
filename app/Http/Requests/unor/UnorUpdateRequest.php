<?php

namespace App\Http\Requests\unor;

use Illuminate\Foundation\Http\FormRequest;

class UnorUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "nama" => ["nullable" , "min:1" , "max:100"],
            "nip" => ["nullable" , "min:1" , "max:100"],
            "nama_pimpinan" => ["nullable" , "min:1" ,"max:100"],
            "jabatan" => ["nullable" , "min:1" , "max:100"],
            "cap" => ["nullable" , "image", "max:2048"],
            "ttd" => ["nullable" , "image", "max:2048"],
            "kordinat" => ["nullable" , "min:5" , "max:100"],
            "nomor_telepon" => ["nullable" , "min:10" , "max:15"]
        ];
    }
}
