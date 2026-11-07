<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Controller for Session-based Authentication Foundation
 */
class AuthController extends Controller
{
    /**
     * Show the login page with persona options
     */
    public function showLoginForm()
    {
        return view('auth.login', [
            'personas' => [
                [
                    'role' => 'Nelayan / Nakhoda',
                    'email' => 'nelayan@seacast.id',
                    'desc' => 'Akses zona penangkapan ikan (Z1, Z2), rute aman gelombang, dan peringatan dini cuaca BMKG.',
                ],
                [
                    'role' => 'Syahbandar & Otoritas Pelabuhan',
                    'email' => 'syahbandar@seacast.id',
                    'desc' => 'Pemantauan keselamatan pelayaran, verifikasi SPB, dan mitigasi bahaya gelombang ekstrem.',
                ],
                [
                    'role' => 'Peneliti Oseanografi & BRIN',
                    'email' => 'peneliti@seacast.id',
                    'desc' => 'Akses telemetri satelit (SST, Klorofil-a), evaluasi ensemble SDM (AUC/TSS), dan skenario iklim.',
                ],
                [
                    'role' => 'Administrator Sistem',
                    'email' => 'admin@seacast.id',
                    'desc' => 'Pengawasan kesehatan basis data PostGIS 3.6, latensi query, dan orkestrasi inferensi harian.',
                ],
            ]
        ]);
    }

    /**
     * Handle authentication login attempt
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Phase 1.1 Remediation: Production Demo Credential Guard
        $knownDemoEmails = ['nelayan@seacast.id', 'syahbandar@seacast.id', 'peneliti@seacast.id', 'admin@seacast.id'];
        $isProduction = app()->environment('production') || config('app.env') === 'production';
        if ($isProduction && in_array(strtolower($credentials['email']), $knownDemoEmails, true) && $credentials['password'] === 'seacast2026') {
            throw ValidationException::withMessages([
                'email' => ['Kredensial demo bawaan dinonaktifkan secara otomatis pada lingkungan produksi demi kepatuhan keamanan.'],
            ]);
        }

        $throttleKey = 'login-attempt:' . $request->ip() . '|' . strtolower($credentials['email']);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => ["Terlalu banyak percobaan masuk. Silakan coba kembali dalam {$seconds} detik."],
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang di Ruang Kendali SEACAST.');
        }

        RateLimiter::hit($throttleKey, 60);

        throw ValidationException::withMessages([
            'email' => ['Kredensial yang diberikan tidak cocok dengan data pengguna kami.'],
        ]);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Anda telah berhasil keluar dari sesi sistem.');
    }
}

