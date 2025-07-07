<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Jadwalkantor;
use Carbon\Carbon;

class CekJadwalHariIni
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $today = Carbon::now('Asia/Jayapura')->toDateString();
        // throw new HttpResponseException(response(["data" => $today]));
        $jadwal = Jadwalkantor::whereDate('tanggal', $today)->first();

        if (!$jadwal) {

            Jadwalkantor::create([
                'tanggal' => Carbon::now('Asia/Jayapura'),
            ]);
        }

        return $next($request);
    }
}
