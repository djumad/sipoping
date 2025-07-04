<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                "nama" => "PPNPN"
            ],
            [
                "nama" => "Outsourching"
            ],
            [
                "nama" => "Admin Sipoping"
            ],
        ];

        foreach ($data as $key => $value) {
            Status::create($value);
        }
    }
}
