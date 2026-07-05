@extends('layouts.auth', ['title' => $business->name])

@section('content')
    <div class="col-xl-5 col-lg-7">
        <div class="card auth-card">
            <div class="card-body p-4 text-center">
                <i class="ri-calendar-close-line text-muted" style="font-size: 56px"></i>
                <h3 class="mt-2">{{ $business->name }}</h3>
                <p class="text-muted mb-1">Bu işletme şu anda online randevu kabul etmiyor.</p>
                @if($business->phone)
                    <p class="mb-0">Randevu için arayabilirsiniz: <a href="tel:{{ $business->phone }}" class="fw-semibold">{{ $business->phone }}</a></p>
                @endif
            </div>
        </div>
    </div>
@endsection
