<!DOCTYPE html>
{{-- Musteriye acik sayfalar (giris, rezervasyon, odeme) her zaman acik temadir;
     panel kullanicisinin koyu mod tercihi bu sayfalara tasinmaz. --}}
<html lang="tr" data-bs-theme="light">

<head>
    @include('layouts.partials.title-meta', ['title' => $title ?? 'Giriş'])
    @yield('css')
    @vite(['resources/scss/icons.scss', 'resources/scss/app.scss'])
</head>

<body class="authentication-bg">

<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            @yield('content')
        </div>
    </div>
</div>

@vite(['resources/js/app.js'])
@yield('script-bottom')

</body>

</html>
