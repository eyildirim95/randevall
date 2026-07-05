<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\Support\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Super admin tarafi destek talepleri (tum isletmeler). */
class TicketController extends Controller
{
    public function __construct(private readonly TicketService $tickets) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $tickets = Ticket::query()
            ->with(['business:id,name,slug', 'user:id,name'])
            ->withCount('messages')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 ELSE 2 END")
            ->orderByDesc('last_reply_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'open' => Ticket::query()->where('status', TicketStatus::Open)->count(),
            'answered' => Ticket::query()->where('status', TicketStatus::Answered)->count(),
            'closed' => Ticket::query()->where('status', TicketStatus::Closed)->count(),
        ];

        return view('superadmin.tickets.index', compact('tickets', 'counts', 'status'));
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load(['messages.user', 'business', 'user']);

        return view('superadmin.tickets.show', compact('ticket'));
    }

    public function reply(Ticket $ticket, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $this->tickets->reply($ticket, $request->user(), $data['body'], asAdmin: true);

        return back()->with('success', 'Yanıt gönderildi, işletmeye bildirildi.');
    }

    public function updateStatus(Ticket $ticket, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['closed', 'open'])],
        ]);

        $data['status'] === 'closed'
            ? $this->tickets->close($ticket)
            : $this->tickets->reopen($ticket);

        return back()->with('success', 'Talep durumu güncellendi.');
    }
}
