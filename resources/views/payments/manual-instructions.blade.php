{{-- Havale/EFT talimatlari (ManualPaymentProvider tarafindan render edilir) --}}
<div class="alert alert-info">
    <h5 class="alert-heading"><i class="ri-bank-line me-1"></i>Havale / EFT ile Ödeme</h5>
    <p class="mb-2">Aşağıdaki hesaba ödemenizi yaptıktan sonra aboneliğiniz yönetici onayıyla aktifleştirilir.</p>
    <table class="table table-sm table-borderless mb-2">
        @if($bankName)<tr><th style="width:140px">Banka</th><td>{{ $bankName }}</td></tr>@endif
        @if($accountHolder)<tr><th>Hesap Sahibi</th><td>{{ $accountHolder }}</td></tr>@endif
        @if($iban)<tr><th>IBAN</th><td><code class="user-select-all">{{ $iban }}</code></td></tr>@endif
        <tr><th>Açıklama</th><td><code class="user-select-all">RSR-{{ $payment->id }} / {{ $payment->business->slug }}</code></td></tr>
        <tr><th>Tutar</th><td class="fw-bold">{{ number_format($payment->amount, 2, ',', '.') }} {{ $payment->currency }}</td></tr>
    </table>
    <small class="text-muted">Açıklama alanına yukarıdaki kodu yazmayı unutmayın; ödemenizin eşleştirilmesini hızlandırır.</small>
</div>
