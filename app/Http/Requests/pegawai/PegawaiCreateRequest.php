<?php

namespace App\Http\Requests\pegawai;

use Illuminate\Foundation\Http\FormRequest;

class PegawaiCreateRequest extends FormRequest
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
            "nama" => ["required", "min:1", "max:100"],
            "jenis_kelamin" => ["required", "min:1", "min:10" , "in:laki-laki,perempuan"],
            "nomor_telepon" => ["required", "min:10", "max:15"],
            "foto" => ["required", "min:1", "max:100"],

            "status_id" => ["required", "exists:statuses,id"],
            "jabatan_id" => ["required", "exists:jabatans,id"],
            "unor_id" => ["required", "exists:unors,id"],
        ];
    }
}
