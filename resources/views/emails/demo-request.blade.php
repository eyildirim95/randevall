<x-mail::message>
# Yeni Demo Talebi 🎯

| | |
|---|---|
| **Ad Soyad** | {{ $demoRequest->name }} |
| **İşletme** | {{ $demoRequest->business_name ?? '—' }} |
| **Sektör** | {{ $demoRequest->sector ?? '—' }} |
| **Telefon** | {{ $demoRequest->phone }} |
| **E-posta** | {{ $demoRequest->email ?? '—' }} |
| **Tarih** | {{ $demoRequest->created_at->format('d.m.Y H:i') }} |

@if($demoRequest->message)
**Mesaj:**
> {{ $demoRequest->message }}
@endif

<x-mail::button :url="route('admin.demo-requests.index')">
Panelde Görüntüle
</x-mail::button>

BooKıbrıs
</x-mail::message>
