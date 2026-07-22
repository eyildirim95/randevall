<meta charset="utf-8"/>
<title>{{ $title ?? 'Panel' }} | {{ config('app.name') }} — Akıllı Randevu Sistemi</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="description" content="{{ config('app.name') }} — İşletmeniz için online randevu ve müşteri yönetim sistemi"/>
<meta name="author" content="{{ config('app.name') }}"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
@include('layouts.partials.favicons')
