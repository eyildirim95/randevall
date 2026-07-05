<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Business $business): View
    {
        $services = Service::query()->with('staff')->ordered()->get();

        return view('panel.services.index', compact('business', 'services'));
    }

    public function create(Business $business): View
    {
        return view('panel.services.form', [
            'business' => $business,
            'service' => new Service(),
            'staffList' => Staff::query()->active()->ordered()->get(),
        ]);
    }

    public function store(Business $business, Request $request): RedirectResponse
    {
        $data = $this->validated($business, $request);

        $service = new Service($data);
        $service->business_id = $business->id;
        $service->save();

        $service->staff()->sync($data['staff_ids'] ?? []);

        return redirect()->route('panel.services.index', $business)->with('success', 'Hizmet eklendi.');
    }

    public function edit(Business $business, Service $service): View
    {
        return view('panel.services.form', [
            'business' => $business,
            'service' => $service,
            'staffList' => Staff::query()->active()->ordered()->get(),
        ]);
    }

    public function update(Business $business, Service $service, Request $request): RedirectResponse
    {
        $data = $this->validated($business, $request);

        $service->update($data);
        $service->staff()->sync($data['staff_ids'] ?? []);

        return redirect()->route('panel.services.index', $business)->with('success', 'Hizmet güncellendi.');
    }

    public function destroy(Business $business, Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('panel.services.index', $business)->with('success', 'Hizmet silindi.');
    }

    private function validated(Business $business, Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:1000'],
            'duration_minutes' => ['required', 'integer', 'between:5,600'],
            'buffer_minutes' => ['nullable', 'integer', 'between:0,120'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'is_active' => ['nullable', 'boolean'],
            'online_bookable' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'staff_ids' => ['nullable', 'array'],
            'staff_ids.*' => ['integer', Rule::exists('staff', 'id')->where('business_id', $business->id)],
        ]);
    }
}
