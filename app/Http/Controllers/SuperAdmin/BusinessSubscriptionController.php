<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusinessSubscriptionController extends Controller
{
    public function store(Business $business, Request $request, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate([
            'plan_id' => ['required', 'integer', Rule::exists('subscription_plans', 'id')->where('is_active', true)],
            'period' => ['required', Rule::in(['monthly', 'yearly'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $plan = SubscriptionPlan::query()->findOrFail($data['plan_id']);

        $startsAt = isset($data['starts_at']) ? \Carbon\Carbon::parse($data['starts_at']) : now();
        if (isset($data['ends_at']) && \Carbon\Carbon::parse($data['ends_at'])->lte($startsAt)) {
            return back()->withErrors(['ends_at' => 'Bitiş tarihi başlangıçtan sonra olmalıdır.'])->withInput();
        }

        $payments->grantManualSubscription($business, $plan, $data['period'], [
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'amount' => $data['amount'] ?? null,
            'note' => $data['note'] ?? null,
            'granted_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Manuel abonelik eklendi ve işletme planı güncellendi.');
    }
}
