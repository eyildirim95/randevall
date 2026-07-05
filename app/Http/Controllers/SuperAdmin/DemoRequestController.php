<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DemoRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $demoRequests = DemoRequest::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('superadmin.demo-requests', [
            'demoRequests' => $demoRequests,
            'statuses' => DemoRequest::statuses(),
            'status' => $status,
        ]);
    }

    public function update(DemoRequest $demoRequest, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(DemoRequest::statuses()))],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $demoRequest->forceFill($data)->save();

        return back()->with('success', 'Demo talebi güncellendi.');
    }
}
