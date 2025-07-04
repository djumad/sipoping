<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $guarded = [];

    public function user(){
        return $this->hasOne(User::class);
    }

    public function jabatan(){
        return $this->belongsToMany(Jabatan::class , "jabatan_pegawais");
    }

    public function unor(){
        return $this->belongsToMany(Unor::class ,"pegawai_unors");
    }

    public function status(){
        return $this->belongsToMany(Status::class ,"pegawai_statuses");
    }
}
