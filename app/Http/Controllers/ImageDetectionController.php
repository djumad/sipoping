<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Illuminate\Support\Facades\Log;
use Gemini\Client;
use Illuminate\Http\JsonResponse;
use JsonException;

class ImageDetectionController extends Controller
{
    /**
     * Membandingkan dua gambar dan mendeteksi apakah orangnya sama atau berbeda.
     * Juga mendeteksi apakah gambar merupakan "jebretan HP".
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function compareImages(Request $request)
    {
        $request->validate([
            'image1' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'image2' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        try {
            $image1File = $request->file('image1');
            $image2File = $request->file('image2');

            $image1Content = $image1File->get();
            $image2Content = $image2File->get();

            $image1MimeType = $this->mapMimeTypeToGeminiEnum($image1File->getMimeType());
            $image2MimeType = $this->mapMimeTypeToGeminiEnum($image2File->getMimeType());

            if (!$image1MimeType || !$image2MimeType) {
                return response()->json([
                    "error" => "Tipe file gambar tidak didukung oleh Gemini API."
                ], 400);
            }

            // PERBAIKAN: Selalu gunakan base64_encode untuk data biner.
            // Ini adalah cara paling aman untuk mengirim gambar melalui JSON.
            $image1Blob = new Blob(
                mimeType: $image1MimeType,
                data: base64_encode($image1Content)
            );

            $image2Blob = new Blob(
                mimeType: $image2MimeType,
                data: base64_encode($image2Content)
            );

            $isImage1Jebretan = $this->isJebretanHp($image1Blob);
            $isImage2Jebretan = $this->isJebretanHp($image2Blob);

            Log::info("Image 1 Jebretan: " . ($isImage1Jebretan ? 'Yes' : 'No'));
            Log::info("Image 2 Jebretan: " . ($isImage2Jebretan ? 'Yes' : 'No'));

            if ($isImage1Jebretan || $isImage2Jebretan) {
                return response()->json([
                    "hasil" => "jangan gunakan foto hasil jebretan lain, gunakan foto anda yang sekarang"
                ]);
            } else {
                return $this->compareFaces($image1Blob, $image2Blob);
            }

        } catch (\Exception $e) {
            Log::error('Error in ImageDetectionController: ' . $e->getMessage());
            return response()->json([
                "error" => "Terjadi kesalahan pada server.",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Maps a standard MIME type string to a Gemini\Enums\MimeType enum.
     *
     * @param string $mimeTypeString
     * @return MimeType|null
     */
    private function mapMimeTypeToGeminiEnum(string $mimeTypeString): ?MimeType
    {
        return match ($mimeTypeString) {
            'image/jpeg', 'image/jpg' => MimeType::IMAGE_JPEG,
            'image/png' => MimeType::IMAGE_PNG,
            'image/webp' => MimeType::IMAGE_WEBP,
            default => null,
        };
    }

    /**
     * Create Gemini API client with proper configuration.
     *
     * @return Client
     */
    private function createGeminiClient(): Client
    {
        return \Gemini::client(config('gemini.api_key'));
    }

    /**
     * Fungsi helper untuk mendeteksi apakah gambar adalah "jebretan HP".
     *
     * @param Blob $imageBlob
     * @return bool
     */
    private function isJebretanHp(Blob $imageBlob): bool
    {
        try {
            $prompt = "Deteksi apakah gambar ini adalah hasil foto dari layar (screenshot atau foto dari layar hp/monitor lain) atau hasil foto langsung dari kamera. Jawab hanya dengan satu kata: 'layar' atau 'langsung'.";
            
            $client = $this->createGeminiClient();

            $response = $client->generativeModel('gemini-1.5-flash')->generateContent([
                $prompt,
                $imageBlob
            ]);

            $text = trim(strtolower($response->text()));
            Log::info("Jebretan Detection Raw Text: " . $text);

            return str_contains($text, 'layar') || str_contains($text, 'screenshot') || str_contains($text, 'jebretan');

        } catch (JsonException $e) {
            Log::warning('Error in isJebretanHp (JsonException): ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error('Error in isJebretanHp (Exception): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fungsi helper untuk membandingkan wajah di dua gambar.
     *
     * @param Blob $image1Blob
     * @param Blob $image2Blob
     * @return \Illuminate\Http\JsonResponse
     */
    private function compareFaces(Blob $image1Blob, Blob $image2Blob): JsonResponse
    {
        try {
            $prompt = "Apakah kedua gambar ini menampilkan orang yang sama? Jawab hanya dengan satu kata: 'sama' atau 'berbeda'.";
            
            $client = $this->createGeminiClient();

            $response = $client->generativeModel('gemini-1.5-flash')->generateContent([
                $prompt,
                $image1Blob,
                $image2Blob
            ]);

            $text = trim(strtolower($response->text()));
            Log::info("Comparison Raw Text: " . $text);

            if (str_contains($text, 'sama')) {
                return response()->json(["hasil" => "sama"]);
            } elseif (str_contains($text, 'berbeda')) {
                return response()->json(["hasil" => "berbeda"]);
            } else {
                return response()->json(["hasil" => "tidak dapat memastikan", "reason" => $text], 200);
            }

        } catch (JsonException $e) {
            Log::warning('Error in compareFaces (JsonException): ' . $e->getMessage());
            return response()->json([
                "error" => "Gagal membandingkan wajah.",
                "message" => "API mengembalikan respons yang tidak valid.",
            ], 502);
        } catch (\Exception $e) {
            Log::error('Error in compareFaces (Exception): ' . $e->getMessage());
            return response()->json([
                "error" => "Gagal membandingkan wajah.",
                "message" => $e->getMessage()
            ], 500);
        }
    }
}