<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CalendarClosure;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClosureController extends Controller
{
    public function index(Business $business): View
    {
        $closures = CalendarClosure::query()
            ->with('staff')
            ->orderByDesc('starts_at')
            ->paginate(20);

        return view('panel.closures', [
            'business' => $business,
            'closures' => $closures,
            'staffList' => Staff::query()->active()->ordered()->get(),
        ]);
    }

    public function store(Business $business, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id' => ['nullable', 'integer', Rule::exists('staff', 'id')->where('business_id', $business->id)],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'reason' => ['nullable', 'string', 'max:190'],
        ]);

        $closure = new CalendarClosure($data);
        $closure->business_id = $business->id;
        $closure->created_by = $request->user()->id;
        $closure->save();

        return back()->with('success', 'Takvim kapatma eklendi.');
    }

    public function destroy(Business $business, CalendarClosure $closure): RedirectResponse
    {
        $closure->delete();

        return back()->with('success', 'Takvim kapatma kaldırıldı.');
    }
}
