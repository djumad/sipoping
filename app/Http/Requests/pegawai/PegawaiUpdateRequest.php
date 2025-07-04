<?php

namespace App\Http\Requests\pegawai;

use Illuminate\Foundation\Http\FormRequest;

class PegawaiUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "nama" => ["nullable", "min:1", "max:100"],
            "jenis_kelamin" => ["nullable", "min:1", "min:10" , "in:laki-laki,perempuan"],
            "nomor_telepon" => ["nullable", "min:10", "max:15"],
            "foto" => ["nullable", "min:1", "max:100"],

            "status_id" => ["nullable", "exists:statuses,id"],
            "jabatan_id" => ["nullable", "exists:jabatans,id"],
            "unor_id" => ["nullable", "exists:unors,id"],
        ];
    }
}
