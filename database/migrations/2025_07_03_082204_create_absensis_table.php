<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_jadwal_kantor')->constrained('jadwalkantors')->cascadeOnDelete();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->string('foto_pegawai');
            $table->enum('status' , [ 'pagi', 'siang' , 'malam'])->default('pagi');
            $table->string('pekerjaan');
            $table->string('dokumentasi');
            $table->string('titik_lokasi');
            $table->timestamp('jam');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
