<?php

namespace App\Services\Reporting;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Isletme raporlari. Tum sorgular tenant global scope'u altinda calisir.
 */
class ReportService
{
    public function summary(Carbon $from, Carbon $to): array
    {
        $income = (float) Transaction::query()->income()
            ->whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $expense = (float) Transaction::query()->expense()
            ->whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $appointments = Appointment::query()->between($from, $to);

        $total = (clone $appointments)->count();
        $completed = (clone $appointments)->where('status', AppointmentStatus::Completed)->count();
        $cancelled = (clone $appointments)->where('status', AppointmentStatus::Cancelled)->count();
        $noShow = (clone $appointments)->where('status', AppointmentStatus::NoShow)->count();

        return [
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
            'appointments_total' => $total,
            'appointments_completed' => $completed,
            'appointments_cancelled' => $cancelled,
            'appointments_no_show' => $noShow,
            'completion_rate' => $total > 0 ? round($completed / $total * 100, 1) : 0,
            'new_customers' => Customer::query()->whereBetween('created_at', [$from, $to])->count(),
        ];
    }

    public function staffPerformance(Carbon $from, Carbon $to): Collection
    {
        return Appointment::query()
            ->selectRaw('staff_id, COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = ? THEN price ELSE 0 END) as revenue,
                AVG(rating) as avg_rating', [
                AppointmentStatus::Completed->value,
                AppointmentStatus::Completed->value,
            ])
            ->with('staff:id,name,color')
            ->between($from, $to)
            ->whereNotNull('staff_id')
            ->groupBy('staff_id')
            ->orderByDesc('revenue')
            ->get();
    }

    public function serviceBreakdown(Carbon $from, Carbon $to): Collection
    {
        return Appointment::query()
            ->selectRaw('service_id, COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN price ELSE 0 END) as revenue', [
                AppointmentStatus::Completed->value,
            ])
            ->with('service:id,name,color')
            ->between($from, $to)
            ->whereNotNull('service_id')
            ->groupBy('service_id')
            ->orderByDesc('total')
            ->get();
    }

    /** Gunluk randevu ve gelir serisi (grafik icin). */
    public function dailySeries(Carbon $from, Carbon $to): Collection
    {
        $appointmentsByDay = Appointment::query()
            ->selectRaw('DATE(starts_at) as day, COUNT(*) as total')
            ->between($from, $to)
            ->groupBy('day')
            ->pluck('total', 'day');

        $incomeByDay = Transaction::query()->income()
            ->selectRaw('transaction_date as day, SUM(amount) as total')
            ->whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = collect();
        $cursor = $from->copy();

        while ($cursor->lessThanOrEqualTo($to)) {
            $key = $cursor->toDateString();
            $series->push([
                'date' => $key,
                'label' => $cursor->translatedFormat('d M'),
                'appointments' => (int) ($appointmentsByDay[$key] ?? 0),
                'income' => (float) ($incomeByDay[$key] ?? 0),
            ]);
            $cursor->addDay();
        }

        return $series;
    }

    public function topCustomers(Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        return Appointment::query()
            ->selectRaw('customer_id, COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN price ELSE 0 END) as spent', [
                AppointmentStatus::Completed->value,
            ])
            ->with('customer:id,name,phone,loyalty_points')
            ->between($from, $to)
            ->groupBy('customer_id')
            ->orderByDesc('spent')
            ->limit($limit)
            ->get();
    }

    public function sourceBreakdown(Carbon $from, Carbon $to): array
    {
        $rows = Appointment::query()
            ->selectRaw('source, COUNT(*) as total')
            ->between($from, $to)
            ->groupBy('source')
            ->pluck('total', 'source');

        return [
            'panel' => (int) ($rows['panel'] ?? 0),
            'online' => (int) ($rows['online'] ?? 0),
        ];
    }

    public function exportTransactionsCsv(Business $business, Carbon $from, Carbon $to): StreamedResponse
    {
        $filename = sprintf('gelir-gider-%s-%s.csv', $from->format('Ymd'), $to->format('Ymd'));

        return response()->streamDownload(function () use ($from, $to) {
            $out = fopen('php://output', 'w');

            // Excel'in Turkce karakterleri tanimasi icin BOM
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tarih', 'Tür', 'Kategori', 'Tutar', 'Ödeme Yöntemi', 'Açıklama'], ';');

            Transaction::query()
                ->whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()])
                ->orderBy('transaction_date')
                ->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $t) {
                        fputcsv($out, [
                            $t->transaction_date->format('d.m.Y'),
                            $t->type->label(),
                            $t->category,
                            number_format((float) $t->amount, 2, ',', ''),
                            $t->payment_method,
                            $t->description,
                        ], ';');
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
