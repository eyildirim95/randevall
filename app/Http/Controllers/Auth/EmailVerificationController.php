<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * E-posta dogrulama akisi (soft): kullanici panele girebilir,
 * ancak dogrulamasi icin nazik bir hatirlatma gorur.
 */
class EmailVerificationController extends Controller
{
    /** Dogrulama bilgilendirme sayfasi. */
    public function notice(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectHome($request);
        }

        return view('auth.verify-email');
    }

    /** İmzali dogrulama linki. */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->fulfill(); // email_verified_at atar + Verified event
        }

        return $this->redirectHome($request)->with('success', 'E-posta adresiniz doğrulandı. Teşekkürler! 🎉');
    }

    /** Dogrulama e-postasini yeniden gonder. */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectHome($request);
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Doğrulama bağlantısı e-posta adresinize gönderildi.');
    }

    /** Kullaniciyi kendi isletme paneline (veya super admin ise admin'e) yonlendir. */
    private function redirectHome(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $business = $user->businesses()->first();

        return $business
            ? redirect()->route('panel.dashboard', $business)
            : redirect()->route('landing');
    }
}
