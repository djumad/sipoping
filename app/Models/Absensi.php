<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $guarded = [];

    public function jadwalKantor(){
        return $this->belongsTo(Jadwalkantor::class);
    }
}
