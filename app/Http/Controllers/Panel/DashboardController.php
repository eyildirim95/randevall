<?php

namespace App\Http\Controllers\Panel;

use App\Enums\AppointmentStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Note;
use App\Models\Transaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Business $business, \Illuminate\Http\Request $request): View
    {
        $today = today();
        $monthStart = $today->copy()->startOfMonth();

        // Personel rolu kendi randevularini gorur; finansal kartlar yoneticiye ozeldir
        $user = $request->user();
        $ownStaffId = $user->isStaffOnly($business) ? ($user->staffIdIn($business) ?? -1) : null;
        $canViewFinance = ! $user->isStaffOnly($business);

        $todaysAppointments = Appointment::query()
            ->with(['customer', 'service', 'staff'])
            ->whereDate('starts_at', $today)
            ->when($ownStaffId, fn ($q) => $q->where('staff_id', $ownStaffId))
            ->orderBy('starts_at')
            ->get();

        $stats = [
            'today_count' => $todaysAppointments->count(),
            'pending_count' => Appointment::query()->where('status', AppointmentStatus::Pending)->count(),
            'month_appointments' => Appointment::query()->where('starts_at', '>=', $monthStart)->count(),
            'month_income' => (float) Transaction::query()->income()->where('transaction_date', '>=', $monthStart)->sum('amount'),
            'month_expense' => (float) Transaction::query()->expense()->where('transaction_date', '>=', $monthStart)->sum('amount'),
            'customer_count' => Customer::query()->count(),
            'month_new_customers' => Customer::query()->where('created_at', '>=', $monthStart)->count(),
            'month_cancelled' => Appointment::query()
                ->where('status', AppointmentStatus::Cancelled)
                ->where('starts_at', '>=', $monthStart)
                ->count(),
        ];

        // Son 7 gunun randevu dagilimi (grafik)
        $weekSeries = collect(range(6, 0))->map(function (int $daysAgo) {
            $date = today()->subDays($daysAgo);

            return [
                'label' => $date->translatedFormat('d M'),
                'count' => Appointment::query()->whereDate('starts_at', $date)->count(),
            ];
        });

        $upcoming = Appointment::query()
            ->with(['customer', 'service', 'staff'])
            ->blocking()
            ->upcoming()
            ->when($ownStaffId, fn ($q) => $q->where('staff_id', $ownStaffId))
            ->limit(8)
            ->get();

        $pinnedNotes = Note::query()
            ->where('is_pinned', true)
            ->latest()
            ->limit(5)
            ->get();

        $birthdayCustomers = Customer::query()
            ->whereNotNull('birthday')
            ->whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->get();

        return view('panel.dashboard', compact(
            'business', 'stats', 'todaysAppointments', 'weekSeries', 'upcoming', 'pinnedNotes', 'birthdayCustomers', 'canViewFinance'
        ));
    }
}
