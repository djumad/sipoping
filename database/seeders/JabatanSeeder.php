<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                "nama" => "Pemelihara Sarana dan Prasarana"
            ],
            [
                "nama" => "Pengadministrasi Umum"
            ],
            [
                "nama" => "Pengelola Administrasi Keuangaan"
            ],
            [
                "nama" => "Petugas Perlindungan dan Peredaran TSL"
            ],
            [
                "nama" => "Tenaga Pembantu Urusan Perencanaan dan Teknis"
            ],
            [
                "nama" => "Animal Keeper/Perawat Satwa"
            ],
            [
                "nama" => "Pengelola Kendaraan Dinas"
            ],
            [
                "nama" => "Driver"
            ],
            [
                "nama" => "Security"
            ],
            [
                "nama" => "Cleaning Service"
            ],
            [
                "nama" => "Tenaga IT"
            ],
            [
                "nama" => "Lainnya"
            ],
        ];

        foreach ($data as $row) { 
            Jabatan::create($row);
        }
    }
}
