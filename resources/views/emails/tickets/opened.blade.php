<x-mail::message>
# Yeni Destek Talebi 🎫

| | |
|---|---|
| **Talep No** | #{{ $ticket->id }} |
| **İşletme** | {{ $ticket->business->name }} (/{{ $ticket->business->slug }}) |
| **Konu** | {{ $ticket->subject }} |
| **Kategori** | {{ \App\Models\Ticket::categories()[$ticket->category] ?? $ticket->category }} |
| **Öncelik** | {{ \App\Models\Ticket::priorities()[$ticket->priority] ?? $ticket->priority }} |
| **Açan** | {{ $ticket->user?->name }} |

**Mesaj:**
> {{ $ticket->messages->first()?->body }}

<x-mail::button :url="route('admin.tickets.show', $ticket)">
Talebi Görüntüle
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
