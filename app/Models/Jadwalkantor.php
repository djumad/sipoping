<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwalkantor extends Model
{
    protected $guarded = [];

    public function absen(){
        return $this->hasMany(Absensi::class);
    }
}
