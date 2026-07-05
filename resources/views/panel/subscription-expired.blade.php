@extends('layouts.auth', ['title' => 'Abonelik Süresi Doldu'])

@section('content')
    <div class="col-xl-5 col-lg-7">
        <div class="card auth-card">
            <div class="card-body p-4 text-center">
                <i class="ri-lock-2-line text-warning" style="font-size: 56px"></i>
                <h3 class="mt-2">Abonelik Süresi Doldu</h3>
                <p class="text-muted">
                    <strong>{{ $business->name }}</strong> işletmesinin aboneliği sona erdi.
                    Panele yeniden erişmek için lütfen işletme yöneticinizle iletişime geçin.
                </p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-soft-secondary">Çıkış Yap</button>
                </form>
            </div>
        </div>
    </div>
@endsection
