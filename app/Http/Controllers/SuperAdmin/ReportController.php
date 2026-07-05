<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\MessageLog;
use App\Models\Payment;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        // Isletme bazinda platform kullanimi (super admin gorunumu — tenant scope yok)
        $businessUsage = Business::query()
            ->with('plan:id,name')
            ->withCount([
                'appointments as month_appointments' => fn ($q) => $q->where('starts_at', '>=', today()->startOfMonth()),
                'customers',
                'staff',
            ])
            ->orderByDesc('month_appointments')
            ->paginate(25);

        // Aylik platform metrikleri (son 12 ay)
        $monthlySeries = collect(range(11, 0))->map(function (int $monthsAgo) {
            $month = today()->subMonths($monthsAgo);

            return [
                'label' => $month->translatedFormat('M y'),
                'appointments' => Appointment::withoutTenantScope()
                    ->whereYear('starts_at', $month->year)
                    ->whereMonth('starts_at', $month->month)
                    ->count(),
                'revenue' => (float) Payment::query()
                    ->where('status', 'paid')
                    ->whereYear('paid_at', $month->year)
                    ->whereMonth('paid_at', $month->month)
                    ->sum('amount'),
                'messages' => MessageLog::query()
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        });

        return view('superadmin.reports', compact('businessUsage', 'monthlySeries'));
    }
}
