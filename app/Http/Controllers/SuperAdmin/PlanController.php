<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::query()
            ->withCount('businesses')
            ->orderBy('sort_order')
            ->get();

        return view('superadmin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('superadmin.plans.form', ['plan' => new SubscriptionPlan()]);
    }

    public function store(Request $request): RedirectResponse
    {
        SubscriptionPlan::create($this->validated($request));

        return redirect()->route('admin.plans.index')->with('success', 'Plan oluşturuldu.');
    }

    public function edit(SubscriptionPlan $plan): View
    {
        return view('superadmin.plans.form', compact('plan'));
    }

    public function update(SubscriptionPlan $plan, Request $request): RedirectResponse
    {
        $plan->update($this->validated($request, $plan));

        return redirect()->route('admin.plans.index')->with('success', 'Plan güncellendi.');
    }

    public function destroy(SubscriptionPlan $plan): RedirectResponse
    {
        if ($plan->businesses()->exists()) {
            return back()->withErrors(['plan' => 'Bu plana bağlı işletmeler var; silmek yerine pasif yapın.']);
        }

        $plan->delete();

        return back()->with('success', 'Plan silindi.');
    }

    private function validated(Request $request, ?SubscriptionPlan $plan = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'string', 'max:80', Rule::unique('subscription_plans', 'slug')->ignore($plan?->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(['TRY', 'EUR', 'USD', 'GBP'])],
            'max_staff' => ['required', 'integer', 'between:0,1000'],
            'max_customers' => ['required', 'integer', 'min:0'],
            'max_appointments_per_month' => ['required', 'integer', 'min:0'],
            'whatsapp_quota_monthly' => ['required', 'integer', 'min:0'],
            'features_text' => ['nullable', 'string', 'max:2000'],
            'trial_days' => ['required', 'integer', 'between:0,90'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['features'] = array_values(array_filter(array_map('trim', explode("\n", $data['features_text'] ?? ''))));
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        unset($data['features_text']);

        return $data;
    }
}
