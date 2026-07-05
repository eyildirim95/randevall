<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\DemoRequest;
use App\Models\MessageLog;
use App\Models\Payment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $monthStart = today()->startOfMonth();

        $stats = [
            'business_count' => Business::query()->count(),
            'active_businesses' => Business::query()->where('is_active', true)->whereNull('suspended_at')->count(),
            'trial_businesses' => Business::query()->where('trial_ends_at', '>', now())->count(),
            'month_revenue' => (float) Payment::query()->where('status', 'paid')->where('paid_at', '>=', $monthStart)->sum('amount'),
            'total_appointments' => Appointment::withoutTenantScope()->count(),
            'month_appointments' => Appointment::withoutTenantScope()->where('starts_at', '>=', $monthStart)->count(),
            'new_demo_requests' => DemoRequest::query()->where('status', 'new')->count(),
            'month_messages' => MessageLog::query()->where('created_at', '>=', $monthStart)->count(),
        ];

        // Son 30 gunde olusturulan isletmeler
        $recentBusinesses = Business::query()
            ->with('plan')
            ->latest()
            ->limit(8)
            ->get();

        $recentDemoRequests = DemoRequest::query()
            ->where('status', 'new')
            ->latest()
            ->limit(8)
            ->get();

        $pendingPayments = Payment::query()
            ->with('business')
            ->where('status', 'pending')
            ->latest()
            ->limit(8)
            ->get();

        // Aylik buyume grafigi (son 6 ay)
        $growthSeries = collect(range(5, 0))->map(function (int $monthsAgo) {
            $month = today()->subMonths($monthsAgo);

            return [
                'label' => $month->translatedFormat('M Y'),
                'businesses' => Business::query()
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
                'revenue' => (float) Payment::query()
                    ->where('status', 'paid')
                    ->whereYear('paid_at', $month->year)
                    ->whereMonth('paid_at', $month->month)
                    ->sum('amount'),
            ];
        });

        return view('superadmin.dashboard', compact(
            'stats', 'recentBusinesses', 'recentDemoRequests', 'pendingPayments', 'growthSeries'
        ));
    }
}
