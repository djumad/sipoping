<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'username' => $this->username,
            'token' => $this->token,
            'nama' => $this->pegawai->nama,
            'foto' => $this->pegawai->foto,
            "jabatan" => $this->pegawai->jabatan->pluck("nama"),
            "status" => $this->pegawai->status->pluck("nama"),
            "unor" => $this->pegawai->unor->pluck("nama"),
            "role" => $this->role,
        ];
    }
}
