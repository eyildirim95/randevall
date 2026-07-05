<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\BusinessRole;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessController extends Controller
{
    /** Rezervasyon linkleriyle cakisamayacak slug'lar. */
    public const RESERVED_SLUGS = [
        'admin', 'panel', 'giris', 'cikis', 'kayit', 'api', 'odeme', 'randevu',
        'demo-talebi', 'sifremi-unuttum', 'sifre-sifirla', 'storage', 'build',
        'images', 'up', 'login', 'logout', 'register', 'assets', 'vendor',
    ];

    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');

        $businesses = Business::query()
            ->with('plan')
            ->withCount(['customers', 'appointments'])
            ->when($q, fn ($query) => $query->where(
                fn ($qq) => $qq->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%")
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('superadmin.businesses.index', compact('businesses', 'q'));
    }

    public function create(): View
    {
        return view('superadmin.businesses.form', [
            'business' => new Business(),
            'plans' => SubscriptionPlan::query()->active()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $business = DB::transaction(function () use ($data) {
            $business = new Business($data['business']);
            $business->subscription_plan_id = $data['plan_id'] ?? null;

            $plan = $data['plan_id'] ? SubscriptionPlan::find($data['plan_id']) : null;
            $business->trial_ends_at = now()->addDays($plan?->trial_days ?? 14);
            $business->save();

            // Varsayilan calisma saatleri: Pzt-Cmt 09:00-19:00, Pazar kapali
            foreach (range(0, 6) as $dow) {
                WorkingHour::query()->create([
                    'business_id' => $business->id,
                    'staff_id' => null,
                    'day_of_week' => $dow,
                    'is_closed' => $dow === 0,
                    'start_time' => $dow === 0 ? null : '09:00',
                    'end_time' => $dow === 0 ? null : '19:00',
                ]);
            }

            // Isletme sahibi hesabi
            $owner = User::query()->where('email', $data['owner']['email'])->first();

            if (! $owner) {
                $owner = User::create([
                    'name' => $data['owner']['name'],
                    'email' => $data['owner']['email'],
                    'phone' => $data['owner']['phone'] ?? null,
                    'password' => Hash::make($data['owner']['password']),
                ]);
            }

            $business->users()->attach($owner->id, ['role' => BusinessRole::Owner->value]);

            return $business;
        });

        return redirect()
            ->route('admin.businesses.show', $business)
            ->with('success', 'İşletme oluşturuldu. Giriş: '.$data['owner']['email']);
    }

    public function show(Business $business): View
    {
        $business->load(['plan', 'users', 'subscriptions.plan'])
            ->loadCount(['customers', 'appointments', 'staff', 'services']);

        return view('superadmin.businesses.show', compact('business'));
    }

    public function edit(Business $business): View
    {
        return view('superadmin.businesses.form', [
            'business' => $business,
            'plans' => SubscriptionPlan::query()->active()->get(),
        ]);
    }

    public function update(Business $business, Request $request): RedirectResponse
    {
        $data = $this->validated($request, $business);

        $business->update($data['business']);

        $business->forceFill([
            'subscription_plan_id' => $data['plan_id'] ?? null,
            'plan_expires_at' => $data['plan_expires_at'] ?? $business->plan_expires_at,
        ])->save();

        return redirect()->route('admin.businesses.show', $business)->with('success', 'İşletme güncellendi.');
    }

    public function suspend(Business $business, Request $request): RedirectResponse
    {
        $business->forceFill([
            'suspended_at' => now(),
            'suspension_reason' => $request->input('reason', 'Yönetici tarafından askıya alındı'),
        ])->save();

        return back()->with('success', 'İşletme askıya alındı.');
    }

    public function activate(Business $business): RedirectResponse
    {
        $business->forceFill([
            'is_active' => true,
            'suspended_at' => null,
            'suspension_reason' => null,
        ])->save();

        return back()->with('success', 'İşletme aktifleştirildi.');
    }

    public function destroy(Business $business): RedirectResponse
    {
        $business->delete(); // soft delete

        return redirect()->route('admin.businesses.index')->with('success', 'İşletme silindi (arşivlendi).');
    }

    private function validated(Request $request, ?Business $business = null): array
    {
        $rules = [
            'business.name' => ['required', 'string', 'max:150'],
            'business.slug' => [
                'required', 'string', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn(self::RESERVED_SLUGS),
                Rule::unique('businesses', 'slug')->ignore($business?->id),
            ],
            'business.sector' => ['nullable', 'string', 'max:80'],
            'business.phone' => ['nullable', 'string', 'max:30'],
            'business.email' => ['nullable', 'email', 'max:190'],
            'business.address' => ['nullable', 'string', 'max:255'],
            'business.city' => ['nullable', 'string', 'max:80'],
            'plan_id' => ['nullable', 'integer', Rule::exists('subscription_plans', 'id')],
            'plan_expires_at' => ['nullable', 'date'],
        ];

        // Yeni isletmede sahip hesabi zorunlu
        if (! $business) {
            $rules += [
                'owner.name' => ['required', 'string', 'max:120'],
                'owner.email' => ['required', 'email', 'max:190'],
                'owner.phone' => ['nullable', 'string', 'max:30'],
                'owner.password' => ['required', 'string', 'min:8'],
            ];
        }

        $validated = $request->validate($rules);
        $validated['business']['slug'] = Str::lower($validated['business']['slug']);

        return $validated;
    }
}
