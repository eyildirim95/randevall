<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Tek giris noktasi: super admin, isletme sahibi ve personel
 * ayni formdan giris yapar; rolune gore yonlendirilir.
 */
class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = strtolower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Çok fazla deneme yaptınız. Lütfen '.RateLimiter::availableIn($throttleKey).' saniye sonra tekrar deneyin.',
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'E-posta adresi veya şifre hatalı.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Hesabınız devre dışı bırakılmış. Lütfen destek ile iletişime geçin.',
            ]);
        }

        // Oturum sabitleme (session fixation) korumasi
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(self::homeFor($user));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }

    /** Kullanicinin rolune gore varsayilan panel adresi. */
    public static function homeFor($user): string
    {
        if ($user->isSuperAdmin()) {
            return route('admin.dashboard');
        }

        $business = $user->businesses()->first();

        if ($business) {
            return route('panel.dashboard', $business);
        }

        return route('landing');
    }
}
