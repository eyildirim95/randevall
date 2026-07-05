@extends('layouts.auth', ['title' => 'Ödeme'])

@section('content')
    <div class="col-xl-6 col-lg-8">
        <div class="card auth-card">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <h4>Abonelik Ödemesi</h4>
                    <p class="text-muted mb-0">{{ $payment->business->name }}</p>
                    <h2 class="fw-bold mt-2">{{ number_format($payment->amount, 2, ',', '.') }} {{ $payment->currency }}</h2>
                    @if($payment->subscription?->plan)
                        <span class="badge bg-soft-primary text-primary">{{ $payment->subscription->plan->name }} — {{ $payment->subscription->period === 'yearly' ? 'Yıllık' : 'Aylık' }}</span>
                    @endif
                </div>

                {!! $initiation['value'] !!}
            </div>
        </div>
    </div>
@endsection
