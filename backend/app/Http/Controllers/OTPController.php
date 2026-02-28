<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OTPController extends Controller
{
    public function generateOTP(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna belum terautentikasi. Silakan login terlebih dahulu.'
            ], 401);
        }

        $user = $request->user();

        if (!$user->no_wa) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp pengguna tidak terdaftar.'
            ], 400);
        }

        $apiUrl = config('services.whatsapp.api_url', env('WHATSAPP_API_URL'));
        $appKey = config('services.whatsapp.app_key', env('WHATSAPP_APP_KEY'));
        $authKey = config('services.whatsapp.auth_key', env('WHATSAPP_AUTH_KEY'));

        if (!$apiUrl || !$appKey || !$authKey) {
            return response()->json([
                'success' => false,
                'message' => 'Konfigurasi API WhatsApp belum lengkap. Cek file .env'
            ], 500);
        }

        // $noWa = $user->no_wa;

        // if (substr($noWa, 0, 1) === '0') {
        //     $noWa = '62' . substr($noWa, 1);
        // }

        $otpCode = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(1);

        $user->update([
            'otp_code' => $otpCode,
            'otp_expires_at' => $expiresAt,
        ]);

        $message = "Kode OTP Anda adalah: {$otpCode}. Berlaku selama 1 menit.";

        try {
            $response = Http::withHeaders([
                "Accept" => "*/*",
                "Content-Type" => "application/json"
            ])->post($apiUrl, [
                "appkey" => $appKey,
                "authkey" => $authKey,
                "to" => $user->no_wa,
                "message" => $message,
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim OTP ke WhatsApp.',
                    'error' => $response->json()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP dikirim ke WhatsApp anda.',
                'whatsapp_response' => $response->json()
            ], 200);
        } catch (Exception $e) {
            Log::error('Error kirim OTP: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim OTP.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifikasiOTP(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|min:6|max:6',
        ]);

        $otp = User::where('otp_code', $request->otp_code)->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid.'
            ], 400);
        }

        if ($otp->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP sudah kadaluwarsa.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil diverifikasi.'
        ], 200);
    }


    public function kirimUlangOTP(Request $request)
    {
        $user = $request->user();

        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna belum terautentikasi. Silakan login terlebih dahulu.'
            ], 401);
        }

        if (!$user->no_wa) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp pengguna tidak terdaftar.'
            ], 400);
        }

        $apiUrl = config('services.whatsapp.api_url', env('WHATSAPP_API_URL'));
        $appKey = config('services.whatsapp.app_key', env('WHATSAPP_APP_KEY'));
        $authKey = config('services.whatsapp.auth_key', env('WHATSAPP_AUTH_KEY'));

        if (!$apiUrl || !$appKey || !$authKey) {
            return response()->json([
                'success' => false,
                'message' => 'Konfigurasi API WhatsApp belum lengkap. Cek file .env'
            ], 500);
        }

        if ($user->otp_code && !$user->isExpired()) {
            $otpCode = $user->otp_code;
        } else {
            $otpCode = rand(100000, 999999);
            $expiresAt = Carbon::now()->addMinutes(1);

            $user->update([
                'otp_code' => $otpCode,
                'otp_expires_at' => $expiresAt,
            ]);
        }

        $message = "Kode OTP Anda adalah: {$otpCode}. Berlaku selama 1 menit.";

        $response = Http::withHeaders([
            "Accept" => "*/*",
            "Content-Type" => "application/json"
        ])->post($apiUrl, [
            "appkey" => $appKey,
            "authkey" => $authKey,
            "to" => $user->no_wa,
            "message" => $message,
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim OTP ke WhatsApp.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP dikirim ulang ke WhatsApp anda.',
            'whatsapp_response' => $response->json()
        ], 200);
    }

    public function getCities()
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY')
        ])->get('https://api.rajaongkir.com/starter/city');

        $data = $response->json();

        if ($response->successful()) {
            $cities = collect($data['rajaongkir']['results'])->pluck('city_name'); // Ambil hanya nama kota
            $randomCity = $cities->random(); // Pilih kota secara acak

            return response()->json([
                'success' => true,
                'message' => 'OTP berhasil dibuat.',
                'otp' => $randomCity
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kota.',
                'error' => $response->json()
            ], 500);
        }

        // if ($response->successful()) {
        //     return response()->json([
        //         'success' => true,
        //         'data' => $response->json()['rajaongkir']['results']
        //     ]);
        // } else {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Gagal mengambil data kota',
        //         'status_code' => $response->status(),
        //         'error' => $response->body()
        //     ]);
        // }
    }

    // public function getCities($city_id = null)
    // {
    //     if (!$city_id) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'ID provinsi tidak boleh kosong'
    //         ], 400);
    //     }

    //     $response = Http::get("https://ibnux.github.io/data-indonesia/kota/{$city_id}.json");

    //     if ($response->successful()) {
    //         return response()->json([
    //             'success' => true,
    //             'data' => $response->json()
    //         ]);
    //     } else {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal mengambil data kota'
    //         ], 500);
    //     }
    // }

    // public function getRandomCity()
    // {
    //     $cities = [];

    //     for ($i = 1; $i <= 34; $i++) { 
    //         $response = Http::get("https://ibnux.github.io/data-indonesia/kota/{$i}.json");

    //         if ($response->successful()) {
    //             $cities = array_merge($cities, $response->json());
    //         }
    //     }

    //     if (empty($cities)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal mengambil data kota'
    //         ], 500);
    //     }

    //     $randomCity = $cities[array_rand($cities)];

    //     return response()->json([
    //         'success' => true,
    //         'data' => $randomCity['nama']
    //     ]);
    // }
}
