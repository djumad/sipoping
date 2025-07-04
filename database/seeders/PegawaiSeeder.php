<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Status;
use App\Models\Unor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $djumad = Pegawai::create([
            "nama" => "Djumad Bantan",
            "jenis_kelamin" => "laki-laki",
            "nomor_telepon" => "085251210772",
            "foto" => "pegawai/djumad.jpg",
        ]);

        $djumad->status()->attach(Status::where('nama' , "Outsourching")->first());
        $djumad->jabatan()->attach(Jabatan::where("nama" , "Tenaga IT")->first());
        $djumad->unor()->attach(Unor::where("nama", "Kantor Balai")->first());
        User::create([
            "username" => "djumad@23",
            "password" => Hash::make("djumad@23123"),
            "pegawai_id" => $djumad->id,
            "role" => "user",
        ]);

        
        $adminSipoping = Pegawai::create([
            "nama" => "Admin Sipoping",
            "jenis_kelamin" => "laki-laki",
            "nomor_telepon" => "082249751210",
            "foto" => "pegawai/admin.jpg"
        ]);
        
        User::create([
            "username" => "adminsipoping@123",
            "password" => Hash::make("passwordAdminSipoping#121234"),
            "pegawai_id" => $adminSipoping->id,
            "role" => "admin",
        ]);
    }
}
