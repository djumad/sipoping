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
        Schema::create('pegawai_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_id')->constrained('statuses')->onDelete('CASCADE');
            $table->foreignId('pegawai_id')->constrained('pegawais')->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai_statuses');
    }
};
