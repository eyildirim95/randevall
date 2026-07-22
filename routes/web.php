<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Booking\BookingController;
use App\Http\Controllers\Booking\PublicAppointmentController;
use App\Http\Controllers\Landing\LandingController;
use App\Http\Controllers\Panel;
use App\Http\Controllers\SuperAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing (pazarlama sitesi)
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('landing');

/*
|--------------------------------------------------------------------------
| Yasal sayfalar (KVKK, kosullar, mesafeli satis, cerez)
|--------------------------------------------------------------------------
*/
Route::view('/kullanim-kosullari', 'legal.terms')->name('legal.terms');
Route::view('/gizlilik-politikasi', 'legal.privacy')->name('legal.privacy');
Route::view('/mesafeli-satis-sozlesmesi', 'legal.distance-sales')->name('legal.distance-sales');
Route::view('/cerez-politikasi', 'legal.cookies')->name('legal.cookies');

// Basit sitemap (landing + yasal sayfalar)
Route::get('/sitemap.xml', function () {
    $urls = [
        route('landing'),
        route('legal.terms'),
        route('legal.privacy'),
        route('legal.distance-sales'),
        route('legal.cookies'),
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
        .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach ($urls as $url) {
        $xml .= '  <url><loc>'.e($url).'</loc></url>'."\n";
    }
    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');

/*
|--------------------------------------------------------------------------
| Kimlik dogrulama (tek giris noktasi)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/kayit', [\App\Http\Controllers\Auth\RegisterController::class, 'create'])->name('register');
    Route::post('/kayit', [\App\Http\Controllers\Auth\RegisterController::class, 'store'])->middleware('throttle:5,10')->name('register.store');
    Route::get('/kayit/slug-kontrol', [\App\Http\Controllers\Auth\RegisterController::class, 'checkSlug'])->middleware('throttle:30,1')->name('register.check-slug');

    Route::get('/giris', [LoginController::class, 'show'])->name('login');
    Route::post('/giris', [LoginController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
    Route::get('/sifremi-unuttum', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/sifremi-unuttum', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:5,10')->name('password.email');
    Route::get('/sifre-sifirla/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/sifre-sifirla', [PasswordResetController::class, 'reset'])->middleware('throttle:5,10')->name('password.update');
});

Route::post('/cikis', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| E-posta dogrulama (soft — panel erisimini engellemez)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/e-posta/dogrula', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'notice'])
        ->name('verification.notice');
    Route::get('/e-posta/dogrula/{id}/{hash}', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/e-posta/dogrula/tekrar-gonder', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Musteri randevu goruntuleme / iptal (token ile, giris gerektirmez)
|--------------------------------------------------------------------------
*/
Route::get('/randevu/{token}', [PublicAppointmentController::class, 'show'])->name('appointment.public.show');
Route::post('/randevu/{token}/iptal', [PublicAppointmentController::class, 'cancel'])
    ->middleware('throttle:10,10')
    ->name('appointment.public.cancel');
Route::get('/randevu/{token}/yeniden-planla', [PublicAppointmentController::class, 'rescheduleForm'])->name('appointment.public.reschedule');
Route::post('/randevu/{token}/yeniden-planla', [PublicAppointmentController::class, 'reschedule'])
    ->middleware('throttle:10,10')
    ->name('appointment.public.reschedule.store');
Route::get('/randevu/{token}/degerlendir', [PublicAppointmentController::class, 'ratingForm'])->name('appointment.public.rate');
Route::post('/randevu/{token}/degerlendir', [PublicAppointmentController::class, 'rate'])
    ->middleware('throttle:10,10')
    ->name('appointment.public.rate.store');

/*
|--------------------------------------------------------------------------
| Takvim beslemesi (ICS — Google/Apple/Outlook "URL ile ekle")
|--------------------------------------------------------------------------
*/
Route::get('/takvim-feed/{token}.ics', [\App\Http\Controllers\Booking\CalendarFeedController::class, 'show'])
    ->where('token', '[0-9a-f-]{36}')
    ->name('calendar.feed');

/*
|--------------------------------------------------------------------------
| Odeme sayfalari (token ile erisim)
|--------------------------------------------------------------------------
*/
Route::get('/odeme/{payment:token}', [Panel\PaymentPageController::class, 'show'])->name('payment.show');
Route::match(['GET', 'POST'], '/odeme/{payment:token}/callback', [Panel\PaymentPageController::class, 'callback'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('payment.callback');
Route::get('/odeme/{payment:token}/sonuc', [Panel\PaymentPageController::class, 'result'])->name('payment.result');

/*
|--------------------------------------------------------------------------
| Super Admin paneli (/admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'superadmin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [SuperAdmin\DashboardController::class, 'index'])->name('dashboard');

        // Isletmeler
        Route::get('/isletmeler', [SuperAdmin\BusinessController::class, 'index'])->name('businesses.index');
        Route::get('/isletmeler/yeni', [SuperAdmin\BusinessController::class, 'create'])->name('businesses.create');
        Route::post('/isletmeler', [SuperAdmin\BusinessController::class, 'store'])->name('businesses.store');
        Route::get('/isletmeler/{business}', [SuperAdmin\BusinessController::class, 'show'])->name('businesses.show');
        Route::get('/isletmeler/{business}/duzenle', [SuperAdmin\BusinessController::class, 'edit'])->name('businesses.edit');
        Route::put('/isletmeler/{business}', [SuperAdmin\BusinessController::class, 'update'])->name('businesses.update');
        Route::post('/isletmeler/{business}/abonelik', [SuperAdmin\BusinessSubscriptionController::class, 'store'])->name('businesses.subscriptions.store');
        Route::post('/isletmeler/{business}/askiya-al', [SuperAdmin\BusinessController::class, 'suspend'])->name('businesses.suspend');
        Route::post('/isletmeler/{business}/aktif-et', [SuperAdmin\BusinessController::class, 'activate'])->name('businesses.activate');
        Route::delete('/isletmeler/{business}', [SuperAdmin\BusinessController::class, 'destroy'])->name('businesses.destroy');

        // Impersonation
        Route::post('/isletmeler/{business}/giris-yap', [SuperAdmin\ImpersonationController::class, 'start'])->name('impersonate.start');

        // Planlar
        Route::resource('/planlar', SuperAdmin\PlanController::class)
            ->parameters(['planlar' => 'plan'])
            ->names('plans')
            ->except(['show']);

        // Odemeler
        Route::get('/odemeler', [SuperAdmin\PaymentController::class, 'index'])->name('payments.index');
        Route::post('/odemeler/{payment}/onayla', [SuperAdmin\PaymentController::class, 'markPaid'])->name('payments.mark-paid');

        // Duyurular
        Route::resource('/duyurular', SuperAdmin\AnnouncementController::class)
            ->parameters(['duyurular' => 'announcement'])
            ->names('announcements')
            ->only(['index', 'store', 'update', 'destroy']);

        // Raporlar & ayarlar
        Route::get('/raporlar', [SuperAdmin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/ayarlar', [SuperAdmin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::post('/ayarlar', [SuperAdmin\SettingsController::class, 'update'])->name('settings.update');
        Route::post('/ayarlar/whatsapp-test', [SuperAdmin\SettingsController::class, 'testWhatsApp'])->name('settings.whatsapp-test');
        Route::post('/ayarlar/sms-test', [SuperAdmin\SettingsController::class, 'testSms'])->name('settings.sms-test');

        // Mesaj loglari
        Route::get('/mesajlar', [SuperAdmin\MessageLogController::class, 'index'])->name('messages.index');

        // Destek talepleri
        Route::get('/destek', [SuperAdmin\TicketController::class, 'index'])->name('tickets.index');
        Route::get('/destek/{ticket}', [SuperAdmin\TicketController::class, 'show'])->name('tickets.show');
        Route::post('/destek/{ticket}/yanit', [SuperAdmin\TicketController::class, 'reply'])->name('tickets.reply');
        Route::patch('/destek/{ticket}/durum', [SuperAdmin\TicketController::class, 'updateStatus'])->name('tickets.status');
    });

// Impersonation'dan cikis (super admin, tenant baglami disinda)
Route::post('/admin/impersonation-birak', [SuperAdmin\ImpersonationController::class, 'stop'])
    ->middleware('auth')
    ->name('admin.impersonate.stop');

/*
|--------------------------------------------------------------------------
| Tenant: online rezervasyon (public) — domain.com/{slug}
|--------------------------------------------------------------------------
*/
Route::prefix('{business}')
    ->where(['business' => '[a-z0-9-]+'])
    ->middleware('tenant')
    // Alt kayitlar (randevu, musteri...) her zaman isletme uzerinden cozulur (IDOR korumasi)
    ->scopeBindings()
    ->group(function () {

        // Public rezervasyon sayfasi
        Route::get('/', [BookingController::class, 'show'])->name('booking.show');
        Route::get('/uygun-saatler', [BookingController::class, 'slots'])->name('booking.slots');
        Route::post('/randevu', [BookingController::class, 'store'])
            ->middleware('throttle:10,10')
            ->name('booking.store');
        Route::post('/bekleme-listesi', [BookingController::class, 'joinWaitlist'])
            ->middleware('throttle:10,10')
            ->name('booking.waitlist');

        /*
        |----------------------------------------------------------------------
        | Tenant: isletme paneli — domain.com/{slug}/panel
        |----------------------------------------------------------------------
        */
        Route::prefix('panel')
            ->name('panel.')
            ->middleware(['auth', 'business.access', 'subscription.active'])
            ->group(function () {
                Route::get('/', [Panel\DashboardController::class, 'index'])->name('dashboard');

                // Bildirimler
                Route::post('/bildirimler/okundu', [Panel\NotificationController::class, 'markAllRead'])->name('notifications.read');

                // Takvim
                Route::get('/takvim', [Panel\CalendarController::class, 'index'])->name('calendar');
                Route::get('/takvim/olaylar', [Panel\CalendarController::class, 'events'])->name('calendar.events');

                // Randevular
                Route::get('/randevular', [Panel\AppointmentController::class, 'index'])->name('appointments.index');
                Route::post('/randevular', [Panel\AppointmentController::class, 'store'])->name('appointments.store');
                Route::get('/randevular/{appointment}', [Panel\AppointmentController::class, 'show'])->name('appointments.show');
                Route::put('/randevular/{appointment}', [Panel\AppointmentController::class, 'update'])->name('appointments.update');
                Route::patch('/randevular/{appointment}/tasi', [Panel\AppointmentController::class, 'move'])->name('appointments.move');
                Route::patch('/randevular/{appointment}/durum', [Panel\AppointmentController::class, 'updateStatus'])->name('appointments.status');
                Route::delete('/randevular/{appointment}', [Panel\AppointmentController::class, 'destroy'])->name('appointments.destroy');

                // Musteriler
                Route::resource('/musteriler', Panel\CustomerController::class)
                    ->parameters(['musteriler' => 'customer'])
                    ->names('customers');
                Route::post('/musteriler/{customer}/puan', [Panel\CustomerController::class, 'adjustPoints'])->name('customers.points');
                Route::post('/musteriler/{customer}/takip-notu', [Panel\CustomerController::class, 'storeRecord'])->name('customers.records.store');
                Route::delete('/musteriler/{customer}/takip-notu/{record}', [Panel\CustomerController::class, 'destroyRecord'])->name('customers.records.destroy');

                // Destek talepleri
                Route::get('/destek', [Panel\TicketController::class, 'index'])->name('tickets.index');
                Route::get('/destek/yeni', [Panel\TicketController::class, 'create'])->name('tickets.create');
                Route::post('/destek', [Panel\TicketController::class, 'store'])->middleware('throttle:10,10')->name('tickets.store');
                Route::get('/destek/{ticket}', [Panel\TicketController::class, 'show'])->name('tickets.show');
                Route::post('/destek/{ticket}/yanit', [Panel\TicketController::class, 'reply'])->middleware('throttle:20,10')->name('tickets.reply');
                Route::post('/destek/{ticket}/kapat', [Panel\TicketController::class, 'close'])->name('tickets.close');

                // Notlar
                Route::resource('/notlar', Panel\NoteController::class)
                    ->parameters(['notlar' => 'note'])
                    ->names('notes')
                    ->only(['index', 'store', 'update', 'destroy']);

                // Yonetici bolumleri
                Route::middleware('business.manager')->group(function () {
                    // Hizmetler
                    Route::resource('/hizmetler', Panel\ServiceController::class)
                        ->parameters(['hizmetler' => 'service'])
                        ->names('services')
                        ->except(['show']);

                    // Personel
                    Route::resource('/personel', Panel\StaffController::class)
                        ->parameters(['personel' => 'staff'])
                        ->names('staff')
                        ->except(['show']);

                    // Calisma saatleri
                    Route::get('/calisma-saatleri', [Panel\WorkingHourController::class, 'edit'])->name('working-hours.edit');
                    Route::post('/calisma-saatleri', [Panel\WorkingHourController::class, 'update'])->name('working-hours.update');

                    // Kampanyalar
                    Route::get('/kampanyalar', [Panel\CampaignController::class, 'index'])->name('campaigns.index');
                    Route::post('/kampanyalar', [Panel\CampaignController::class, 'store'])->middleware('throttle:5,60')->name('campaigns.store');
                    Route::delete('/kampanyalar/{campaign}', [Panel\CampaignController::class, 'destroy'])->name('campaigns.destroy');

                    // Bekleme listesi
                    Route::get('/bekleme-listesi', [Panel\WaitlistController::class, 'index'])->name('waitlist.index');
                    Route::delete('/bekleme-listesi/{waitingListEntry}', [Panel\WaitlistController::class, 'destroy'])->name('waitlist.destroy');

                    // Takvim kapatma
                    Route::get('/takvim-kapatma', [Panel\ClosureController::class, 'index'])->name('closures.index');
                    Route::post('/takvim-kapatma', [Panel\ClosureController::class, 'store'])->name('closures.store');
                    Route::delete('/takvim-kapatma/{closure}', [Panel\ClosureController::class, 'destroy'])->name('closures.destroy');

                    // Gelir-Gider
                    Route::get('/gelir-gider', [Panel\TransactionController::class, 'index'])->name('transactions.index');
                    Route::post('/gelir-gider', [Panel\TransactionController::class, 'store'])->name('transactions.store');
                    Route::put('/gelir-gider/{transaction}', [Panel\TransactionController::class, 'update'])->name('transactions.update');
                    Route::delete('/gelir-gider/{transaction}', [Panel\TransactionController::class, 'destroy'])->name('transactions.destroy');

                    // Raporlar
                    Route::get('/raporlar', [Panel\ReportController::class, 'index'])->name('reports.index');
                    Route::get('/raporlar/disa-aktar', [Panel\ReportController::class, 'export'])->name('reports.export');

                    // Ayarlar
                    Route::get('/ayarlar', [Panel\SettingsController::class, 'edit'])->name('settings.edit');
                    Route::post('/ayarlar', [Panel\SettingsController::class, 'update'])->name('settings.update');
                    Route::post('/ayarlar/whatsapp-test', [Panel\SettingsController::class, 'testWhatsApp'])->name('settings.whatsapp-test');
                    Route::post('/ayarlar/sms-test', [Panel\SettingsController::class, 'testSms'])->name('settings.sms-test');

                    // Abonelik
                    Route::get('/abonelik', [Panel\SubscriptionController::class, 'index'])->name('subscription.index');
                });
            });
    });
