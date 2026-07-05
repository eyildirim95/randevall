<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Customer;
use App\Services\Loyalty\LoyaltyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Business $business, Request $request): View
    {
        $q = (string) $request->query('q', '');

        $customers = Customer::query()
            ->search($q)
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('panel.customers.index', compact('business', 'customers', 'q'));
    }

    public function create(Business $business): View
    {
        return view('panel.customers.form', ['business' => $business, 'customer' => new Customer()]);
    }

    public function store(Business $business, Request $request): RedirectResponse
    {
        $data = $this->validated($business, $request);

        // Plan bazli musteri limiti (0 = sinirsiz)
        $limit = (int) ($business->plan?->max_customers ?? 0);

        if ($limit > 0 && Customer::query()->count() >= $limit) {
            return back()->withInput()->withErrors([
                'name' => "Müşteri limitiniz ({$limit}) doldu. Daha yüksek bir plana geçerek limitinizi artırabilirsiniz.",
            ]);
        }

        $customer = new Customer($data);
        $customer->business_id = $business->id;
        $customer->kvkk_consent_at = now();
        $customer->save();

        return redirect()
            ->route('panel.customers.show', [$business, $customer])
            ->with('success', 'Müşteri eklendi.');
    }

    public function show(Business $business, Customer $customer): View
    {
        $customer->load([
            'appointments' => fn ($q) => $q->with(['service', 'staff'])->orderByDesc('starts_at')->limit(30),
            'loyaltyTransactions' => fn ($q) => $q->latest()->limit(20),
        ]);

        return view('panel.customers.show', compact('business', 'customer'));
    }

    public function edit(Business $business, Customer $customer, Request $request): View
    {
        $this->guardManager($business, $request);

        return view('panel.customers.form', compact('business', 'customer'));
    }

    public function update(Business $business, Customer $customer, Request $request): RedirectResponse
    {
        $this->guardManager($business, $request);

        $customer->update($this->validated($business, $request, $customer));

        return redirect()
            ->route('panel.customers.show', [$business, $customer])
            ->with('success', 'Müşteri güncellendi.');
    }

    public function destroy(Business $business, Customer $customer, Request $request): RedirectResponse
    {
        $this->guardManager($business, $request);

        $customer->delete();

        return redirect()
            ->route('panel.customers.index', $business)
            ->with('success', 'Müşteri silindi.');
    }

    /** Sadakat puani duzeltme / odul kullanimi. */
    public function adjustPoints(Business $business, Customer $customer, Request $request, LoyaltyService $loyalty): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['adjust', 'redeem'])],
            'points' => ['required_if:action,adjust', 'nullable', 'integer', 'between:-10000,10000'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['action'] === 'redeem') {
            try {
                $loyalty->redeem($customer, $request->user()->id);
            } catch (\InvalidArgumentException $e) {
                return back()->withErrors(['points' => $e->getMessage()]);
            }

            return back()->with('success', 'Sadakat ödülü kullanıldı.');
        }

        $loyalty->adjust($customer, (int) $data['points'], $data['description'] ?? null, $request->user()->id);

        return back()->with('success', 'Puan güncellendi.');
    }

    /** Musteri duzenleme/silme yalnizca yonetici yetkisidir. */
    private function guardManager(Business $business, Request $request): void
    {
        $user = $request->user();

        abort_unless($user->canManage($business) || $user->isSuperAdmin(), 403);
    }

    private function validated(Business $business, Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => [
                'required', 'string', 'max:30',
                Rule::unique('customers', 'phone')
                    ->where('business_id', $business->id)
                    ->whereNull('deleted_at')
                    ->ignore($customer?->id),
            ],
            'email' => ['nullable', 'email', 'max:190'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_blacklisted' => ['nullable', 'boolean'],
        ]);
    }
}
