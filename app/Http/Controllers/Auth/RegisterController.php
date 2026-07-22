<?php

namespace App\Http\Controllers\Auth;

use App\Enums\BusinessRole;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SuperAdmin\BusinessController;
use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Self-servis isletme kaydi: landing'den isletme ac, panele gir.
 */
class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'sectors' => ['Berber', 'Kuaför', 'Güzellik Salonu', 'Klinik / Sağlık', 'Spor / PT', 'Diğer'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:150'],
            'slug' => [
                'required', 'string', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn(BusinessController::RESERVED_SLUGS),
                Rule::unique('businesses', 'slug'),
            ],
            'sector' => ['nullable', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'min:10', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
            'website' => ['prohibited'], // honeypot
        ], [
            'slug.regex' => 'Adres yalnızca küçük harf, rakam ve tire içerebilir.',
            'slug.unique' => 'Bu adres alınmış; başka bir adres deneyin.',
            'slug.not_in' => 'Bu adres kullanılamaz; başka bir adres deneyin.',
        ]);

        $user = DB::transaction(function () use ($data) {
            // Varsayilan plan: one cikan, yoksa ilk aktif plan
            $plan = SubscriptionPlan::query()->active()->orderByDesc('is_featured')->first();

            $business = new Business([
                'name' => $data['business_name'],
                'slug' => Str::lower($data['slug']),
                'sector' => $data['sector'] ?? null,
                'phone' => $data['phone'],
                'email' => $data['email'],
            ]);
            $business->subscription_plan_id = $plan?->id;
            $business->trial_ends_at = ($plan?->trial_days ?? config('legal.trial_days', 0)) > 0
                ? now()->addDays($plan->trial_days)
                : null;
            $business->save();

            // Varsayilan calisma saatleri: Pzt-Cmt 09:00-19:00, Pazar kapali
            foreach (range(0, 6) as $dow) {
                $hour = new WorkingHour([
                    'day_of_week' => $dow,
                    'is_closed' => $dow === 0,
                    'start_time' => $dow === 0 ? null : '09:00',
                    'end_time' => $dow === 0 ? null : '19:00',
                ]);
                $hour->business_id = $business->id;
                $hour->save();
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
            ]);

            $business->users()->attach($user->id, ['role' => BusinessRole::Owner->value]);

            return $user->fresh();
        });

        Auth::login($user);
        $request->session()->regenerate();

        // Dogrulama e-postasi gonder (mail hatasi kaydi engellemesin)
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);
        }

        $business = $user->businesses()->first();

        return redirect()
            ->route('panel.dashboard', $business)
            ->with('success', 'Hoş geldiniz! 🎉 İlk adım: Hizmetler ve Personel bölümlerini doldurun, ardından rezervasyon linkinizi paylaşın.');
    }

    /** Canli slug uygunluk kontrolu (kayit formu). */
    public function checkSlug(Request $request): JsonResponse
    {
        $slug = Str::lower((string) $request->query('slug', ''));

        $valid = preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1
            && ! in_array($slug, BusinessController::RESERVED_SLUGS, true)
            && ! Business::query()->where('slug', $slug)->exists();

        return response()->json(['available' => $valid]);
    }
}
