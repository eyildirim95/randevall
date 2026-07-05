<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::query()->latest()->paginate(20);

        return view('superadmin.announcements', compact('announcements'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $announcement = new Announcement($data);
        $announcement->created_by = $request->user()->id;
        $announcement->save();

        return back()->with('success', 'Duyuru yayınlandı.');
    }

    public function update(Announcement $announcement, Request $request): RedirectResponse
    {
        if ($request->has('toggle')) {
            $announcement->update(['is_active' => ! $announcement->is_active]);

            return back();
        }

        $announcement->update($this->validated($request));

        return back()->with('success', 'Duyuru güncellendi.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('success', 'Duyuru silindi.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:3000'],
            'level' => ['required', Rule::in(['info', 'warning', 'success', 'danger'])],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ]);
    }
}
