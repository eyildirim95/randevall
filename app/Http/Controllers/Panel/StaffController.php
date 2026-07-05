<?php

namespace App\Http\Controllers\Panel;

use App\Enums\BusinessRole;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Business $business): View
    {
        $staffList = Staff::query()->with(['services', 'user'])->ordered()->get();

        return view('panel.staff.index', compact('business', 'staffList'));
    }

    public function create(Business $business): View
    {
        return view('panel.staff.form', [
            'business' => $business,
            'staff' => new Staff(),
            'services' => Service::query()->active()->ordered()->get(),
        ]);
    }

    public function store(Business $business, Request $request): RedirectResponse
    {
        $data = $this->validated($business, $request);

        // Plan limiti kontrolu
        $plan = $business->plan;
        if ($plan && $plan->max_staff > 0 && Staff::query()->count() >= $plan->max_staff) {
            return back()
                ->withInput()
                ->withErrors(['name' => "Planınız en fazla {$plan->max_staff} personele izin veriyor. Yükseltme için abonelik sayfasına bakın."]);
        }

        DB::transaction(function () use ($business, $data, $request) {
            $staff = new Staff($data);
            $staff->business_id = $business->id;

            // Panele giris istenirse kullanici hesabi olustur/bagla
            if ($request->boolean('create_login') && ! empty($data['email'])) {
                $staff->user_id = $this->provisionUser($business, $data);
            }

            $staff->save();
            $staff->services()->sync($data['service_ids'] ?? []);
        });

        return redirect()->route('panel.staff.index', $business)->with('success', 'Personel eklendi.');
    }

    public function edit(Business $business, Staff $staff): View
    {
        return view('panel.staff.form', [
            'business' => $business,
            'staff' => $staff,
            'services' => Service::query()->active()->ordered()->get(),
        ]);
    }

    public function update(Business $business, Staff $staff, Request $request): RedirectResponse
    {
        $data = $this->validated($business, $request);

        DB::transaction(function () use ($business, $staff, $data, $request) {
            if ($request->boolean('create_login') && ! $staff->user_id && ! empty($data['email'])) {
                $staff->user_id = $this->provisionUser($business, $data);
            }

            $staff->update($data);
            $staff->services()->sync($data['service_ids'] ?? []);
        });

        return redirect()->route('panel.staff.index', $business)->with('success', 'Personel güncellendi.');
    }

    public function destroy(Business $business, Staff $staff): RedirectResponse
    {
        DB::transaction(function () use ($business, $staff) {
            if ($staff->user_id) {
                $business->users()->detach($staff->user_id);
            }

            $staff->delete();
        });

        return redirect()->route('panel.staff.index', $business)->with('success', 'Personel silindi.');
    }

    /** Personel icin panel kullanicisi olusturur ve isletmeye baglar. */
    private function provisionUser(Business $business, array $data): int
    {
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make(str()->random(24)),
            ]);

            // Personel sifresini "sifremi unuttum" ile belirler
            \Illuminate\Support\Facades\Password::sendResetLink(['email' => $user->email]);
        }

        $business->users()->syncWithoutDetaching([
            $user->id => ['role' => BusinessRole::Staff->value],
        ]);

        return $user->id;
    }

    private function validated(Business $business, Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:190'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accepts_online_booking' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', Rule::exists('services', 'id')->where('business_id', $business->id)],
        ]);
    }
}
