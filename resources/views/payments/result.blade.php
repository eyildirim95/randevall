@extends('layouts.auth', ['title' => 'Ödeme Sonucu'])

@section('content')
    <div class="col-xl-5 col-lg-7">
        <div class="card auth-card">
            <div class="card-body p-4 text-center">
                @if($payment->isPaid() || $status === 'ok')
                    <i class="ri-checkbox-circle-fill text-success" style="font-size: 64px"></i>
                    <h3 class="mt-2">Ödeme Alındı!</h3>
                    <p class="text-muted">Aboneliğiniz aktifleştirildi. Panelinizi kullanmaya devam edebilirsiniz.</p>
                @else
                    <i class="ri-close-circle-fill text-danger" style="font-size: 64px"></i>
                    <h3 class="mt-2">Ödeme Tamamlanamadı</h3>
                    <p class="text-muted">İşlem başarısız oldu veya iptal edildi. Tekrar deneyebilirsiniz.</p>
                    <a href="{{ route('payment.show', $payment->token) }}" class="btn btn-primary">Tekrar Dene</a>
                @endif

                @if($payment->business)
                    <div class="d-grid mt-3">
                        <a href="{{ route('panel.subscription.index', $payment->business) }}" class="btn btn-soft-primary">Abonelik Sayfasına Dön</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
