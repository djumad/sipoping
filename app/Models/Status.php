<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $guarded = [];

    public function pegawai(){
        return $this->belongsToMany(Pegawai::class , "pegawai_unors");
    }

}
