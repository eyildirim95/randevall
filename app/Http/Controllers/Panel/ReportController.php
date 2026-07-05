<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\Reporting\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(Business $business, Request $request): View
    {
        [$from, $to] = $this->range($request);

        return view('panel.reports', [
            'business' => $business,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'summary' => $this->reports->summary($from, $to),
            'staffPerformance' => $this->reports->staffPerformance($from, $to),
            'serviceBreakdown' => $this->reports->serviceBreakdown($from, $to),
            'dailySeries' => $this->reports->dailySeries($from, $to),
            'topCustomers' => $this->reports->topCustomers($from, $to),
            'sourceBreakdown' => $this->reports->sourceBreakdown($from, $to),
        ]);
    }

    /** Gelir-gider CSV disa aktarimi. */
    public function export(Business $business, Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        return $this->reports->exportTransactionsCsv($business, $from, $to);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function range(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from'])->startOfDay() : today()->startOfMonth();
        $to = isset($validated['to']) ? Carbon::parse($validated['to'])->endOfDay() : today()->endOfDay();

        return [$from, $to];
    }
}
