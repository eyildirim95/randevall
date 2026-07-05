<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function index(Business $business): View
    {
        $notes = Note::query()
            ->with('user:id,name')
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(30);

        return view('panel.notes', compact('business', 'notes'));
    }

    public function store(Business $business, Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $note = new Note($data);
        $note->business_id = $business->id;
        $note->user_id = $request->user()->id;
        $note->save();

        return back()->with('success', 'Not eklendi.');
    }

    public function update(Business $business, Note $note, Request $request): RedirectResponse
    {
        // Hizli islem: yalnizca tamamlandi/sabitle degisimi de gelebilir
        if ($request->has('toggle')) {
            $field = $request->input('toggle') === 'pin' ? 'is_pinned' : 'is_completed';
            $note->update([$field => ! $note->{$field}]);

            return back();
        }

        $note->update($this->validated($request));

        return back()->with('success', 'Not güncellendi.');
    }

    public function destroy(Business $business, Note $note): RedirectResponse
    {
        $note->delete();

        return back()->with('success', 'Not silindi.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:5000'],
            'color' => ['nullable', Rule::in(['primary', 'secondary', 'success', 'danger', 'warning', 'info'])],
            'is_pinned' => ['nullable', 'boolean'],
            'is_task' => ['nullable', 'boolean'],
            'due_date' => ['nullable', 'date'],
        ]);
    }
}
