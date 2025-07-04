<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $guarded = [];
    
    public function pegawai(){
        return $this->belongsToMany(Pegawai::class , "jabatan_pegawais");
    }
    
}
