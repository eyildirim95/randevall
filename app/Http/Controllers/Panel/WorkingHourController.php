<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Staff;
use App\Models\WorkingHour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkingHourController extends Controller
{
    public function edit(Business $business, Request $request): View
    {
        $staffId = $request->integer('staff_id') ?: null;

        if ($staffId) {
            $request->validate([
                'staff_id' => [Rule::exists('staff', 'id')->where('business_id', $business->id)],
            ]);
        }

        $hours = WorkingHour::query()
            ->where('staff_id', $staffId)
            ->get()
            ->keyBy('day_of_week');

        return view('panel.working-hours', [
            'business' => $business,
            'hours' => $hours,
            'staffId' => $staffId,
            'staffList' => Staff::query()->active()->ordered()->get(),
            'dayNames' => WorkingHour::dayNames(),
        ]);
    }

    public function update(Business $business, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id' => ['nullable', 'integer', Rule::exists('staff', 'id')->where('business_id', $business->id)],
            'days' => ['required', 'array'],
            'days.*.is_closed' => ['nullable', 'boolean'],
            'days.*.start_time' => ['nullable', 'date_format:H:i'],
            'days.*.end_time' => ['nullable', 'date_format:H:i', 'after:days.*.start_time'],
            'days.*.break_start' => ['nullable', 'date_format:H:i'],
            'days.*.break_end' => ['nullable', 'date_format:H:i', 'after:days.*.break_start'],
        ]);

        DB::transaction(function () use ($business, $data) {
            foreach ($data['days'] as $dow => $day) {
                if (! in_array((int) $dow, range(0, 6), true)) {
                    continue;
                }

                $isClosed = (bool) ($day['is_closed'] ?? false);

                WorkingHour::query()->updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'staff_id' => $data['staff_id'] ?? null,
                        'day_of_week' => (int) $dow,
                    ],
                    [
                        'is_closed' => $isClosed,
                        'start_time' => $isClosed ? null : ($day['start_time'] ?? null),
                        'end_time' => $isClosed ? null : ($day['end_time'] ?? null),
                        'break_start' => $isClosed ? null : ($day['break_start'] ?? null),
                        'break_end' => $isClosed ? null : ($day['break_end'] ?? null),
                    ],
                );
            }
        });

        return back()->with('success', 'Çalışma saatleri kaydedildi.');
    }
}
