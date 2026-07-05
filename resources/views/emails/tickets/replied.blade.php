<x-mail::message>
# Destek Talebinize Yanıt Var 💬

**Talep #{{ $ticket->id }} — {{ $ticket->subject }}**

{{ $message->is_admin ? 'Destek ekibi' : $ticket->business->name }} yazdı:

> {{ $message->body }}

<x-mail::button :url="$message->is_admin ? route('panel.tickets.show', [$ticket->business, $ticket]) : route('admin.tickets.show', $ticket)">
Yanıtla
</x-mail::button>

Reserup
</x-mail::message>
