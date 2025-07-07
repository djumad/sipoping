<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Gemini\Client;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Illuminate\Http\JsonResponse;
use App\Models\Absensi;
use Carbon\Carbon;

class AbsenController extends Controller
{
    // Tentukan jarak maksimal dalam meter
    private const MAX_DISTANCE_METERS = 25;

    /**
     * Memverifikasi wajah dan menyimpan data absensi dengan aturan lengkap.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id_jadwal_kantor
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, int $id_jadwal_kantor): JsonResponse
    {
        // 1. Validasi input
        $validatedData = $request->validate([
            'foto_absen' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'pekerjaan' => 'required|string|max:255',
            'dokumentasi' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'titik_lokasi' => [
                'required',
                'string',
                'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?),[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'
            ],
        ]);

        try {
            $user = Auth::user();
            $now = Carbon::now("Asia/Jayapura");

            // --- ATURAN BISNIS #1: Verifikasi Jarak Lokasi (Geofencing) ---
            $koordinatKantorStr = $user->pegawai->unor->pluck('kordinat')->first();
            if (!$koordinatKantorStr) {
                return response()->json(['error' => 'Lokasi kantor tidak terdaftar untuk pegawai ini.'], 404);
            }

            list($latKantor, $lonKantor) = array_map('trim', explode(',', $koordinatKantorStr));
            list($latAbsen, $lonAbsen) = array_map('trim', explode(',', $validatedData['titik_lokasi']));

            $distance = $this->calculateDistance((float)$latKantor, (float)$lonKantor, (float)$latAbsen, (float)$lonAbsen);
            Log::info("Jarak absensi pegawai ID {$user->pegawai->id}: " . round($distance, 2) . " meter.");

            if ($distance > self::MAX_DISTANCE_METERS) {
                return response()->json([
                    'error' => 'Gagal absen.',
                    'alasan' => 'Anda berada terlalu jauh dari lokasi kantor. Jarak Anda saat ini ' . round($distance, 2) . ' meter.'
                ], 403);
            }

            // --- ATURAN BISNIS #2: Tentukan Shift Otomatis ---
            $currentShift = $this->getCurrentShift($now);
            if (!$currentShift) {
                return response()->json(["error" => "Gagal absen.", "alasan" => "Saat ini di luar jam kerja."], 400);
            }

            // --- ATURAN BISNIS #3: Cek Absen Duplikat ---
            $alreadyAbsen = Absensi::where('pegawai_id', $user->pegawai->id)
                ->where('id_jadwal_kantor', $id_jadwal_kantor)
                ->where('status', $currentShift)
                ->whereDate('created_at', $now->today())
                ->exists();

            if ($alreadyAbsen) {
                return response()->json(["error" => "Gagal absen.", "alasan" => "Anda sudah absen untuk shift " . $currentShift . " hari ini."], 409);
            }

            // --- BAGIAN VERIFIKASI WAJAH ---
            $pathFotoPegawaiDB = $user->pegawai->foto;
            if (!$pathFotoPegawaiDB || !Storage::disk('public')->exists($pathFotoPegawaiDB)) {
                return response()->json(["error" => "Foto utama pegawai tidak ditemukan."], 404);
            }
            
            $fotoPegawaiContent = Storage::disk('public')->get($pathFotoPegawaiDB);
            $fotoAbsenFile = $request->file('foto_absen');
            
            if ($this->isJebretanHp($fotoAbsenFile)) {
                return response()->json(["error" => "Verifikasi wajah gagal.", "alasan" => "Gunakan foto selfie langsung."], 400);
            }

            if (!$this->_verifyFace($fotoPegawaiContent, $fotoAbsenFile)) {
                return response()->json(["error" => "Verifikasi wajah gagal.", "alasan" => "Wajah tidak cocok."], 403);
            }

            // --- JIKA SEMUA VERIFIKASI BERHASIL, LANJUTKAN PROSES ABSENSI ---
            Log::info("Verifikasi wajah berhasil untuk pegawai ID: " . $user->pegawai->id);

            $pathFotoAbsen = $request->file('foto_absen')->store('absen/selfie', 'public');
            $pathDokumentasi = $request->file('dokumentasi')->store('absen/dokumentasi', 'public');

            $absen = Absensi::create([
                'id_jadwal_kantor' => $id_jadwal_kantor,
                'pegawai_id' => $user->pegawai->id,
                'foto_pegawai' => $pathFotoAbsen,
                'status' => $currentShift,
                'pekerjaan' => $validatedData['pekerjaan'],
                'dokumentasi' => $pathDokumentasi,
                'titik_lokasi' => $validatedData['titik_lokasi'],
                'jam' => $now->toDateTimeString(),
            ]);

            return response()->json(['message' => 'Absen ' . $currentShift . ' berhasil dicatat!', 'data' => $absen], 201);

        } catch (\Exception $e) {
            Log::error('Error in AbsenController@store: ' . $e->getMessage());
            return response()->json(["error" => "Terjadi kesalahan pada server.", "message" => $e->getMessage()], 500);
        }
    }

    /**
     * Menghitung jarak antara dua titik koordinat GPS menggunakan formula Haversine.
     * @return float Jarak dalam meter.
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    private function getCurrentShift(Carbon $now): ?string
    {
        $pagiStart = $now->copy()->setTime(6, 0, 0);
        $pagiEnd = $now->copy()->setTime(12, 0, 0);
        $siangEnd = $now->copy()->setTime(17, 0, 0);
        $malamEnd = $now->copy()->setTime(23, 59, 59);

        if ($now->between($pagiStart, $pagiEnd, true)) return 'pagi';
        if ($now->between($pagiEnd, $siangEnd, false)) return 'siang';
        if ($now->between($siangEnd, $malamEnd, false)) return 'malam';
        
        return null;
    }

    private function _verifyFace(string $pegawaiImageContent, \Illuminate\Http\UploadedFile $absenImageFile): bool
    {
        try {
            $blobPegawai = new Blob(
                mimeType: $this->mapMimeTypeToGeminiEnum(Storage::disk('public')->mimeType(Auth::user()->pegawai->foto)),
                data: base64_encode($pegawaiImageContent)
            );
            $blobAbsen = new Blob(
                mimeType: $this->mapMimeTypeToGeminiEnum($absenImageFile->getMimeType()),
                data: base64_encode($absenImageFile->get())
            );

            $prompt = "Apakah kedua gambar ini menampilkan orang yang sama? Jawab hanya dengan satu kata: 'sama' atau 'berbeda'.";
            $client = $this->createGeminiClient();
            $response = $client->generativeModel('gemini-1.5-flash')->generateContent([$prompt, $blobPegawai, $blobAbsen]);
            $text = trim(strtolower($response->text()));
            Log::info("Face Verification Result: " . $text);
            return str_contains($text, 'sama');
        } catch (\Exception $e) {
            Log::error('Error in _verifyFace: ' . $e->getMessage());
            return false;
        }
    }

    private function isJebretanHp(\Illuminate\Http\UploadedFile $imageFile): bool
    {
        try {
            $blob = new Blob(
                mimeType: $this->mapMimeTypeToGeminiEnum($imageFile->getMimeType()),
                data: base64_encode($imageFile->get())
            );

            $prompt = "Deteksi apakah gambar ini adalah hasil foto dari layar. Jawab singkat: 'layar' atau 'langsung'.";
            $client = $this->createGeminiClient();
            $response = $client->generativeModel('gemini-1.5-flash')->generateContent([$prompt, $blob]);
            $text = trim(strtolower($response->text()));
            Log::info("Jebretan Detection Result: " . $text);
            return str_contains($text, 'layar') || str_contains($text, 'screenshot');
        } catch (\Exception $e) {
            Log::error('Error in isJebretanHp: ' . $e->getMessage());
            return false;
        }
    }

    private function mapMimeTypeToGeminiEnum(string $mimeTypeString): ?MimeType
    {
        return match ($mimeTypeString) {
            'image/jpeg', 'image/jpg' => MimeType::IMAGE_JPEG,
            'image/png' => MimeType::IMAGE_PNG,
            'image/webp' => MimeType::IMAGE_WEBP,
            default => null,
        };
    }

    private function createGeminiClient(): Client
    {
        return \Gemini::client(config('gemini.api_key'));
    }
}