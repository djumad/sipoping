<?php

namespace App\Console\Commands;

use App\Models\Jadwalkantor;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateJadwalKantor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-jadwal-kantor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat jadwal kantor setiap 5 menit (jika belum ada)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $currentMinute = $now->minute;
        $roundedMinute = floor($currentMinute / 5) * 5;
        
        $tanggalSaatIni = $now->copy()
                            ->minute($roundedMinute)
                            ->second(0)
                            ->startOfMinute();

        // Cek apakah data dengan waktu yang sama sudah ada
        $existing = Jadwalkantor::where('tanggal', $tanggalSaatIni)->exists();

        if (!$existing) {
            Jadwalkantor::create([
                'tanggal' => $tanggalSaatIni,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->info("✅ Jadwal kantor berhasil dibuat: {$tanggalSaatIni}");
        } else {
            $this->info("ℹ️ Jadwal kantor pada {$tanggalSaatIni} sudah ada.");
        }

        return Command::SUCCESS;
    }
}