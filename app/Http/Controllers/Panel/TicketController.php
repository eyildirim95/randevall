<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Ticket;
use App\Services\Support\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Isletme tarafi destek talepleri. */
class TicketController extends Controller
{
    public function __construct(private readonly TicketService $tickets) {}

    public function index(Business $business): View
    {
        $tickets = Ticket::query()
            ->with('user:id,name')
            ->withCount('messages')
            ->orderByDesc('last_reply_at')
            ->paginate(15);

        return view('panel.tickets.index', compact('business', 'tickets'));
    }

    public function create(Business $business): View
    {
        return view('panel.tickets.create', compact('business'));
    }

    public function store(Business $business, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:190'],
            'category' => ['required', Rule::in(array_keys(Ticket::categories()))],
            'priority' => ['required', Rule::in(array_keys(Ticket::priorities()))],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $ticket = $this->tickets->open($business, $request->user(), $data);

        return redirect()
            ->route('panel.tickets.show', [$business, $ticket])
            ->with('success', 'Destek talebiniz oluşturuldu. En kısa sürede yanıtlayacağız.');
    }

    public function show(Business $business, Ticket $ticket): View
    {
        $ticket->load(['messages.user', 'user']);

        return view('panel.tickets.show', compact('business', 'ticket'));
    }

    public function reply(Business $business, Ticket $ticket, Request $request): RedirectResponse
    {
        if ($ticket->isClosed()) {
            return back()->withErrors(['body' => 'Kapalı talebe yanıt yazılamaz. Yeni talep oluşturabilirsiniz.']);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $this->tickets->reply($ticket, $request->user(), $data['body'], asAdmin: false);

        return back()->with('success', 'Yanıtınız gönderildi.');
    }

    public function close(Business $business, Ticket $ticket): RedirectResponse
    {
        $this->tickets->close($ticket);

        return back()->with('success', 'Talep kapatıldı.');
    }
}
